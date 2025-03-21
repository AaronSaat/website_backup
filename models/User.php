<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

class User extends ActiveRecord implements IdentityInterface
{
    public $password;
    public static function tableName()
    {
        return 'user';
    }

    public function rules()
    {
        return [
            [['username', 'nama', 'biro_pekerjaan_id'], 'required'],
            [['username', 'nama'], 'string', 'max' => 255],
            [['password'], 'safe'], 
            [['password'], 'required', 'on' => 'create'],
            [['biro_pekerjaan_id'], 'integer'],
            [['username'], 'unique'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'username' => 'Username',
            'password' => 'Password',
            'password_hash' => 'Password Hash',
            'nama' => 'Nama',
            'biro_pekerjaan_id' => 'Biro Pekerjaan',
        ];
    }

    public function getBiro()
    {
        return $this->hasOne(BiroPekerjaan::class, ['id' => 'biro_pekerjaan']);
    }
    
    public function beforeSave($insert)
    {
        if (!empty($this->password)) {
            $this->password_hash = Yii::$app->security->generatePasswordHash($this->password);
        }
        return parent::beforeSave($insert);
    }
    public function getBiroPekerjaan()
    {
        return $this->hasOne(BiroPekerjaan::class, ['id' => 'biro_pekerjaan_id']);
    }

    public static function findIdentity($id)
    {
        return static::findOne($id);
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
        return null;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getAuthKey()
    {
        return null;
    }

    public function validateAuthKey($authKey)
    {
        return false;
    }

    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username]);
    }

    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }
}

