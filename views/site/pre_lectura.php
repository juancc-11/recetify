<?php
/** @var yii\web\View $this */
/** @var app\models\Recipe $recipe */

use yii\helpers\Html;

$this->title = $recipe->titulo;
$this->params['meta_description'] = mb_strimwidth($recipe->descripcion ?? '', 0, 155, '…');
$this->params['meta_image']       = $recipe->imagen_portada_url ?? '';

$this->registerCssFile(Yii::$app->request->baseUrl . '/css/pre_lectura.css');
$this->registerJsFile(Yii::$app->request->baseUrl . '/js/pre_lectura.js', [
    'depends'  => [\yii\web\JqueryAsset::class],
    'position' => \yii\web\View::POS_END,
]);

$userId    = Yii::$app->user->isGuest ? 0 : Yii::$app->user->id;
$isGuest   = Yii::$app->user->isGuest ? 'true' : 'false';

$this->registerJs("
    var RECIPE_ID   = {$recipe->id};
    var USER_ID     = {$userId};
    var IS_GUEST    = {$isGuest};
    var CSRF_TOKEN  = '" . Yii::$app->request->getCsrfToken() . "';
    var BASE_URL    = '" . Yii::$app->request->baseUrl . "';
    var COLLECTION  = " . json_encode($collection ?? []) . ";
    var USER_REVIEW = " . json_encode($userReview ?? null) . ";
", \yii\web\View::POS_HEAD);
?>

<div class="pl-page">

    <!-- ── HERO CARD ── -->
    <div class="pl-hero-card">

        <!-- Imagen -->
        <div class="pl-hero-img-wrap">
            <?php if ($recipe->imagen_portada_url): ?>
                <img src="<?= Html::encode($recipe->imagen_portada_url) ?>"
                     alt="<?= Html::encode($recipe->titulo) ?>"
                     class="pl-hero-img">
            <?php else: ?>
                <div class="pl-hero-img-placeholder">
                    <i class="fa-solid fa-bowl-food"></i>
                </div>
            <?php endif; ?>
        </div>

        <!-- Info principal -->
        <div class="pl-hero-info">

            <h1 class="pl-title"><?= Html::encode($recipe->titulo) ?></h1>

            <!-- Rating resumen -->
            <div class="pl-rating-summary">
                <div class="pl-stars-display" id="avg-stars-display">
                    <?php
                    $avg   = round($recipe->avgScore ?? 0, 1);
                    $full  = floor($avg);
                    $half  = ($avg - $full) >= 0.5;
                    for ($i = 1; $i <= 5; $i++):
                        if ($i <= $full): ?>
                            <i class="fa-solid fa-star pl-star-filled"></i>
                        <?php elseif ($half && $i === $full + 1): ?>
                            <i class="fa-solid fa-star-half-stroke pl-star-filled"></i>
                        <?php else: ?>
                            <i class="fa-regular fa-star pl-star-empty"></i>
                        <?php endif;
                    endfor; ?>
                </div>
                <span class="pl-avg-value" id="avg-value"><?= $avg ?></span>
                <span class="pl-review-count" id="review-count">
                    (<?= $recipe->totalComments ?? 0 ?> reseñas)
                </span>
            </div>

            <!-- Meta datos -->
            <div class="pl-meta-row">
                <div class="pl-meta-item">
                    <span class="pl-meta-label">Calificación</span>
                    <span class="pl-meta-val"><?= number_format($avg, 1) ?> / 5.0</span>
                </div>
                <div class="pl-meta-item">
                    <span class="pl-meta-label">Visitas</span>
                    <span class="pl-meta-val"><?= number_format($recipe->totalViews ?? 0) ?></span>
                </div>
                <div class="pl-meta-item">
                    <span class="pl-meta-label">Lectores</span>
                    <span class="pl-meta-val"><?= number_format($recipe->totalComments ?? 0) ?></span>
                </div>
            </div>

            <!-- Etiquetas -->
            <?php if (!empty($tags)): ?>
            <div class="pl-tags">
                <?php foreach ($tags as $tag): ?>
                    <span class="pl-tag"><?= Html::encode($tag->name) ?></span>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <!-- Autor -->
            <div class="pl-author">
                <?php if ($recipe->user->avatar_url ?? null): ?>
                    <img src="<?= Html::encode($recipe->user->avatar_url) ?>"
                         class="pl-author-avatar" alt="Avatar">
                <?php else: ?>
                    <div class="pl-author-avatar-default">
                        <?= strtoupper(substr($recipe->user->username ?? 'U', 0, 1)) ?>
                    </div>
                <?php endif; ?>
                <div class="pl-author-info">
                    <span class="pl-author-name"><?= Html::encode($recipe->user->username ?? 'Autor') ?></span>
                    <span class="pl-author-role">Autor de receta</span>
                </div>
            </div>

            <!-- Botones de acción -->
            <div class="pl-actions">
                <a href="<?= Yii::$app->urlManager->createUrl(['site/leer-receta', 'id' => $recipe->id]) ?>"
                   class="pl-btn-read">
                    <i class="fa-solid fa-book-open"></i> Leer receta
                </a>

                <!-- Botón Añadir con dropdown -->
                <div class="pl-add-wrap" id="pl-add-wrap">
                    <button class="pl-btn-add" id="pl-btn-add">
                        <i class="fa-solid fa-bookmark" id="pl-add-icon"></i>
                        <span id="pl-add-label">Guardar</span>
                        <i class="fa-solid fa-chevron-down pl-chevron" id="pl-chevron"></i>
                    </button>
                    <div class="pl-add-dropdown" id="pl-add-dropdown">
                        <button class="pl-dropdown-item" id="btn-guardar"
                                data-tipo="guardado">
                            <i class="fa-solid fa-bookmark"></i>
                            <span>Guardar receta</span>
                        </button>
                        <button class="pl-dropdown-item" id="btn-favorito"
                                data-tipo="favorito">
                            <i class="fa-solid fa-heart"></i>
                            <span>Añadir a favoritos</span>
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- ── TABS: Descripción / Reseñas ── -->
    <div class="pl-tabs-wrap">

        <div class="pl-tabs">
            <button class="pl-tab active" data-tab="descripcion">
                <i class="fa-solid fa-align-left"></i> Descripción
            </button>
            <button class="pl-tab" data-tab="resenas">
                <i class="fa-solid fa-star"></i> Reseñas
            </button>
        </div>

        <!-- Tab Descripción -->
        <div class="pl-tab-panel active" id="tab-descripcion">
            <p class="pl-desc-text">
                <?= nl2br(Html::encode($recipe->descripcion ?? '')) ?>
            </p>
            <?php if ($recipe->utensilios ?? null): ?>
            <div class="pl-utensilios">
                <h4 class="pl-utensilios-title">
                    <i class="fa-solid fa-utensils"></i> Utensilios necesarios
                </h4>
                <p class="pl-utensilios-text">
                    <?= nl2br(Html::encode($recipe->utensilios)) ?>
                </p>
            </div>
            <?php endif; ?>
        </div>

        <!-- Tab Reseñas -->
        <div class="pl-tab-panel" id="tab-resenas">

            <!-- Barra de distribución de estrellas -->
            <div class="pl-rating-breakdown">
                <div class="pl-breakdown-left">
                    <div class="pl-big-score" id="big-score"><?= $avg ?></div>
                    <div class="pl-big-stars" id="big-stars-display">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i class="<?= $i <= $full ? 'fa-solid' : 'fa-regular' ?> fa-star pl-star-filled"></i>
                        <?php endfor; ?>
                    </div>
                    <div class="pl-total-votes" id="total-votes">
                        <?= $recipe->totalComments ?? 0 ?> votos
                    </div>
                </div>
                <div class="pl-breakdown-bars" id="breakdown-bars">
                    <?php for ($s = 5; $s >= 1; $s--): ?>
                    <div class="pl-bar-row">
                        <span class="pl-bar-label"><?= $s ?></span>
                        <div class="pl-bar-track">
                            <div class="pl-bar-fill"
                                 style="width:<?= $starDistribution[$s] ?? 0 ?>%"
                                 data-star="<?= $s ?>"></div>
                        </div>
                        <span class="pl-bar-count" id="star-count-<?= $s ?>">
                            <?= $starCounts[$s] ?? 0 ?>
                        </span>
                    </div>
                    <?php endfor; ?>
                </div>
            </div>

            <!-- Formulario de reseña -->
            <?php if (!Yii::$app->user->isGuest): ?>
            <div class="pl-review-form" id="pl-review-form">
                <h4 class="pl-review-form-title">Tu calificación</h4>
                <div class="pl-star-picker" id="star-picker">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <button type="button" class="pl-star-btn" data-val="<?= $i ?>">
                            <i class="fa-regular fa-star"></i>
                        </button>
                    <?php endfor; ?>
                </div>
                <textarea class="pl-review-textarea" id="review-text"
                          placeholder="Escribe una reseña (opcional)..."
                          maxlength="1000" rows="3"></textarea>
                <button class="pl-btn-submit-review" id="btn-submit-review" disabled>
                    Publicar reseña
                </button>
            </div>
            <?php else: ?>
            <p class="pl-login-prompt">
                <a href="<?= Yii::$app->urlManager->createUrl(['site/login']) ?>">
                    Inicia sesión
                </a> para dejar una reseña.
            </p>
            <?php endif; ?>

            <!-- Lista de reseñas -->
            <div class="pl-reviews-list" id="reviews-list">
                <?php if (empty($comments)): ?>
                    <p class="pl-no-reviews">Aún no hay reseñas. ¡Sé el primero!</p>
                <?php else: ?>
                    <?php foreach ($comments as $comment): ?>
                    <div class="pl-review-item">
                        <div class="pl-review-header">
                            <?php if ($comment->user->avatar_url ?? null): ?>
                                <img src="<?= Html::encode($comment->user->avatar_url) ?>"
                                     class="pl-review-avatar" alt="Avatar">
                            <?php else: ?>
                                <div class="pl-review-avatar-default">
                                    <?= strtoupper(substr($comment->user->username ?? 'U', 0, 1)) ?>
                                </div>
                            <?php endif; ?>
                            <div class="pl-review-meta">
                                <span class="pl-review-user">
                                    <?= Html::encode($comment->user->username ?? 'Usuario') ?>
                                </span>
                                <div class="pl-review-stars">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="<?= $i <= $comment->score ? 'fa-solid' : 'fa-regular' ?> fa-star pl-star-filled"></i>
                                    <?php endfor; ?>
                                </div>
                            </div>
                        </div>
                        <?php if ($comment->comment): ?>
                        <p class="pl-review-text">
                            <?= nl2br(Html::encode($comment->comment)) ?>
                        </p>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

        </div>
    </div>

</div>