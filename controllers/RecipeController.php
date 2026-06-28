<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\db\Query;
use yii\db\Expression;
 
class RecipeController extends Controller
{
    // =========================================================
    // actionSearch — búsqueda con filtros
    // =========================================================

    public function actionSearch($q = '', $filter = 'all')
    {
        $userId = Yii::$app->user->id; // null si es invitado

        $queryBuilder = (new Query())
->select([
    'recipes.id',
    'recipes.user_id',
    'recipes.titulo',
    'recipes.descripcion',
    'recipes.imagen_portada_url',
    'recipes.created_at',
    'users.username',
    'users.avatar_url',

    'avg_score' => new Expression('AVG(comments.score)'),

    'total_ratings' => new Expression('COUNT(DISTINCT comments.id)'),

    'total_views' => new Expression(
        '(SELECT COALESCE(SUM(rv.views),0)
          FROM recipe_views rv
          WHERE rv.recipe_id = recipes.id)'
    ),
])
    ->from('recipes')
    ->leftJoin('users',    'users.id = recipes.user_id')
    ->leftJoin('comments', 'comments.recipe_id = recipes.id AND comments.is_visible = 1')
    ->where([
        'recipes.is_published' => 1,
        'recipes.is_deleted'   => 0,
    ])
    ->groupBy([
        'recipes.id',
        'recipes.user_id',
        'recipes.titulo',
        'recipes.descripcion',
        'recipes.imagen_portada_url',
        'recipes.created_at',
        'users.username',
        'users.avatar_url',
    ]);

        // BUSCADOR (texto + tags)
        if (!empty($q)) {
            $queryBuilder->andWhere([
                    'or',
                    ['like', 'recipes.titulo',      $q],
                    ['like', 'recipes.descripcion', $q],
                    ['like', 'recipes.receta_texto', $q],
                    ['like', 'tags.name',           $q],   // ← busca por nombre de tag
                    ['like', 'tags.slug',           $q],   // ← busca por slug de tag
                ]);
        }

        // FILTROS
        switch ($filter) {

            case 'popular':
                $queryBuilder->orderBy([
                    'avg_score'     => SORT_DESC,
                    'total_ratings' => SORT_DESC,
                ]);
                break;

            case 'recent':
                $queryBuilder->orderBy(['recipes.created_at' => SORT_DESC]);
                break;

            case 'favorites':
                $queryBuilder->orderBy(['recipes.id' => SORT_ASC]);
                break;

            default:
                $queryBuilder->orderBy(['recipes.created_at' => SORT_DESC]);
                break;
        }

        $recipes = $queryBuilder->all();

        // ----------------------------------------------------
        // Marcar cuáles recetas ya tiene guardadas el usuario
        // ----------------------------------------------------
        $savedIds = [];

        if ($userId && !empty($recipes)) {
            $recipeIds = array_column($recipes, 'id');

            $savedIds = (new Query())
                ->select('recipe_id')
                ->from('recipe_collections')
                ->where([
                    'user_id'   => $userId,
                    'tipo'      => 'guardado',
                    'recipe_id' => $recipeIds,
                ])
                ->column();

            // column() devuelve strings desde la BD; normalizamos a int para comparar bien
            $savedIds = array_map('intval', $savedIds);
        }

        foreach ($recipes as &$recipe) {
            $recipe['is_saved'] = in_array((int) $recipe['id'], $savedIds, true);
        }
        unset($recipe);

        return $this->render('search', [
            'recipes' => $recipes,
            'query'   => $q,
            'filter'  => $filter,
        ]);
    }

    // =========================================================
    // actionCollections — Guardados / Historial / Favoritos
    // =========================================================

    public function actionCollections($tab = 'guardado')
    {
        // Redirigir si no está autenticado
        if (Yii::$app->user->isGuest) {
            return $this->redirect(['site/login']);
        }

        $validTabs = ['guardado', 'historial', 'favorito'];
        if (!in_array($tab, $validTabs, true)) {
            $tab = 'guardado';
        }

        $userId = Yii::$app->user->id;

        $recipes = (new Query())
            ->select([
                'recipes.id',
                'recipes.titulo',
                'recipes.imagen_portada_url',
                'users.username',
                'users.avatar_url',
                'avg_score'    => new Expression('AVG(comments.score)'),
                'total_ratings' => new Expression('COUNT(DISTINCT comments.id)'),
                'total_views' => new Expression(
    '(SELECT COALESCE(SUM(rv.views),0)
      FROM recipe_views rv
      WHERE rv.recipe_id = recipes.id)'
),
            ])
            ->from('recipe_collections')
            ->innerJoin('recipes',      'recipes.id = recipe_collections.recipe_id AND recipes.is_published = 1 AND recipes.is_deleted = 0')
            ->innerJoin('users',        'users.id = recipes.user_id')
            ->leftJoin('comments',      'comments.recipe_id = recipes.id AND comments.is_visible = 1')
            
            ->where([
                'recipe_collections.user_id' => $userId,
                'recipe_collections.tipo'    => $tab,
            ])
            ->groupBy([
                'recipes.id',
                'recipes.titulo',
                'recipes.imagen_portada_url',
                'users.username',
                'users.avatar_url',
            ])
            ->orderBy(['recipe_collections.created_at' => SORT_DESC])
            ->all();

        return $this->render('collections', [
            'recipes'   => $recipes,
            'activeTab' => $tab,
        ]);
    }

    // =========================================================
    // actionToggleCollection — AJAX: guardar / quitar receta
    // =========================================================

    public function actionToggleCollection()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        // Solo POST
        if (!Yii::$app->request->isPost) {
            return ['success' => false, 'message' => 'Método no permitido.'];
        }

        // Solo usuarios autenticados
        if (Yii::$app->user->isGuest) {
            return ['success' => false, 'message' => 'Debes iniciar sesión.'];
        }

        $userId   = Yii::$app->user->id;
        $recipeId = (int) Yii::$app->request->post('recipe_id');
        $tipo     = Yii::$app->request->post('tipo',   'guardado');
        $action   = Yii::$app->request->post('action', 'add');

        $validTipos = ['guardado', 'historial', 'favorito'];

        if (!in_array($tipo, $validTipos, true) || $recipeId <= 0) {
            return ['success' => false, 'message' => 'Parámetros inválidos.'];
        }

        $db = Yii::$app->db;

        try {

            if ($action === 'add') {

                $exists = (new Query())
                    ->from('recipe_collections')
                    ->where([
                        'user_id'   => $userId,
                        'recipe_id' => $recipeId,
                        'tipo'      => $tipo,
                    ])
                    ->exists();

                if (!$exists) {
                    $db->createCommand()->insert('recipe_collections', [
                        'user_id'    => $userId,
                        'recipe_id'  => $recipeId,
                        'tipo'       => $tipo,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ])->execute();
                }

                return ['success' => true, 'saved' => true];

            } else {

                $db->createCommand()->delete('recipe_collections', [
                    'user_id'   => $userId,
                    'recipe_id' => $recipeId,
                    'tipo'      => $tipo,
                ])->execute();

                return ['success' => true, 'saved' => false];
            }

        } catch (\Exception $e) {
            Yii::error($e->getMessage(), 'collections');
            return ['success' => false, 'message' => 'Error interno. Intenta de nuevo.'];
        }
    }
}