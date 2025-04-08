<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

class User extends ActiveRecord implements IdentityInterface
{
    public $password;
    public $old_password;
    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 10;
    public static function tableName()
    {
        return 'user';
    }

    public function rules()
    {
        return [
            [['username', 'nama', 'biro_pekerjaan_id'], 'required'],
            [['username', 'nama'], 'string', 'max' => 255],
            [['old_password', 'password'], 'safe'], 
            [['old_password'], 'validateOldPassword'],
            [['password'], 'required', 'on' => 'create'],
            [['biro_pekerjaan_id'], 'integer'],
            [['username'], 'unique'],
            [['status'], 'default', 'value' => self::STATUS_ACTIVE],
            [['status'], 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_INACTIVE]],
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
        return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['access_token' => $token, 'status' => self::STATUS_ACTIVE]);
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

    public function validateOldPassword($attribute, $params)
    {
        if (!Yii::$app->security->validatePassword($this->$attribute, $this->getOldAttribute('password_hash'))) {
            $this->addError($attribute, 'Password lama tidak sesuai.');
        }
    }
}

