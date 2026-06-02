<?php
/** @var yii\web\View $this */
/** @var app\models\User $user */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Mis Recetas';

$this->registerCssFile(Yii::$app->request->baseUrl . '/css/mis_recetas.css');
$this->registerJsFile(Yii::$app->request->baseUrl . '/js/mis_recetas.js', ['depends' => [\yii\web\JqueryAsset::class]]);

$user = Yii::$app->user->identity;
?>

<!-- LOADING SPINNER GLOBAL -->
<div id="rl-loading" class="rl-loading-overlay" style="display:none">
    <div class="rl-spinner"></div>
</div>

<div class="mis-recetas-wrapper">

    <nav class="tab-nav">
        <button class="tab-btn active" data-tab="mis-recetas">Mis recetas</button>
        <button class="tab-btn" data-tab="estadisticas">Estadísticas</button>
        <button class="tab-btn" data-tab="nueva-receta">Nueva receta</button>
    </nav>

    <!-- TAB 1: MIS RECETAS -->
    <section class="tab-panel active" id="tab-mis-recetas">
        <div class="recetas-lista" id="recetas-lista">
            <?php if (empty($recipes)): ?>
                <div class="empty-state">
                    <p>Aún no has publicado ninguna receta.</p>
                </div>
            <?php else: ?>
                <?php foreach ($recipes as $recipe): ?>
                <div class="receta-card" data-id="<?= $recipe->id ?>">

                    <div class="receta-left">
                        <div class="receta-thumb">
                            <?php if ($recipe->imagen_portada_url): ?>
                                <img src="<?= Html::encode($recipe->imagen_portada_url) ?>"
                                     alt="Portada de <?= Html::encode($recipe->titulo) ?>">
                            <?php else: ?>
                                <div class="thumb-placeholder"></div>
                            <?php endif; ?>
                        </div>
                        <button class="btn-borrar-receta"
                                data-recipe-id="<?= $recipe->id ?>"
                                data-titulo="<?= Html::encode($recipe->titulo) ?>"
                                title="Eliminar receta">
                            ✕ Eliminar
                        </button>
                    </div>

                    <div class="receta-info">
                        <h3 class="receta-titulo"><?= Html::encode($recipe->titulo) ?></h3>
                        <p class="receta-desc"><?= Html::encode(mb_strimwidth($recipe->descripcion, 0, 120, '…')) ?></p>
                        <ul class="receta-meta">
                            <li><span>Calificación:</span> <?= number_format($recipe->avgScore ?? 0, 1) ?> ★</li>
                            <li><span>Visitas:</span> <?= number_format($recipe->totalViews ?? 0) ?></li>
                            <li><span>Lectores:</span> <?= number_format($recipe->totalComments ?? 0) ?></li>
                        </ul>
                    </div>

                    <div class="receta-actions">
                        <button class="btn-publicar btn-toggle-publish <?= $recipe->is_published ? 'despublicar' : '' ?>"
                                data-recipe-id="<?= $recipe->id ?>"
                                data-published="<?= (int)$recipe->is_published ?>">
                            <?= $recipe->is_published ? 'Despublicar' : 'Publicar' ?>
                        </button>
                        <button class="btn-editar" data-recipe-id="<?= $recipe->id ?>">
                            Editar
                        </button>
                    </div>

                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>

    <!-- TAB 2: ESTADÍSTICAS -->
    <section class="tab-panel" id="tab-estadisticas">
        <div class="stats-header">
            <h2>Estadísticas</h2>
            <div class="select-receta-wrap">
                <select id="stats-recipe-select" class="select-receta">
                    <option value="">— Seleccionar receta —</option>
                    <?php if (!empty($recipes)): ?>
                        <?php foreach ($recipes as $recipe): ?>
                            <option value="<?= $recipe->id ?>">
                                <?= Html::encode($recipe->titulo) ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
        </div>
        <div class="stats-content" id="stats-content">
            <div class="stats-placeholder">
                <p>Selecciona una receta para ver sus estadísticas.</p>
            </div>
        </div>
        <template id="stats-template">
            <div class="stats-grid">
                <div class="stat-card stat-wide">
                    <h4>Visitas en el tiempo</h4>
                    <canvas id="chart-visitas" height="200"></canvas>
                </div>
                <div class="stat-card stat-wide">
                    <h4>Datos varios</h4>
                    <canvas id="chart-datos" height="200"></canvas>
                </div>
                <div class="stat-card stat-wide">
                    <h4>Posición por rating</h4>
                    <div class="rating-display">
                        <span id="stat-rating-pos" class="rating-pos">—</span>
                        <span class="rating-label">de <strong id="stat-total-recipes">—</strong> recetas</span>
                    </div>
                    <div class="rating-bar-wrap">
                        <div class="rating-bar-fill" id="stat-rating-bar"></div>
                    </div>
                </div>
            </div>
        </template>
    </section>

    <!-- TAB 3: NUEVA RECETA -->
    <section class="tab-panel" id="tab-nueva-receta">
        <div class="form-card">
            <?php $form = \yii\widgets\ActiveForm::begin([
                'id'      => 'nueva-receta-form',
                'options' => ['enctype' => 'multipart/form-data'],
                'action'  => Url::to(['site/crear-receta']),
            ]); ?>

            <div class="field-group">
                <label class="field-label">Título:</label>
                <input type="text" name="Recipe[titulo]" class="field-input" maxlength="255" required>
            </div>

            <div class="field-group">
                <label class="field-label">Descripción:</label>
                <textarea name="Recipe[descripcion]" class="field-textarea" maxlength="900" rows="3"></textarea>
            </div>

            <div class="field-group">
                <label class="field-label">Portada:</label>
                <div class="upload-area">
                    <div class="upload-preview" id="portada-preview">
                        <div class="upload-placeholder">
                            <span>📷</span>
                            <small>Haz clic para elegir</small>
                        </div>
                    </div>
                    <div class="upload-meta">
                        <input type="file" id="portada-input" name="Recipe[portada]"
                               accept="image/png,image/jpeg,image/webp" style="display:none">
                        <label for="portada-input" class="btn-upload">Elegir imagen</label>
                        <span class="upload-hint">PNG, JPG o WEBP · Máx. 5 MB</span>
                    </div>
                </div>
            </div>

            <div class="field-group">
                <label class="field-label">Utensilios necesarios:</label>
                <input type="text" name="Recipe[utensilios]" class="field-input" maxlength="1200">
            </div>

            <div class="field-group">
                <label class="field-label">Imágenes adicionales (máximo 3):</label>
                <p class="field-hint">Sube tus imágenes y luego insértalas en el editor de abajo.</p>
                <div class="multi-upload" id="multi-upload">
                    <?php for ($i = 1; $i <= 3; $i++): ?>
                    <div class="multi-thumb" id="extra-slot-<?= $i ?>">
                        <input type="file" id="extra-img-<?= $i ?>" name="Recipe[extra_images][]"
                               accept="image/png,image/jpeg,image/webp"
                               style="display:none" data-slot="<?= $i ?>">
                        <label for="extra-img-<?= $i ?>" class="btn-slot-upload">
                            <i class="fa-solid fa-image"></i> Subir imagen <?= $i ?>
                        </label>
                        <div class="slot-preview-wrap" style="display:none">
                            <img class="slot-preview" alt="imagen <?= $i ?>">
                            <button type="button" class="slot-remove" data-slot="<?= $i ?>">✕</button>
                        </div>
                    </div>
                    <?php endfor; ?>
                </div>
            </div>

            <div class="field-group" id="nueva-receta-texto-group">
                <label class="field-label">Receta completa:</label>
                <div class="img-insert-toolbar" id="nueva-img-toolbar" style="display:none"></div>
                <div id="nueva-receta-editor"
                     class="receta-editor"
                     contenteditable="true"
                     data-placeholder="Escribe aquí tu receta. Usa los botones de arriba para insertar imágenes..."></div>
                <textarea id="nueva-receta-texto" name="Recipe[receta_texto]"
                          style="display:none" maxlength="120000"></textarea>
            </div>

            <div class="field-group">
                <label class="field-label">Etiquetas:</label>
                <div class="tags-input-wrap" id="tags-input-wrap">
                    <div class="tags-chips" id="tags-chips"></div>
                    <input type="text" id="tag-input" class="tag-text-input"
                           placeholder="Escribe y presiona Enter…" autocomplete="off"
                           list="tags-datalist">
                    <datalist id="tags-datalist">
                        <?php foreach ($availableTags ?? [] as $tag): ?>
                            <option value="<?= Html::encode($tag->name) ?>">
                        <?php endforeach; ?>
                    </datalist>
                </div>
                <input type="hidden" name="Recipe[tags]" id="tags-hidden">
            </div>

            <div class="form-actions">
                <button type="button" class="btn-cancelar" id="btn-cancelar-nueva">Cancelar</button>
                <button type="submit" class="btn-subir">Subir</button>
            </div>

            <?php \yii\widgets\ActiveForm::end(); ?>
        </div>
    </section>

</div>

<!-- MODAL EDITAR -->
<div class="modal-overlay" id="modal-editar" aria-hidden="true">
    <div class="modal-box">
        <button class="modal-close" id="modal-close-btn" aria-label="Cerrar">✕</button>
        <h3 class="modal-title">Editar receta</h3>
        <div class="modal-body" id="modal-editar-body">
            <form id="edit-recipe-form" enctype="multipart/form-data">
                <input type="hidden" id="edit-recipe-id" name="Recipe[id]">

                <div class="field-group">
                    <label class="field-label">Título:</label>
                    <input type="text" id="edit-titulo" name="Recipe[titulo]"
                           class="field-input" maxlength="255" required>
                </div>

                <div class="field-group">
                    <label class="field-label">Descripción:</label>
                    <textarea id="edit-descripcion" name="Recipe[descripcion]"
                              class="field-textarea" maxlength="900" rows="3"></textarea>
                </div>

                <div class="field-group">
                    <label class="field-label">Portada:</label>
                    <div class="upload-area">
                        <div class="upload-preview" id="edit-portada-preview">
                            <div class="upload-placeholder"><span>📷</span></div>
                        </div>
                        <div class="upload-meta">
                            <input type="file" id="edit-portada-input" name="Recipe[portada]"
                                   accept="image/png,image/jpeg,image/webp" class="file-hidden">
                            <label for="edit-portada-input" class="btn-upload">Cambiar imagen</label>
                            <span class="upload-hint">Deja vacío para mantener la actual</span>
                        </div>
                    </div>
                </div>

                <div class="field-group">
                    <label class="field-label">Utensilios necesarios:</label>
                    <input type="text" id="edit-utensilios" name="Recipe[utensilios]"
                           class="field-input" maxlength="1200">
                </div>

                <div class="field-group">
                    <label class="field-label">Imágenes adicionales (máximo 3):</label>
                    <p class="field-hint">Sube imágenes e insértalas en el editor.</p>
                    <div class="multi-upload" id="edit-multi-upload">
                        <?php for ($i = 1; $i <= 3; $i++): ?>
                        <div class="multi-thumb" id="edit-extra-slot-<?= $i ?>">
                            <input type="hidden" name="Recipe[delete_image][]"
                                   class="delete-img-flag" value="" disabled>
                            <input type="file" id="edit-extra-img-<?= $i ?>" name="Recipe[extra_images][]"
                                   accept="image/png,image/jpeg,image/webp"
                                   style="display:none" data-slot="<?= $i ?>">
                            <label for="edit-extra-img-<?= $i ?>" class="btn-slot-upload">
                                <i class="fa-solid fa-image"></i> Subir imagen <?= $i ?>
                            </label>
                            <div class="slot-preview-wrap" style="display:none">
                                <img class="slot-preview" alt="imagen <?= $i ?>">
                                <button type="button" class="slot-remove" data-slot="<?= $i ?>">✕</button>
                            </div>
                        </div>
                        <?php endfor; ?>
                        <div class="multi-upload-hints">
                            <span>PNG, JPG o WEBP · Máx. 5 MB c/u</span>
                        </div>
                    </div>
                </div>

                <div class="field-group">
                    <label class="field-label">Receta completa:</label>
                    <div class="img-insert-toolbar" id="edit-img-toolbar" style="display:none"></div>
                    <div id="edit-receta-editor"
                         class="receta-editor"
                         contenteditable="true"
                         data-placeholder="Escribe aquí tu receta..."></div>
                    <textarea id="edit-receta-texto" name="Recipe[receta_texto]"
                              style="display:none" maxlength="120000"></textarea>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn-cancelar" id="modal-close-btn-2">Cancelar</button>
                    <button type="submit" class="btn-subir">Guardar cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL CONFIRMAR BORRAR -->
<div class="modal-overlay" id="modal-confirmar-borrar" aria-hidden="true">
    <div class="modal-box modal-box--sm">
        <h3 class="modal-title">¿Eliminar receta?</h3>
        <p class="modal-confirm-msg">
            Estás a punto de eliminar <strong id="confirm-recipe-titulo"></strong>.<br>
            Esta acción <strong>no se puede deshacer</strong>.
        </p>
        <div class="form-actions form-actions--center">
            <button type="button" class="btn-cancelar" id="btn-confirmar-no">No, cancelar</button>
            <button type="button" class="btn-borrar-confirm" id="btn-confirmar-si">Sí, eliminar</button>
        </div>
    </div>
</div>

<!-- LIGHTBOX -->
<div class="lightbox-overlay" id="lightbox" style="display:none">
    <button class="lightbox-close" id="lightbox-close" aria-label="Cerrar">✕</button>
    <img id="lightbox-img" src="" alt="Vista ampliada">
</div>

<?php $this->registerJsFile(
    'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js',
    ['position' => \yii\web\View::POS_HEAD]
); ?>

<?php $this->registerJs(
    'var CSRF_TOKEN = "' . Yii::$app->request->getCsrfToken() . '";
     var BASE_URL   = "' . Yii::$app->request->baseUrl . '";',
    \yii\web\View::POS_HEAD
); ?>