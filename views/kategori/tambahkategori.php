<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Tambah Kategori';
?>

<div class="box box-primary">
    <div class="box-body">
        <?php $form = ActiveForm::begin(); ?>

        <?= $form->field($model, 'nama_kategori')->textInput(['placeholder' => 'Masukkan Nama Kategori']) ?>

        <div class="form-group">
            <?= Html::submitButton('<i class="fa fa-save"></i> Simpan', ['class' => 'btn btn-success']) ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>
