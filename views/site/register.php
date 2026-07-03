<?php
/** @var yii\web\View $this */
/** @var app\models\Usuario $model */
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Registro';

$this->registerCssFile('@web/css/register.css?v=3');
$this->registerJsFile('@web/js/register.js', [
    'depends' => [\yii\web\JqueryAsset::class],
    'position' => \yii\web\View::POS_END,
]);
?>

<div class="rg-container">
    <div class="rg-outer-box">
        <div class="rg-box">

            <?php $form = ActiveForm::begin([
                'options' => ['enctype' => 'multipart/form-data']
            ]); ?>

            <div class="rg-body">

                <!-- IZQUIERDA -->
                <div class="rg-left">

                    <img id="preview"
                        src="<?= Yii::getAlias('@web/img/logos/logo_kiwi.png') ?>"
                        class="rg-avatar"
                        alt="Avatar"
                    >

                    <?= $form->field($model, 'avatar_file')
                        ->fileInput(['id' => 'avatarInput', 'style' => 'display:none', 'accept' => 'image/*'])
                        ->label(false) ?>

                    <button type="button" class="rg-upload-btn"
                        onclick="document.getElementById('avatarInput').click()">
                        Ingresar imagen
                    </button>

                </div>

                <!-- DERECHA -->
                <div class="rg-right">

                    <div class="rg-field">
                        <label class="rg-label">Username:</label>
                        <?= $form->field($model, 'username')
                            ->textInput(['class' => 'rg-input'])->label(false) ?>
                    </div>

                    <div class="rg-field">
                        <label class="rg-label">Dirección de Mail:</label>
                        <?= $form->field($model, 'email')
                            ->textInput(['class' => 'rg-input'])->label(false) ?>
                    </div>

                    <div class="rg-field">
                        <label class="rg-label">Password:</label>
                        <?= $form->field($model, 'password')
                            ->passwordInput(['id' => 'password', 'class' => 'rg-input'])
                            ->label(false) ?>
                    </div>

                    <div id="strength"></div>

                    <div class="rg-field">
                        <label class="rg-label">Repeat Password:</label>
                        <?= $form->field($model, 'repeat_password')
                            ->passwordInput(['id' => 'repeat_password', 'class' => 'rg-input'])
                            ->label(false) ?>
                    </div>

                    <div class="rg-buttons">
                        <a href="<?= Yii::$app->homeUrl ?>" class="rg-btn-outline">Cancelar</a>
                        <?= Html::submitButton('Registrarse', ['class' => 'rg-btn-filled']) ?>
                    </div>

                </div>
            </div>

            <?php ActiveForm::end(); ?>

        </div>
    </div>
</div>