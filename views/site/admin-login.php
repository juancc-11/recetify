<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Admin Login';

// ✅ usar mismo css que login
$this->registerCssFile('@web/css/login.css?v=2');
?>

<div class="lg-container">
    <div class="lg-box">

        <!-- HEADER -->
        <div class="lg-header">
            <img src="<?= Yii::getAlias('@web/img/logos/logo_kiwi.png') ?>" class="lg-logo">
            <h1 class="lg-title">Recetify Lab</h1>
        </div>

        <h2 class="lg-subtitle">Admin Login</h2>

        <?php $form = ActiveForm::begin([
            'id' => 'admin-login-form',
        ]); ?>

        <!-- USERNAME -->
        <div class="lg-form-group">
            <?= $form->field($model, 'username')
                ->textInput([
                    'class' => 'lg-input',
                    'placeholder' => 'Username',
                    'autofocus' => true
                ])->label(false) ?>
        </div>

        <!-- PASSWORD -->
        <div class="lg-form-group">
            <?= $form->field($model, 'password')
                ->passwordInput([
                    'class' => 'lg-input',
                    'placeholder' => 'Password'
                ])->label(false) ?>
        </div>

        <!-- ERROR -->
        <?php if ($model->hasErrors()): ?>
            <div class="lg-error">
                Acceso solo para administradores
            </div>
        <?php endif; ?>

        <div class="lg-register-link">
            <?= Html::a('¿No eres administrador?', ['site/login']) ?>
        </div>

        <!-- BOTONES -->
        <div class="lg-buttons">
            <?= Html::a('Cancelar', ['site/index'], ['class' => 'lg-btn-outline']) ?>
            <?= Html::submitButton('Login', ['class' => 'lg-btn-filled']) ?>
        </div>

        <?php ActiveForm::end(); ?>

    </div>
</div>