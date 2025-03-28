<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "laporan".
 *
 * @property int $id
 * @property int $user_id
 * @property string $tanggal_backup
 * @property string $created_at
 * @property string|null $updated_at
 * @property string $status
 *
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
            [['updated_at'], 'default', 'value' => null],
            [['status'], 'default', 'value' => 'Waiting for Approval'],
            [['user_id', 'tanggal_backup'], 'required', 'on' => 'create'],
            [['user_id'], 'integer'],
            [['tanggal_backup', 'created_at', 'updated_at'], 'safe'],
            [['status'], 'string', 'max' => 255],
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
            'tanggal_backup' => 'Tanggal Backup',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'status' => 'Status',
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

     public function getFiles()
    {
        return $this->hasMany(File::class, ['user_id' => 'user_id']);
    }

    public function getNote()
    {
        return $this->hasOne(Notes::class, ['user_id' => 'user_id'])->orderBy(['created_at' => SORT_DESC]);
    }

    public function beforeSave($insert)
    {
        if (empty($this->tanggal_backup)) {
            $this->tanggal_backup = null;
        }
        return parent::beforeSave($insert);
    }
}
