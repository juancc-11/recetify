<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;
use app\models\User;
use yii\web\UploadedFile;        
use Cloudinary\Cloudinary;

class SiteController extends Controller
{
    public function behaviors()
    {
        return [
            // JSON SOLO para acciones del editor
            'contentNegotiator' => [
                'class' => \yii\filters\ContentNegotiator::class,
                'only' => ['get-recipe', 'actualizar-receta', 'subir-imagen-temp'],
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ],
            
            'access' => [
                'class' => AccessControl::class,
                'only' => ['logout', 'profile', 'delete-account', 'mis-recetas', 'crear-receta',
                   'toggle-publish', 'get-recipe', 'actualizar-receta', 'recipe-stats',
                   'borrar-receta', 'subir-imagen-temp', 'submit-review', 'toggle-collection',
                    'subir-comment', 'reportar-receta', 'mark-read', 'mark-all-read', 'guardar-bio'],
                'rules' => [
                    [
                        'actions' => ['logout', 'profile', 'delete-account', 'mis-recetas', 'crear-receta',
                           'toggle-publish', 'get-recipe', 'actualizar-receta', 'recipe-stats',
                           'borrar-receta', 'subir-imagen-temp', 'pre-lectura', 'submit-review', 'toggle-collection',
                            'subir-comment', 'lectura', 'reportar-receta', 'mark-read', 'mark-all-read', 'mi-perfil', 'guardar-bio'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout'              => ['post'],
                    'delete-account'      => ['post'],
                    'get-recipe'          => ['get'],
                    'actualizar-receta'   => ['post'],
                    'subir-imagen-temp'   => ['post'],
                    'toggle-publish'      => ['post'],
                    'borrar-receta'       => ['post'],
                    'lectura'             => ['get'],
                    'reportar-receta'     => ['post'],
                ],
            ],
        ];
    }

    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }

        $model->password = '';
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    public function actionLogout()
    {
        Yii::$app->user->logout();
        return $this->redirect(['site/login']);
    }

    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');
            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    public function actionAbout()
    {
        return $this->render('about');
    }

    public function actionAdminLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->redirect(['admin/dashboard']);
        }
        
        $model = new \app\models\LoginForm();
        
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            if (Yii::$app->user->identity->rol !== 'admin') {
                Yii::$app->user->logout();
                Yii::$app->session->setFlash('error', 'Acceso solo para administradores');
            }
            return $this->redirect(['admin/dashboard']);
        }
        
        return $this->render('admin-login', [
            'model' => $model,
        ]);
    }

    public function actionMarkRead()
{
    Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    if (Yii::$app->user->isGuest) return ['success' => false];

    $messageId = (int) Yii::$app->request->post('message_id');
    $updated = Yii::$app->db->createCommand(
        'UPDATE messages SET is_read = 1
         WHERE id = :id AND receiver_id = :uid',
        [':id' => $messageId, ':uid' => Yii::$app->user->id]
    )->execute();

    return ['success' => $updated > 0];
}

public function actionMarkAllRead()
{
    Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    if (Yii::$app->user->isGuest) return ['success' => false];

    Yii::$app->db->createCommand(
        'UPDATE messages SET is_read = 1 WHERE receiver_id = :uid',
        [':uid' => Yii::$app->user->id]
    )->execute();

    return ['success' => true];
}

    public function actionProfile()
    {
        if (Yii::$app->user->isGuest) {
            return $this->redirect(['site/login']);
        }
        
        $model = User::findOne(Yii::$app->user->id);
        
        if (Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->post());
            $post = Yii::$app->request->post('User', []);

            if (empty($post['username'])) {
                $model->username = Yii::$app->user->identity->username;
            }
            if (empty($post['email'])) {
                $model->email = Yii::$app->user->identity->email;
            }

            if (!empty($post['password'])) {
                if ($post['password'] !== ($post['repeat_password'] ?? '')) {
                    Yii::$app->session->setFlash('error', 'Las contraseñas no coinciden.');
                    return $this->render('perfil', ['model' => $model]);
                }
                $model->password_hash = Yii::$app->security->generatePasswordHash($post['password']);
            } else {
                $model->password_hash = Yii::$app->user->identity->password_hash;
            }

            $avatarFile = UploadedFile::getInstance($model, 'avatar_file');

            if ($avatarFile) {
                $config     = Yii::$app->params['cloudinary'];
                $cloudinary = new Cloudinary([
                    'cloud' => [
                        'cloud_name' => trim($config['cloud_name']),
                        'api_key'    => $config['api_key'],
                        'api_secret' => $config['api_secret'],
                    ],
                ]);

                $upload = $cloudinary->uploadApi()->upload(
                    $avatarFile->tempName,
                    ['folder' => 'recetify/users']
                );
                $model->avatar_url = $upload['secure_url'];
            } else {
                $model->avatar_url = Yii::$app->user->identity->avatar_url;
            }

            if ($model->save(false)) {
                Yii::$app->session->setFlash('success', 'Perfil actualizado correctamente.');
                return $this->redirect(['site/profile']);
            } else {
                Yii::$app->session->setFlash('error', 'Error al guardar los cambios.');
            }
        }
        
        return $this->render('perfil', ['model' => $model]);
    }
    
    public function actionDeleteAccount()
    {
        if (Yii::$app->user->isGuest) {
            return $this->redirect(['site/login']);
        }
        
        $user = User::findOne(Yii::$app->user->id);
        Yii::$app->user->logout();

        if ($user) {
            $user->delete();
        }

        Yii::$app->session->setFlash('success', 'Cuenta eliminada.');
        return $this->redirect(['site/index']);
    }
    
    public function actionRegister()
    {
        $model = new User();
        
        if ($model->load(Yii::$app->request->post())) {
            $model->avatar_file = UploadedFile::getInstance($model, 'avatar_file');

            if ($model->validate()) {
                $model->password_hash = Yii::$app->security->generatePasswordHash($model->password);

                if ($model->avatar_file) {
                    $config     = Yii::$app->params['cloudinary'];
                    $cloudinary = new Cloudinary([
                        'cloud' => [
                            'cloud_name' => trim($config['cloud_name']),
                            'api_key'    => $config['api_key'],
                            'api_secret' => $config['api_secret'],
                        ],
                    ]);

                    $upload = $cloudinary->uploadApi()->upload(
                        $model->avatar_file->tempName,
                        ['folder' => 'recetify/users']
                    );
                    $model->avatar_url = $upload['secure_url'];
                }

                $model->rol       = 'usuario';
                $model->is_active = 1;

                if ($model->save(false)) {
                    return $this->redirect(['site/login']);
                }
            }
        }

        return $this->render('register', [
            'model' => $model,
        ]);
    }

    public function actionMiPerfil($id = null)
{
    if ($id === null) {
        if (Yii::$app->user->isGuest) {
            return $this->redirect(['site/login']); // sin id y sin sesión → login
        }
        $id = Yii::$app->user->id;
    }

    $profileUser = \app\models\User::findOne((int) $id);
    if (!$profileUser || !$profileUser->is_active) {
        throw new \yii\web\NotFoundHttpException('Usuario no encontrado');
    }

    $isOwner = !Yii::$app->user->isGuest && (int) Yii::$app->user->id === (int) $profileUser->id;

    // Reseñas que ha dado el usuario
    $reviews = \app\models\Comment::find()
        ->where(['user_id' => $profileUser->id, 'is_visible' => 1])
        ->orderBy(['created_at' => SORT_DESC])
        ->all();

    // Recetas publicadas del usuario
    $recipes = \app\models\Recipe::find()
        ->where(['user_id' => $profileUser->id, 'is_published' => 1, 'is_deleted' => 0])
        ->orderBy(['created_at' => SORT_DESC])
        ->all();

    return $this->render('mi_perfil', [
        'profileUser' => $profileUser,
        'reviews'     => $reviews,
        'recipes'     => $recipes,
        'isOwner'     => $isOwner,
    ]);
}

public function actionGuardarBio()
{
    Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    if (Yii::$app->user->isGuest) return ['success' => false];

    $bio  = trim(Yii::$app->request->post('bio', ''));
    $user = \app\models\User::findOne(Yii::$app->user->id);
    if (!$user) return ['success' => false];

    $user->full_name = mb_substr($bio, 0, 300);
    if ($user->save(false)) {
        return ['success' => true, 'bio' => $user->full_name];
    }
    return ['success' => false];
}

    public function actionMisRecetas()
    {
        if (Yii::$app->user->isGuest) {
            return $this->redirect(['site/login']);
        }

        $recipes = \app\models\Recipe::find()
            ->where([
                'user_id'    => Yii::$app->user->id,
                'is_deleted' => 0,
            ])
            ->orderBy(['created_at' => SORT_DESC])
            ->all();

        $availableTags = \app\models\Tag::find()->all();

        return $this->render('mis_recetas', [
            'recipes'       => $recipes,
            'availableTags' => $availableTags,
        ]);
    }

    public function actionSitemap()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
        Yii::$app->response->headers->add('Content-Type', 'application/xml; charset=utf-8');

        $baseUrl = 'https://recetifylab.gzgroup.dev';

        $recetas = \app\models\Recipe::find()
            ->where(['is_published' => 1, 'is_deleted' => 0])
            ->orderBy(['created_at' => SORT_DESC])
            ->all();

        $xml  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        $staticPages = [
            ['url' => '/',               'priority' => '1.0', 'freq' => 'daily'],
            ['url' => '/site/login',     'priority' => '0.4', 'freq' => 'monthly'],
            ['url' => '/site/register',  'priority' => '0.4', 'freq' => 'monthly'],
        ];

        foreach ($staticPages as $page) {
            $xml .= "  <url>\n";
            $xml .= "    <loc>{$baseUrl}{$page['url']}</loc>\n";
            $xml .= "    <changefreq>{$page['freq']}</changefreq>\n";
            $xml .= "    <priority>{$page['priority']}</priority>\n";
            $xml .= "  </url>\n";
        }

        foreach ($recetas as $receta) {
            $url  = $baseUrl . '/site/receta?id=' . $receta->id;
            $date = date('Y-m-d', strtotime($receta->created_at));
            $xml .= "  <url>\n";
            $xml .= "    <loc>" . htmlspecialchars($url) . "</loc>\n";
            $xml .= "    <lastmod>{$date}</lastmod>\n";
            $xml .= "    <changefreq>weekly</changefreq>\n";
            $xml .= "    <priority>0.8</priority>\n";
            $xml .= "  </url>\n";
        }

        $xml .= '</urlset>';
        return $xml;
    }

/* ══════════════════════════════════════════════════════
       CREAR RECETA
       FIX: lee URLs de Cloudinary enviadas por el JS
            en lugar de intentar re-subir archivos
    ══════════════════════════════════════════════════════ */
    public function actionCrearReceta()
    {
        if (Yii::$app->user->isGuest) {
            return $this->redirect(['site/login']);
        }

        if (Yii::$app->request->isPost) {
            $post = Yii::$app->request->post('Recipe', []);

            $recipe = new \app\models\Recipe();
            $recipe->user_id      = Yii::$app->user->id;
            $recipe->titulo       = $post['titulo']       ?? '';
            $recipe->descripcion  = $post['descripcion']  ?? '';
            $recipe->utensilios   = $post['utensilios']   ?? '';
            $recipe->receta_texto = $post['receta_texto'] ?? '';
            $recipe->is_published = 0;
            $recipe->is_deleted   = 0;

            $config     = Yii::$app->params['cloudinary'];
            $cloudinary = new \Cloudinary\Cloudinary([
                'cloud' => [
                    'cloud_name' => trim($config['cloud_name']),
                    'api_key'    => $config['api_key'],
                    'api_secret' => $config['api_secret'],
                ],
            ]);

            // Imagen portada (se sube desde el form normal)
            $portada = UploadedFile::getInstanceByName('Recipe[portada]');
            if ($portada) {
                $upload = $cloudinary->uploadApi()->upload(
                    $portada->tempName,
                    ['folder' => 'recetify/recipes']
                );
                $recipe->imagen_portada_url = $upload['secure_url'];
            }

            if ($recipe->save(false)) {

                // ✅ FIX: leer URLs de Cloudinary que el JS ya subió
                // El JS envía Recipe[cloudinary_urls][1], [2], [3]
                $cloudinaryUrls = $post['cloudinary_urls'] ?? [];
                foreach ($cloudinaryUrls as $pos => $url) {
                    $pos = (int) $pos;
                    $url = trim($url);
                    if (!$url || !in_array($pos, [1, 2, 3])) continue;

                    $img            = new \app\models\RecipeImage();
                    $img->recipe_id = $recipe->id;
                    $img->image_url = $url;
                    $img->posicion  = $pos;
                    $img->save(false);
                }

                // Tags
                $tagsJson = $post['tags'] ?? '[]';
                $tagNames = json_decode($tagsJson, true) ?? [];
                foreach ($tagNames as $name) {
                    $name = trim($name);
                    if (!$name) continue;
                    $tag = \app\models\Tag::findOne(['name' => $name]);
                    if (!$tag) {
                        $tag       = new \app\models\Tag();
                        $tag->name = $name;
                        $tag->slug = strtolower(str_replace(' ', '-', $name));
                        $tag->save(false);
                    }
                    Yii::$app->db->createCommand()->insert('recipe_tags', [
                        'recipe_id' => $recipe->id,
                        'tag_id'    => $tag->id,
                    ])->execute();
                }

                Yii::$app->session->setFlash('success', 'Receta creada correctamente.');
                return $this->redirect(['site/mis-recetas']);
            }

            Yii::$app->session->setFlash('error', 'Error al guardar la receta.');
        }

        return $this->redirect(['site/mis-recetas']);
    }

    /* ── BORRAR RECETA ──────────────────────────────────────────── */
    public function actionBorrarReceta()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if (Yii::$app->user->isGuest) {
            return ['success' => false, 'message' => 'No autenticado'];
        }

        $recipeId = (int) Yii::$app->request->post('recipe_id');

        $recipe = \app\models\Recipe::findOne([
            'id'      => $recipeId,
            'user_id' => Yii::$app->user->id,
        ]);

        if (!$recipe) {
            return ['success' => false, 'message' => 'Receta no encontrada'];
        }

        if ($recipe->delete()) {
            return ['success' => true];
        }

        return ['success' => false, 'message' => 'Error al eliminar'];
    }

     /* ── TOGGLE PUBLICAR / DESPUBLICAR ─────────────────────────── */
    public function actionTogglePublish()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if (Yii::$app->user->isGuest) {
            return ['success' => false, 'message' => 'No autenticado'];
        }

        $recipeId  = (int) Yii::$app->request->post('recipe_id');
        $published = (int) Yii::$app->request->post('published');

        $recipe = \app\models\Recipe::findOne([
            'id'         => $recipeId,
            'user_id'    => Yii::$app->user->id,
            'is_deleted' => 0,
        ]);

        if (!$recipe) {
            return ['success' => false, 'message' => 'Receta no encontrada'];
        }

        $recipe->is_published = $published;

        if ($recipe->save(false)) {
            return ['success' => true];
        }

        return ['success' => false, 'message' => 'Error al guardar'];
    } 

     /* ── ESTADÍSTICAS DE UNA RECETA ─────────────────────────────── */
    public function actionRecipeStats()
{
    Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

    try {
        if (Yii::$app->user->isGuest) {
            return ['error' => true, 'message' => 'No autenticado'];
        }

        $recipeId = (int) Yii::$app->request->get('recipe_id');

        $recipe = \app\models\Recipe::find()
            ->where(['id' => $recipeId, 'user_id' => Yii::$app->user->id])
            ->andWhere(['or', ['is_deleted' => 0], ['is_deleted' => null]])
            ->one();

        if (!$recipe) {
            return ['error' => true, 'message' => 'Receta no encontrada'];
        }

        // Visitas últimos 30 días
       $visitas = Yii::$app->db->createCommand(
       "SELECT DATE_FORMAT(view_date, '%d/%m') AS fecha, SUM(views) AS total
        FROM recipe_views
        WHERE recipe_id = " . (int)$recipeId . "
        AND view_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
        GROUP BY view_date
        ORDER BY view_date ASC"
        )->queryAll();

        // Totales
        $totalVisitas = (int) Yii::$app->db->createCommand(
            'SELECT COALESCE(SUM(views), 0) FROM recipe_views WHERE recipe_id = :id',
            [':id' => $recipeId]
        )->queryScalar();

        $totalComentarios = (int) \app\models\Comment::find()
            ->where(['recipe_id' => $recipeId])
            ->count();

        $totalGuardados = (int) Yii::$app->db->createCommand(
            "SELECT COUNT(*) FROM recipe_collections WHERE recipe_id = :id AND tipo = 'guardado'",
            [':id' => $recipeId]
        )->queryScalar();

        $totalFavoritos = (int) Yii::$app->db->createCommand(
            "SELECT COUNT(*) FROM recipe_collections WHERE recipe_id = :id AND tipo = 'favorito'",
            [':id' => $recipeId]
        )->queryScalar();

        $totalHistorial = (int) Yii::$app->db->createCommand(
            "SELECT COUNT(*) FROM recipe_collections WHERE recipe_id = :id AND tipo = 'historial'",
            [':id' => $recipeId]
        )->queryScalar();

        // Promedio actual de esta receta
        $avgActual = (float) (\app\models\Comment::find()
            ->where(['recipe_id' => $recipeId])
            ->average('score') ?? 0);

        // Posición en ranking — evitar subconsulta con alias problemático
        $allAvgs = Yii::$app->db->createCommand('
            SELECT AVG(score) AS avg_score
            FROM comments
            GROUP BY recipe_id
            HAVING AVG(score) > :avg
        ', [':avg' => $avgActual])->queryColumn();

        $posicion = count($allAvgs) + 1;

        $totalRecetas = (int) \app\models\Recipe::find()
            ->where(['is_published' => 1, 'is_deleted' => 0])
            ->count();

        return [
            'visitas' => $visitas,
            'totales' => [
                'visitas'     => $totalVisitas,
                'comentarios' => $totalComentarios,
                'guardados'   => $totalGuardados,
                'favoritos'   => $totalFavoritos,
                'historial'   => $totalHistorial,
            ],
            'ranking' => [
                'posicion' => $posicion,
                'total'    => $totalRecetas,
            ],
        ];

    } catch (\Throwable $e) {
        Yii::$app->response->statusCode = 500;
        return [
            'error'   => true,
            'message' => 'Excepción: ' . $e->getMessage(),
            'file'    => $e->getFile(),
            'line'    => $e->getLine(),
        ];
    }
}

    /* ══════════════════════════════════════════════════════
       OBTENER RECETA PARA EDITAR
    ══════════════════════════════════════════════════════ */
    public function actionGetRecipe()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (Yii::$app->user->isGuest) {
            return ['success' => false, 'message' => 'No autenticado'];
        }

        $id = (int) Yii::$app->request->get('id');

        if (!$id) {
            Yii::$app->response->statusCode = 400;
            return ['success' => false, 'message' => 'ID inválido'];
        }

        $recipe = \app\models\Recipe::find()
            ->where([
                'id' => $id,
                'user_id' => Yii::$app->user->id
            ])
            ->andWhere(['or',
                ['is_deleted' => 0],
                ['is_deleted' => null]
            ])
            ->one();

        if (!$recipe) {
            Yii::$app->response->statusCode = 404;
            return ['success' => false, 'message' => 'Receta no encontrada'];
        }

        $images = \app\models\RecipeImage::find()
            ->where(['recipe_id' => $id])
            ->orderBy(['posicion' => SORT_ASC, 'id' => SORT_ASC])
            ->all();

        return [
            'success' => true,
            'id' => $recipe->id,
            'titulo' => $recipe->titulo,
            'descripcion' => $recipe->descripcion,
            'utensilios' => $recipe->utensilios ?? '',
            'receta_texto' => $recipe->receta_texto ?? '',
            'imagen_portada_url' => $recipe->imagen_portada_url ?? '',
            'images' => array_map(function ($img) {
                return [
                    'id' => $img->id,
                    'posicion' => $img->posicion,
                    'image_url' => $img->image_url,
                ];
            }, $images)
        ];
    }

    /* ══════════════════════════════════════════════════════
       ACTUALIZAR RECETA
    ══════════════════════════════════════════════════════ */
    public function actionActualizarReceta()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (Yii::$app->user->isGuest) {
            return ['success' => false, 'message' => 'No autenticado'];
        }

        $post = Yii::$app->request->post('Recipe', []);
        $id   = (int) ($post['id'] ?? 0);

        $recipe = \app\models\Recipe::find()
            ->where(['id' => $id, 'user_id' => Yii::$app->user->id])
            ->andWhere(['or', ['is_deleted' => 0], ['is_deleted' => null]])
            ->one();

        if (!$recipe) {
            return ['success' => false, 'message' => 'Receta no encontrada'];
        }

        $recipe->titulo       = $post['titulo'] ?? $recipe->titulo;
        $recipe->descripcion  = $post['descripcion'] ?? $recipe->descripcion;
        $recipe->utensilios   = $post['utensilios'] ?? $recipe->utensilios;
        $recipe->receta_texto = $post['receta_texto'] ?? $recipe->receta_texto;

        // PORTADA
        $portada = UploadedFile::getInstanceByName('Recipe[portada]');
        if ($portada) {
            $cfg = Yii::$app->params['cloudinary'];

            $cl = new \Cloudinary\Cloudinary([
                'cloud' => [
                    'cloud_name' => trim($cfg['cloud_name']),
                    'api_key'    => $cfg['api_key'],
                    'api_secret' => $cfg['api_secret'],
                ],
            ]);

            $up = $cl->uploadApi()->upload(
                $portada->tempName,
                ['folder' => 'recetify/recipes']
            );

            $recipe->imagen_portada_url = $up['secure_url'];
        }

        if (!$recipe->save(false)) {
            return ['success' => false, 'message' => 'Error al guardar receta'];
        }

        // ── ELIMINAR IMÁGENES ──
        $toDelete = $post['delete_image'] ?? [];
        foreach ($toDelete as $imgId) {
            $img = \app\models\RecipeImage::findOne([
                'id' => (int)$imgId,
                'recipe_id' => $id
            ]);
            if ($img) $img->delete();
        }

        // ── GUARDAR NUEVAS IMÁGENES ──
        $cloudinaryUrls = $post['cloudinary_urls'] ?? [];

        if (!is_array($cloudinaryUrls)) {
            $cloudinaryUrls = json_decode($cloudinaryUrls, true);
            if (!is_array($cloudinaryUrls)) {
                $cloudinaryUrls = [];
            }
        }

        foreach ($cloudinaryUrls as $pos => $url) {
            $pos = (int)$pos;
            $url = trim($url);

            if (!$url || !in_array($pos, [1, 2, 3])) continue;

            $img = \app\models\RecipeImage::findOne([
                'recipe_id' => $id,
                'posicion' => $pos
            ]);

            if (!$img) {
                $img = new \app\models\RecipeImage();
                $img->recipe_id = $id;
                $img->posicion = $pos;
            }

            $img->image_url = $url;
            $img->save(false);
        }

        return [
            'success' => true,
            'message' => 'Receta actualizada correctamente'
        ];
    }

    /* ══════════════════════════════════════════════════════
       SUBIR IMAGEN TEMPORAL A CLOUDINARY
    ══════════════════════════════════════════════════════ */
    public function actionSubirImagenTemp()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (Yii::$app->user->isGuest) {
            return ['success' => false, 'message' => 'No autenticado'];
        }

        $file = UploadedFile::getInstanceByName('imagen');

        if (!$file || !$file->tempName) {
            return ['success' => false, 'message' => 'No se recibió imagen'];
        }

        $allowed = ['image/jpeg', 'image/png', 'image/webp'];

        if (!in_array($file->type, $allowed)) {
            return ['success' => false, 'message' => 'Tipo de archivo no permitido'];
        }

        if ($file->size > 5 * 1024 * 1024) {
            return ['success' => false, 'message' => 'Imagen supera 5MB'];
        }

        try {
            $cfg = Yii::$app->params['cloudinary'];

            $cloudinary = new \Cloudinary\Cloudinary([
                'cloud' => [
                    'cloud_name' => trim($cfg['cloud_name']),
                    'api_key'    => $cfg['api_key'],
                    'api_secret' => $cfg['api_secret'],
                ],
            ]);

            $upload = $cloudinary->uploadApi()->upload(
                $file->tempName,
                ['folder' => 'recetify/recipes']
            );

            return [
                'success' => true,
                'url' => $upload['secure_url']
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }

    public function actionSubmitReview()
{
    Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    if (Yii::$app->user->isGuest) return ['success' => false, 'message' => 'No autenticado'];

    $recipeId = (int) Yii::$app->request->post('recipe_id');
    $score    = (int) Yii::$app->request->post('score');
    $body     = trim(Yii::$app->request->post('comment', '')); // campo 'body' en BD

    if (!$recipeId || $score < 1 || $score > 5) {
        return ['success' => false, 'message' => 'Datos inválidos'];
    }

    // BD requiere body no vacío (CHECK constraint)
    if (empty($body)) {
        $body = '⭐'; // placeholder mínimo si no escribe nada
    }

    $userId = Yii::$app->user->id;

    // Buscar reseña existente o crear nueva
    $review = \app\models\Comment::findOne([
        'recipe_id' => $recipeId,
        'user_id'   => $userId,
    ]);

    if (!$review) {
        $review            = new \app\models\Comment();
        $review->recipe_id = $recipeId;
        $review->user_id   = $userId;
    }

    $review->score      = $score;
    $review->body       = $body;       // ← campo correcto en BD
    $review->is_visible = 1;

    if (!$review->save(false)) {
        return ['success' => false, 'message' => 'Error al guardar la reseña'];
    }

    // Recalcular stats
    $total = (int) \app\models\Comment::find()
        ->where(['recipe_id' => $recipeId, 'is_visible' => 1])
        ->count();

    $avg = \app\models\Comment::find()
        ->where(['recipe_id' => $recipeId, 'is_visible' => 1])
        ->average('score') ?? 0;

    // Distribución por estrella
    $counts       = [];
    $distribution = [];
    for ($s = 1; $s <= 5; $s++) {
        $cnt            = (int) \app\models\Comment::find()
            ->where(['recipe_id' => $recipeId, 'score' => $s, 'is_visible' => 1])
            ->count();
        $counts[$s]       = $cnt;
        $distribution[$s] = $total > 0 ? round(($cnt / $total) * 100) : 0;
    }

    return [
        'success'      => true,
        'avg'          => round((float) $avg, 2),
        'total'        => $total,
        'score'        => $score,
        'comment'      => $body === '⭐' ? '' : $body, // no mostrar placeholder
        'username'     => Yii::$app->user->identity->username,
        'avatar_url'   => Yii::$app->user->identity->avatar_url ?? null,
        'counts'       => $counts,
        'distribution' => $distribution,
    ];
}

public function actionToggleCollection()
{
    Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

    try {
        if (Yii::$app->user->isGuest) return ['success' => false, 'message' => 'No autenticado'];

        $recipeId = (int) Yii::$app->request->post('recipe_id');
        $tipo     = Yii::$app->request->post('tipo');
        $action   = Yii::$app->request->post('action');
        $userId   = Yii::$app->user->id;

        if (!in_array($tipo, ['guardado', 'favorito'])) {
            return ['success' => false, 'message' => 'Tipo inválido'];
        }

        $db = Yii::$app->db;

        $exists = (int) $db->createCommand(
            'SELECT COUNT(*) FROM recipe_collections
             WHERE recipe_id = :rid AND user_id = :uid AND tipo = :tipo',
            [':rid' => $recipeId, ':uid' => $userId, ':tipo' => $tipo]
        )->queryScalar();

        if ($action === 'add' && !$exists) {
            $db->createCommand()->insert('recipe_collections', [
                'recipe_id' => $recipeId,
                'user_id'   => $userId,
                'tipo'      => $tipo,
            ])->execute();
        } elseif ($action === 'remove' && $exists) {
            $db->createCommand()->delete('recipe_collections', [
                'recipe_id' => $recipeId,
                'user_id'   => $userId,
                'tipo'      => $tipo,
            ])->execute();
        }

        return ['success' => true];

    } catch (\Throwable $e) {
        Yii::$app->response->statusCode = 500;
        return [
            'success' => false,
            'message' => 'Excepción: ' . $e->getMessage(),
            'file'    => $e->getFile(),
            'line'    => $e->getLine(),
        ];
    }
}

public function actionPreLectura($id)
{
    $id = (int) $id;

    $recipe = \app\models\Recipe::findOne([
        'id'           => $id,
        'is_published' => 1,
        'is_deleted'   => 0,
    ]);

    if (!$recipe) {
        throw new \yii\web\NotFoundHttpException('Receta no encontrada');
    }

    // Tags
    $tags = $recipe->tags;

    // Autor
    $author = \app\models\User::findOne($recipe->user_id);

    // Comentarios visibles
    $comments = \app\models\Comment::find()
        ->where(['recipe_id' => $id, 'is_visible' => 1])
        ->orderBy(['created_at' => SORT_DESC])
        ->all();

    // Cargar usuario de cada comentario
    $commentUsers = [];
    foreach ($comments as $comment) {
        $commentUsers[$comment->user_id] = \app\models\User::findOne($comment->user_id);
    }

    // Stats
    $total    = count($comments);
    $avgScore = $total > 0
        ? array_sum(array_map(fn($c) => $c->score, $comments)) / $total
        : 0;
    $avgScore = round($avgScore, 1);

    // Distribución de estrellas
    $starCounts       = [];
    $starDistribution = [];
    for ($s = 1; $s <= 5; $s++) {
        $cnt = (int) \app\models\Comment::find()
            ->where(['recipe_id' => $id, 'score' => $s, 'is_visible' => 1])
            ->count();
        $starCounts[$s]       = $cnt;
        $starDistribution[$s] = $total > 0 ? round(($cnt / $total) * 100) : 0;
    }

    // Visitas totales
    $totalViews = (int) Yii::$app->db->createCommand(
        'SELECT COALESCE(SUM(views),0) FROM recipe_views WHERE recipe_id = :rid',
        [':rid' => $id]
    )->queryScalar();

    // Colección y reseña del usuario
    $collection = [];
    $userReview = null;

    if (!Yii::$app->user->isGuest) {
        $userId = Yii::$app->user->id;
        foreach (['guardado', 'favorito'] as $tipo) {
            $collection[$tipo] = (bool) Yii::$app->db->createCommand(
                'SELECT COUNT(*) FROM recipe_collections
                 WHERE recipe_id = :rid AND user_id = :uid AND tipo = :tipo',
                [':rid' => $id, ':uid' => $userId, ':tipo' => $tipo]
            )->queryScalar();
        }
        $userReview = \app\models\Comment::findOne([
            'recipe_id' => $id,
            'user_id'   => $userId,
        ]);
    }

    // Registrar visita del día
    $today = date('Y-m-d');
    $db    = Yii::$app->db;
    $viewExists = $db->createCommand(
        'SELECT id FROM recipe_views WHERE recipe_id = :rid AND view_date = :date',
        [':rid' => $id, ':date' => $today]
    )->queryScalar();

    if ($viewExists) {
        $db->createCommand(
            'UPDATE recipe_views SET views = views + 1
             WHERE recipe_id = :rid AND view_date = :date',
            [':rid' => $id, ':date' => $today]
        )->execute();
    } else {
        $db->createCommand()->insert('recipe_views', [
            'recipe_id' => $id,
            'view_date' => $today,
            'views'     => 1,
        ])->execute();
    }

    return $this->render('pre_lectura', [
        'recipe'           => $recipe,
        'author'           => $author,
        'tags'             => $tags,
        'comments'         => $comments,
        'commentUsers'     => $commentUsers,
        'avgScore'         => $avgScore,
        'totalComments'    => $total,
        'totalViews'       => $totalViews,
        'starCounts'       => $starCounts,
        'starDistribution' => $starDistribution,
        'collection'       => $collection,
        'userReview'       => $userReview ? [
            'score'   => $userReview->score,
            'comment' => $userReview->body,
        ] : null,
    ]);
}

public function actionSubirComment()
{
    Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

    try {
        if (Yii::$app->user->isGuest) {
            return ['success' => false, 'message' => 'No autenticado'];
        }

        $recipeId = (int) Yii::$app->request->post('recipe_id');
        $score    = (int) Yii::$app->request->post('score');
        $body     = trim(Yii::$app->request->post('comment', ''));

        if (!$recipeId) {
            return ['success' => false, 'message' => 'ID de receta inválido'];
        }
        if ($score < 1 || $score > 5) {
            return ['success' => false, 'message' => 'Calificación inválida (1-5)'];
        }

        if (empty($body)) {
            $body = (string) $score;
        }

        $recipe = \app\models\Recipe::findOne([
            'id'           => $recipeId,
            'is_published' => 1,
            'is_deleted'   => 0,
        ]);
        if (!$recipe) {
            return ['success' => false, 'message' => 'Receta no encontrada'];
        }

        $userId = Yii::$app->user->id;

        $comment = \app\models\Comment::findOne([
            'recipe_id' => $recipeId,
            'user_id'   => $userId,
        ]);

        if (!$comment) {
            $comment            = new \app\models\Comment();
            $comment->recipe_id = $recipeId;
            $comment->user_id   = $userId;
        }

        $comment->score      = $score;
        $comment->body       = $body;
        $comment->is_visible = 1;

        if (!$comment->save()) {
            return [
                'success' => false,
                'message' => 'Error al guardar',
                'errors'  => $comment->errors,
            ];
        }

        $total = (int) \app\models\Comment::find()
            ->where(['recipe_id' => $recipeId, 'is_visible' => 1])
            ->count();

        $avg = (float) (\app\models\Comment::find()
            ->where(['recipe_id' => $recipeId, 'is_visible' => 1])
            ->average('score') ?? 0);

        $counts       = [];
        $distribution = [];
        for ($s = 1; $s <= 5; $s++) {
            $cnt              = (int) \app\models\Comment::find()
                ->where(['recipe_id' => $recipeId, 'score' => $s, 'is_visible' => 1])
                ->count();
            $counts[$s]       = $cnt;
            $distribution[$s] = $total > 0 ? round(($cnt / $total) * 100) : 0;
        }

        $displayComment = ((string)$score === $body) ? '' : $body;

        return [
            'success'      => true,
            'avg'          => round($avg, 2),
            'total'        => $total,
            'score'        => $score,
            'comment'      => $displayComment,
            'username'     => Yii::$app->user->identity->username,
            'avatar_url'   => Yii::$app->user->identity->avatar_url ?? null,
            'counts'       => $counts,
            'distribution' => $distribution,
        ];

    } catch (\Throwable $e) {
        Yii::$app->response->statusCode = 500;
        return [
            'success' => false,
            'message' => 'Excepción: ' . $e->getMessage(),
            'file'    => $e->getFile(),
            'line'    => $e->getLine(),
        ];
    }
}


public function actionLectura($id)
{
    $id = (int) $id;

    $recipe = \app\models\Recipe::findOne([
        'id'           => $id,
        'is_published' => 1,
        'is_deleted'   => 0,
    ]);

    if (!$recipe) {
        throw new \yii\web\NotFoundHttpException('Receta no encontrada');
    }

    $collection = [];
    if (!Yii::$app->user->isGuest) {
        $userId = Yii::$app->user->id;
        foreach (['guardado', 'favorito'] as $tipo) {
            $collection[$tipo] = (bool) Yii::$app->db->createCommand(
                'SELECT COUNT(*) FROM recipe_collections
                 WHERE recipe_id = :rid AND user_id = :uid AND tipo = :tipo',
                [':rid' => $id, ':uid' => $userId, ':tipo' => $tipo]
            )->queryScalar();
        }

        // ── Registrar en historial ──────────────────────────────────
    $db  = Yii::$app->db;
    $now = date('Y-m-d H:i:s');

    $db->createCommand(
    'INSERT INTO recipe_collections (user_id, recipe_id, tipo, created_at, updated_at)
     VALUES (:uid, :rid, :tipo, :now, :now)
     ON DUPLICATE KEY UPDATE updated_at = :now',
    [':uid' => $userId, ':rid' => $id, ':tipo' => 'historial', ':now' => $now]
    )->execute();
// ────────────────────────────────────────────────────────────
    }

    return $this->render('lectura', [
        'recipe'     => $recipe,
        'collection' => $collection,
    ]);
}

public function actionReportarReceta()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        try {
            if (Yii::$app->user->isGuest) {
                return ['success' => false, 'message' => 'No autenticado'];
            }

            $recipeId = (int) Yii::$app->request->post('recipe_id', 0);
            $userId   = (int) Yii::$app->request->post('user_id', 0);
            $motivo   = Yii::$app->request->post('motivo');
            $desc     = trim(Yii::$app->request->post('descripcion', ''));

            // Debe venir exactamente uno de los dos: recipe_id o user_id
            if (!$recipeId && !$userId) {
                return ['success' => false, 'message' => 'Falta especificar receta o usuario a reportar'];
            }
            if ($recipeId && $userId) {
                return ['success' => false, 'message' => 'No se puede reportar receta y usuario a la vez'];
            }

            $validMotivos = ['contenido_inapropiado', 'spam', 'plagio', 'informacion_falsa', 'acoso', 'otro'];
            if (!in_array($motivo, $validMotivos)) {
                return ['success' => false, 'message' => 'Motivo inválido'];
            }

            $reportedRecipeId = null;
            $reportedUserId   = null;

            if ($recipeId) {
                $recipe = \app\models\Recipe::findOne(['id' => $recipeId]);
                if (!$recipe) {
                    return ['success' => false, 'message' => 'Receta no encontrada'];
                }
                $reportedRecipeId = $recipeId;
            } else {
                $user = \app\models\User::findOne(['id' => $userId]);
                if (!$user) {
                    return ['success' => false, 'message' => 'Usuario no encontrado'];
                }
                if ($userId === (int) Yii::$app->user->id) {
                    return ['success' => false, 'message' => 'No puedes reportarte a ti mismo'];
                }
                $reportedUserId = $userId;
            }

            // Guardar reporte
            Yii::$app->db->createCommand()->insert('reports', [
                'reporter_id'        => Yii::$app->user->id,
                'reported_recipe_id' => $reportedRecipeId,
                'reported_user_id'   => $reportedUserId,
                'motivo'             => $motivo,
                'descripcion'        => $desc ?: null,
                'status'             => 'pendiente',
            ])->execute();

            // ── Notificar a todos los admins activos ──────────────────
            $admins = \app\models\User::find()
                ->select(['id'])
                ->where(['rol' => 'admin', 'is_active' => 1])
                ->asArray()
                ->all();

            if ($reportedRecipeId) {
                $asunto = 'Nueva receta reportada';
                $cuerpo = 'La receta #' . $reportedRecipeId
                        . ' ha recibido un nuevo reporte con motivo "'  . $motivo . '".'
                        . ' Revísala en el panel de administración.';
            } else {
                $asunto = 'Nuevo usuario reportado';
                $cuerpo = 'El usuario #' . $reportedUserId
                        . ' ha recibido un nuevo reporte con motivo "' . $motivo . '".'
                        . ' Revísalo en el panel de administración.';
            }

            $now = date('Y-m-d H:i:s');

            foreach ($admins as $admin) {
                Yii::$app->db->createCommand()->insert('messages', [
                    'receiver_id' => $admin['id'],
                    'sender_id'   => null,          // mensaje del sistema
                    'tipo'        => 'notificacion',
                    'asunto'      => $asunto,
                    'cuerpo'      => $cuerpo,
                    'is_read'     => 0,
                    'created_at'  => $now,
                    'updated_at'  => $now,
                ])->execute();
            }
            // ─────────────────────────────────────────────────────────

            return ['success' => true];

        } catch (\Throwable $e) {
            Yii::$app->response->statusCode = 500;
            return ['success' => false, 'message' => 'Excepción: ' . $e->getMessage()];
        }
    }

}
