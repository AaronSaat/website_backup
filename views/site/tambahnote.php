<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Tambah Note';
?>

<div class="box box-primary">
    <div class="box-body">
        <?php $form = ActiveForm::begin(); ?>

        <?= $form->field($model, 'notes')->textInput(['placeholder' => 'Masukkan Notes']) ?>

        <div class="form-group text-center">
            <?= Html::submitButton('<i class="fa fa-save"></i> Simpan', [
                'class' => 'btn btn-success btn-lg px-4 py-2'
            ]) ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>
