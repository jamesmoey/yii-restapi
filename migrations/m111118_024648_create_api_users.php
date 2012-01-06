<?php

class m111118_024648_create_api_users extends CDbMigration
{
	public function up()
	{
    $this->createTable("{{api_users}}", array(
      'id'=>'pk',
      'username'=>'string NOT NULL',
      'password'=>'string NOT NULL',
      'key'=>'string NOT NULL',
      'secret'=>'string NOT NULL',
      'token'=>'string',
      'token_expire'=>'datetime',
      'created'=>'datetime NOT NULL',
      'active'=>'boolean DEFAULT "1"'
    ));
    $this->createIndex("api_users_token_unq", "{{api_users}}", "token", true);
    if (strpos($this->getDbConnection()->getDriverName(), 'mysql') !== false) {
      $this->getDbConnection()->createCommand("ALTER TABLE {{api_users}} ENGINE=InnoDB")->execute();
    }
    $this->insert("{{api_users}}", array(
      "username"=>"Api Tester",
      "password"=>"pHuFum8tewuPregesWaWuSw9",
      "key"=>"hewr65ufruwaXUw5EspezA8e",
      "secret"=>"pRaxUBrAxEfrAwubrEsE6ARa",
      "token"=>"pravesTEBRUjeYecHuc4busTmUKamePapAtaWrewAzu2uPacSWUGacafadRAtRebe2RuTRav",
      "token_expire"=>date('Y-m-d H:i:s', time()+(60*60*24*365)),
      "created"=>date('Y-m-d H:i:s'),
    ));
	}

	public function down()
	{
    $this->dropTable("{{api_users}}");
	}
}