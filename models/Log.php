<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "log".
 *
 * @property int $id
 * @property int $user_id
 * @property string|null $tanggal_waktu
 * @property string $tipe
 * @property string $nama
 * @property int $ukuran
 *
 * @property User $user
 */
class Log extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'log';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'tipe', 'nama', 'ukuran'], 'required'],
            [['user_id'], 'integer'],
            [['ukuran'], 'number', 'min' => 0, 'max' => 999999999999.9999], // Ubah ke tipe number (double)
            [['tanggal_waktu'], 'safe'],
            [['tipe'], 'string', 'max' => 50],
            [['nama'], 'string', 'max' => 255],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'tanggal_waktu' => 'Tanggal Waktu',
            'tipe' => 'Tipe',
            'nama' => 'Nama',
            'ukuran' => 'Ukuran',
        ];
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

}
