<?php
    use yii\helpers\Html;
    use yii\widgets\ActiveForm;
    use yii\helpers\ArrayHelper;
    use kartik\date\DatePicker;
    use kartik\file\FileInput;
    
    $this->title = 'Tambah Laporan Baru';
?>

<div class="laporan-form">
    <?php $form = ActiveForm::begin([
        // 'action' => Url::to(['/site/unggahlaporan']),
        'options' => ['enctype' => 'multipart/form-data']
    ]); ?>

    <!-- Nama User -->
    <div class="form-group">
        <label>Nama</label>
        <input type="text" class="form-control" value="Aaron Julyan" readonly>
    </div>

    <!-- Biro Pekerjaan -->
    <div class="form-group">
        <label>Biro Pekerjaan</label>
        <input type="text" class="form-control" value="IT" readonly>
    </div>

    <!-- Tanggal Backup -->
    <div class="form-group">
        <label>Tanggal Backup</label>
        <?= DatePicker::widget([
            'name' => 'tanggal_backup',
            'options' => ['placeholder' => 'Pilih tanggal backup...'],
            'pluginOptions' => [
                'autoclose' => true,
                'format' => 'yyyy-mm-dd',
                'todayHighlight' => true
            ]
        ]); ?>
    </div>

    <!-- Kategori Backup -->
    <div class="form-group">
        <label>Kategori Backup</label>
        <select name="kategori_backup" class="form-control">
            <option value="">Pilih Kategori</option>
            <option value="HDD / SSD">HDD / SSD</option>
            <option value="Google Drive">Google Drive</option>
            <option value="NAS">NAS</option>
        </select>
    </div>

    <!-- Upload File (Maksimal 5 file, max 10MB) -->
    <div class="form-group">
        <label>Upload File</label>
        <?= FileInput::widget([
            'name' => 'files[]',
            'options' => ['accept' => 'image/*', 'multiple' => true],
            'pluginOptions' => [
                'maxFileCount' => 5,
                'maxFileSize' => 10240, // 10MB
                'showUpload' => false,
                'allowedFileExtensions' => ['jpg', 'png', 'jpeg'],
            ]
        ]); ?>
    </div>

    <div class="form-group">
        <?= Html::submitButton('Upload', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>
