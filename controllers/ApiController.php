<?php

Yii::import("ext.tools.components.*");

class ApiController extends CController {

  public $result;

  /**
	 * This method is invoked right before an action is to be executed (after all possible filters.)
	 * You may override this method to do last-minute preparation for the action.
	 * @param CAction $action the action to be executed.
	 * @return boolean whether the action should be executed.
	 */
  protected function beforeAction($action) {
    $this->layout = false;
    Yii::app()->getErrorHandler()->errorAction = 'restapi/api/error';
    if (isset($_GET['model'])) {
      if (!$this->getModule()->checkModel($_GET['model'])) {
        throw new CHttpException(501, $_GET['model'].' is not implemented');
      } else {
        $_GET['model'] = $this->getModule()->includeModel($_GET['model']);
      }
    } else {
      throw new CHttpException(501, 'Model is not specify');
    }
    return parent::beforeAction($action);
  }

  public function actionError() {
    $error = Yii::app()->getErrorHandler()->getError();
    Yii::log($error['trace'], CLogger::LEVEL_ERROR, "restapi");
    $this->renderText($error['message']);
    Yii::app()->end(1);
  }

  /**
	 * This method is invoked right after an action is executed.
	 * You may override this method to do some postprocessing for the action.
	 * @param CAction $action the action just executed.
	 */
  protected function afterAction($action) {
    if (empty($this->result) && !is_array($this->result)) throw new CHttpException(404, "Not found with ID:".$_GET['id']);
    $list = array();
    if (is_array($this->result)) {
      foreach ($this->result as $instance) {
        $list[] = $this->getRecordAttribute($instance);
      }
    } else {
      $list[] = $this->getRecordAttribute($this->result);
    }
    $this->renderText(CJSON::encode(array("root"=>$list)));
  }

  protected function getRecordAttribute($record) {
    /** @var $record CActiveRecord */
    $attributes = $record->getAttributes();
    $excludeArray = array_flip($this->getModule()->getExcludedAttribute($_GET['model']));
    $attributes = array_intersect_key($attributes, array_diff_key($attributes, $excludeArray));
    foreach ($this->getModule()->getIncludedAttribute($_GET['model']) as $includedAttribute) {
      try {
        $attributes[$includedAttribute] = $record->$includedAttribute;
      } catch (CException $e) {
        Yii::log($e->getMessage(), CLogger::LEVEL_ERROR, "restapi");
      }
    }
    return $attributes;
  }

  public function actionList($model) {
    $modelInstance = new $model();
    $limit = isset($_GET['limit'])?$_GET['limit']:50;
    $start = isset($_GET['start'])?$_GET['start']:0;
    if (isset($_GET['page'])) {
      $start += ($_GET['page'] * $limit);
    }
    if ($modelInstance instanceof CActiveRecord) {
      $c = new CDbCriteria();
      $c->offset = $start;
      $c->limit = $limit;
      if (isset($_GET['filter'])) {
        $filter = CJSON::decode($_GET['filter']);
        foreach ($filter as $field=>$condition) {
          if (is_array($condition)) {
            if (is_int($field)) {
              $c->addCondition($condition['property'] . " = " . $condition["value"]);
            } else $c->addCondition("$field $condition[0] $condition[1]");
          } else $c->addCondition("$field = $condition");
        }
      }
      if (isset($_GET['sort'])) $c->order = $_GET['sort'];
      if (isset($_GET['group'])) $c->group = $_GET['group'];
      $this->result = CActiveRecord::model($model)->findAll($c);
    } else if ($modelInstance instanceof EMongoDocument) {
      $mc = new EMongoCriteria();
      $mc->setLimit($limit);
      $mc->setOffset($start);
      if (isset($_GET['filter'])) {
        $filter = CJSON::decode($_GET['filter']);
        foreach ($filter as $field=>$condition) {
          if (is_array($condition)) {
            if (is_int($field)) {
              $field = $condition['property'];
              $mc->$field = $condition["value"];
            } else $mc->$field($condition[0], $condition[1]);
          } else $mc->$field = $condition;
        }
      }
      if (isset($_GET['sort'])) $mc->setSort($_GET['sort']);
      $this->result = EMongoDocument::model($model)->findAll($mc);
    }
  }

  public function actionView($model, $id) {
    $modelInstance = new $model();
    if ($modelInstance instanceof CActiveRecord) {
      $this->result = CActiveRecord::model($model)->findByPk($id);
    } else if ($modelInstance instanceof EMongoDocument) {
      $this->result = EMongoDocument::model($model)->findByPk(new MongoId($id));
    }
  }

  public function actionUpdate($model, $id, $type = 'update') {
    $this->actionView($model, $id);
    if (is_null($this->result)) {
      throw new CHttpException(400, "Did not find any model with ID: " . $id);
    }
    $modelInstance = $this->result;
    $modelInstance->setScenario($type);
    $vars = CJSON::decode(file_get_contents('php://input'));
    if (!is_array($vars)) {
      Yii::log("Input need to be Json: ".var_export($vars, true), CLogger::LEVEL_ERROR, "restapi");
      throw new CHttpException(500, "Input need to be JSON");
    }
    $modelInstance->setAttributes($vars);
    if ($modelInstance->save()) {
      $modelInstance->refresh();
    } else {
      Yii::log(ArrayHelper::recursiveImplode("\n", $modelInstance->getErrors()), CLogger::LEVEL_ERROR, "restapi");
      throw new CHttpException(500, "Can not save model ID: " . $id);
    }
  }

  public function actionDelete($model, $id) {
    $this->actionView($model, $id);
    if (is_null($this->result)) {
      throw new CHttpException(400, "Did not find any model with ID: " . $id);
    }
    $modelInstance = $this->result;
    if (!$modelInstance->delete()) {
      Yii::log(ArrayHelper::recursiveImplode("\n", $modelInstance->getErrors()), CLogger::LEVEL_ERROR, "restapi");
      throw new CHttpException(500, "Can not delete model ID: " . $id);
    } else {
      Yii::app()->end();
    }
  }

  public function actionCreate($model) {
    $modelInstance = new $model();
    $vars = CJSON::decode(file_get_contents('php://input'));
    if (!is_array($vars)) {
      Yii::log("Input need to be Json: ".var_export($vars, true), CLogger::LEVEL_ERROR, "restapi");
      throw new CHttpException(500, "Input need to be JSON");
    }
    $modelInstance->setAttributes($vars);
    if ($modelInstance->save()) {
      $modelInstance->refresh();
      $this->result = $modelInstance;
    } else {
      Yii::log(ArrayHelper::recursiveImplode("\n", $modelInstance->getErrors()), CLogger::LEVEL_ERROR, "restapi");
      throw new CHttpException(500, "Can not save model: " . $model);
    }
  }
}
