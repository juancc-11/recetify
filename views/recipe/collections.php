<?php

/** @var yii\web\View $this */
/** @var array $recipes */
/** @var string $activeTab */  // 'guardado' | 'historial' | 'favorito'
/** @var string $q */
/** @var string $filter */

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

    <!-- BUSCADOR + FILTROS -->
    <div class="collections-toolbar">

        <form method="get" action="<?= Url::to(['recipe/collections']) ?>" class="toolbar-form">

            <input type="hidden" name="tab" value="<?= Html::encode($activeTab) ?>">

            <!-- Buscador -->
            <div class="search-box">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round" class="search-icon">
                    <circle cx="11" cy="11" r="8"/>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                <input
                    type="text"
                    name="q"
                    value="<?= Html::encode($q) ?>"
                    placeholder="Buscar en <?= Html::encode($tabLabels[$activeTab]) ?>..."
                    class="search-input"
                >
            </div>

            <!-- Filtros de orden -->
            <div class="filter-buttons">

                <a
                    href="<?= Url::to(['recipe/collections', 'tab' => $activeTab, 'q' => $q, 'filter' => 'recent']) ?>"
                    class="filter-btn <?= $filter === 'recent' ? 'filter-btn--active' : '' ?>"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="2"
                         stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/>
                        <polyline points="12 6 12 12 16 14"/>
                    </svg>
                    Recientes
                </a>

                <a
                    href="<?= Url::to(['recipe/collections', 'tab' => $activeTab, 'q' => $q, 'filter' => 'popular']) ?>"
                    class="filter-btn <?= $filter === 'popular' ? 'filter-btn--active' : '' ?>"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="2"
                         stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/>
                        <polyline points="17 6 23 6 23 12"/>
                    </svg>
                    Populares
                </a>

                <a
                    href="<?= Url::to(['recipe/collections', 'tab' => $activeTab, 'q' => $q, 'filter' => 'az']) ?>"
                    class="filter-btn <?= $filter === 'az' ? 'filter-btn--active' : '' ?>"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="2"
                         stroke-linecap="round" stroke-linejoin="round">
                        <line x1="3" y1="6"  x2="21" y2="6"/>
                        <line x1="3" y1="12" x2="15" y2="12"/>
                        <line x1="3" y1="18" x2="9"  y2="18"/>
                    </svg>
                    A–Z
                </a>

                <?php if (!empty($q) || $filter !== 'recent'): ?>
                    <a
                        href="<?= Url::to(['recipe/collections', 'tab' => $activeTab]) ?>"
                        class="filter-btn filter-btn--clear"
                        title="Limpiar filtros"
                    >
                        ✕ Limpiar
                    </a>
                <?php endif; ?>

            </div>

        </form>

    </div>

    <!-- CONTADOR -->
    <?php if (!empty($recipes)): ?>
        <p class="results-count">
            <?= count($recipes) ?> <?= count($recipes) === 1 ? 'receta' : 'recetas' ?>
            <?= !empty($q) ? 'para "' . Html::encode($q) . '"' : '' ?>
        </p>
    <?php endif; ?>

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
                                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24"
                                     fill="none" stroke="currentColor" stroke-width="1.5"
                                     stroke-linecap="round" stroke-linejoin="round">
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

                        <!-- RATING + VISITAS -->
                        <div class="card-rating">
                            <?php if ($avg > 0): ?>
                                <span class="star-icon">&#9733;</span>
                                <span class="rating-value"><?= number_format($avg, 1) ?></span>
                                <span class="meta-dot">&middot;</span>
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24"
                                     fill="none" stroke="currentColor" stroke-width="2"
                                     stroke-linecap="round" stroke-linejoin="round">
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
                                href="<?= Url::to(['site/pre-lectura', 'id' => $recipe['id']]) ?>"
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
                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24"
                                     fill="<?= $isSaved ? 'currentColor' : 'none' ?>"
                                     stroke="currentColor" stroke-width="2"
                                     stroke-linecap="round" stroke-linejoin="round">
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

            <svg xmlns="http://www.w3.org/2000/svg" width="52" height="52" viewBox="0 0 24 24"
                 fill="none" stroke="currentColor" stroke-width="1.2"
                 stroke-linecap="round" stroke-linejoin="round">
                <path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/>
            </svg>

            <?php if (!empty($q)): ?>
                <h2>Sin resultados para "<?= Html::encode($q) ?>"</h2>
                <p>Intenta con otra palabra.</p>
                <a href="<?= Url::to(['recipe/collections', 'tab' => $activeTab]) ?>" class="btn-explore">
                    Ver todas
                </a>
            <?php else: ?>
                <h2>No hay recetas aquí todavía</h2>
                <?php if ($activeTab === 'guardado'): ?>
                    <p>Guarda recetas que te gusten para encontrarlas rápido después.</p>
                <?php elseif ($activeTab === 'historial'): ?>
                    <p>Las recetas que leas aparecerán en tu historial.</p>
                <?php else: ?>
                    <p>Agrega recetas a favoritos para verlas aquí.</p>
                <?php endif; ?>
                <a href="<?= Url::to(['recipe/search']) ?>" class="btn-explore">
                    Explorar recetas
                </a>
            <?php endif; ?>

        </div>

    <?php endif; ?>

</div>

<!-- Toast de notificación -->
<div class="save-toast" id="save-toast" aria-live="polite"></div>
