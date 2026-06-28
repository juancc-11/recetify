<?php
/** @var yii\web\View $this */
/** @var app\models\User $profileUser */
/** @var app\models\Comment[] $reviews */
/** @var app\models\Recipe[] $recipes */
/** @var bool $isOwner */

use yii\helpers\Html;

$this->title = $profileUser->username . ' | Perfil';
$this->params['meta_description'] = 'Perfil de ' . $profileUser->username . ' en Recetify Lab.';

$this->registerCssFile(Yii::$app->request->baseUrl . '/css/mi_perfil.css');
$this->registerJsFile(Yii::$app->request->baseUrl . '/js/mi_perfil.js', [
    'depends'  => [\yii\web\JqueryAsset::class],
    'position' => \yii\web\View::POS_END,
]);

$this->registerJs("
    var PROFILE_USER_ID = {$profileUser->id};
    var IS_OWNER        = " . ($isOwner ? 'true' : 'false') . ";
    var CSRF_TOKEN      = '" . Yii::$app->request->getCsrfToken() . "';
    var BASE_URL        = '" . Yii::$app->request->baseUrl . "';
", \yii\web\View::POS_HEAD);
?>

<div class="pf-page">

    <!-- ── HERO / BANNER ── -->
    <div class="pf-hero">
        <div class="pf-hero-banner"></div>

        <div class="pf-hero-body">
            <!-- Avatar -->
            <div class="pf-avatar-wrap">
                <?php if ($profileUser->avatar_url): ?>
                    <img src="<?= Html::encode($profileUser->avatar_url) ?>"
                         class="pf-avatar" alt="Avatar de <?= Html::encode($profileUser->username) ?>">
                <?php else: ?>
                    <div class="pf-avatar-default">
                        <?= strtoupper(substr($profileUser->username, 0, 1)) ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Stats -->
            <div class="pf-stats-row">
                <div class="pf-stat">
                    <span class="pf-stat-val"><?= count($recipes) ?></span>
                    <span class="pf-stat-label">Recetas</span>
                </div>
                <div class="pf-stat-divider"></div>
                <div class="pf-stat">
                    <span class="pf-stat-val"><?= count($reviews) ?></span>
                    <span class="pf-stat-label">Reseñas</span>
                </div>
                <div class="pf-stat-divider"></div>
                <div class="pf-stat">
                    <span class="pf-stat-val">
                        <?php
                        $totalFavs = (int) Yii::$app->db->createCommand(
                              "SELECT COUNT(*) FROM recipe_collections rc
                              JOIN recipes r ON r.id = rc.recipe_id
                               WHERE r.user_id = :uid AND rc.tipo = 'favorito'",
                               [':uid' => $profileUser->id]
                               )->queryScalar();
                        echo number_format($totalFavs);
                        ?>
                    </span>
                    <span class="pf-stat-label">Favoritos recibidos</span>
                </div>
            </div>
        </div>
    </div>

    <!-- ── INFO DEL USUARIO ── -->
    <div class="pf-info-section">
        <div class="pf-info-left">
            <h1 class="pf-username"><?= Html::encode($profileUser->username) ?></h1>
            <div class="pf-meta">
                <span class="pf-meta-item">
                    <i class="fa-regular fa-calendar"></i>
                    Miembro desde <?= Yii::$app->formatter->asDate($profileUser->created_at, 'long') ?>
                </span>
                <?php if ($profileUser->rol === 'admin'): ?>
                <span class="pf-meta-item pf-badge-admin">
                    <i class="fa-solid fa-shield-halved"></i> Admin
                </span>
                <?php endif; ?>
            </div>

            <!-- Descripción / Bio -->
            <div class="pf-bio-wrap" id="pf-bio-wrap">
                <?php if ($isOwner): ?>
                    <div class="pf-bio-display" id="pf-bio-display">
                        <p class="pf-bio-text" id="pf-bio-text">
                            <?= $profileUser->full_name
                                ? nl2br(Html::encode($profileUser->full_name))
                                : '<span class="pf-bio-empty">Añade una descripción sobre ti...</span>' ?>
                        </p>
                        <button class="pf-bio-edit-btn" id="pf-bio-edit-btn" title="Editar descripción">
                            <i class="fa-solid fa-pen"></i>
                        </button>
                    </div>
                    <div class="pf-bio-editor" id="pf-bio-editor" style="display:none">
                        <textarea class="pf-bio-textarea" id="pf-bio-textarea"
                                  maxlength="300" placeholder="Cuéntanos sobre ti..."
                                  rows="3"><?= Html::encode($profileUser->full_name ?? '') ?></textarea>
                        <div class="pf-bio-actions">
                            <span class="pf-bio-counter"><span id="pf-bio-count">0</span>/300</span>
                            <button class="pf-bio-cancel" id="pf-bio-cancel">Cancelar</button>
                            <button class="pf-bio-save" id="pf-bio-save">Guardar</button>
                        </div>
                    </div>
                <?php else: ?>
                    <p class="pf-bio-text">
                        <?= $profileUser->full_name
                            ? nl2br(Html::encode($profileUser->full_name))
                            : '<span class="pf-bio-empty">Este usuario no tiene descripción.</span>' ?>
                    </p>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($isOwner): ?>
        <div class="pf-info-right">
            <a href="<?= Yii::$app->urlManager->createUrl(['site/profile']) ?>"
               class="pf-edit-btn">
                <i class="fa-solid fa-user-pen"></i> Editar perfil
            </a>
        </div>
        <?php endif; ?>
    </div>

    <!-- ── TABS ── -->
    <div class="pf-tabs-wrap">
        <div class="pf-tabs">
            <button class="pf-tab active" data-tab="resenas">
                <i class="fa-solid fa-star"></i> Reseñas dadas
            </button>
            <button class="pf-tab" data-tab="recetas">
                <i class="fa-solid fa-utensils"></i> Recetas
            </button>
        </div>

        <!-- Tab: Reseñas dadas -->
        <div class="pf-tab-panel active" id="tab-resenas">
            <?php if (empty($reviews)): ?>
                <div class="pf-empty">
                    <i class="fa-regular fa-star"></i>
                    <p>Aún no ha dejado reseñas.</p>
                </div>
            <?php else: ?>
                <div class="pf-reviews-list">
                    <?php foreach ($reviews as $review):
                        $recipe = \app\models\Recipe::findOne($review->recipe_id);
                        if (!$recipe || !$recipe->is_published) continue;
                    ?>
                    <div class="pf-review-card">
                        <!-- Receta referenciada -->
                        <a href="<?= Yii::$app->urlManager->createUrl(['site/pre-lectura', 'id' => $recipe->id]) ?>"
                           class="pf-review-recipe">
                            <?php if ($recipe->imagen_portada_url): ?>
                                <img src="<?= Html::encode($recipe->imagen_portada_url) ?>"
                                     class="pf-review-recipe-img" alt="Portada">
                            <?php else: ?>
                                <div class="pf-review-recipe-img pf-review-no-img">
                                    <i class="fa-solid fa-bowl-food"></i>
                                </div>
                            <?php endif; ?>
                            <div class="pf-review-recipe-info">
                                <span class="pf-review-recipe-title">
                                    <?= Html::encode($recipe->titulo) ?>
                                </span>
                                <span class="pf-review-recipe-author">
                                    por <?= Html::encode(\app\models\User::findOne($recipe->user_id)->username ?? 'Autor') ?>
                                </span>
                            </div>
                        </a>

                        <!-- Calificación y comentario -->
                        <div class="pf-review-content">
                            <div class="pf-review-stars">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="<?= $i <= $review->score ? 'fa-solid' : 'fa-regular' ?> fa-star pf-star"></i>
                                <?php endfor; ?>
                                <span class="pf-review-score"><?= $review->score ?>/5</span>
                            </div>
                            <?php
                            $bodyText = $review->body ?? '';
                            $isPlaceholder = is_numeric($bodyText) && (int)$bodyText === $review->score;
                            if ($bodyText && !$isPlaceholder):
                            ?>
                            <p class="pf-review-body">
                                <?= nl2br(Html::encode($bodyText)) ?>
                            </p>
                            <?php endif; ?>
                            <span class="pf-review-date">
                                <?= Yii::$app->formatter->asRelativeTime($review->created_at) ?>
                            </span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Tab: Recetas del usuario -->
        <div class="pf-tab-panel" id="tab-recetas">
            <?php if (empty($recipes)): ?>
                <div class="pf-empty">
                    <i class="fa-solid fa-bowl-food"></i>
                    <p>Aún no ha publicado recetas.</p>
                </div>
            <?php else: ?>
                <div class="pf-recipes-grid">
                    <?php foreach ($recipes as $recipe): ?>
                    <a href="<?= Yii::$app->urlManager->createUrl(['site/pre-lectura', 'id' => $recipe->id]) ?>"
                       class="pf-recipe-card">
                        <div class="pf-recipe-img-wrap">
                            <?php if ($recipe->imagen_portada_url): ?>
                                <img src="<?= Html::encode($recipe->imagen_portada_url) ?>"
                                     alt="<?= Html::encode($recipe->titulo) ?>"
                                     class="pf-recipe-img">
                            <?php else: ?>
                                <div class="pf-recipe-no-img">
                                    <i class="fa-solid fa-bowl-food"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="pf-recipe-info">
                            <h3 class="pf-recipe-title"><?= Html::encode($recipe->titulo) ?></h3>
                            <p class="pf-recipe-desc">
                                <?= Html::encode(mb_strimwidth($recipe->descripcion ?? '', 0, 80, '…')) ?>
                            </p>
                            <div class="pf-recipe-meta">
                                <?php
                                $avgR = (float) (\app\models\Comment::find()
                                    ->where(['recipe_id' => $recipe->id, 'is_visible' => 1])
                                    ->average('score') ?? 0);
                                $totalR = (int) \app\models\Comment::find()
                                    ->where(['recipe_id' => $recipe->id, 'is_visible' => 1])
                                    ->count();
                                ?>
                                <span class="pf-recipe-rating">
                                    <i class="fa-solid fa-star pf-star"></i>
                                    <?= number_format($avgR, 1) ?>
                                    <span class="pf-recipe-rating-count">(<?= $totalR ?>)</span>
                                </span>
                            </div>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

    </div>
</div>