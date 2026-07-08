<?php
/** @var yii\web\View $this */
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Términos de Uso';
$this->params['meta_description'] = 'Lee los Términos de Uso de Recetify Lab antes de usar la plataforma.';
$this->registerCssFile('@web/css/terminos.css');
$fecha = '7 de julio de 2025';
?>

<div class="legal-wrapper">

    <div class="legal-hero">
        <span class="legal-icon"></span>
        <h1>Términos de Uso</h1>
        <p class="legal-subtitle">Última actualización: <?= $fecha ?></p>
    </div>

    <div class="legal-card">

        <div class="legal-intro">
            <p>Bienvenido a <strong>Recetify Lab</strong>. Al acceder o utilizar nuestra plataforma, aceptas cumplir con los siguientes Términos de Uso. Si no estás de acuerdo con alguno de ellos, por favor no uses el servicio.</p>
        </div>

        <nav class="legal-toc">
            <p class="toc-title">Contenido</p>
            <ol>
                <li><a href="#servicio">Descripción del servicio</a></li>
                <li><a href="#cuenta">Registro y cuenta</a></li>
                <li><a href="#contenido">Contenido del usuario</a></li>
                <li><a href="#prohibido">Conductas prohibidas</a></li>
                <li><a href="#propiedad">Propiedad intelectual</a></li>
                <li><a href="#moderacion">Moderación y sanciones</a></li>
                <li><a href="#disponibilidad">Disponibilidad del servicio</a></li>
                <li><a href="#responsabilidad">Limitación de responsabilidad</a></li>
                <li><a href="#cancelacion">Cancelación de cuenta</a></li>
                <li><a href="#cambios">Cambios a estos términos</a></li>
                <li><a href="#contacto">Contacto</a></li>
            </ol>
        </nav>

        <section class="legal-section" id="servicio">
            <h2><span class="section-num">1</span> Descripción del servicio</h2>
            <p>Recetify Lab es una plataforma web para compartir, descubrir y organizar recetas de cocina. Los usuarios pueden crear una cuenta gratuita para publicar sus propias recetas, comentar las de otros, guardar favoritos y acceder a estadísticas de sus publicaciones.</p>
        </section>

        <section class="legal-section" id="cuenta">
            <h2><span class="section-num">2</span> Registro y cuenta</h2>
            <p>Para publicar recetas y acceder a funciones personalizadas debes crear una cuenta. Al registrarte te comprometes a:</p>
            <ul>
                <li>Proporcionar información veraz, completa y actualizada.</li>
                <li>Mantener la confidencialidad de tu contraseña.</li>
                <li>No compartir tu cuenta con terceros.</li>
                <li>Notificarnos inmediatamente si sospechas de un acceso no autorizado a tu cuenta.</li>
            </ul>
            <p>Eres responsable de todas las actividades realizadas desde tu cuenta.</p>
            <div class="legal-note">
                <span>📌</span>
                <p>Nos reservamos el derecho de rechazar el registro o cancelar cuentas que violen estos términos.</p>
            </div>
        </section>

        <section class="legal-section" id="contenido">
            <h2><span class="section-num">3</span> Contenido del usuario</h2>
            <p>Al publicar recetas, comentarios o imágenes en Recetify Lab:</p>
            <ul>
                <li>Declaras ser el autor o tener los derechos necesarios para publicar dicho contenido.</li>
                <li>Otorgas a Recetify Lab una licencia no exclusiva para mostrar y distribuir tu contenido dentro de la plataforma.</li>
                <li>Conservas la propiedad intelectual de tu contenido original.</li>
                <li>Aceptas que el contenido publicado puede ser visible para todos los usuarios de la plataforma.</li>
            </ul>
        </section>

        <section class="legal-section" id="prohibido">
            <h2><span class="section-num">4</span> Conductas prohibidas</h2>
            <p>Está estrictamente prohibido en Recetify Lab:</p>
            <div class="prohibited-grid">
                <div class="prohibited-item">
                    <span>🚫</span>
                    <p>Publicar contenido ofensivo, violento, discriminatorio o inapropiado.</p>
                </div>
                <div class="prohibited-item">
                    <span>🚫</span>
                    <p>Copiar recetas de otros usuarios o fuentes externas sin atribución adecuada (plagio).</p>
                </div>
                <div class="prohibited-item">
                    <span>🚫</span>
                    <p>Publicar spam, publicidad no autorizada o contenido irrelevante.</p>
                </div>
                <div class="prohibited-item">
                    <span>🚫</span>
                    <p>Acosar, amenazar o intimidar a otros usuarios.</p>
                </div>
                <div class="prohibited-item">
                    <span>🚫</span>
                    <p>Crear múltiples cuentas para evadir sanciones.</p>
                </div>
                <div class="prohibited-item">
                    <span>🚫</span>
                    <p>Intentar acceder sin autorización a cuentas de otros usuarios o al sistema.</p>
                </div>
                <div class="prohibited-item">
                    <span>🚫</span>
                    <p>Publicar información falsa o engañosa sobre recetas o ingredientes.</p>
                </div>
                <div class="prohibited-item">
                    <span>🚫</span>
                    <p>Usar la plataforma para actividades ilegales de cualquier tipo.</p>
                </div>
            </div>
        </section>

        <section class="legal-section" id="propiedad">
            <h2><span class="section-num">5</span> Propiedad intelectual</h2>
            <p>El nombre <strong>Recetify Lab</strong>, el logotipo, el diseño visual, el código fuente y los elementos propios de la plataforma son propiedad de sus creadores y están protegidos por las leyes de propiedad intelectual aplicables.</p>
            <p>Queda prohibida la reproducción, distribución o modificación de cualquier elemento de la plataforma sin autorización expresa por escrito.</p>
        </section>

        <section class="legal-section" id="moderacion">
            <h2><span class="section-num">6</span> Moderación y sanciones</h2>
            <p>Recetify Lab cuenta con un equipo de administración que puede:</p>
            <ul>
                <li>Revisar reportes enviados por usuarios sobre contenido inapropiado.</li>
                <li>Ocultar o eliminar recetas o comentarios que violen estos términos.</li>
                <li>Enviar advertencias formales a los usuarios infractores.</li>
                <li>Suspender o eliminar permanentemente cuentas por infracciones graves o reincidentes.</li>
            </ul>
            <div class="legal-note warn">
                <span>⚠️</span>
                <p>Las decisiones de moderación son definitivas. Si consideras que una sanción fue injusta, puedes contactarnos para revisión.</p>
            </div>
        </section>

        <section class="legal-section" id="disponibilidad">
            <h2><span class="section-num">7</span> Disponibilidad del servicio</h2>
            <p>Nos esforzamos por mantener Recetify Lab disponible en todo momento. Sin embargo, podemos suspender temporalmente el servicio para mantenimiento, actualizaciones o por causas fuera de nuestro control. No garantizamos disponibilidad ininterrumpida del servicio.</p>
        </section>

        <section class="legal-section" id="responsabilidad">
            <h2><span class="section-num">8</span> Limitación de responsabilidad</h2>
            <p>Recetify Lab no se hace responsable de:</p>
            <ul>
                <li>Daños derivados del uso o la imposibilidad de uso del servicio.</li>
                <li>La exactitud, seguridad o calidad de las recetas publicadas por usuarios.</li>
                <li>Pérdida de datos causada por interrupciones del servicio o errores técnicos.</li>
                <li>Contenido de sitios externos enlazados desde la plataforma.</li>
            </ul>
            <p>El uso de las recetas publicadas en la plataforma es bajo la responsabilidad exclusiva del usuario. Siempre verifica los ingredientes ante posibles alergias o restricciones alimentarias.</p>
        </section>

        <section class="legal-section" id="cancelacion">
            <h2><span class="section-num">9</span> Cancelación de cuenta</h2>
            <p>Puedes eliminar tu cuenta en cualquier momento desde la configuración de tu perfil. Al eliminar tu cuenta:</p>
            <ul>
                <li>Todos tus datos personales serán eliminados de forma permanente.</li>
                <li>Tus recetas publicadas serán eliminadas de la plataforma.</li>
                <li>Esta acción es irreversible y no podrás recuperar tu contenido.</li>
            </ul>
        </section>

        <section class="legal-section" id="cambios">
            <h2><span class="section-num">10</span> Cambios a estos términos</h2>
            <p>Podemos modificar estos Términos de Uso en cualquier momento. Los cambios entrarán en vigor al publicarse en esta página. El uso continuado de Recetify Lab tras la publicación de nuevos términos implica tu aceptación de los mismos. Te recomendamos revisar esta página periódicamente.</p>
        </section>

        <section class="legal-section" id="contacto">
            <h2><span class="section-num">11</span> Contacto</h2>
            <p>Si tienes preguntas o inquietudes sobre estos Términos de Uso, puedes contactarnos en:</p>
            <div class="contact-box">
                <span>📧</span>
                <div>
                    <strong>Recetify Lab</strong>
                    <a href="mailto:legal@recetifylab.com">legal@recetifylab.com</a>
                </div>
            </div>
        </section>

    </div>

    <div class="legal-footer-links">
        <a href="<?= Url::to(['site/privacy']) ?>">Política de Privacidad</a>
        <span>·</span>
        <a href="<?= Url::to(['site/index']) ?>">Volver al inicio</a>
    </div>

</div>