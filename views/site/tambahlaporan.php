<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\date\DatePicker;
use kartik\file\FileInput;
use yii\helpers\ArrayHelper;
use app\models\Kategori;

$this->title = 'Tambah Laporan Baru';
?>

<div class="site-tambah-laporan">
    <h1><?= Html::encode($this->title) ?></h1>

    <?php $form = ActiveForm::begin([
        'action' => ['site/tambahlaporan'], // Pastikan ini benar
        'options' => ['enctype' => 'multipart/form-data']
    ]); ?>

    <!-- Nama User -->
    <?= $form->field($model, 'user_id')->hiddenInput(['value' => Yii::$app->user->id])->label(false) ?>

    <div class="form-group">
        <label>Nama</label>
        <input type="text" class="form-control" value="<?= Yii::$app->user->identity->nama ?>" readonly>
    </div>

    <!-- Biro Pekerjaan -->
    <div class="form-group">
        <label>Biro Pekerjaan</label>
        <input type="text" class="form-control" value="<?= Yii::$app->user->identity->biroPekerjaan->nama ?>" readonly>
    </div>

    <!-- Tanggal Backup -->
    <?= $form->field($model, 'tanggal_backup')->widget(DatePicker::class, [
        'options' => ['placeholder' => 'Pilih tanggal backup...'],
        'pluginOptions' => [
            'autoclose' => true,
            'format' => 'yyyy-mm-dd',
            'todayHighlight' => true
        ]
    ]) ?>

    <!-- Kategori Backup -->
    <?= $form->field($model, 'kategori_id')->dropDownList(
        ArrayHelper::map(Kategori::find()->all(), 'id', 'nama_kategori'),
        ['prompt' => 'Pilih Kategori']
    ) ?>

    <!-- Upload File (Maksimal 5 file, max 10MB) -->
    <?= $form->field($model, 'files[]')->widget(FileInput::class, [
        'options' => ['accept' => 'image/*', 'multiple' => true],
        'pluginOptions' => [
            'maxFileCount' => 5,
            'maxFileSize' => 10240, // 10MB
            'showUpload' => false,
            'allowedFileExtensions' => ['jpg', 'png', 'jpeg'],
        ]
    ]) ?>

    <div class="form-group">
        <?= Html::submitButton('Simpan', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>
