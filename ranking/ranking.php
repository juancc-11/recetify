<?php
/** @var yii\web\View $this */
/** @var array $recetas */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Ranking de Recetas';

$this->registerCssFile('@web/css/ranking.css');
$this->registerJsFile('@web/js/ranking.js', ['depends' => [\yii\web\JqueryAsset::class]]);
?>

<section class="rl-ranking">
    <div class="rl-ranking-header">
        <h2><i class="fa fa-trophy"></i> Ranking de Recetas</h2>
        <p>Las recetas mejor calificadas por la comunidad</p>
    </div>

    <div class="rl-ranking-list">
        <?php if (empty($recetas)): ?>
            <p class="rl-ranking-empty">Todavía no hay recetas con calificaciones suficientes.</p>
        <?php else: ?>
            <?php foreach ($recetas as $index => $receta): ?>
                <div class="rl-ranking-item">
                    <div class="rl-ranking-position pos-<?= $index + 1 ?>">
                        <?= $index + 1 ?>
                    </div>

                    <div class="rl-ranking-img">
                        <img src="<?= Html::encode($receta['imagen']) ?>" alt="<?= Html::encode($receta['nombre']) ?>">
                    </div>

                    <div class="rl-ranking-info">
                        <h4><?= Html::encode($receta['nombre']) ?></h4>
                        <span class="rl-ranking-autor">por <?= Html::encode($receta['autor']) ?></span>
                    </div>

                    <div class="rl-ranking-score">
                        <div class="rl-stars" data-score="<?= $receta['puntuacion'] ?>">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="fa fa-star <?= $i <= round($receta['puntuacion']) ? 'active' : '' ?>"></i>
                            <?php endfor; ?>
                        </div>
                        <span class="rl-votos"><?= $receta['votos'] ?> votos</span>
                    </div>

                    <a href="<?= Url::to(['recipe/search', 'q' => $receta['nombre']]) ?>" class="rl-ranking-btn">
                        Ver receta
                    </a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>