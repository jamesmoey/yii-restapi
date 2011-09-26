<?php

class RestUrlRule extends CBaseUrlRule {

  /**
   * Creates a URL based on this rule.
   * @param CUrlManager $manager the manager
   * @param string $route the route
   * @param array $params list of parameters (name=>value) associated with the route
   * @param string $ampersand the token separating name-value pairs in the URL.
   * @return mixed the constructed URL. False if this rule does not apply.
   */
  public function createUrl($manager, $route, $params, $ampersand) {
    return false;
  }

  /**
   * Parses a URL based on this rule.
   * @param CUrlManager $manager the URL manager
   * @param CHttpRequest $request the request object
   * @param string $pathInfo path info part of the URL (URL suffix is already removed based on {@link CUrlManager::urlSuffix})
   * @param string $rawPathInfo path info that contains the potential URL suffix
   * @return mixed the route that consists of the controller ID and action ID. False if this rule does not apply.
   */
  public function parseUrl($manager, $request, $pathInfo, $rawPathInfo) {
    $validRule = array(
      array('/^api\/([a-zA-Z][a-z0-9A-Z\._\-]+)\/(.*)$/', 'GET', 'restapi/api/view',
        'parameters'=>array("model"=>1, "id"=>2),
      ),
      array('/^api\/([a-zA-Z][a-z0-9A-Z\._\-]+)$/', 'GET', 'restapi/api/list',
        'parameters'=>array("model"=>1)
      ),
      array('/^api\/([a-zA-Z][a-z0-9A-Z\._\-]+)\/(.*)$/', 'PUT', 'restapi/api/update',
        'parameters'=>array("model"=>1, "id"=>2)
      ),
      array('/^api\/([a-zA-Z][a-z0-9A-Z\._\-]+)\/(.*)$/', 'DELETE', 'restapi/api/delete',
        'parameters'=>array("model"=>1, "id"=>2)
      ),
      array('/^api\/([a-zA-Z][a-z0-9A-Z\._\-]+)$/', 'POST', 'restapi/api/create',
        'parameters'=>array("model"=>1)
      ),
    );
    foreach ($validRule as $rule) {
      if ($request->getRequestType() == $rule[1]) {
        $matches = array();
        if (preg_match($rule[0], $pathInfo, $matches) >= 1) {
          if (isset($rule['parameters'])) {
            foreach ($rule['parameters'] as $param=>$index) {
              $_GET[$param] = $matches[$index];
            }
            return $rule[2];
          }
        }
      }
    }
    return false;  // this rule does not apply
  }
}
