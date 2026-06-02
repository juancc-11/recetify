<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Mi Perfil';

$this->registerCssFile('@web/css/perfil.css?v=4');
$this->registerJsFile('@web/js/perfil.js', [
    'depends' => [\yii\web\JqueryAsset::class],
    'position' => \yii\web\View::POS_END,
]);
?>

<div class="pf-container">
    <div class="pf-outer-box">
        <div class="pf-box">

            <!-- TÍTULO + ELIMINAR -->
            <div class="pf-title-bar">
                <span class="pf-title">Account Settings</span>
                <button type="button" class="pf-btn-danger" id="pf-delete-btn">
                    Eliminar Cuenta
                </button>
            </div>

            <?php $form = ActiveForm::begin([
                'action' => ['site/profile'],
                'options' => [
                    'enctype' => 'multipart/form-data',
                    'id' => 'pf-form'
                ],
            ]); ?>

            <div class="pf-body">

                <!-- IZQUIERDA: Avatar -->
                <div class="pf-left">

                    <div class="pf-avatar-wrap" id="pf-avatar-wrap">
                        <img
                            id="pf-preview"
                            src="<?= Html::encode(Yii::$app->user->identity->avatar_url ?: Yii::getAlias('@web/img/logos/logo_kiwi.png')) ?>"
                            class="pf-avatar"
                            alt="Avatar"
                        >
                        <div class="pf-avatar-overlay">📷</div>
                    </div>

                    <!-- Input oculto imagen -->
                    <?= $form->field($model, 'avatar_file')
                        ->fileInput([
                            'id' => 'pf-avatar-input',
                            'style' => 'display:none',
                            'accept' => 'image/*'
                        ])->label(false) ?>

                    <!-- Info actual -->
                    <div class="pf-info-badge pf-info-username">
                        <?= Html::encode(Yii::$app->user->identity->username) ?>
                    </div>
                    <div class="pf-info-badge pf-info-password">
                        ••••••••••
                    </div>
                    <div class="pf-info-badge pf-info-email">
                        <?= Html::encode(Yii::$app->user->identity->email) ?>
                    </div>

                </div>

                <!-- DERECHA: Campos -->
                <div class="pf-right">

                    <!-- USERNAME -->
                    <div class="pf-field">
                        <label class="pf-label">New Username:</label>
                        <?= $form->field($model, 'username')
                            ->textInput(['class' => 'pf-input', 'placeholder' => ''])
                            ->label(false) ?>
                    </div>

                    <!-- EMAIL -->
                    <div class="pf-field">
                        <label class="pf-label">Dirección de Mail:</label>
                        <?= $form->field($model, 'email')
                            ->textInput(['class' => 'pf-input', 'placeholder' => ''])
                            ->label(false) ?>
                    </div>

                    <!-- NEW PASSWORD — ojo como span flotante, sin botón naranja -->
                    <div class="pf-field">
                        <label class="pf-label">New Password:</label>
                        <div class="pf-pass-view">
                            <?= $form->field($model, 'password')
                                ->passwordInput([
                                    'id' => 'pf-password',
                                    'class' => 'pf-input',
                                ])->label(false) ?>
                            <span class="pf-eye-toggle" data-target="pf-password"></span>
                        </div>
                    </div>

                    <!-- REPEAT PASSWORD -->
                    <div class="pf-field">
                        <label class="pf-label">Repeat Password:</label>
                        <div class="pf-pass-view">
                            <?= $form->field($model, 'repeat_password')
                                ->passwordInput([
                                    'id' => 'pf-repeat-password',
                                    'class' => 'pf-input',
                                ])->label(false) ?>
                            <span class="pf-eye-toggle" data-target="pf-repeat-password"></span>
                        </div>
                    </div>

                    <!-- BOTONES -->
                    <div class="pf-buttons">
                        <a href="<?= Yii::$app->homeUrl ?>" class="pf-btn-outline">Cancelar</a>
                        <?= Html::submitButton('Actualizar datos', ['class' => 'pf-btn-filled']) ?>
                    </div>

                </div>

            </div>

            <?php ActiveForm::end(); ?>

        </div>
    </div>
</div>

<!-- MODAL ELIMINAR CUENTA -->
<div id="pf-modal-delete" class="pf-modal-overlay pf-modal-hidden">
    <div class="pf-modal">
        <h3 class="pf-modal-title">⚠️ Eliminar Cuenta</h3>
        <p class="pf-modal-text">
            ¿Estás seguro que deseas eliminar tu cuenta?<br>
            <strong>Esta acción no se puede deshacer.</strong>
        </p>
        <div class="pf-modal-buttons">
            <button type="button" class="pf-btn-outline" id="pf-modal-cancel">Cancelar</button>
            <?php
                echo Html::beginForm(['site/delete-account'], 'post');
                echo Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken);
                echo Html::submitButton('Sí, eliminar', ['class' => 'pf-btn-danger']);
                echo Html::endForm();
            ?>
        </div>
    </div>
</div>

<!-- MODAL CAMBIAR FOTO -->
<div id="pf-modal-photo" class="pf-modal-overlay pf-modal-hidden">
    <div class="pf-modal">
        <h3 class="pf-modal-title">📷 Cambiar foto de perfil</h3>
        <p class="pf-modal-text">¿Deseas cambiar tu foto de perfil?</p>
        <div class="pf-modal-buttons">
            <button type="button" class="pf-btn-outline" id="pf-photo-no">No</button>
            <button type="button" class="pf-btn-filled" id="pf-photo-yes">Sí, cambiar</button>
        </div>
    </div>
</div>