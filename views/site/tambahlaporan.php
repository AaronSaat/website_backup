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
        'action' => ['site/tambahlaporan'], 
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
    <div class="form-group">
        <?= $form->field($model, 'tanggal_backup')->widget(DatePicker::class, [
            'options' => [
                'placeholder' => 'Pilih tanggal backup...',
                'value' => $model->isNewRecord ? date('Y-m-d') : $model->tanggal_backup,],
            'pluginOptions' => [
                'autoclose' => true,
                'format' => 'yyyy-mm-dd',
                'todayHighlight' => true
            ]
        ]) ?>
    </div>
    
    <!-- Upload File (Maksimal .. file, max 10MB) -->
    <div class="form-group">
        <?= $form->field($model, 'files[]')->widget(FileInput::class, [
            'options' => ['accept' => 'image/*, .csv', 'multiple' => true],
            'pluginOptions' => [
                // 'maxFileCount' => 5,
                'maxFileSize' => 10240, // 10MB
                'showUpload' => false,
                'allowedFileExtensions' => ['jpg', 'png', 'jpeg', 'csv'],
                'msgPlaceholder' => 'Allowed file types: JPG, PNG, JPEG, CSV',
                'previewFileType' => 'any',
                'overwriteInitial' => false,
                // 'showPreview' => false,
            ]
        ]) ?>
    </div>

    <div class="form-group text-center">
        <?= Html::submitButton('<i class="fa fa-save"></i> Simpan', [
            'class' => 'btn btn-success btn-lg px-4 py-2'
        ]) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>
