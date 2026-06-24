<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use app\models\ChefLideres;

/**
 * ChefLideresController
 * Página: Chef Líderes
 * Muestra los 3 mejores chefs por categoría (calificación, mes, semana)
 * con carrusel de recetas destacadas.
 *
 * Ruta sugerida en config/web.php:
 *   'chef-lideres' => 'chef-lideres/index',
 */
class ChefLideresController extends Controller
{
    public $layout = 'main'; // usa el mismo layout del proyecto

    /**
     * Página principal de Chef Líderes
     */
    public function actionIndex()
    {
        $model = new ChefLideres();

        return $this->render('index', [
            'mejoresCalificados' => $model->getMejoresCalificados(3),
            'mejoresDelMes'      => $model->getMejoresDelMes(3),
            'mejoresDeLaSemana'  => $model->getMejoresDeLaSemana(3),
        ]);
    }
}
