How to use this module.
=======================
This module is only for Yii version 1.1.8 and above.

You need to load this module into the Yii modules configuration and use the RestUrlRule as one of the rules in Url
Manager.

In modules configuration, you must specify the modelMap configuration. If you need the module to output valid Origin for
cross platform capability, include validOrigin configuration as well.

e.g
<pre><code>
    'modules' => array(
      'restapi' => array(
        'modelMap'=>'application.restmodel',
        'validOrigin'=>array(
          '/preg*expression/',
        ),
      ),
    ),
</code></pre>

- modelMap need to be in application alias format. It should point to a file that contain the mapping configuration for
models.

- validOrigin need to an array, it should contain a list of domain (in Regular Expression) that is allow to access this
Rest API through AJAX. Check out Cross-Site HTTP request on (https://developer.mozilla.org/En/HTTP_Access_Control)

Sample of the model map configuration file.
<pre><code>
    array(
      'ModelRestName' => array(
        'class' => 'application.path.alias.ModelRestName',
        'excludeAll' => true,
        'exclude' => array( 'field_to_exclude', 'field_to_exclude', ),
        'include' => array( 'addition_field_to_include', ),
        'attributeAccessControl' => true,
        'defaultCriteria' => array for CDbCriteria,
      ),
    );
</code></pre>

Add URL routing rule class in URL Manager.

<pre><code>
    'urlManager' => array(
      'class'=>'CUrlManager',
      'urlFormat' => 'path',
      'rules' => array(
        array(
          'class' => 'restapi.components.RestUrlRule'
        ),
      ),
    ),
</code></pre>

Token Access to REST API
------------------------
If you are going to use Access Control in this module, you will need run migration tool from Yii.

e.g
<pre><code>
    php yiic migrate --migrationPath=application.modules.restapi.migrations
</code></pre>

This will create the needed table in the database to keep track of API access token, key and secret.

In controller outside of restapi module that need Rest Api Access Control just add
restapi.components.RestApiAccessControl as one of its filter.

e.g
<pre><code>
    public function filters() {
      return array(
        array(
          'restapi.components.RestApiAccessControl'
        ),
        'accessControl',
      );
    }
</code></pre>

API user will need to get temporary token from Api controller in the restapi module using their api key and secret key.