<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Tambah Biro Pekerjaan';
?>

<div class="box box-primary">
    <div class="box-body">
        <?php $form = ActiveForm::begin(); ?>

        <?= $form->field($model, 'nama')->textInput(['placeholder' => 'Masukkan Nama Biro']) ?>

        <div class="form-group">
            <?= Html::submitButton('<i class="fa fa-save"></i> Simpan', ['class' => 'btn btn-success']) ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>
