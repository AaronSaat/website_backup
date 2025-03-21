<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "biro_pekerjaan".
 *
 * @property int $id
 * @property string $nama
 * @property string $created_at
 *
 * @property User[] $users
 */
class BiroPekerjaan extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'biro_pekerjaan';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['nama'], 'required'],
            [['created_at'], 'safe'],
            [['nama'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'nama' => 'Nama',
            'created_at' => 'Created At',
        ];
    }

    /**
     * Gets query for [[Users]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUsers()
    {
        return $this->hasMany(User::class, ['biro_pekerjaan_id' => 'id']);
    }

    public static function getBiroList()
    {
        return self::find()->select(['nama', 'id'])->indexBy('id')->column();
    }
}
