<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use app\models\Report;
use app\models\User;

class AdminController extends Controller
{
    public function beforeAction($action)
    {
        // PROTEGER TODO EL CONTROLLER
        if (Yii::$app->user->isGuest || Yii::$app->user->identity->rol !== 'admin') {
            throw new \yii\web\ForbiddenHttpException('No autorizado');
        }

        return parent::beforeAction($action);
    }
//funcion para refrescar el dashboard
    public function actionDashboard()
    {
        $tab = Yii::$app->request->get('tab', 'users');
        $search = Yii::$app->request->get('search');

        $userReports = [];
        $recipeReports = [];
        $bannedUsers = [];

        // USUARIOS
        if ($tab === 'users') {
            $query = Report::find()
                ->where(['status' => Report::SP])
                ->andWhere(['IS NOT', 'reported_user_id', null])
                ->joinWith('user');

            if (!empty($search)) {
                $query->andWhere(['like', 'user.username', $search]);
            }

            $userReports = $query->all();
        }

        // RECETAS
        if ($tab === 'recipes') {
            $query = Report::find()
                ->where(['status' => Report::SP]) // Uso de variables constantes
                ->andWhere(['not', ['reported_recipe_id' => null]])
                ->joinWith('recipe');

            if (!empty($search)) {
                $query->andWhere(['like', 'recipe.titulo', $search]);
            }

            $recipeReports = $query->all();
        }

        if ($tab === 'banned') {
            $bannedUsers = \app\models\User::find()
            ->where(['is_active' => 0])
            ->all();
        }

        return $this->render('dashboard', [
            'userReports' => $userReports,
            'recipeReports' => $recipeReports,
            'bannedUsers' => $bannedUsers,
        ]);
    }
//funcion de enviar en mensaje de advertencia al usuario reportado
    public function actionKick()
    {
        $id = Yii::$app->request->post('id');

        Yii::$app->db->createCommand()->insert('messages', [
            'receiver_id' => $id,
            'tipo' => 'kick',
            'asunto' => 'Advertencia',
            'cuerpo' => 'Has sido advertido por incumplir normas',
            'created_at' => date('Y-m-d H:i:s') // funcion php
        ])->execute();

        Yii::$app->db->createCommand()->update('reports', [
            'status' => 'resuelto'
        ], ['reported_user_id' => $id])->execute();

        return $this->redirect(['dashboard']);
    }
//funcion de banear permanente usuario reportado
    public function actionBan()
    {
        $id = (int) Yii::$app->request->post('id'); //conversión de tipo de dato
        
        if ($id <= 0) {
            return $this->redirect(['dashboard']);
        }

        Yii::$app->db->createCommand()->update('users', [
            'is_active' => 0
        ], ['id' => $id])->execute();
        
        Yii::$app->db->createCommand()->update('reports', [
            'status' => 'resuelto'
        ], ['reported_user_id' => $id])->execute();
        
        Yii::$app->session->setFlash('success', 'Usuario baneado y reporte resuelto');
        return $this->redirect(['dashboard', 'tab' => 'users']);
    }
//funcion de eliminar receta reportada
    public function actionDeleteRecipe()
    {
        $id = Yii::$app->request->post('id');

        Yii::$app->db->createCommand()->update('recipes', [
            'is_deleted' => 1
        ], ['id' => $id])->execute();

        Yii::$app->db->createCommand()->update('reports', [
            'status' => 'resuelto'
        ], ['reported_recipe_id' => $id])->execute();

        Yii::$app->session->setFlash('success', 'Receta eliminada correctamente.');

        return $this->redirect(['dashboard', 'tab' => 'recipes']);
    }
//funcion de eliminar usuario reportado del dashboard
    public function actionDismissReport()
    {
        $id = Yii::$app->request->post('report_id');
        
        Yii::$app->db->createCommand()->update('reports', [
            'status' => 'resuelto'
        ], ['id' => $id])->execute();

        Yii::$app->session->setFlash('info', 'Reporte descartado');
        return $this->redirect(['dashboard']);
    }
//funcion de desbanear usuario
    public function actionToggleBan()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $id = Yii::$app->request->post('id');
        $action = Yii::$app->request->post('action');
        
        $user = \app\models\User::findOne($id);
        if (!$user) {
            return ['success' => false];
        }
        if ($action === 'ban') {
            $user->is_active = 0;
        } 
        elseif ($action === 'unban') {
            $user->is_active = 1;
        }
        $user->save(false);
        return ['success' => true];
    }
    //funcion de la barra de busqueda
    public function actionSearchAjax()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        
        $tab = Yii::$app->request->get('tab');
        $search = trim(Yii::$app->request->get('search', ''));
        $order = Yii::$app->request->get('order', 'all');
        
        $data = [];
        
        if ($tab === 'users') {
            $query = Report::find()
            ->alias('r')
            ->joinWith(['user u'])
            ->where(['r.status' => 'pendiente'])
            ->andWhere(['IS NOT', 'r.reported_user_id', null]);
            
            if (!empty($search)) {
                $query->andWhere([
                    'OR',
                    ['like', 'u.username', $search],
                    ['like', 'r.motivo', $search],
                    ['like', 'r.descripcion', $search],
                ]);
            }

            if ($order === 'recent') {
                $query->orderBy(['r.created_at' => SORT_DESC]);
                } elseif ($order === 'old') {
                    $query->orderBy(['r.created_at' => SORT_ASC]);
                    } else {
                        $query->orderBy(['r.id' => SORT_DESC]);
            }

            $data = $query->all();
        }
        
        if ($tab === 'recipes') {
            $query = Report::find()
            ->alias('r')
            ->joinWith(['recipe rc'])
            ->where(['r.status' => 'pendiente'])
            ->andWhere(['IS NOT', 'r.reported_recipe_id', null]);
            
            if (!empty($search)) {
                $query->andWhere([
                    'OR',
                    ['like', 'rc.titulo', $search],
                    ['like', 'r.descripcion', $search],
                ]);
            }

            if ($order === 'recent') {
                $query->orderBy(['r.created_at' => SORT_DESC]);
                } elseif ($order === 'old') {
                    $query->orderBy(['r.created_at' => SORT_ASC]);
                    } else {
                        $query->orderBy(['r.id' => SORT_DESC]);
            }

            $data = $query->all();
        }

        if ($tab === 'banned') {
            $query = User::find()
            ->where(['is_active' => 0]);

            if (!empty($search)) {
                $query->andWhere(['like', 'username', $search]);
            }
            $data = $query->all();

            if ($order === 'recent') {
                $query->orderBy(['r.created_at' => SORT_DESC]);
                } elseif ($order === 'old') {
                    $query->orderBy(['r.created_at' => SORT_ASC]);
                    } else {
                        $query->orderBy(['r.id' => SORT_DESC]);
            }

        }
        
        $html = $this->renderPartial('_results', [
            'tab' => $tab,
            'data' => $data
        ]);
        return ['success' => true,'html' => $html];
    }
}