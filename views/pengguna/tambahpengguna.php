<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models\BiroPekerjaan;

$this->title = 'Tambah Pengguna Baru';
?>

<div class="box box-primary">
    <div class="box-body">
        <?php $form = ActiveForm::begin(); ?>

        <?= $form->field($model, 'username')->textInput(['placeholder' => 'Masukkan Username']) ?>

        <?= $form->field($model, 'password')->textInput(['placeholder' => 'Masukkan Password']) ?>

        <?= $form->field($model, 'nama')->textInput(['placeholder' => 'Masukkan Nama']) ?>

        <?= $form->field($model, 'biro_pekerjaan_id')->dropDownList(
            BiroPekerjaan::getBiroList(),
            ['prompt' => 'Pilih Biro Pekerjaan']
        ) ?>

        <div class="form-group">
            <?= Html::submitButton('<i class="fa fa-save"></i> Simpan', ['class' => 'btn btn-success']) ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>
