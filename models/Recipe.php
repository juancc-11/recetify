<?php

namespace app\models;

use yii\db\ActiveRecord;
use app\models\Tag;
use app\models\RecipeImage;
use app\models\Comment;
use app\models\RecipeView;

class Recipe extends ActiveRecord
{
    public static function tableName()
    {
        return 'recipes';
    }

    // Relación con tags (N:M)
    public function getTags()
    {
        return $this->hasMany(Tag::class, ['id' => 'tag_id'])
            ->viaTable('recipe_tags', ['recipe_id' => 'id']);
    }

    // Relación con imágenes extra
    public function getImages()
    {
        return $this->hasMany(RecipeImage::class, ['recipe_id' => 'id'])
            ->orderBy(['posicion' => SORT_ASC]);
    }

    // Promedio de calificación (de comments)
    public function getAvgScore()
    {
        return (float) $this->hasMany(Comment::class, ['recipe_id' => 'id'])
            ->average('score');
    }

    // Total visitas
    public function getTotalViews()
    {
        return (int) $this->hasMany(RecipeView::class, ['recipe_id' => 'id'])
            ->sum('views');
    }

    // Total comentarios
    public function getTotalComments()
    {
        return (int) $this->hasMany(Comment::class, ['recipe_id' => 'id'])
            ->count();
    }
}