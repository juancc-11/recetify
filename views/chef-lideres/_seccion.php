<?php
/**
 * Partial: _seccion.php
 *
 * @var string $titulo
 * @var string $subtitulo
 * @var string $icono
 * @var array  $chefs        Arreglo de hasta 3 chefs
 * @var string $id           ID único de la sección (calificados | mes | semana)
 * @var string $metrica      'score' | 'views'
 */

use yii\helpers\Html;

$carouselId = 'carousel-' . $id;
?>

<section class="cl-seccion" id="seccion-<?= Html::encode($id) ?>">

    <div class="cl-seccion-header">
        <h2 class="cl-seccion-titulo">
            <span><?= $icono ?></span> <?= Html::encode($titulo) ?>
        </h2>
        <p class="cl-seccion-subtitulo"><?= Html::encode($subtitulo) ?></p>
    </div>

    <div class="cl-card-outer">

        <?php if (empty($chefs)): ?>
            <div class="cl-empty">
                <span class="cl-empty-icon">🍽️</span>
                <p>Aún no hay datos suficientes para esta categoría.</p>
            </div>

        <?php else: ?>

            <!-- Carrusel -->
            <div class="cl-carousel" id="<?= $carouselId ?>">

                <!-- Track con las cards -->
                <div class="cl-carousel-track" data-carousel="<?= $carouselId ?>">
                    <?php foreach ($chefs as $pos => $chef): ?>
                        <?= $this->render('_card', [
                            'chef'    => $chef,
                            'pos'     => $pos + 1,
                            'metrica' => $metrica,
                        ]) ?>
                    <?php endforeach; ?>
                </div>

                <!-- Controles (solo visibles si hay más de 1 chef) -->
                <?php if (count($chefs) > 1): ?>
                    <button class="cl-carousel-btn cl-carousel-prev"
                            data-target="<?= $carouselId ?>"
                            aria-label="Anterior">
                        &#8249;
                    </button>
                    <button class="cl-carousel-btn cl-carousel-next"
                            data-target="<?= $carouselId ?>"
                            aria-label="Siguiente">
                        &#8250;
                    </button>

                    <!-- Indicadores / dots -->
                    <div class="cl-carousel-dots" data-dots="<?= $carouselId ?>">
                        <?php foreach ($chefs as $i => $_): ?>
                            <button class="cl-dot <?= $i === 0 ? 'cl-dot--active' : '' ?>"
                                    data-index="<?= $i ?>"
                                    data-carousel="<?= $carouselId ?>"
                                    aria-label="Ir al chef <?= $i + 1 ?>">
                            </button>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

            </div><!-- /.cl-carousel -->

        <?php endif; ?>

    </div><!-- /.cl-card-outer -->

</section>
