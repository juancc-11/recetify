<?php

/** @var yii\web\View $this */

use yii\helpers\Url;

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
                action="<?= Url::to(['/recipe/search']) ?>"
                method="GET"
                class="search-form"
            >

                <input
                    type="text"
                    name="q"
                    placeholder="Buscar recetas..."
                    class="search-input"
                    required
                >

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
