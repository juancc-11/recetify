<?php
namespace app\models;

use yii\db\ActiveRecord;
//encapsulamiento de report
class Report extends ActiveRecord //herencia
{
    const SP = 'pendiente'; // Declarar variables constantes
    const SR  = 'revisado';
    const ST  = 'resuelto';

    public static function tableName()
    {
        return 'reports';
    }

    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'reported_user_id']);
    }

    public function getRecipe()
    {
        return $this->hasOne(Recipe::class, ['id' => 'reported_recipe_id']);
    }
    
    public function __construct($config = [])
    {
        parent::__construct($config);
        
        // Valor por defecto del estado
        if (empty($this->status)) {
            $this->status = self::SP;
        }
    }
    
    //obtener usuario baneado
    public function isUserBanned()
    {
        return $this->user && $this->user->is_active == 0;
    }
}