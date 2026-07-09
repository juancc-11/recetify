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
            Recetify Lab nació con una idea simple: que cualquier persona,
            sin importar su nivel en la cocina, pueda compartir lo que sabe hacer
            y aprender de los demás. Somos una plataforma web dedicada al mundo
            culinario, donde los usuarios pueden publicar sus propias recetas,
            descubrir creaciones de otros chefs de la comunidad, guardar sus favoritas
            y dejar reseñas con calificaciones para ayudar a otros a elegir qué cocinar.
        </p>

        <p>
            Creemos que la cocina es una forma de expresión, cultura e identidad.
            Por eso diseñamos Recetify Lab como un espacio donde cada receta cuenta
            una historia, ya sea la pizza casera de todos los viernes o ese platillo
            tradicional que aprendiste de tu abuela. Aquí todas las recetas tienen valor.
        </p>

    </section>

        <!-- QUÉ OFRECEMOS -->
    <section class="hero-section">

        <h1>¿Qué ofrecemos?</h1>

        <p>
            Recetify Lab va más allá de ser un simple recetario. Contamos con un
            sistema completo donde los usuarios registrados pueden crear y publicar
            sus recetas con imágenes, descripción detallada y lista de utensilios.
            Cada receta puede ser calificada y comentada por la comunidad, lo que
            permite destacar las mejores preparaciones de forma orgánica.
        </p>

        <p>
            Además, los usuarios pueden guardar recetas en su colección personal,
            marcarlas como favoritas y llevar un historial de todo lo que han leído.
            Para quienes destacan más, contamos con la sección de
            <strong>Chefs Líderes</strong>, donde reconocemos a los creadores con
            mejor calificación, más visitas del mes y más visitas de la semana.
        </p>

        <p>
            La plataforma también cuenta con un panel de administración donde se
            gestionan reportes de contenido inapropiado, se moderan usuarios y se
            mantiene la comunidad en un ambiente seguro y respetuoso para todos.
        </p>

    </section>

    <!-- NUESTRA MISIÓN -->
    <section class="hero-section">

        <h1>Nuestra Misión</h1>

        <p>
            Nuestra misión es construir la comunidad culinaria más activa y
            accesible de habla hispana. Queremos que Recetify Lab sea el lugar
            al que acudas cuando no sabes qué cocinar, cuando quieres sorprender
            a alguien especial o cuando simplemente deseas compartir ese platillo
            del que todos te piden la receta.
        </p>

        <p>
            Trabajamos constantemente para mejorar la experiencia de cada usuario,
            agregando nuevas funcionalidades, mejorando el rendimiento y escuchando
            las sugerencias de nuestra comunidad. Recetify Lab está en constante
            crecimiento, igual que la pasión de quienes lo usan.
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

                <img src="<?= Yii::getAlias('@web/img/aboutUs/juan.jpeg') ?>">

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

                <img src="<?= Yii::getAlias('@web/img/aboutUs/keisy.png') ?>">

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

                <img src="<?= Yii::getAlias('@web/img/aboutUs/andrew.jpeg') ?>">

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

                <img src="<?= Yii::getAlias('@web/img/aboutUs/yari.jpeg') ?>">

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

</div>
