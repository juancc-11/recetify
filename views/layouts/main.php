<?php

/** @var yii\web\View $this */
/** @var string $content */

use app\assets\AppAsset;
use app\widgets\Alert;
use yii\bootstrap5\Breadcrumbs;
use yii\bootstrap5\Html;


AppAsset::register($this);

$this->registerCsrfMetaTags();

$this->registerCssFile('@web/css/menus.css?v=5', [
    'position' => \yii\web\View::POS_HEAD,
]);

$this->registerJsFile('@web/js/index.js', [
    'depends' => [\yii\web\JqueryAsset::class],
    'position' => \yii\web\View::POS_END,
]);

$this->registerMetaTag(['charset' => Yii::$app->charset], 'charset');
$this->registerMetaTag(['name' => 'viewport', 'content' => 'width=device-width, initial-scale=1']);
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

// Meta básicos
$this->registerMetaTag(['name' => 'description', 'content' => $seoDescription]);
$this->registerMetaTag(['name' => 'robots',      'content' => 'index, follow']);
$this->registerMetaTag(['name' => 'author',      'content' => 'Recetify Lab']);

// Open Graph (Facebook, WhatsApp, LinkedIn)
$this->registerMetaTag(['property' => 'og:type',        'content' => 'website']);
$this->registerMetaTag(['property' => 'og:site_name',   'content' => 'Recetify Lab']);
$this->registerMetaTag(['property' => 'og:title',       'content' => $seoTitle]);
$this->registerMetaTag(['property' => 'og:description', 'content' => $seoDescription]);
$this->registerMetaTag(['property' => 'og:image',       'content' => $seoImage]);
$this->registerMetaTag(['property' => 'og:url',         'content' => $seoUrl]);
$this->registerMetaTag(['property' => 'og:locale',      'content' => 'es_ES']);

// Twitter Card
$this->registerMetaTag(['name' => 'twitter:card',        'content' => 'summary_large_image']);
$this->registerMetaTag(['name' => 'twitter:title',       'content' => $seoTitle]);
$this->registerMetaTag(['name' => 'twitter:description', 'content' => $seoDescription]);
$this->registerMetaTag(['name' => 'twitter:image',       'content' => $seoImage]);

// Canonical
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

    <div class="rl-nav-left">
        <button id="rl-sidebar-toggle" class="rl-nav-btn" title="Menú">☰</button>

        <?php if (!in_array(Yii::$app->controller->action->id, ['index', 'login', 'register', 'admin-login'])): ?>
            <img src="<?= Yii::getAlias('@web/img/logos/logo_kiwi.png') ?>" class="rl-nav-logo" alt="Logo">
            <span class="rl-nav-brand">Recetify Lab</span>
        <?php endif; ?>
    </div>

    <?php if (!in_array(Yii::$app->controller->action->id, ['index', 'login', 'register', 'admin-login'])): ?>
        <form
    action="<?= yii\helpers\Url::to(['/recipe/search']) ?>"
    method="GET"
    class="rl-nav-search"
>

    <input
        type="text"
        name="q"
        class="rl-search-input"
        placeholder="Buscar recetas..."
        value="<?= Html::encode(Yii::$app->request->get('q', '')) ?>"
    >

    <button
        type="submit"
        class="rl-search-btn"
    >
        <i class="fa-solid fa-magnifying-glass"></i>
    </button>

</form>
    <?php endif; ?>

    <div class="rl-nav-right">

        <?php if (Yii::$app->user->isGuest): ?>
            <img
                id="rl-menu-toggle"
                src="<?= Yii::getAlias('@web/img/logos/logo_kiwi.png') ?>"
                class="rl-nav-avatar"
                alt="Cuenta"
            >
        <?php else: ?>
            <img
                id="rl-menu-toggle"
                src="<?= Yii::$app->user->identity->avatar_url ?: Yii::getAlias('@web/img/logos/logo_kiwi.png') ?>"
                class="rl-nav-avatar"
                alt="Avatar"
            >
        <?php endif; ?>

        <button class="rl-nav-btn" title="Configuración">
            <i class="fa-solid fa-gear"></i>
        </button>

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
            <img
                src="<?= Yii::$app->user->identity->avatar_url ?: Yii::getAlias('@web/img/default.png') ?>"
                class="rl-menu-avatar"
                alt="Avatar"
            >
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
        <a href="#" class="rl-sidebar-item">
            <i class="fa-solid fa-bookmark"></i> Guardado
        </a>
        <a
    href="<?= Yii::$app->urlManager->createUrl(['recipe/search']) ?>"
    class="rl-sidebar-item"
>
    <i class="fa-solid fa-magnifying-glass"></i> Buscar recetas
</a>
    </div>

    <div class="rl-sidebar-divider"></div>

    <div class="rl-sidebar-section">
        <a href="#" class="rl-sidebar-item">
            <i class="fa-solid fa-magnifying-glass"></i> Buscar recetas
        </a>
        <a href="#" class="rl-sidebar-item">
            <i class="fa-solid fa-trophy"></i> Ranking de recetas
        </a>
        <a href="#" class="rl-sidebar-item">
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
