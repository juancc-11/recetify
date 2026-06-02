<?php
namespace app\models;
use yii\db\ActiveRecord;

class RecipeView extends ActiveRecord
{
    public static function tableName() { return 'recipe_views'; }
}