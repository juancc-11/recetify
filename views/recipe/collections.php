<?php

/** @var yii\web\View $this */
/** @var array $recipes */
/** @var string $activeTab */  // 'guardado' | 'historial' | 'favorito'

use yii\helpers\Html;
use yii\helpers\Url;

$tabLabels = [
    'guardado'  => 'Guardados',
    'historial' => 'Historial',
    'favorito'  => 'Favoritos',
];

$this->title = $tabLabels[$activeTab] ?? 'Colecciones';
$this->registerCssFile('@web/css/collections.css');
$this->registerJsFile('@web/js/collections.js', ['position' => \yii\web\View::POS_END]);

?>

<div class="collections-page">
    <div class="collections-page tab-<?= Html::encode($activeTab) ?>">

    <!-- TABS -->
    <div class="collections-tabs">
        <?php foreach ($tabLabels as $key => $label): ?>
            <a
                href="<?= Url::to(['recipe/collections', 'tab' => $key]) ?>"
                class="tab-item <?= $activeTab === $key ? 'tab-active' : '' ?>"
            >
                <?= Html::encode($label) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- FILTRO CATEGORÍAS -->
    <div class="collections-toolbar">
        <label class="categories-toggle">
            <input type="checkbox" id="toggle-categories">
            <span>Categorías</span>
        </label>
    </div>

    <!-- CATEGORÍAS (desplegable) -->
    <div class="categories-panel" id="categories-panel" style="display:none;">
        <!-- Aquí se renderizan los tags dinámicamente vía JS o PHP si los pasas desde el controlador -->
        <span class="category-chip active">Todas</span>
        <span class="category-chip">Desayuno</span>
        <span class="category-chip">Almuerzo</span>
        <span class="category-chip">Cena</span>
        <span class="category-chip">Postres</span>
        <span class="category-chip">Bebidas</span>
        <span class="category-chip">Vegano</span>
        <span class="category-chip">Rápido</span>
    </div>

    <!-- GRILLA DE RECETAS -->
    <?php if (!empty($recipes)): ?>

        <div class="recipes-grid">

            <?php foreach ($recipes as $recipe): ?>

                <?php
                    $avg   = round((float)($recipe['avg_score'] ?? 0), 1);
                    $views = (int)($recipe['total_views'] ?? 0);
                    $isSaved = $activeTab === 'guardado';
                ?>

                <div class="recipe-card" data-recipe-id="<?= (int)$recipe['id'] ?>">

                    <!-- IMAGEN -->
                    <div class="card-image">
                        <?php if (!empty($recipe['imagen_portada_url'])): ?>
                            <img
                                src="<?= Html::encode($recipe['imagen_portada_url']) ?>"
                                alt="<?= Html::encode($recipe['titulo']) ?>"
                                loading="lazy"
                            >
                        <?php else: ?>
                            <div class="card-image-placeholder">
                                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                                    <circle cx="8.5" cy="8.5" r="1.5"/>
                                    <polyline points="21 15 16 10 5 21"/>
                                </svg>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- CUERPO -->
                    <div class="card-body">

                        <h3 class="card-title">
                            <?= Html::encode($recipe['titulo']) ?>
                        </h3>

                        <!-- RATING + VISITAS (una sola línea) -->
                        <div class="card-rating">
                            <?php if ($avg > 0): ?>
                                <span class="star-icon">&#9733;</span>
                                <span class="rating-value"><?= number_format($avg, 1) ?></span>
                                <span class="meta-dot">&middot;</span>
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                                <span class="meta-text"><?= number_format($views) ?> lecturas</span>
                            <?php else: ?>
                                <span class="rating-empty">Sin reseñas aún</span>
                            <?php endif; ?>
                        </div>

                        <!-- ACCIONES -->
                        <div class="card-actions">

                            <a
                                href="<?= Url::to([
                                                  'site/pre-lectura',
                                                  'id' => $recipe['id']
                                                  ]) ?>"
                                class="btn-leer"
                                >
                                Leer receta
                            </a>

                            <button
                                class="btn-guardar <?= $isSaved ? 'saved' : '' ?>"
                                data-recipe-id="<?= (int)$recipe['id'] ?>"
                                data-saved="<?= $isSaved ? '1' : '0' ?>"
                                data-tipo="guardado"
                                aria-label="<?= $isSaved ? 'Quitar de guardados' : 'Guardar receta' ?>"
                                title="<?= $isSaved ? 'Quitar de guardados' : 'Guardar receta' ?>"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="<?= $isSaved ? 'currentColor' : 'none' ?>" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/>
                                </svg>
                            </button>

                        </div>

                    </div>

                </div>

            <?php endforeach; ?>

        </div>

    <?php else: ?>

        <!-- ESTADO VACÍO -->
        <div class="empty-state">

            <svg xmlns="http://www.w3.org/2000/svg" width="52" height="52" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/>
            </svg>

            <h2>No hay recetas aquí todavía</h2>

            <?php if ($activeTab === 'guardado'): ?>
                <p>Guarda recetas que te gusten para encontrarlas rápido después.</p>
            <?php elseif ($activeTab === 'historial'): ?>
                <p>Las recetas que leas aparecerán en tu historial.</p>
            <?php else: ?>
                <p>Agrega recetas a favoritos para verlas aquí.</p>
            <?php endif; ?>

            <a href="<?= Yii::$app->urlManager->createUrl(['recipe/search']) ?>" class="btn-explore">
                Explorar recetas
            </a>

        </div>

    <?php endif; ?>

</div>

<!-- Toast de notificación -->
<div class="save-toast" id="save-toast" aria-live="polite"></div>
