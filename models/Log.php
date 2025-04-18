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
 * @property float|null $ukuran
 * @property string|null $approved_at
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
            [['ukuran', 'approved_at'], 'default', 'value' => null],
            [['user_id', 'tipe', 'nama'], 'required'],
            [['user_id'], 'integer'],
            [['tanggal_waktu', 'approved_at'], 'safe'],
            [['ukuran'], 'number'],
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
            'approved_at' => 'Approved At',
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
