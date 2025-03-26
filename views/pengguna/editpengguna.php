<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models\BiroPekerjaan;

$this->title = 'Edit Pengguna';
?>

<div class="box box-primary">
    <div class="box-body">
        <?php $form = ActiveForm::begin(); ?>

        <?= $form->field($model, 'username')->textInput(['placeholder' => 'Masukkan Username']) ?>

        <?= $form->field($model, 'password')->textInput([
            'placeholder' => 'Kosongkan jika tidak ingin mengubah password',
        ]) ?>

        <?= $form->field($model, 'nama')->textInput(['placeholder' => 'Masukkan Nama']) ?>

        <?= $form->field($model, 'biro_pekerjaan_id')->dropDownList(
            BiroPekerjaan::getBiroList(),
            ['prompt' => 'Pilih Biro Pekerjaan']
        ) ?>

        <div class="form-group text-center">
            <?= Html::submitButton('<i class="fa fa-save"></i> Simpan', [
                'class' => 'btn btn-success btn-lg px-4 py-2'
            ]) ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>
