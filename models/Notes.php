<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "notes".
 *
 * @property int $id
 * @property int $user_id
 * @property string $notes
 * @property string $created_at
 *
 * @property User $user
 */
class Notes extends ActiveRecord
{
    public static function tableName()
    {
        return 'notes';
    }

    public function rules()
    {
        return [
            [['user_id', 'notes'], 'required'],
            [['user_id'], 'integer'],
            [['created_at'], 'safe'],
            [['notes'], 'string'],
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }
}

