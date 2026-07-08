<?php
/** @var yii\web\View $this */
use yii\helpers\Url;

$this->title = 'Recetify Lab';
$this->registerCssFile('@web/css/index.css');
?>

<div class="index-container">

    <!-- HERO -->
    <div class="box-1">

        <div class="header">

            <!-- Fila: logo + título alineados por la parte inferior -->
            <div class="header-row">
                <img src="<?= Yii::getAlias('@web/img/logos/logo_kiwi.png') ?>"
                     class="logo" alt="Logo Recetify Lab">
                <h1 class="title">Recetify <span>Lab</span></h1>
            </div>

        </div>

        <!-- BUSCADOR UNIFICADO -->
        <div class="search-container">
            <form action="<?= Url::to(['/recipe/search']) ?>" method="GET" class="search-form">
                <input type="text" name="q" placeholder="Buscar recetas..." class="search-input">
                <button type="submit" class="btn-search">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <span>Buscar</span>
                </button>
            </form>
        </div>
                      <!-- Tagline debajo de ambos, centrado -->
        <p class="tagline">Descubre, crea y comparte recetas de cocina con la comunidad.</p>

    </div>

    <!-- SOBRE LA PLATAFORMA -->
    <div class="box-2">
        <h2>Sobre la plataforma</h2>
        <p>
            Recetify Lab es una plataforma comunitaria diseñada para amantes de la cocina de todos los niveles.
            Aquí puedes explorar cientos de recetas creadas y compartidas por chefs aficionados y apasionados
            culinarios de todo el mundo. Cada receta incluye ingredientes, utensilios, pasos detallados e imágenes
            para que puedas seguirla con facilidad desde tu cocina.
        </p>
        <p>
            Crea tu perfil, publica tus propias recetas, guarda tus favoritas, deja reseñas y descubre qué están
            cocinando los chefs más populares de la comunidad. Con Recetify Lab, la cocina se convierte en una
            experiencia compartida donde cada plato tiene una historia que contar.
        </p>

    </div>

</div>