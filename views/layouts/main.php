<?php

/** @var yii\web\View $this */
/** @var string $content */

use app\assets\AppAsset;
use app\widgets\Alert;
use yii\bootstrap5\Breadcrumbs;
use yii\bootstrap5\Html;

AppAsset::register($this);

$this->registerCsrfMetaTags();

$this->registerCssFile('@web/css/menus.css?v=6', [
    'position' => \yii\web\View::POS_HEAD,
]);

$this->registerJsFile('@web/js/index.js', [
    'depends' => [\yii\web\JqueryAsset::class],
    'position' => \yii\web\View::POS_END,
]);

$this->registerMetaTag(['charset' => Yii::$app->charset], 'charset');
$this->registerMetaTag(['name' => 'viewport', 'content' => 'width=device-width, initial-scale=1']);
$this->registerMetaTag(['name' => 'base-url', 'content' => Yii::$app->request->baseUrl]);
$this->registerLinkTag(['rel' => 'icon', 'href' => Yii::getAlias('@web/img/logos/logo_kiwi.png')]);

// ── SEO META TAGS ──────────────────────────────
$seoTitle       = $this->title
    ? Html::encode($this->title) . ' | Recetify Lab'
    : 'Recetify Lab — Descubre y comparte recetas de cocina';

$seoDescription = $this->params['meta_description']
    ?? 'Recetify Lab — Descubre, crea y comparte recetas de cocina. Explora recetas de chefs, guarda tus favoritas y publica las tuyas.';

$seoImage = $this->params['meta_image']
    ?? 'https://recetifylab.gzgroup.dev/img/logos/logo_kiwi.png';

$seoUrl = 'https://recetifylab.gzgroup.dev' . Yii::$app->request->url;

$this->registerMetaTag(['name' => 'description', 'content' => $seoDescription]);
$this->registerMetaTag(['name' => 'robots',      'content' => 'index, follow']);
$this->registerMetaTag(['name' => 'author',      'content' => 'Recetify Lab']);
$this->registerMetaTag(['property' => 'og:type',        'content' => 'website']);
$this->registerMetaTag(['property' => 'og:site_name',   'content' => 'Recetify Lab']);
$this->registerMetaTag(['property' => 'og:title',       'content' => $seoTitle]);
$this->registerMetaTag(['property' => 'og:description', 'content' => $seoDescription]);
$this->registerMetaTag(['property' => 'og:image',       'content' => $seoImage]);
$this->registerMetaTag(['property' => 'og:url',         'content' => $seoUrl]);
$this->registerMetaTag(['property' => 'og:locale',      'content' => 'es_ES']);
$this->registerMetaTag(['name' => 'twitter:card',        'content' => 'summary_large_image']);
$this->registerMetaTag(['name' => 'twitter:title',       'content' => $seoTitle]);
$this->registerMetaTag(['name' => 'twitter:description', 'content' => $seoDescription]);
$this->registerMetaTag(['name' => 'twitter:image',       'content' => $seoImage]);
$this->registerLinkTag(['rel' => 'canonical', 'href' => $seoUrl]);
?>

<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<head>
    <title><?= $seoTitle ?></title>
    <?php $this->head() ?>
</head>

<body class="rl-body">
<?php $this->beginBody() ?>

<!-- NAVBAR -->
<nav class="rl-navbar">

    <!-- IZQUIERDA -->
    <div class="rl-nav-left">
        <button id="rl-sidebar-toggle" class="rl-nav-btn" title="Menú">☰</button>

        <?php if (!in_array(Yii::$app->controller->action->id, ['index', 'login', 'register', 'admin-login'])): ?>
            <img src="<?= Yii::getAlias('@web/img/logos/logo_kiwi.png') ?>" class="rl-nav-logo" alt="Logo">
            <span class="rl-nav-brand">Recetify Lab</span>
        <?php endif; ?>
    </div>

    <!-- CENTRO: buscador -->
    <?php if (!in_array(Yii::$app->controller->action->id, ['index', 'login', 'register', 'admin-login'])): ?>
        <form action="<?= yii\helpers\Url::to(['/recipe/search']) ?>" method="GET" class="rl-nav-search">
            <input type="text" name="q" class="rl-search-input" placeholder="Buscar recetas..."
                   value="<?= Html::encode(Yii::$app->request->get('q', '')) ?>">
            <button type="submit" class="rl-search-btn">
                <i class="fa-solid fa-magnifying-glass"></i>
            </button>
        </form>
    <?php endif; ?>

    <!-- DERECHA -->
    <div class="rl-nav-right">

        <?php if (!Yii::$app->user->isGuest): ?>
        <!-- NOTIFICACIONES -->
        <div class="rl-notif-wrap" id="rl-notif-wrap">
            <button class="rl-nav-btn rl-notif-btn" id="rl-notif-toggle" title="Notificaciones">
                <i class="fa-solid fa-bell"></i>
                <?php
                $unreadCount = (int) Yii::$app->db->createCommand(
                    'SELECT COUNT(*) FROM messages WHERE receiver_id = :uid AND is_read = 0',
                    [':uid' => Yii::$app->user->id]
                )->queryScalar();
                ?>
                <?php if ($unreadCount > 0): ?>
                    <span class="rl-notif-badge"><?= $unreadCount > 99 ? '99+' : $unreadCount ?></span>
                <?php endif; ?>
            </button>

            <div id="rl-notif-menu" class="rl-notif-menu rl-hidden">
                <div class="rl-notif-header">
                    <span class="rl-notif-title">Notificaciones</span>
                    <?php if ($unreadCount > 0): ?>
                        <button class="rl-notif-mark-all" id="rl-mark-all-read">
                            Marcar todas como leídas
                        </button>
                    <?php endif; ?>
                </div>
                <div class="rl-notif-list" id="rl-notif-list">
                    <?php
                    $messages = Yii::$app->db->createCommand(
                        'SELECT id, tipo, asunto, cuerpo, is_read, created_at
                         FROM messages
                         WHERE receiver_id = :uid
                         ORDER BY created_at DESC
                         LIMIT 20',
                        [':uid' => Yii::$app->user->id]
                    )->queryAll();
                    ?>
                    <?php if (empty($messages)): ?>
                        <div class="rl-notif-empty">
                            <i class="fa-regular fa-bell-slash"></i>
                            <p>Sin notificaciones</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($messages as $msg): ?>
                        <div class="rl-notif-item <?= $msg['is_read'] ? '' : 'unread' ?>"
                             data-id="<?= $msg['id'] ?>"
                             data-asunto="<?= Html::encode($msg['asunto']) ?>"
                             data-cuerpo="<?= Html::encode($msg['cuerpo']) ?>"
                             data-tipo="<?= Html::encode($msg['tipo']) ?>"
                             data-fecha="<?= Html::encode($msg['created_at']) ?>">
                            <div class="rl-notif-icon <?= $msg['tipo'] ?>">
                                <?php if ($msg['tipo'] === 'notificacion'): ?>
                                    <i class="fa-solid fa-circle-info"></i>
                                <?php elseif ($msg['tipo'] === 'advertencia'): ?>
                                    <i class="fa-solid fa-triangle-exclamation"></i>
                                <?php else: ?>
                                    <i class="fa-solid fa-ban"></i>
                                <?php endif; ?>
                            </div>
                            <div class="rl-notif-body">
                                <p class="rl-notif-asunto"><?= Html::encode($msg['asunto']) ?></p>
                                <p class="rl-notif-preview"><?= Html::encode(mb_strimwidth($msg['cuerpo'], 0, 60, '…')) ?></p>
                                <span class="rl-notif-fecha"><?= Yii::$app->formatter->asRelativeTime($msg['created_at']) ?></span>
                            </div>
                            <?php if (!$msg['is_read']): ?>
                                <span class="rl-notif-dot"></span>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- AVATAR -->
        <?php if (Yii::$app->user->isGuest): ?>
            <img id="rl-menu-toggle"
                 src="<?= Yii::getAlias('@web/img/logos/logo_kiwi.png') ?>"
                 class="rl-nav-avatar" alt="Cuenta">
        <?php else: ?>
            <img id="rl-menu-toggle"
                 src="<?= Yii::$app->user->identity->avatar_url ?: Yii::getAlias('@web/img/logos/logo_kiwi.png') ?>"
                 class="rl-nav-avatar" alt="Avatar">
        <?php endif; ?>

        <!-- CONFIGURACIÓN -->
        <div class="rl-config-wrap" id="rl-config-wrap">
            <button class="rl-nav-btn" id="rl-config-toggle" title="Configuración">
                <i class="fa-solid fa-gear"></i>
            </button>
            <div id="rl-config-menu" class="rl-config-menu rl-hidden">
                <p class="rl-menu-title">Configuración</p>

                <?php if (!Yii::$app->user->isGuest): ?>
                <a href="<?= Yii::$app->urlManager->createUrl(['site/profile']) ?>">
                    <i class="fa-solid fa-user-pen"></i> Editar perfil
                </a>
                
                <a href="<?= Yii::$app->urlManager->createUrl(['site/about']) ?>">
                    <i class="fa-solid fa-circle-info"></i> Nosotros
                </a>

                
                <?php endif; ?>

                <div class="rl-config-divider"></div>

                <a href="<?= Yii::$app->urlManager->createUrl(['site/privacy']) ?>">
                <i class="fa-solid fa-shield-halved"></i> Privacidad
                </a>
                <a href="<?= Yii::$app->urlManager->createUrl(['site/terms']) ?>">
                <i class="fa-solid fa-file-lines"></i> Términos de uso
                </a>

                <div class="rl-config-row">
                    <span><i class="fa-solid fa-moon"></i> Tema oscuro</span>
                    <label class="rl-switch-mini">
                        <input type="checkbox" id="rl-dark-toggle">
                        <span class="rl-switch-mini-slider"></span>
                    </label>
                </div>

                <div class="rl-config-divider"></div>

                <p style="margin:8px 8px 2px;font-size:11px;color:#adb5bd;text-align:center;">
                  Recetify Lab v1.0
                </p>
            </div>
        </div>

    </div>
</nav>

<!-- POPUP MENU (avatar) -->
<div id="rl-popup-menu" class="rl-popup-menu rl-hidden">
    <?php if (Yii::$app->user->isGuest): ?>
        <p class="rl-menu-title">Mi cuenta</p>
        <a href="<?= Yii::$app->urlManager->createUrl(['site/login']) ?>">
            <i class="fa-solid fa-right-to-bracket"></i> Iniciar sesión
        </a>
        <a href="<?= Yii::$app->urlManager->createUrl(['site/register']) ?>">
            <i class="fa-solid fa-user-plus"></i> Registrarse
        </a>
    <?php else: ?>
        <div class="rl-menu-user">
            <img src="<?= Yii::$app->user->identity->avatar_url ?: Yii::getAlias('@web/img/default.png') ?>"
                 class="rl-menu-avatar" alt="Avatar">
            <p><?= Html::encode(Yii::$app->user->identity->username) ?></p>
        </div>
        <a href="<?= Yii::$app->urlManager->createUrl(['site/profile']) ?>">
            <i class="fa-solid fa-user"></i> Perfil
        </a>
        <?php if (Yii::$app->user->identity->rol === 'admin'): ?>
            <a href="<?= Yii::$app->urlManager->createUrl(['admin/dashboard']) ?>">
                <i class="fa-solid fa-shield-halved"></i> Panel Admin
            </a>
        <?php endif; ?>
        <form method="post" action="<?= Yii::$app->urlManager->createUrl(['site/logout']) ?>">
            <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>
            <button type="submit" class="rl-menu-btn">
                <i class="fa-solid fa-right-from-bracket"></i> Cerrar sesión
            </button>
        </form>
    <?php endif; ?>
</div>

<!-- MODAL NOTIFICACIÓN COMPLETA -->
<div class="rl-notif-modal-overlay rl-hidden" id="rl-notif-modal">
    <div class="rl-notif-modal-box">
        <button class="rl-notif-modal-close" id="rl-notif-modal-close">✕</button>
        <div class="rl-notif-modal-tipo" id="rl-notif-modal-tipo"></div>
        <h3 class="rl-notif-modal-asunto" id="rl-notif-modal-asunto"></h3>
        <p class="rl-notif-modal-fecha" id="rl-notif-modal-fecha"></p>
        <div class="rl-notif-modal-cuerpo" id="rl-notif-modal-cuerpo"></div>
    </div>
</div>

<!-- OVERLAY SIDEBAR -->
<div id="rl-sidebar-overlay" class="rl-sidebar-overlay rl-hidden"></div>

<!-- SIDEBAR LATERAL -->
<aside id="rl-sidebar" class="rl-sidebar rl-sidebar-hidden">

    <div class="rl-sidebar-header">
        <div class="rl-sidebar-brand">
            <img src="<?= Yii::getAlias('@web/img/logos/logo_kiwi.png') ?>" class="rl-sidebar-logo" alt="Logo">
            <span>Recetify Lab</span>
        </div>
        <button id="rl-sidebar-close" class="rl-sidebar-close" title="Cerrar">✕</button>
    </div>

    <div class="rl-sidebar-section">
        <?php if (!Yii::$app->user->isGuest): ?>
            <p class="rl-sidebar-section-title"><?= Html::encode(Yii::$app->user->identity->username) ?></p>
        <?php endif; ?>

        <a href="<?= Yii::$app->urlManager->createUrl(['site/index']) ?>" class="rl-sidebar-item">
            <i class="fa-solid fa-house"></i> Home
        </a>
        <a href="#" class="rl-sidebar-item">
            <i class="fa-solid fa-clock-rotate-left"></i> Historial
        </a>
        <a href="<?= Yii::$app->urlManager->createUrl(['recipe/collections', 'tab' => 'guardado']) ?>" class="rl-sidebar-item">
            <i class="fa-solid fa-bookmark"></i> Guardados
        </a>
        <a href="<?= Yii::$app->urlManager->createUrl(['site/mis-recetas']) ?>" class="rl-sidebar-item">
            <i class="fa-solid fa-utensils"></i> Mis recetas
        </a>
    </div>

    <div class="rl-sidebar-divider"></div>

    <div class="rl-sidebar-section">
        <a href="<?= Yii::$app->urlManager->createUrl(['recipe/search']) ?>" class="rl-sidebar-item">
            <i class="fa-solid fa-magnifying-glass"></i> Buscar recetas
        </a>
        <a href="#" class="rl-sidebar-item">
            <i class="fa-solid fa-trophy"></i> Ranking de recetas
        </a>
        <a href="<?= Yii::$app->urlManager->createUrl(['chef-lideres/index']) ?>" class="rl-sidebar-item">
            <i class="fa-solid fa-hat-chef"></i> Chefs líderes
        </a>
    </div>

    <div class="rl-sidebar-divider"></div>

    <div class="rl-sidebar-section">
        <p class="rl-sidebar-section-title">Idioma de lectura</p>
        <select class="rl-sidebar-select">
            <option value="es">Español</option>
            <option value="en">English</option>
        </select>
    </div>

</aside>

<!-- CONTENIDO PRINCIPAL -->
<main class="rl-main-content">
    <div class="container">
        <?php if (!empty($this->params['breadcrumbs'])): ?>
            <?= Breadcrumbs::widget(['links' => $this->params['breadcrumbs']]) ?>
        <?php endif ?>
        <?= Alert::widget() ?>
        <?= $content ?>
    </div>
</main>

<footer class="rl-footer">
    <p>Información futura...</p>
</footer>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>