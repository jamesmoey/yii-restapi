<?php
class RestApiModule extends CWebModule {

  /**
   * Configuration Map to setup REST model.
   *
   * <code>
   * array(
   *   'ModelRestName' => array(
   *     'class' => 'application.path.alias.ModelRestName',
   *     'excludeAll' => true,
   *     'exclude' => array( 'field_to_exclude', 'field_to_exclude', ),
   *     'include' => array( 'addition_field_to_include', ),
   *   ),
   * );
   * </code>
   */
  public $modelMap;

  public function checkModel($model) {
    if (!isset($this->modelMap[$model])) return false;
    return true;
  }

  public function includeModel($model) {
    $classname = Yii::import($this->modelMap[$model]['class']);
    $this->modelMap[$classname] = $this->modelMap[$model];
    return $classname;
  }

  public function getExcludedAttribute($model) {
    if (isset($this->modelMap[$model]['excludeAll']) && $this->modelMap[$model]['excludeAll'] === true) {
      $instance = new $model();
      $attributes = array_keys($instance->getAttributes());
      if (isset($this->modelMap[$model]['include'])) {
        return array_intersect($attributes, array_diff($attributes, $this->modelMap[$model]['include']));
      } else {
        return $attributes;
      }
    } else return isset($this->modelMap[$model]['exclude'])?$this->modelMap[$model]['exclude']:array();
  }

  public function getIncludedAttribute($model) {
    return isset($this->modelMap[$model]['include'])?$this->modelMap[$model]['include']:array();
  }

  public function init() {
    if (empty($this->modelMap)) {
      Yii::log("Model Map is not set. No Model will rest.", CLogger::LEVEL_ERROR, "restapi");
    }
    parent::init();
  }
}
