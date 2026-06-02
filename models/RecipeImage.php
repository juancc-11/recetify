<?php
namespace app\models;
use yii\db\ActiveRecord;

class RecipeImage extends ActiveRecord
{
    public static function tableName() { return 'recipe_images'; }
}