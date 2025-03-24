<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use kartik\date\DatePicker;
use kartik\file\FileInput;

$this->title = 'Update Laporan';
?>

<div class="laporan-form">
    <?php $form = ActiveForm::begin([
        'options' => ['enctype' => 'multipart/form-data']
    ]); ?>

    <!-- Nama User -->
    <div class="form-group">
        <label>Nama</label>
        <input type="text" class="form-control" value="<?= $model->user->nama ?>" readonly>
    </div>

    <!-- Biro Pekerjaan -->
    <div class="form-group">
        <label>Biro Pekerjaan</label>
        <input type="text" class="form-control" value="<?= $model->user->biroPekerjaan->nama ?>" readonly>
    </div>

    <!-- Tanggal Backup -->
    <div class="form-group">
        <label>Tanggal Backup</label>
        <?= $form->field($model, 'tanggal_backup')->widget(DatePicker::classname(), [
            'options' => ['placeholder' => 'Pilih tanggal backup...'],
            'pluginOptions' => [
                'autoclose' => true,
                'format' => 'yyyy-mm-dd',
                'todayHighlight' => true
            ]
        ])->label(false); ?>
    </div>

    <!-- Kategori Backup -->
    <div class="form-group">
        <label>Kategori Backup</label>
        <?= $form->field($model, 'kategori_id')->dropDownList(
            ArrayHelper::map(\app\models\Kategori::find()->all(), 'id', 'nama_kategori'),
            ['prompt' => 'Pilih Kategori']
        )->label(false); ?>
    </div>

    <!-- Upload File -->
    <?= $form->field($model, 'files[]')->widget(FileInput::class, [
        'options' => ['multiple' => true, 'accept' => 'image/*'],
        'pluginOptions' => [
            'maxFileCount' => 5 - count(array_filter(explode(',', $model->file))), // Hitung sisa slot file
            'maxFileSize' => 10240, // 10MB
            'showUpload' => false,
            'initialPreview' => array_map(function ($file) {
                return Yii::getAlias('@web/uploads/') . trim($file);
            }, array_filter(explode(',', $model->file))),
            'initialPreviewAsData' => true,
            'initialPreviewConfig' => array_map(function ($file) use ($model) {
                return [
                    'caption' => basename($file),
                    'key' => basename($file),
                    'url' => Yii::$app->urlManager->createUrl(['site/delete-file', 'id' => $model->id, 'file' => trim($file)]),
                ];
            }, array_filter(explode(',', $model->file))),
            'overwriteInitial' => false,
            'showRemove' => true,
            'showCancel' => true,
            'deleteUrl' => Yii::$app->urlManager->createUrl(['site/delete-file']), // URL penghapusan
        ]
    ]) ?>

    <div class="form-group">
        <?= Html::submitButton('Update', ['class' => 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>
