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
            'access' => [
                'class' => AccessControl::class,
                'only' => ['logout', 'profile', 'delete-account', 'mis-recetas', 'crear-receta',
                   'toggle-publish', 'get-recipe', 'actualizar-receta', 'recipe-stats',
                   'borrar-receta', 'subir-imagen-temp'],
                'rules' => [
                    [
                        'actions' => ['logout', 'profile', 'delete-account', 'mis-recetas', 'crear-receta',
                           'toggle-publish', 'get-recipe', 'actualizar-receta', 'recipe-stats',
                           'borrar-receta', 'subir-imagen-temp'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout'         => ['post'],
                    'delete-account' => ['post'],
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


    /* ── OBTENER RECETA (para modal editar) ─────────────────────── */
    public function actionGetRecipe()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if (Yii::$app->user->isGuest) {
            return ['error' => true, 'message' => 'No autenticado'];
        }

        $id = (int) Yii::$app->request->get('id');

        // ✅ FIX: buscar también con is_deleted = null por si la columna no tiene default
        $recipe = \app\models\Recipe::find()
            ->where(['id' => $id, 'user_id' => Yii::$app->user->id])
            ->andWhere(['or', ['is_deleted' => 0], ['is_deleted' => null]])
            ->one();

        if (!$recipe) {
            return ['error' => true, 'message' => 'Receta no encontrada'];
        }

        $images = \app\models\RecipeImage::find()
            ->where(['recipe_id' => $id])
            ->orderBy(['posicion' => SORT_ASC])
            ->all();

        $imagesData = array_map(function ($img) {
            return [
                'id'        => $img->id,
                'posicion'  => $img->posicion,
                'image_url' => $img->image_url,
            ];
        }, $images);

        return [
            'id'                 => $recipe->id,
            'titulo'             => $recipe->titulo,
            'descripcion'        => $recipe->descripcion,
            'utensilios'         => $recipe->utensilios ?? '',
            'receta_texto'       => $recipe->receta_texto ?? '',
            'imagen_portada_url' => $recipe->imagen_portada_url ?? '',
            'images'             => $imagesData,
        ];
    }


    /* ══════════════════════════════════════════════════════
       ACTUALIZAR RECETA
       FIX: lee URLs de Cloudinary para imágenes nuevas
            en lugar de intentar re-subir archivos
    ══════════════════════════════════════════════════════ */
    public function actionActualizarReceta()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if (Yii::$app->user->isGuest) {
            return ['success' => false];
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

        $recipe->titulo       = $post['titulo']       ?? $recipe->titulo;
        $recipe->descripcion  = $post['descripcion']  ?? $recipe->descripcion;
        $recipe->utensilios   = $post['utensilios']   ?? $recipe->utensilios;
        $recipe->receta_texto = $post['receta_texto'] ?? $recipe->receta_texto;

        // Nueva portada (se sube desde el form normal, es el único archivo real)
        $portada = \yii\web\UploadedFile::getInstanceByName('Recipe[portada]');
        if ($portada) {
            $cfg = Yii::$app->params['cloudinary'];
            $cl  = new \Cloudinary\Cloudinary([
                'cloud' => [
                    'cloud_name' => trim($cfg['cloud_name']),
                    'api_key'    => $cfg['api_key'],
                    'api_secret' => $cfg['api_secret'],
                ],
            ]);
            $up = $cl->uploadApi()->upload($portada->tempName, ['folder' => 'recetify/recipes']);
            $recipe->imagen_portada_url = $up['secure_url'];
        }

        if (!$recipe->save(false)) {
            return ['success' => false, 'message' => 'Error al guardar'];
        }

        // ── 1. Borrar imágenes marcadas para eliminar ─────────────
        $toDelete = $post['delete_image'] ?? [];
        foreach ($toDelete as $imgId) {
            $imgId = (int) $imgId;
            if (!$imgId) continue;
            $img = \app\models\RecipeImage::findOne(['id' => $imgId, 'recipe_id' => $id]);
            if ($img) $img->delete();
        }

        // ── 2. Guardar imágenes nuevas ────────────────────────────
        // ✅ FIX: el JS ya subió las imágenes a Cloudinary y envía las URLs.
        //         Solo hay que guardar el registro en BD, sin re-subir nada.
        $cloudinaryUrls = $post['cloudinary_urls'] ?? [];
        foreach ($cloudinaryUrls as $pos => $url) {
            $pos = (int) $pos;
            $url = trim($url);
            if (!$url || !in_array($pos, [1, 2, 3])) continue;

            // Si ya existe una imagen en esa posición, actualizarla
            $img = \app\models\RecipeImage::findOne(['recipe_id' => $id, 'posicion' => $pos]);
            if (!$img) {
                $img            = new \app\models\RecipeImage();
                $img->recipe_id = $id;
                $img->posicion  = $pos;
            }
            $img->image_url = $url;
            $img->save(false);
        }

        return ['success' => true];
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

    /* ── ESTADÍSTICAS DE UNA RECETA ─────────────────────────────── */
    public function actionRecipeStats()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if (Yii::$app->user->isGuest) {
            return ['error' => true];
        }

        $recipeId = (int) Yii::$app->request->get('recipe_id');

        $recipe = \app\models\Recipe::find()
            ->where(['id' => $recipeId, 'user_id' => Yii::$app->user->id])
            ->andWhere(['or', ['is_deleted' => 0], ['is_deleted' => null]])
            ->one();

        if (!$recipe) {
            return ['error' => true];
        }

        $visitas = Yii::$app->db->createCommand('
            SELECT DATE_FORMAT(view_date, "%d/%m") AS fecha, SUM(views) AS total
            FROM recipe_views
            WHERE recipe_id = :id
              AND view_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
            GROUP BY view_date
            ORDER BY view_date ASC
        ', [':id' => $recipeId])->queryAll();

        $totalVisitas = Yii::$app->db->createCommand(
            'SELECT COALESCE(SUM(views),0) FROM recipe_views WHERE recipe_id = :id',
            [':id' => $recipeId]
        )->queryScalar();

        $totalComentarios = \app\models\Comment::find()
            ->where(['recipe_id' => $recipeId])->count();

        $totalGuardados = Yii::$app->db->createCommand(
            "SELECT COUNT(*) FROM recipe_collections WHERE recipe_id = :id AND tipo='guardado'",
            [':id' => $recipeId]
        )->queryScalar();

        $totalFavoritos = Yii::$app->db->createCommand(
            "SELECT COUNT(*) FROM recipe_collections WHERE recipe_id = :id AND tipo='favorito'",
            [':id' => $recipeId]
        )->queryScalar();

        $totalHistorial = Yii::$app->db->createCommand(
            "SELECT COUNT(*) FROM recipe_collections WHERE recipe_id = :id AND tipo='historial'",
            [':id' => $recipeId]
        )->queryScalar();

        $avgActual = \app\models\Comment::find()
            ->where(['recipe_id' => $recipeId])
            ->average('score') ?? 0;

        $posicion = (int) Yii::$app->db->createCommand('
            SELECT COUNT(*) + 1
            FROM (
                SELECT recipe_id, AVG(score) AS avg_score
                FROM comments
                GROUP BY recipe_id
            ) AS ranks
            WHERE avg_score > :avg
        ', [':avg' => $avgActual])->queryScalar();

        $totalRecetas = (int) \app\models\Recipe::find()
            ->where(['is_published' => 1, 'is_deleted' => 0])
            ->count();

        return [
            'visitas' => $visitas,
            'totales' => [
                'visitas'     => (int) $totalVisitas,
                'comentarios' => (int) $totalComentarios,
                'guardados'   => (int) $totalGuardados,
                'favoritos'   => (int) $totalFavoritos,
                'historial'   => (int) $totalHistorial,
            ],
            'ranking' => [
                'posicion' => $posicion,
                'total'    => $totalRecetas,
            ],
        ];
    }

    /* ── SUBIR IMAGEN TEMPORAL A CLOUDINARY ─────────────────────── */
    public function actionSubirImagenTemp()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if (Yii::$app->user->isGuest) {
            return ['success' => false, 'message' => 'No autenticado'];
        }

        $file = \yii\web\UploadedFile::getInstanceByName('imagen');

        if (!$file || !$file->tempName) {
            return ['success' => false, 'message' => 'No se recibió imagen'];
        }

        $allowed = ['image/jpeg', 'image/png', 'image/webp'];
        if (!in_array($file->type, $allowed)) {
            return ['success' => false, 'message' => 'Tipo de archivo no permitido'];
        }
        if ($file->size > 5 * 1024 * 1024) {
            return ['success' => false, 'message' => 'La imagen supera los 5 MB'];
        }

        try {
            $cfg        = Yii::$app->params['cloudinary'];
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
                'url'     => $upload['secure_url'],
            ];

        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Error al subir: ' . $e->getMessage()];
        }
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
}