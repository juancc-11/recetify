<?php
/** @var yii\web\View $this */
use yii\helpers\Html;

$this->title = 'Política de Privacidad';
$this->params['meta_description'] = 'Conoce cómo Recetify Lab protege tu privacidad y maneja tus datos personales.';
$this->registerCssFile('@web/css/privacidad.css');
$fecha = '7 de julio de 2025';
?>

<div class="legal-wrapper">

    <div class="legal-hero">
        <span class="legal-icon">🔒</span>
        <h1>Política de Privacidad</h1>
        <p class="legal-subtitle">Última actualización: <?= $fecha ?></p>
    </div>

    <div class="legal-card">

        <div class="legal-intro">
            <p>En <strong>Recetify Lab</strong> nos tomamos muy en serio la privacidad de nuestros usuarios. Esta política explica qué datos recopilamos, cómo los usamos y qué derechos tienes sobre ellos. Al usar nuestra plataforma, aceptas las prácticas descritas aquí.</p>
        </div>

        <nav class="legal-toc">
            <p class="toc-title">Contenido</p>
            <ol>
                <li><a href="#datos">Datos que recopilamos</a></li>
                <li><a href="#uso">Cómo usamos tus datos</a></li>
                <li><a href="#almacenamiento">Almacenamiento y seguridad</a></li>
                <li><a href="#imagenes">Imágenes y contenido</a></li>
                <li><a href="#cookies">Cookies</a></li>
                <li><a href="#terceros">Servicios de terceros</a></li>
                <li><a href="#derechos">Tus derechos</a></li>
                <li><a href="#menores">Menores de edad</a></li>
                <li><a href="#cambios">Cambios a esta política</a></li>
                <li><a href="#contacto">Contacto</a></li>
            </ol>
        </nav>

        <section class="legal-section" id="datos">
            <h2><span class="section-num">1</span> Datos que recopilamos</h2>
            <p>Cuando te registras o usas Recetify Lab podemos recopilar los siguientes datos:</p>
            <ul>
                <li><strong>Datos de cuenta:</strong> nombre de usuario, dirección de correo electrónico y contraseña (almacenada en forma cifrada).</li>
                <li><strong>Imagen de perfil:</strong> foto que subes voluntariamente como avatar.</li>
                <li><strong>Contenido publicado:</strong> recetas, descripciones, imágenes de recetas, etiquetas y comentarios que publicas en la plataforma.</li>
                <li><strong>Datos de uso:</strong> visitas a recetas, interacciones (guardados, favoritos, historial) y reportes enviados.</li>
                <li><strong>Datos técnicos:</strong> dirección IP, tipo de navegador y sistema operativo, obtenidos automáticamente para garantizar el funcionamiento del servicio.</li>
            </ul>
        </section>

        <section class="legal-section" id="uso">
            <h2><span class="section-num">2</span> Cómo usamos tus datos</h2>
            <p>Utilizamos la información recopilada para:</p>
            <ul>
                <li>Crear y gestionar tu cuenta de usuario.</li>
                <li>Mostrar tus recetas publicadas a otros usuarios.</li>
                <li>Calcular estadísticas de visitas y popularidad de tus recetas.</li>
                <li>Enviarte mensajes del sistema (notificaciones, advertencias o confirmaciones).</li>
                <li>Mantener la seguridad de la plataforma y prevenir el abuso.</li>
                <li>Mejorar la experiencia de usuario y el funcionamiento de la plataforma.</li>
            </ul>
            <div class="legal-note">
                <span>📌</span>
                <p>Nunca vendemos ni compartimos tus datos personales con terceros para fines publicitarios.</p>
            </div>
        </section>

        <section class="legal-section" id="almacenamiento">
            <h2><span class="section-num">3</span> Almacenamiento y seguridad</h2>
            <p>Tus datos se almacenan en servidores seguros. Las contraseñas se guardan usando algoritmos de hash criptográfico (bcrypt) y nunca en texto plano. Implementamos medidas técnicas razonables para proteger tu información contra accesos no autorizados, alteraciones o pérdida.</p>
            <p>Sin embargo, ningún sistema de seguridad es completamente infalible. Te recomendamos usar una contraseña segura y no compartirla con nadie.</p>
        </section>

        <section class="legal-section" id="imagenes">
            <h2><span class="section-num">4</span> Imágenes y contenido</h2>
            <p>Las imágenes que subes (avatar, portada de receta, imágenes adicionales) se almacenan en <strong>Cloudinary</strong>, un servicio de gestión de medios en la nube. Al subir una imagen aceptas que ésta sea procesada y almacenada en los servidores de Cloudinary bajo sus propios términos de servicio.</p>
            <p>Eres responsable de asegurarte de que las imágenes que publicas no infrinjan derechos de autor de terceros ni contengan material inapropiado.</p>
        </section>

        <section class="legal-section" id="cookies">
            <h2><span class="section-num">5</span> Cookies</h2>
            <p>Recetify Lab utiliza cookies de sesión esenciales para mantener tu sesión activa mientras navegas. Estas cookies se eliminan al cerrar el navegador. No utilizamos cookies de rastreo publicitario ni de análisis de comportamiento de terceros.</p>
        </section>

        <section class="legal-section" id="terceros">
            <h2><span class="section-num">6</span> Servicios de terceros</h2>
            <p>Recetify Lab integra los siguientes servicios externos:</p>
            <div class="thirds-grid">
                <div class="third-item">
                    <strong>Cloudinary</strong>
                    <span>Almacenamiento y procesamiento de imágenes</span>
                </div>
                <div class="third-item">
                    <strong>MySQL</strong>
                    <span>Base de datos de la plataforma</span>
                </div>
                <div class="third-item">
                    <strong>PHP / Yii2</strong>
                    <span>Servidor de aplicaciones</span>
                </div>
            </div>
            <p>Cada servicio externo tiene su propia política de privacidad. Te recomendamos revisarlas si tienes dudas sobre cómo procesan tus datos.</p>
        </section>

        <section class="legal-section" id="derechos">
            <h2><span class="section-num">7</span> Tus derechos</h2>
            <p>Como usuario de Recetify Lab tienes los siguientes derechos sobre tus datos:</p>
            <div class="rights-grid">
                <div class="right-item">
                    <span class="right-icon"></span>
                    <strong>Acceso</strong>
                    <p>Puedes consultar los datos asociados a tu cuenta en cualquier momento desde tu perfil.</p>
                </div>
                <div class="right-item">
                    <span class="right-icon"></span>
                    <strong>Rectificación</strong>
                    <p>Puedes actualizar tu nombre, correo, contraseña e imagen de perfil desde tu perfil.</p>
                </div>
                <div class="right-item">
                    <span class="right-icon"></span>
                    <strong>Eliminación</strong>
                    <p>Puedes eliminar tu cuenta permanentemente desde la configuración de perfil. Esto eliminará todos tus datos y recetas.</p>
                </div>
                <div class="right-item">
                    <span class="right-icon"></span>
                    <strong>Portabilidad</strong>
                    <p>Puedes solicitar una copia de tus datos contactándonos por correo electrónico.</p>
                </div>
            </div>
        </section>

        <section class="legal-section" id="menores">
            <h2><span class="section-num">8</span> Menores de edad</h2>
            <p>Recetify Lab no está dirigida a menores de 13 años. Si tienes conocimiento de que un menor nos ha proporcionado información personal sin consentimiento parental, contáctanos para eliminar esos datos.</p>
        </section>

        <section class="legal-section" id="cambios">
            <h2><span class="section-num">9</span> Cambios a esta política</h2>
            <p>Podemos actualizar esta Política de Privacidad ocasionalmente. Cuando lo hagamos, actualizaremos la fecha en la parte superior de esta página. Te recomendamos revisarla periódicamente. El uso continuado de la plataforma tras la publicación de cambios constituye tu aceptación de dichos cambios.</p>
        </section>

        <section class="legal-section" id="contacto">
            <h2><span class="section-num">10</span> Contacto</h2>
            <p>Si tienes preguntas, solicitudes o inquietudes sobre esta política de privacidad, puedes contactarnos en:</p>
            <div class="contact-box">
                <span>📧</span>
                <div>
                    <strong>Recetify Lab</strong>
                    <a href="mailto:privacidad@recetifylab.com">privacidad@recetifylab.com</a>
                </div>
            </div>
        </section>

    </div>

    <div class="legal-footer-links">
        <a href="<?= \yii\helpers\Url::to(['site/terms']) ?>">Términos de Uso</a>
        <span>·</span>
        <a href="<?= \yii\helpers\Url::to(['site/index']) ?>">Volver al inicio</a>
    </div>

</div>