<?php
/** @var yii\web\View $this */
/** @var app\models\Recipe $recipe */

use yii\helpers\Html;

$this->title = $recipe->titulo;
$this->params['meta_description'] = mb_strimwidth($recipe->descripcion ?? '', 0, 155, '…');
$this->params['meta_image']       = $recipe->imagen_portada_url ?? '';

$this->registerCssFile(Yii::$app->request->baseUrl . '/css/lectura.css');
$this->registerJsFile(Yii::$app->request->baseUrl . '/js/lectura.js', [
    'depends'  => [\yii\web\JqueryAsset::class],
    'position' => \yii\web\View::POS_END,
]);

$userId  = Yii::$app->user->isGuest ? 0 : Yii::$app->user->id;
$isGuest = Yii::$app->user->isGuest ? 'true' : 'false';

$this->registerJs("
    var RECIPE_ID  = {$recipe->id};
    var USER_ID    = {$userId};
    var IS_GUEST   = {$isGuest};
    var CSRF_TOKEN = '" . Yii::$app->request->getCsrfToken() . "';
    var BASE_URL   = '" . Yii::$app->request->baseUrl . "';
    var COLLECTION = " . json_encode($collection ?? []) . ";
", \yii\web\View::POS_HEAD);
?>

<!-- LOADING SPINNER -->
<div id="rl-loading" class="rl-loading-overlay" style="display:none">
    <div class="rl-spinner"></div>
</div>

<div class="lc-page" id="lc-page">

    <!-- ── CONTENIDO DE LA RECETA ── -->
<article class="lc-content">

    <div class="lc-body" id="lc-body">
        <?= $recipe->receta_texto ?>
    </div>

</article>

    <!-- ── OVERLAY DEL MENÚ ── -->
    <div class="lc-overlay" id="lc-overlay"></div>

    <!-- ── MENÚ SUPERIOR ── -->
    <div class="lc-menu lc-menu-top" id="lc-menu-top">
        <div class="lc-menu-top-inner">
            <span class="lc-menu-recipe-title">
                <?= Html::encode($recipe->titulo) ?>
            </span>
        </div>
    </div>

    <!-- ── MENÚ INFERIOR ── -->
    <div class="lc-menu lc-menu-bottom" id="lc-menu-bottom">

        <!-- Panel READ -->
        <div class="lc-panel active" id="lc-panel-read">
            <a href="<?= Yii::$app->urlManager->createUrl(['site/pre-lectura', 'id' => $recipe->id]) ?>"
               class="lc-row-btn lc-row-btn-full">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Volver a portada</span>
            </a>

            <div class="lc-row-split">
                <button class="lc-row-btn" id="lc-btn-favorito">
                    <i class="fa-solid fa-heart"></i>
                    <span id="lc-favorito-label">Favorito</span>
                </button>
                <button class="lc-row-btn" id="lc-btn-guardado">
                    <i class="fa-solid fa-bookmark"></i>
                    <span id="lc-guardado-label">Guardar</span>
                </button>
            </div>
        </div>

        <!-- Panel DISPLAY -->
        <div class="lc-panel" id="lc-panel-display">

            <span class="lc-panel-label">Fuente</span>
            <div class="lc-row-split lc-row-split-3">
                <button class="lc-opt-btn active" data-font="nunito">Nunito Sans</button>
                <button class="lc-opt-btn" data-font="roboto">Roboto</button>
                <button class="lc-opt-btn" data-font="lora">Lora</button>
            </div>

            <span class="lc-panel-label">Tamaño de fuente</span>
            <div class="lc-row-split lc-row-split-3">
                <button class="lc-opt-btn" id="lc-font-dec">A−</button>
                <span class="lc-opt-display" id="lc-font-size-display">16</span>
                <button class="lc-opt-btn" id="lc-font-inc">A+</button>
            </div>

            <span class="lc-panel-label">Interlineado</span>
            <div class="lc-row-split lc-row-split-3">
                <button class="lc-opt-btn" id="lc-line-dec">Altura −</button>
                <span class="lc-opt-display" id="lc-line-display">Normal</span>
                <button class="lc-opt-btn" id="lc-line-inc">Altura +</button>
            </div>

        </div>

        <!-- Panel SPEECH -->
        <div class="lc-panel" id="lc-panel-speech">

            <div class="lc-toggle-row">
                <span>Activar lectura en voz alta</span>
                <label class="lc-switch">
                    <input type="checkbox" id="lc-tts-enable">
                    <span class="lc-switch-slider"></span>
                </label>
            </div>

            <span class="lc-panel-label">Reproducción</span>
            <div class="lc-row-split lc-row-split-2">
                <button class="lc-row-btn" id="lc-tts-play">
                    <i class="fa-solid fa-play"></i> Reproducir
                </button>
                <button class="lc-row-btn" id="lc-tts-stop">
                    <i class="fa-solid fa-stop"></i> Detener
                </button>
            </div>

            <span class="lc-panel-label">Voz</span>
            <select class="lc-select" id="lc-tts-voice"></select>

            <span class="lc-panel-label">Velocidad</span>
            <select class="lc-select" id="lc-tts-speed">
                <option value="0.75">0.75x</option>
                <option value="1" selected>1x</option>
                <option value="1.25">1.25x</option>
                <option value="1.5">1.5x</option>
                <option value="2">2x</option>
            </select>

            <div class="lc-toggle-row">
                <span>Resaltar párrafo mientras se lee</span>
                <label class="lc-switch">
                    <input type="checkbox" id="lc-tts-highlight" checked>
                    <span class="lc-switch-slider"></span>
                </label>
            </div>

        </div>

        <!-- Panel SETTINGS -->
        <div class="lc-panel" id="lc-panel-settings">

            <span class="lc-panel-label">Tema de lectura</span>
            <div class="lc-theme-grid" id="lc-theme-grid">
                <button class="lc-theme-swatch active" data-theme="light"
                        style="background:#ffffff;color:#343A40;" title="Claro">Aa</button>
                <button class="lc-theme-swatch" data-theme="cream"
                        style="background:#f7f0dd;color:#5b4a2f;" title="Crema">Aa</button>
                <button class="lc-theme-swatch" data-theme="sepia"
                        style="background:#f4e3c1;color:#5b3f1f;" title="Sepia">Aa</button>
                <button class="lc-theme-swatch" data-theme="mint"
                        style="background:#dff3ef;color:#1f4a44;" title="Menta">Aa</button>
                <button class="lc-theme-swatch" data-theme="rose"
                        style="background:#fbe2e6;color:#6b2737;" title="Rosa">Aa</button>
                <button class="lc-theme-swatch" data-theme="sage"
                        style="background:#e3ecdc;color:#33442b;" title="Salvia">Aa</button>
                <button class="lc-theme-swatch" data-theme="dark"
                        style="background:#222529;color:#e4e6eb;" title="Oscuro">Aa</button>
            </div>

            <div class="lc-toggle-row">
                <span>Auto-bloqueo de pantalla</span>
                <label class="lc-switch">
                    <input type="checkbox" id="lc-autolock">
                    <span class="lc-switch-slider"></span>
                </label>
            </div>

        </div>

        <!-- Panel MORE -->
        <div class="lc-panel" id="lc-panel-more">
            <button class="lc-row-btn lc-row-btn-full lc-row-danger" id="lc-btn-report">
                <i class="fa-solid fa-flag"></i>
                <span>Reportar receta</span>
            </button>
        </div>

        <!-- TABS -->
        <nav class="lc-tabs">
            <button class="lc-tab active" data-panel="read">
                <i class="fa-solid fa-book-open"></i>
                <span>Leer</span>
            </button>
            <button class="lc-tab" data-panel="display">
                <i class="fa-solid fa-text-height"></i>
                <span>Texto</span>
            </button>
            <button class="lc-tab" data-panel="speech">
                <i class="fa-solid fa-volume-high"></i>
                <span>Voz</span>
            </button>
            <button class="lc-tab" data-panel="settings">
                <i class="fa-solid fa-gear"></i>
                <span>Ajustes</span>
            </button>
            <button class="lc-tab" data-panel="more">
                <i class="fa-solid fa-ellipsis"></i>
                <span>Más</span>
            </button>
        </nav>

    </div>

    <!-- ── BOTÓN FLOTANTE DE CIERRE (flecha) ── -->
    <button class="lc-close-btn" id="lc-close-btn" title="Cerrar menú">
        <i class="fa-solid fa-chevron-down"></i>
    </button>

</div>

<!-- MODAL REPORTE -->
<div class="modal-overlay" id="lc-report-modal" aria-hidden="true">
    <div class="modal-box modal-box--sm">
        <button class="modal-close" id="lc-report-close" aria-label="Cerrar">✕</button>
        <h3 class="modal-title">Reportar receta</h3>
        <div class="field-group">
            <label class="field-label">Motivo:</label>
            <select class="field-input" id="lc-report-motivo">
                <option value="contenido_inapropiado">Contenido inapropiado</option>
                <option value="spam">Spam</option>
                <option value="plagio">Plagio</option>
                <option value="informacion_falsa">Información falsa</option>
                <option value="otro">Otro</option>
            </select>
        </div>
        <div class="field-group">
            <label class="field-label">Descripción (opcional):</label>
            <textarea class="field-textarea" id="lc-report-desc" rows="3"
                      placeholder="Cuéntanos más sobre el problema..." maxlength="1000"></textarea>
        </div>
        <div class="form-actions form-actions--center">
            <button class="btn-cancelar" id="lc-report-cancel">Cancelar</button>
            <button class="btn-subir" id="lc-report-submit">Enviar reporte</button>
        </div>
    </div>
</div>