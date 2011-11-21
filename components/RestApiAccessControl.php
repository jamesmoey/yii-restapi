<?php
Yii::import("restapi.components.RestApiUserIdentity");

class RestApiAccessControl extends CFilter {

  protected function preFilter($filterChain) {
    if (isset($_GET['token'])) {
      $identity = new RestApiUserIdentity();
      $identity->setToken($_GET['token']);
      if ($identity->authenticate()) {
        Yii::app()->user->login($identity);
      }
    }
    if (isset($_SERVER['HTTP_ORIGIN'])) {
      $restapi = Yii::app()->getModule("restapi");
      if ($restapi->validOrigin($_SERVER['HTTP_ORIGIN'])) {
        header('Access-Control-Allow-Origin: '.$_SERVER['HTTP_ORIGIN']);
      }
    }
    if ($_SERVER['REQUEST_METHOD'] == "OPTIONS") {
      return false;
    }
    return true;
  }
}
