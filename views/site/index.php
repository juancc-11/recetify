<?php

/** @var yii\web\View $this */

$this->title = 'Recetify Lab';

$this->registerCssFile('@web/css/index.css');

?>

<div class="index-container">

    <!-- IZQUIERDA -->
    <div class="box-1">

        <!-- HEADER -->
        <div class="header">

            <img
                src="<?= Yii::getAlias('@web/img/logos/logo_kiwi.png') ?>"
                class="logo"
                alt="Logo"
            >

            <h1 class="title">
                Recetify Lab
            </h1>

        </div>


        <!-- BUSCADOR -->
<div class="search-container">

    <form
        action="/blogrecetas/web/index.php"
        method="GET"
        class="search-form"
    >

        <!-- RUTA -->
        <input
            type="hidden"
            name="r"
            value="recipe/search"
        >

        <!-- TEXTO -->
        <input
            type="text"
            name="q"
            placeholder="Buscar recetas..."
            class="search-input"
        >

        <!-- BOTÓN -->
        <button
            type="submit"
            class="btn-search"
        >
            Buscar
        </button>

    </form>

</div>

        <!-- BOTONES -->
        <div class="buttons">

            <button class="btn-outline">
                Explorar
            </button>

            <button class="btn-filled">
                Ranking
            </button>

        </div>

    </div>

    <!-- DERECHA -->
    <div class="box-2">

        <h2>
            Sobre la plataforma
        </h2>

        <p>
            Descubre recetas creadas por la comunidad.
        </p>

    </div>

</div>