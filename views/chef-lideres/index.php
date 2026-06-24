<?php
/** @var yii\web\View $this */
/** @var array $mejoresCalificados */
/** @var array $mejoresDelMes */
/** @var array $mejoresDeLaSemana */

use yii\helpers\Html;
use app\assets\ChefLideresAsset;

ChefLideresAsset::register($this);

$this->title = 'Chef Líderes';
?>

<div class="chef-lideres-page">

    <div class="cl-page-header">
        <h1 class="cl-page-title">
            <span class="cl-trophy-icon">🏆</span> Chef Líderes
        </h1>
        <p class="cl-page-subtitle">Los mejores chefs de la comunidad Recetify Lab</p>
    </div>

    <?= $this->render('_seccion', [
        'titulo'    => 'Mejores Calificados',
        'subtitulo' => 'Chefs con el mayor promedio de puntuación en sus recetas',
        'icono'     => '⭐',
        'chefs'     => $mejoresCalificados,
        'id'        => 'calificados',
        'metrica'   => 'score',
    ]) ?>

    <?= $this->render('_seccion', [
        'titulo'    => 'Mejores del Mes',
        'subtitulo' => 'Los chefs más visitados durante ' . date('F Y'),
        'icono'     => '📅',
        'chefs'     => $mejoresDelMes,
        'id'        => 'mes',
        'metrica'   => 'views',
    ]) ?>

    <?= $this->render('_seccion', [
        'titulo'    => 'Mejores de la Semana',
        'subtitulo' => 'Los chefs con más actividad esta semana',
        'icono'     => '🔥',
        'chefs'     => $mejoresDeLaSemana,
        'id'        => 'semana',
        'metrica'   => 'views',
    ]) ?>

</div>
