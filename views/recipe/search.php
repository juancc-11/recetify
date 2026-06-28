<?php

/** @var yii\web\View $this */
/** @var array $recipes */
/** @var string $query */
/** @var string $filter */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Resultados de búsqueda';

$this->registerCssFile('@web/css/search.css');
$this->registerJsFile('@web/js/search.js', [
    'depends' => [\yii\web\JqueryAsset::class],
    'position' => \yii\web\View::POS_END,
]);

$this->registerJs("
    var toggleCollectionUrl = " . json_encode(Url::to(['recipe/toggle-collection'])) . ";
    var loginUrl            = " . json_encode(Url::to(['site/login'])) . ";
    var reportUrl            = " . json_encode(Url::to(['site/reportar-receta'])) . ";
", \yii\web\View::POS_HEAD);

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

                    <!-- MENU DE OPCIONES (REPORTAR) -->
                    <div class="recipe-options">

                        <button
                            class="btn-options"
                            type="button"
                            aria-label="Más opciones"
                            aria-haspopup="true"
                            aria-expanded="false"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                                <circle cx="12" cy="5" r="2"/>
                                <circle cx="12" cy="12" r="2"/>
                                <circle cx="12" cy="19" r="2"/>
                            </svg>
                        </button>

                        <div class="options-dropdown">

                            <button
                                class="dropdown-item btn-report-recipe"
                                type="button"
                                data-recipe-id="<?= (int)$recipe['id'] ?>"
                                data-recipe-title="<?= Html::encode($recipe['titulo']) ?>"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/>
                                    <line x1="4" y1="22" x2="4" y2="15"/>
                                </svg>
                                Reportar receta
                            </button>

                            <button
                                class="dropdown-item btn-report-user"
                                type="button"
                                data-user-id="<?= (int)($recipe['user_id'] ?? 0) ?>"
                                data-username="<?= Html::encode($recipe['username']) ?>"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                                    <circle cx="9" cy="7" r="4"/>
                                    <line x1="17" y1="8" x2="22" y2="8"/>
                                    <line x1="19.5" y1="5.5" x2="19.5" y2="10.5"/>
                                </svg>
                                Reportar usuario
                            </button>

                        </div>

                    </div>

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
        <a href="<?= Url::to(['/site/mi-perfil', 'id' => $recipe['user_id']]) ?>">
            <img
                src="<?= Html::encode($recipe['avatar_url']) ?>"
                alt="Avatar"
                class="author-avatar"
            >
        </a>
    <?php else: ?>
        <a href="<?= Url::to(['/site/mi-perfil', 'id' => $recipe['user_id']]) ?>">
            <div class="default-avatar">
                <?= strtoupper(substr($recipe['username'], 0, 1)) ?>
            </div>
        </a>
    <?php endif; ?>

    <div class="author-info">
        <a href="<?= Url::to(['/site/mi-perfil', 'id' => $recipe['user_id']]) ?>"
           class="author-name">
            <?= Html::encode($recipe['username']) ?>
        </a>
        <small class="author-role">Autor de receta</small>
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

<span class="recipe-views">
    <svg xmlns="http://www.w3.org/2000/svg"
         width="15"
         height="15"
         viewBox="0 0 24 24"
         fill="none"
         stroke="currentColor"
         stroke-width="2"
         stroke-linecap="round"
         stroke-linejoin="round">
        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8S1 12 1 12z"/>
        <circle cx="12" cy="12" r="3"/>
    </svg>

    <?= number_format((int)$recipe['total_views']) ?>
</span>

                            </div>

                        </div>

                        <!-- BOTONES -->
                        <div class="recipe-buttons">

                            <a
                                href="<?= Url::to(['site/pre-lectura', 'id' => $recipe['id']]) ?>"
                                class="btn-read"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/>
                                    <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>
                                </svg>
                                Leer receta
                            </a>

                            <?php $isSaved = !empty($recipe['is_saved']); ?>
                            <button
    class="btn-save<?= $isSaved ? ' is-saved' : '' ?>"
    data-recipe-id="<?= (int)$recipe['id'] ?>"
    data-tipo="guardado"
    data-saved="<?= $isSaved ? '1' : '0' ?>"
>
    <svg xmlns="http://www.w3.org/2000/svg"
         width="15"
         height="15"
         viewBox="0 0 24 24"
         fill="<?= $isSaved ? 'currentColor' : 'none' ?>"
         stroke="currentColor"
         stroke-width="2"
         stroke-linecap="round"
         stroke-linejoin="round">
        <path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/>
    </svg>

    <span><?= $isSaved ? 'Guardado' : 'Guardar' ?></span>
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

<!-- ══════════════════════════════════════════════════════
     MODAL DE REPORTE (receta o usuario)
     ══════════════════════════════════════════════════════ -->
<div id="report-modal-overlay" class="report-modal-overlay">

    <div class="report-modal" role="dialog" aria-modal="true" aria-labelledby="report-modal-title">

        <button type="button" class="report-modal-close" aria-label="Cerrar">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18"/>
                <line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
        </button>

        <h3 id="report-modal-title" class="report-modal-title">
            Reportar
        </h3>

        <p class="report-modal-subtitle">
            <span id="report-modal-target">¿Por qué quieres reportar este contenido?</span>
        </p>

        <form id="report-form" class="report-form">

            <input type="hidden" id="report-recipe-id" name="recipe_id" value="">
            <input type="hidden" id="report-user-id" name="user_id" value="">

            <div class="report-options-group">

                <label class="report-option">
                    <input type="radio" name="motivo" value="contenido_inapropiado" required>
                    <span>Contenido inapropiado</span>
                </label>

                <label class="report-option">
                    <input type="radio" name="motivo" value="spam">
                    <span>Spam o publicidad</span>
                </label>

                <label class="report-option report-option--recipe-only">
                    <input type="radio" name="motivo" value="plagio">
                    <span>Plagio o copia de otra receta</span>
                </label>

                <label class="report-option">
                    <input type="radio" name="motivo" value="informacion_falsa">
                    <span>Información falsa o engañosa</span>
                </label>

                <label class="report-option report-option--user-only">
                    <input type="radio" name="motivo" value="acoso">
                    <span>Acoso o comportamiento abusivo</span>
                </label>

                <label class="report-option">
                    <input type="radio" name="motivo" value="otro">
                    <span>Otro motivo</span>
                </label>

            </div>

            <textarea
                id="report-descripcion"
                name="descripcion"
                class="report-textarea"
                placeholder="Cuéntanos más detalles (opcional)"
                maxlength="500"
                rows="3"
            ></textarea>

            <div class="report-modal-actions">

                <button type="button" class="btn-report-cancel">
                    Cancelar
                </button>

                <button type="submit" class="btn-report-submit">
                    Enviar reporte
                </button>

            </div>

        </form>

    </div>

</div>