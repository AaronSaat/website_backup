<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models\BiroPekerjaan;
use yii\web\JsExpression;

$this->title = 'Tambah Pengguna Baru';

$this->registerJs(
    '
    $("#generate-password").on("click", function() {
        var passwordLength = 6;
        var characters = "abcdefghijklmnopqrstuvwxyz";
        var password = "";
        for (var i = 0; i < passwordLength; i++) {
            var randomIndex = Math.floor(Math.random() * characters.length);
            password += characters.charAt(randomIndex);
        }
        $("#password-input").val(password);
    });
    ',
    \yii\web\View::POS_READY
);

?>

<div class="box box-primary">
    <div class="box-body">
        <?php $form = ActiveForm::begin(); ?>

        <?= $form->field($model, 'username')->textInput(['placeholder' => 'Masukkan Username']) ?>

        <?= $form->field($model, 'password')->textInput([
            'placeholder' => 'Masukkan Password',
            'id' => 'password-input'
        ]) ?>

        <div class="form-group">
            <?= Html::button('Generate Password', ['class' => 'btn btn-default', 'id' => 'generate-password']) ?>
        </div>

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