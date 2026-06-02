<?php

/** @var yii\web\View $this */
/** @var array $recipes */
/** @var string $query */
/** @var string $filter */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Resultados de búsqueda';

$this->registerCssFile('@web/css/search.css');

?>

<div class="search-page">

    <!-- SIDEBAR -->
    <aside class="sidebar">

        <h2 class="sidebar-title">
            Resultados
        </h2>

        <div class="search-box-info">

            <span class="search-label">
                Buscando:
            </span>

            <h3 class="search-query">
                "<?= Html::encode($query) ?>"
            </h3>

        </div>

        <div class="filters-container">

            <h4 class="filter-title">
                Filtros
            </h4>

            <!-- TODAS -->
            <a
                href="<?= Url::to([
                    'recipe/search',
                    'q' => $query,
                    'filter' => 'all'
                ]) ?>"
                class="filter-item <?= $filter == 'all' ? 'active-filter' : '' ?>"
            >
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="8" y1="6" x2="21" y2="6"/>
                    <line x1="8" y1="12" x2="21" y2="12"/>
                    <line x1="8" y1="18" x2="21" y2="18"/>
                    <line x1="3" y1="6" x2="3.01" y2="6"/>
                    <line x1="3" y1="12" x2="3.01" y2="12"/>
                    <line x1="3" y1="18" x2="3.01" y2="18"/>
                </svg>
                Todas
            </a>

            <!-- POPULARES -->
            <a
                href="<?= Url::to([
                    'recipe/search',
                    'q' => $query,
                    'filter' => 'popular'
                ]) ?>"
                class="filter-item <?= $filter == 'popular' ? 'active-filter' : '' ?>"
            >
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/>
                    <polyline points="17 6 23 6 23 12"/>
                </svg>
                Más populares
            </a>

            <!-- RECIENTES -->
            <a
                href="<?= Url::to([
                    'recipe/search',
                    'q' => $query,
                    'filter' => 'recent'
                ]) ?>"
                class="filter-item <?= $filter == 'recent' ? 'active-filter' : '' ?>"
            >
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/>
                    <polyline points="12 6 12 12 16 14"/>
                </svg>
                Más recientes
            </a>

            <!-- FAVORITAS -->
            <a
                href="<?= Url::to([
                    'recipe/search',
                    'q' => $query,
                    'filter' => 'favorites'
                ]) ?>"
                class="filter-item <?= $filter == 'favorites' ? 'active-filter' : '' ?>"
            >
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                </svg>
                Favoritas
            </a>

        </div>

    </aside>

    <!-- CONTENIDO -->
    <main class="results-container">

        <!-- HEADER RESULTADOS -->
        <div class="results-header">

            <h1>
                Resultados encontrados
            </h1>

            <span>
                <?= count($recipes) ?> recetas
            </span>

        </div>

        <!-- RESULTADOS -->
        <?php if (!empty($recipes)): ?>

            <?php foreach ($recipes as $recipe): ?>

                <div class="recipe-card">

                    <!-- IMAGEN -->
                    <div class="recipe-image-container">

                        <?php if (!empty($recipe['imagen_portada_url'])): ?>

                            <img
                                src="<?= Html::encode($recipe['imagen_portada_url']) ?>"
                                alt="<?= Html::encode($recipe['titulo']) ?>"
                                class="recipe-image"
                            >

                        <?php else: ?>

                            <div class="image-placeholder">
                                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                                    <circle cx="8.5" cy="8.5" r="1.5"/>
                                    <polyline points="21 15 16 10 5 21"/>
                                </svg>
                                Sin imagen
                            </div>

                        <?php endif; ?>

                    </div>

                    <!-- INFORMACIÓN -->
                    <div class="recipe-info">

                        <h2 class="recipe-title">
                            <?= Html::encode($recipe['titulo']) ?>
                        </h2>

                        <p class="recipe-description">
                            <?= Html::encode($recipe['descripcion']) ?>
                        </p>

                        <div class="recipe-meta">

                            <!-- FOTO PERFIL -->
                            <div class="author-profile">

                                <?php if (!empty($recipe['avatar_url'])): ?>

                                    <img
                                        src="<?= Html::encode($recipe['avatar_url']) ?>"
                                        alt="Avatar"
                                        class="author-avatar"
                                    >

                                <?php else: ?>

                                    <div class="default-avatar">
                                        <?= strtoupper(substr($recipe['username'], 0, 1)) ?>
                                    </div>

                                <?php endif; ?>

                                <div class="author-info">

                                    <span class="author-name">
                                        <?= Html::encode($recipe['username']) ?>
                                    </span>

                                    <small class="author-role">
                                        Autor de receta
                                    </small>

                                </div>

                            </div>

                            <!-- CALIFICACIÓN -->
                            <?php
                                $avg   = round((float)($recipe['avg_score'] ?? 0), 1);
                                $total = (int)($recipe['total_ratings'] ?? 0);
                                $full  = floor($avg);
                                $half  = ($avg - $full) >= 0.5 ? 1 : 0;
                                $empty = 5 - $full - $half;
                            ?>

                            <div class="recipe-rating">

                                <div class="stars">

                                    <?php for ($i = 0; $i < $full; $i++): ?>
                                        <!-- ESTRELLA LLENA -->
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="star-full">
                                            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                                        </svg>
                                    <?php endfor; ?>

                                    <?php if ($half): ?>
                                        <!-- ESTRELLA MEDIA -->
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="star-half">
                                            <defs>
                                                <linearGradient id="half-<?= $recipe['id'] ?>" x1="0" x2="1" y1="0" y2="0">
                                                    <stop offset="50%" stop-color="currentColor"/>
                                                    <stop offset="50%" stop-color="transparent"/>
                                                </linearGradient>
                                            </defs>
                                            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" fill="url(#half-<?= $recipe['id'] ?>)"/>
                                        </svg>
                                    <?php endif; ?>

                                    <?php for ($i = 0; $i < $empty; $i++): ?>
                                        <!-- ESTRELLA VACÍA -->
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="star-empty">
                                            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                                        </svg>
                                    <?php endfor; ?>

                                </div>

                                <span class="rating-value">
                                    <?= $avg > 0 ? number_format($avg, 1) : '—' ?>
                                </span>

                                <span class="rating-count">
                                    (<?= $total ?> <?= $total === 1 ? 'reseña' : 'reseñas' ?>)
                                </span>

                            </div>

                        </div>

                        <!-- BOTONES -->
                        <div class="recipe-buttons">

                            <a
                                href="<?= Url::to([
                                    'recipe/view',
                                    'id' => $recipe['id']
                                ]) ?>"
                                class="btn-read"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/>
                                    <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>
                                </svg>
                                Leer receta
                            </a>

                            <button class="btn-save">
                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/>
                                </svg>
                                Guardar
                            </button>

                        </div>

                    </div>

                </div>

            <?php endforeach; ?>

        <?php else: ?>

            <!-- SIN RESULTADOS -->
            <div class="empty-results">

                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"/>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                    <line x1="11" y1="8" x2="11" y2="14"/>
                    <line x1="8" y1="11" x2="14" y2="11"/>
                </svg>

                <h2>
                    No se encontraron recetas
                </h2>

                <p>
                    No existen resultados relacionados con:
                </p>

                <strong>
                    "<?= Html::encode($query) ?>"
                </strong>

                <p>
                    Intenta buscar otra palabra o ingrediente.
                </p>

            </div>

        <?php endif; ?>

    </main>

</div>