<?php
/** @var yii\web\View $this */
/** @var app\models\Usuario $model */
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Login';

// ✅ Registrar CSS correctamente
$this->registerCssFile('@web/css/login.css?v=2');
?>

<div class="lg-container">
    <div class="lg-box">

        <!-- LOGO + TITULO -->
        <div class="lg-header">
            <!-- ✅ Ruta corregida igual que en index.php -->
            <img src="<?= Yii::getAlias('@web/img/logos/logo_kiwi.png') ?>" class="lg-logo">
            <h1 class="lg-title">Recetify Lab</h1>
        </div>

        <h2 class="lg-subtitle">Login</h2>

        <?php $form = ActiveForm::begin(); ?>

        <!-- USERNAME -->
        <div class="lg-form-group">
            <?= $form->field($model, 'username')
                ->textInput([
                    'class' => 'lg-input',
                    'placeholder' => 'Username'
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

        <!-- ✅ Errores de Yii2 por campo — más preciso que un mensaje genérico -->
        <?php if ($model->hasErrors()): ?>
            <div class="lg-error">
                Usuario o contraseña incorrectos.
            </div>
        <?php endif; ?>

        <!-- LINK REGISTRO -->
        <div class="lg-register-link">
            ¿No tienes cuenta? <?= Html::a('Registrarse', ['site/register']) ?>
        </div>

        <div class="lg-register-link">
            <?= Html::a('¿Eres administrador?', ['site/admin-login']) ?>
        </div>

        <!-- BOTONES -->
        <div class="lg-buttons">
            <!-- ✅ Cancelar redirige al home -->
            <a href="<?= Yii::$app->homeUrl ?>" class="lg-btn-outline">Cancelar</a>
            <?= Html::submitButton('Login', ['class' => 'lg-btn-filled']) ?>
        </div>

        <?php ActiveForm::end(); ?>

    </div>
</div>