<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = 'Sign In';
?>

<div class="login-box">
    <div class="login-logo">
        <a href="#"><b>Backup Log</b></a>
    </div>
    <div class="login-box-body">
        <?php if (Yii::$app->session->hasFlash('error')): ?>
            <div class="alert alert-danger">
                <?= Yii::$app->session->getFlash('error') ?>
            </div>
        <?php endif; ?>

        <?php $form = ActiveForm::begin(['id' => 'login-form']); ?>
        
        <?= $form->field($model, 'username')->textInput(['placeholder' => 'Username']) ?>
        <?= $form->field($model, 'password')->passwordInput(['placeholder' => 'Password']) ?>


        <div class="form-group">
            <?= Html::submitButton('Masuk', ['class' => 'btn btn-danger btn-block btn-flat', 'name' => 'login-button']) ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>
