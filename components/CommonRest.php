<?php
/**
 * User: james
 * Date: 10/10/11
 * Time: 9:22 AM
 */
class CommonRest extends CComponent {

  /**
   * Build Json Reply from the list of array
   *
   * @param array $arrayOfRecord of CActiveRecord
   * @param string $model Model Name
   *
   * @return array of attributes
   */
  public static function buildModelJsonReply($arrayOfRecord, $model, $checkPermission = true) {
    $list = array();
    if (is_array($arrayOfRecord)) {
      foreach ($arrayOfRecord as $instance) {
        $list[] = self::getRecordAttribute($instance, $model, $checkPermission);
      }
    } else {
      $list[] = self::getRecordAttribute($arrayOfRecord, $model, $checkPermission);
    }
    if ($checkPermission && Yii::app()->getModule("restapi")->getCheckAttributeAccessControl($model)) {
      $row = $list[0];
      /** @var CWebUser $user */
      $user = Yii::app()->user;
      foreach ($list as $rowId=>$row) {
        foreach ($row as $field=>$value) {
          if (!$user->checkAccess("view/".$_GET['model']."/".$field, array('model'=>$row), true)) {
            unset($list[$rowId][$field]);
          }
        }
      }
    }
    return $list;
  }

  /**
   * Get attribute of the record. Return in array
   *
   * @param CActiveRecord $record
   * @param string $model Model Name
   *
   * @return array of attributes
   */
  public static function getRecordAttribute($record, $model, $checkPermission = true) {
    /** @var $record CActiveRecord */
    $attributes = $record->getAttributes();
    if ($checkPermission) {
      $excludeArray = array_flip(Yii::app()->getModule("restapi")->getExcludedAttribute($model));
      $attributes = array_intersect_key($attributes, array_diff_key($attributes, $excludeArray));
    }
    foreach (Yii::app()->getModule("restapi")->getIncludedAttribute($model) as $includedAttribute) {
      try {
        $attributes[$includedAttribute] = $record->$includedAttribute;
      } catch (CException $e) {
        Yii::log($e->getMessage(), CLogger::LEVEL_INFO, "restapi");
      }
    }
    if (method_exists($record, 'attributeRestMapping')) {
      foreach ($record->attributeRestMapping() as $field=>$map) {
        if (isset($record->$field)) {
          $attributes[$map] = $record->$field;
          unset($attributes[$field]);
        }
      }
    }
    if (method_exists($record, "relations")) {
      foreach ($record->relations() as $name=>$relation) {
        try {
          if ($relation[0] == CActiveRecord::HAS_ONE || $relation[0] == CActiveRecord::BELONGS_TO) {
            if (!@class_exists($relation[1], true)) continue;
            /** @var $related CActiveRecord */
            $related = $record->$name;
            if ($related == null) continue;
            if ($related->hasAttribute("name")) $attributes[$name] = $related->name;
            else if ($related->hasAttribute("title")) $attributes[$name] = $related->title;
            else if (method_exists($related, "toString")) $attributes[$name] = $related->toString();
            else $attributes[$name] = $related->getPrimaryKey();
          }
        } catch (Exception $e) {
          Yii::log($e->getMessage(), CLogger::LEVEL_INFO, "restapi");
        }
      }
    }
    return $attributes;
  }
}