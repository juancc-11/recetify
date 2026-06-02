<?php

/** @var yii\web\View $this */

$this->title = 'Sobre Nosotros';

$this->registerCssFile('@web/css/about.css');
$this->registerJsFile('@web/js/about.js', ['depends' => [\yii\web\JqueryAsset::class]]);

?>

<div class="about-container">

    <!-- HERO -->
    <section class="hero-section">

        <h1>Sobre Nosotros</h1>

        <p>
            Recetify Lab es una plataforma web creada para compartir recetas,
            interactuar con otros usuarios y descubrir nuevas experiencias culinarias.
        </p>

    </section>

    <section class="team-section">

    <div class="team-title">
        <h2>Nuestro Equipo</h2>
    </div>

    <div class="carousel">

        <button class="nav prev" onclick="prevSlide()">❮</button>

        <div class="carousel-content">

            <div class="member-card active">

                <img src="<?= Yii::getAlias('@web/img/aboutUs/persona1.png') ?>">

                <div class="member-info">
                    <h3>Juan Chen</h3>
                    <h4>Frontend Developer</h4>

                    <p>
                        Encargado del diseño visual y experiencia de usuario
                        de la plataforma.
                    </p>
                </div>

            </div>

            <div class="member-card">

                <img src="<?= Yii::getAlias('@web/img/aboutUs/persona2.png') ?>">

                <div class="member-info">
                    <h3>Kaisy Casasola</h3>
                    <h4>Backend Developer</h4>

                    <p>
                        Responsable de la lógica del sistema y conexión
                        con base de datos.
                    </p>
                </div>

            </div>

            <div class="member-card">

                <img src="<?= Yii::getAlias('@web/img/aboutUs/persona3.png') ?>">

                <div class="member-info">
                    <h3>Andrew Acosta</h3>
                    <h4>Database Manager</h4>

                    <p>
                        Encargado de la administración y estructura
                        de la base de datos.
                    </p>
                </div>

            </div>

            <div class="member-card">

                <img src="<?= Yii::getAlias('@web/img/aboutUs/persona4.png') ?>">

                <div class="member-info">
                    <h3>Compañero 4</h3>
                    <h4>Project Manager</h4>

                    <p>
                        Responsable de coordinar y supervisar
                        el desarrollo del proyecto.
                    </p>
                </div>

            </div>

        </div>

        <button class="nav next" onclick="nextSlide()">❯</button>

    </div>

</section>

</div>
