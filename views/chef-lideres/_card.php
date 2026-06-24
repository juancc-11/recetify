<?php
/**
 * Partial: _card.php
 * Una sola card de chef dentro del carrusel.
 *
 * @var array  $chef     Fila del modelo (user_id, username, full_name, avatar_url,
 *                       avg_score, total_recipes, total_views,
 *                       receta_titulo, receta_descripcion, receta_imagen)
 * @var int    $pos      Posición (1, 2, 3) — para la medalla
 * @var string $metrica  'score' | 'views'
 */

use yii\helpers\Html;

$medallas = ['🥇', '🥈', '🥉'];
$medalla  = $medallas[$pos - 1] ?? '';

$avatarUrl = !empty($chef['avatar_url'])
    ? $chef['avatar_url']
    : 'https://i.pravatar.cc/150?u=' . urlencode($chef['username']);

$recetaImagen = !empty($chef['receta_imagen'])
    ? $chef['receta_imagen']
    : 'https://picsum.photos/seed/' . $chef['user_id'] . '/400/300';

$nombreDisplay = !empty($chef['full_name']) ? $chef['full_name'] : $chef['username'];

$metricaLabel = $metrica === 'score'
    ? ($chef['avg_score'] ?? '—') . ' / 5 ⭐'
    : number_format($chef['total_views'] ?? 0) . ' visitas 👁️';
?>

<div class="cl-card">

    <!-- Imagen de la receta destacada -->
    <div class="cl-card-img-wrap">
        <img class="cl-card-img"
             src="<?= Html::encode($recetaImagen) ?>"
             alt="Receta destacada de <?= Html::encode($nombreDisplay) ?>"
             onerror="this.src='https://picsum.photos/seed/<?= $chef['user_id'] ?>/400/300'">
        <span class="cl-card-medalla"><?= $medalla ?></span>
    </div>

    <!-- Contenido -->
    <div class="cl-card-body">

        <!-- Avatar + nombre del chef -->
        <div class="cl-card-chef">
            <img class="cl-card-avatar"
                 src="<?= Html::encode($avatarUrl) ?>"
                 alt="Avatar de <?= Html::encode($nombreDisplay) ?>"
                 onerror="this.src='https://i.pravatar.cc/150?u=<?= urlencode($chef['username']) ?>'">
            <div class="cl-card-chef-info">
                <span class="cl-card-chef-nombre"><?= Html::encode($nombreDisplay) ?></span>
                <span class="cl-card-chef-user">@<?= Html::encode($chef['username']) ?></span>
            </div>
        </div>

        <!-- Receta destacada -->
        <?php if (!empty($chef['receta_titulo'])): ?>
            <h3 class="cl-card-receta-titulo">
                <?= Html::encode($chef['receta_titulo']) ?>
            </h3>
            <?php if (!empty($chef['receta_descripcion'])): ?>
                <p class="cl-card-receta-desc">
                    <?= Html::encode(mb_substr($chef['receta_descripcion'], 0, 100)) ?>
                    <?= mb_strlen($chef['receta_descripcion']) > 100 ? '…' : '' ?>
                </p>
            <?php endif; ?>
        <?php endif; ?>

        <!-- Métricas -->
        <div class="cl-card-metricas">
            <span class="cl-metrica cl-metrica--highlight"><?= Html::encode($metricaLabel) ?></span>
            <span class="cl-metrica"><?= (int)($chef['total_recipes'] ?? 0) ?> recetas</span>
            <?php if ($metrica !== 'views'): ?>
                <span class="cl-metrica"><?= number_format($chef['total_views'] ?? 0) ?> visitas</span>
            <?php endif; ?>
        </div>

        <!-- Botones -->
        <div class="cl-card-actions">
            <a href="<?= \yii\helpers\Url::to(['/site/index', 'user' => $chef['user_id']]) ?>"
               class="cl-btn cl-btn--primary">
                Ver perfil
            </a>
            <a href="<?= \yii\helpers\Url::to(['/recipe/index', 'user_id' => $chef['user_id']]) ?>"
               class="cl-btn cl-btn--outline">
                Sus recetas
            </a>
        </div>

    </div><!-- /.cl-card-body -->

</div><!-- /.cl-card -->
