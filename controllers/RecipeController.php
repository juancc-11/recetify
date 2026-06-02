<?php

namespace app\controllers;

use yii\web\Controller;
use yii\db\Query;
use yii\db\Expression;

class RecipeController extends Controller
{
    public function actionSearch($q = '', $filter = 'all')
    {

        $queryBuilder = (new Query())
            ->select([
                'recipes.id',
                'recipes.titulo',
                'recipes.descripcion',
                'recipes.imagen_portada_url',
                'recipes.created_at',
                'users.username',
                'users.avatar_url',

                // CALIFICACIÓN
                'avg_score' => new Expression('AVG(comments.score)'),
                'total_ratings' => new Expression('COUNT(comments.id)'),
            ])
            ->from('recipes')
            ->leftJoin(
                'users',
                'users.id = recipes.user_id'
            )
            ->leftJoin(
                'comments',
                'comments.recipe_id = recipes.id AND comments.is_visible = 1'
            )
            ->where([
                'recipes.is_published' => 1,
                'recipes.is_deleted'   => 0,
            ])
            ->groupBy([
                'recipes.id',
                'recipes.titulo',
                'recipes.descripcion',
                'recipes.imagen_portada_url',
                'recipes.created_at',
                'users.username',
                'users.avatar_url',
            ]);

        // BUSCADOR
        if (!empty($q)) {

            $queryBuilder->andWhere([
                'or',
                ['like', 'recipes.titulo', $q],
                ['like', 'recipes.descripcion', $q],
                ['like', 'recipes.receta_texto', $q]
            ]);
        }

        // FILTROS
        switch ($filter) {

            case 'popular':

                // Más populares por promedio y cantidad
                $queryBuilder->orderBy([
                    'avg_score'      => SORT_DESC,
                    'total_ratings'  => SORT_DESC,
                ]);

            break;

            case 'recent':

                $queryBuilder->orderBy([
                    'recipes.created_at' => SORT_DESC
                ]);

            break;

            case 'favorites':

                $queryBuilder->orderBy([
                    'recipes.id' => SORT_ASC
                ]);

            break;

            default:

                $queryBuilder->orderBy([
                    'recipes.created_at' => SORT_DESC
                ]);

            break;
        }

        $recipes = $queryBuilder->all();

        return $this->render('search', [
            'recipes' => $recipes,
            'query'   => $q,
            'filter'  => $filter
        ]);
    }
}