<?php
$this->title = 'Admin Panel';
$this->registerCssFile('@web/css/admin-dashboard.css');
$this->registerJsFile('@web/js/dashboard.js', [
    'depends' => [\yii\web\JqueryAsset::class],
    'position' => \yii\web\View::POS_END,
]);

use yii\helpers\Url;
use yii\helpers\Html;

$tab = Yii::$app->request->get('tab', 'users');
?>

<link rel="stylesheet" href="<?= Yii::getAlias('@web/css/index.css') ?>">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="admin-container">

    <!-- Sidebar -->
    <div class="sidebar">
        <a href="<?= Url::to(['admin/dashboard', 'tab' => 'recipes']) ?>"
           class="tab <?= $tab === 'recipes' ? 'active' : '' ?>">
            Recetas reportadas
        </a>

        <a href="<?= Url::to(['admin/dashboard', 'tab' => 'users']) ?>"
           class="tab <?= $tab === 'users' ? 'active' : '' ?>">
            Usuarios reportados
        </a>

        <a href="<?= Url::to(['admin/dashboard', 'tab' => 'banned']) ?>"
            class="tab <?= $tab === 'banned' ? 'active' : '' ?>">
            Usuarios baneados
        </a>
    </div>

    <!-- Contenido -->
    <div class="content">

        <div class="top-filters">

            <select id="filter">
                <option value="all">Todos</option>
                <option value="recent">Más recientes</option>
                <option value="old">Más antiguos</option>
            </select>
            
            <div class="search-box">
                <input type="text" id="searchInput" placeholder="Buscar..." class="search-box-input">
                <button type="button" class="btn-search">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </button>
            </div>
            
        </div>

        <!-- USUARIOS -->
        <?php if ($tab === 'users'): ?>

            <?php if (empty($userReports)): ?>
                <div class="empty-state">
                  <p>Actualmente no hay usuarios reportados</p>
                </div>

            <?php else: ?>
            <div class="grid">
                <?php foreach ($userReports as $report): ?>
                    <?php if ($report->user): ?>
                        <div class="card">

                            
                                <div class="close">
                                    <form method="post" action="<?= Url::to(['admin/dismiss-report']) ?>">
                                        <input type="hidden"
                                        
                                        name="<?= Yii::$app->request->csrfParam ?>"
                                        value="<?= Yii::$app->request->getCsrfToken() ?>">
                                        
                                        <input type="hidden" name="report_id" value="<?= $report->id ?>">
                                        <button type="submit" class="close btn-confirm" 
                                            data-mensaje="¿Seguro que deseas descartar a este usuario?"
                                            data-tipo="error">
                                            ✖
                                        </button>
                                    </form>
                                </div>
                            

                            <div class="user">
                                <div class="avatar"
                                     style="background-image: url('<?= $report->user->avatar_url ?: '/images/default.png' ?>')">
                                </div>
                                <span><?= Html::encode($report->user->username) ?></span>
                            </div>

                            <p class="motivo">
                                <?= Html::encode($report->motivo) ?>
                            </p>

                            <p class="descripcion">
                                <?= Html::encode($report->descripcion) ?>
                            </p>

                            <div class="actions">
                                <form method="post" action="<?= Url::to(['admin/kick']) ?>">
                                    <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->getCsrfToken()) ?>
                                    <input type="hidden" name="id" value="<?= $report->user->id ?>">
                                    
                                    <button type="button" class="kick btn-confirm" 
                                        data-mensaje="¿Seguro que deseas expulsar a este usuario?"
                                        data-tipo="warning">
                                        Kick
                                    </button>
                                </form>
                                
                                <form method="post" action="<?= Url::to(['admin/ban']) ?>">
                                    <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->getCsrfToken()) ?>
                                    <input type="hidden" name="id" value="<?= $report->user->id ?>">

                                    <button type="button" class="ban btn-confirm" 
                                        data-mensaje="¿Seguro que deseas banea a este usuario?"
                                        data-tipo="error">
                                        Ban
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        <?php endif; ?>


        <!-- RECETAS -->
        <?php if ($tab === 'recipes'): ?>

            <?php if (empty($recipeReports)): ?>
                <div class="empty-state">
                   <p>Actualmente no hay recetas reportadas</p>
                </div>

            <?php else: ?>
            <div class="grid">
                <?php foreach ($recipeReports as $report): ?>
                    <?php if ($report->recipe): ?>
<div class="card">

    <!-- Botón descartar -->
    <div class="close">
        <form method="post" action="<?= Url::to(['admin/dismiss-report']) ?>">

            <input type="hidden"
                name="<?= Yii::$app->request->csrfParam ?>"
                value="<?= Yii::$app->request->getCsrfToken() ?>">

            <input type="hidden"
                name="report_id"
                value="<?= $report->id ?>">

            <button
                type="submit"
                class="close btn-confirm"
                data-mensaje="¿Seguro que deseas descartar este reporte?"
                data-tipo="warning">
                ✖
            </button>

        </form>
    </div>

    <div class="image"
        style="background-image:url('<?= $report->recipe->imagen_portada_url ?: '/images/default_recipe.png' ?>')">
    </div>

    <div class="info">
        <h4><?= Html::encode($report->recipe->titulo) ?></h4>
    </div>

    <p class="descripcion">
        <?= Html::encode($report->descripcion) ?>
    </p>

    <div class="actions">

        <a href="<?= Url::to(['site/pre-lectura', 'id' => $report->recipe->id]) ?>"
            class="leer">
            Leer
        </a>

        <form method="post" action="<?= Url::to(['admin/delete-recipe']) ?>">

            <?= Html::hiddenInput(
                Yii::$app->request->csrfParam,
                Yii::$app->request->getCsrfToken()
            ) ?>

            <input type="hidden"
                name="id"
                value="<?= $report->recipe->id ?>">

            <button
                type="button"
                class="estado btn-confirm"
                data-mensaje="¿Seguro que deseas eliminar esta receta?"
                data-tipo="error">
                Eliminar
            </button>

        </form>

    </div>

</div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        <?php endif; ?>

        <!-- USUARIO BANEADO -->
        <?php if ($tab === 'banned'): ?>

            <?php if (empty($bannedUsers)): ?>
                <div class="empty-state">
                    <p>Actualmente no hay usuarios baneados</p>
                </div>
                
            <?php else: ?>
            <div class="grid">
                <?php foreach ($bannedUsers as $user): ?>
                    <div class="card">

                    <div class="user">
                        <div class="avatar"
                            style="background-image: url('<?= $user->avatar_url ?>')">
                        </div>

                        <span>
                            <?= Html::encode($user->username) ?>
                        </span>
                    </div>

                    <button class="btn-ban-toggle"
                         data-id="<?= $user->id ?>"
                         data-action="unban">Desbanear
                    </button>

                    <span class="badge <?= $user->is_active ? 'active' : 'ban' ?>">
                        <?= $user->is_active ? 'ACTIVO' : 'BANEADO' ?>
                    </span>

                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

</div>
