<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "laporan".
 *
 * @property int $id
 * @property int $user_id
 * @property string $tanggal_backup
 * @property int $kategori_id
 * @property string $file
 * @property string $created_at
 *
 * @property Kategori $kategori
 * @property User $user
 */
class Laporan extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'laporan';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'tanggal_backup', 'kategori_id', 'file'], 'required'],
            [['user_id', 'kategori_id'], 'integer'],
            [['tanggal_backup', 'created_at'], 'safe'],
            [['file'], 'string', 'max' => 255],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
            [['kategori_id'], 'exist', 'skipOnError' => true, 'targetClass' => Kategori::class, 'targetAttribute' => ['kategori_id' => 'id']],
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
            'tanggal_backup' => 'Tanggal Backup',
            'kategori_id' => 'Kategori ID',
            'file' => 'File',
            'created_at' => 'Created At',
        ];
    }

    /**
     * Gets query for [[Kategori]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getKategori()
    {
        return $this->hasOne(Kategori::class, ['id' => 'kategori_id']);
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
