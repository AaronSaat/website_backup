<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "file".
 *
 * @property int $id
 * @property int $user_id
 * @property int $kategori_id
 * @property string $direktori_file
 * @property string|null $created_at
 * @property string|null $approved_at
 * @property string|null $tipe
 *
 * @property User $user
 */
class File extends \yii\db\ActiveRecord
{

    /**
     * ENUM field values
     */
    const TIPE_JPG = 'jpg';
    const TIPE_PNG = 'png';
    const TIPE_JPEG = 'jpeg';
    const TIPE_CSV = 'csv';
    const TIPE_TXT = 'txt';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'file';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['created_at', 'approved_at', 'tipe'], 'default', 'value' => null],
            [['user_id', 'kategori_id', 'direktori_file'], 'required'],
            [['user_id', 'kategori_id'], 'integer'],
            [['created_at', 'approved_at'], 'safe'],
            [['tipe'], 'string'],
            [['direktori_file'], 'string', 'max' => 255],
            ['tipe', 'in', 'range' => array_keys(self::optsTipe())],
            [['kategori_id'], 'exist', 'skipOnError' => true, 'targetClass' => Kategori::class, 'targetAttribute' => ['kategori_id' => 'id']],
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
            'kategori_id' => 'Kategori',
            'direktori_file' => 'Direktori File',
            'created_at' => 'Created At',
            'approved_at' => 'Approved At',
            'tipe' => 'Tipe',
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
     * column tipe ENUM value labels
     * @return string[]
     */
    public static function optsTipe()
    {
        return [
            self::TIPE_JPG => 'jpg',
            self::TIPE_PNG => 'png',
            self::TIPE_JPEG => 'jpeg',
            self::TIPE_CSV => 'csv',
            self::TIPE_TXT => 'txt',
        ];
    }

    /**
     * @return string
     */
    public function displayTipe()
    {
        return self::optsTipe()[$this->tipe];
    }

    /**
     * @return bool
     */
    public function isTipeJpg()
    {
        return $this->tipe === self::TIPE_JPG;
    }

    public function setTipeToJpg()
    {
        $this->tipe = self::TIPE_JPG;
    }

    /**
     * @return bool
     */
    public function isTipePng()
    {
        return $this->tipe === self::TIPE_PNG;
    }

    public function setTipeToPng()
    {
        $this->tipe = self::TIPE_PNG;
    }

    /**
     * @return bool
     */
    public function isTipeJpeg()
    {
        return $this->tipe === self::TIPE_JPEG;
    }

    public function setTipeToJpeg()
    {
        $this->tipe = self::TIPE_JPEG;
    }

    /**
     * @return bool
     */
    public function isTipeCsv()
    {
        return $this->tipe === self::TIPE_CSV;
    }

    public function setTipeToCsv()
    {
        $this->tipe = self::TIPE_CSV;
    }

    /**
     * @return bool
     */
    public function isTipeTxt()
    {
        return $this->tipe === self::TIPE_TXT;
    }

    public function setTipeToTxt()
    {
        $this->tipe = self::TIPE_TXT;
    }
}
