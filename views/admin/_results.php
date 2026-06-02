<?php use yii\helpers\Url; ?>
<?php use yii\helpers\Html; ?>

<?php if (empty($data)): ?>
    <div class="empty-state">
        <p>No hay resultados</p>
    </div>
<?php else: ?>

        <?php foreach ($data as $item): ?>

            <!-- 👤 USERS -->
            <?php if ($tab === 'users' && $item->user): ?>
                <div class="card">

                    <!-- ❌ DISMISS -->
                    <div class="close">
                        <form method="post" action="<?= Url::to(['admin/dismiss-report']) ?>">
                            <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->getCsrfToken()) ?>
                            <input type="hidden" name="report_id" value="<?= $item->id ?>">

                            <button type="button" class="close btn-confirm"
                                data-mensaje="¿Descartar reporte?"
                                data-tipo="warning">
                                ✖
                            </button>
                        </form>
                    </div>

                    <div class="user">
                        <div class="avatar"
                            style="background-image: url('<?= $item->user->avatar_url ?: '/images/default.png' ?>')">
                        </div>

                        <span><?= Html::encode($item->user->username) ?></span>
                    </div>

                    <p class="motivo"><?= Html::encode($item->motivo) ?></p>
                    <p class="descripcion"><?= Html::encode($item->descripcion) ?></p>

                    <div class="actions">

                        <!-- KICK -->
                        <form method="post" action="<?= Url::to(['admin/kick']) ?>">
                            <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->getCsrfToken()) ?>
                            <input type="hidden" name="id" value="<?= $item->user->id ?>">

                            <button type="button" class="kick btn-confirm"
                                data-mensaje="¿Expulsar usuario?"
                                data-tipo="warning">
                                Kick
                            </button>
                        </form>

                        <!-- BAN -->
                        <form method="post" action="<?= Url::to(['admin/ban']) ?>">
                            <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->getCsrfToken()) ?>
                            <input type="hidden" name="id" value="<?= $item->user->id ?>">

                            <button type="button" class="ban btn-confirm"
                                data-mensaje="¿Banear usuario?"
                                data-tipo="error">
                                Ban
                            </button>
                        </form>

                    </div>

                </div>
            <?php endif; ?>


            <!-- 🍽 RECIPES -->
            <?php if ($tab === 'recipes' && $item->recipe): ?>
                <div class="card">

                    <div class="image"
                        style="background-image: url('<?= $item->recipe->imagen_portada_url ?: '/images/default_recipe.png' ?>')">
                    </div>

                    <div class="info">
                        <h4><?= Html::encode($item->recipe->titulo) ?></h4>
                    </div>

                    <div class="actions">

                        <a href="<?= Url::to(['recipe/view', 'id' => $item->recipe->id]) ?>" class="leer">
                            Leer
                        </a>

                        <form method="post" action="<?= Url::to(['admin/delete-recipe']) ?>">
                            <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->getCsrfToken()) ?>
                            <input type="hidden" name="id" value="<?= $item->recipe->id ?>">

                            <button type="button" class="estado btn-confirm"
                                data-mensaje="¿Eliminar receta?"
                                data-tipo="error">
                                Eliminar
                            </button>
                        </form>

                    </div>

                </div>
            <?php endif; ?>


            <!-- 🚫 BANNED -->
            <?php if ($tab === 'banned'): ?>
                <div class="card">

                    <div class="user">
                        <div class="avatar"
                            style="background-image: url('<?= $item->avatar_url ?>')">
                        </div>

                        <span><?= Html::encode($item->username) ?></span>
                    </div>

                    <button class="btn-ban-toggle"
                        data-id="<?= $item->id ?>"
                        data-action="unban">
                        Desbanear
                    </button>

                    <span class="badge ban">BANEADO</span>

                </div>
            <?php endif; ?>

        <?php endforeach; ?>

    </div>
<?php endif; ?>