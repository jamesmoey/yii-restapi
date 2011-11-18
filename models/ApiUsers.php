<?php

/**
 * This is the model class for table "api_users".
 *
 * The followings are the available columns in table 'api_users':
 * @property integer $id
 * @property string $username
 * @property string $password
 * @property string $key
 * @property string $secret
 * @property string $token
 * @property string $token_expire
 * @property string $created
 * @property integer $active
 */
class ApiUsers extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @return ApiUsers the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{api_users}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('username, password, key, secret, created', 'required'),
			array('active', 'numerical', 'integerOnly'=>true),
			array('username, password, key, secret, token', 'length', 'max'=>255),
			array('token_expire', 'safe'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, username, password, key, secret, token, token_expire, created, active', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'Id',
			'username' => 'Username',
			'password' => 'Password',
			'key' => 'Key',
			'secret' => 'Secret',
			'token' => 'Token',
			'token_expire' => 'Token Expire',
			'created' => 'Created',
			'active' => 'Active',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id);

		$criteria->compare('username',$this->username,true);

		$criteria->compare('password',$this->password,true);

		$criteria->compare('key',$this->key,true);

		$criteria->compare('secret',$this->secret,true);

		$criteria->compare('token',$this->token,true);

		$criteria->compare('token_expire',$this->token_expire,true);

		$criteria->compare('created',$this->created,true);

		$criteria->compare('active',$this->active);

		return new CActiveDataProvider('ApiUsers', array(
			'criteria'=>$criteria,
		));
	}
}