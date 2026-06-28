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
        if (Yii::$app->user->isGuest || Yii::$app->user->identity->rol !== 'admin') {
            throw new \yii\web\ForbiddenHttpException('No autorizado');
        }
        return parent::beforeAction($action);
    }

    // =========================================================
    // Dashboard
    // =========================================================
    public function actionDashboard()
    {
        $tab    = Yii::$app->request->get('tab', 'users');
        $search = Yii::$app->request->get('search');

        $userReports   = [];
        $recipeReports = [];
        $bannedUsers   = [];

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

        if ($tab === 'recipes') {
            $query = Report::find()
                ->where(['status' => Report::SP])
                ->andWhere(['not', ['reported_recipe_id' => null]])
                ->joinWith('recipe');

            if (!empty($search)) {
                $query->andWhere(['like', 'recipe.titulo', $search]);
            }

            $recipeReports = $query->all();
        }

        if ($tab === 'banned') {
            $bannedUsers = User::find()
                ->where(['is_active' => 0])
                ->all();
        }

        return $this->render('dashboard', [
            'userReports'   => $userReports,
            'recipeReports' => $recipeReports,
            'bannedUsers'   => $bannedUsers,
        ]);
    }

    // =========================================================
    // Kick — advertencia al usuario
    // =========================================================
    public function actionKick()
    {
        $id  = (int) Yii::$app->request->post('id');
        $tab = Yii::$app->request->post('current_tab', 'users');

        Yii::$app->db->createCommand()->insert('messages', [
            'receiver_id' => $id,
            'tipo'        => 'kick',
            'asunto'      => 'Advertencia',
            'cuerpo'      => 'Has sido advertido por incumplir las normas de la comunidad.',
            'created_at'  => date('Y-m-d H:i:s'),
            'updated_at'  => date('Y-m-d H:i:s'),
        ])->execute();

        Yii::$app->db->createCommand()->update('reports', [
            'status' => 'resuelto'
        ], ['reported_user_id' => $id])->execute();

        Yii::$app->session->setFlash('success', 'Advertencia enviada al usuario correctamente.');
        return $this->redirect(['dashboard', 'tab' => $tab]);
    }

    // =========================================================
    // Ban — banear usuario permanentemente
    // =========================================================
    public function actionBan()
    {
        $id  = (int) Yii::$app->request->post('id');
        $tab = Yii::$app->request->post('current_tab', 'users');

        if ($id <= 0) {
            return $this->redirect(['dashboard', 'tab' => $tab]);
        }

        Yii::$app->db->createCommand()->update('users', [
            'is_active' => 0
        ], ['id' => $id])->execute();

        Yii::$app->db->createCommand()->update('reports', [
            'status' => 'resuelto'
        ], ['reported_user_id' => $id])->execute();

        Yii::$app->session->setFlash('success', 'Usuario baneado y reporte resuelto correctamente.');
        return $this->redirect(['dashboard', 'tab' => $tab]);
    }

    // =========================================================
    // Delete Recipe — eliminar receta reportada
    // =========================================================
    public function actionDeleteRecipe()
    {
        $id  = (int) Yii::$app->request->post('id');
        $tab = Yii::$app->request->post('current_tab', 'recipes');

        // Obtener datos de la receta antes de eliminarla
        $recipe = Yii::$app->db->createCommand(
            'SELECT user_id, titulo FROM recipes WHERE id = :id'
        )->bindValue(':id', $id)->queryOne();

        Yii::$app->db->createCommand()->update('recipes', [
            'is_deleted' => 1
        ], ['id' => $id])->execute();

        Yii::$app->db->createCommand()->update('reports', [
            'status' => 'resuelto'
        ], ['reported_recipe_id' => $id])->execute();

        // Notificar al autor de la receta
        if ($recipe) {
            Yii::$app->db->createCommand()->insert('messages', [
                'receiver_id' => $recipe['user_id'],
                'sender_id'   => Yii::$app->user->id,
                'tipo'        => 'advertencia',
                'asunto'      => 'Tu receta ha sido eliminada',
                'cuerpo'      => 'Tu receta "' . $recipe['titulo'] . '" fue eliminada por incumplir las normas de la comunidad.',
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ])->execute();
        }

        Yii::$app->session->setFlash('success', 'Receta eliminada y autor notificado correctamente.');
        return $this->redirect(['dashboard', 'tab' => $tab]);
    }

    // =========================================================
    // Dismiss Report — descartar reporte
    // =========================================================
    public function actionDismissReport()
    {
        $id  = (int) Yii::$app->request->post('report_id');
        $tab = Yii::$app->request->post('current_tab', 'users');

        Yii::$app->db->createCommand()->update('reports', [
            'status' => 'resuelto'
        ], ['id' => $id])->execute();

        Yii::$app->session->setFlash('info', 'Reporte descartado correctamente.');
        return $this->redirect(['dashboard', 'tab' => $tab]);
    }

    // =========================================================
    // Toggle Ban — banear / desbanear vía AJAX
    // =========================================================
    public function actionToggleBan()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $id     = (int) Yii::$app->request->post('id');
        $action = Yii::$app->request->post('action');

        $user = User::findOne($id);
        if (!$user) {
            return ['success' => false, 'message' => 'Usuario no encontrado.'];
        }

        if ($action === 'ban') {

            $user->is_active = 0;
            $user->save(false);

        } elseif ($action === 'unban') {

            $user->is_active = 1;
            $user->save(false);

            // Notificar al usuario que fue desbaneado
            Yii::$app->db->createCommand()->insert('messages', [
                'receiver_id' => $id,
                'sender_id'   => Yii::$app->user->id,
                'tipo'        => 'notificacion',
                'asunto'      => 'Tu cuenta ha sido reactivada',
                'cuerpo'      => 'Tu cuenta ha sido reactivada. Por favor, respeta las normas de la comunidad para evitar futuras sanciones.',
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ])->execute();

        } else {
            return ['success' => false, 'message' => 'Acción inválida.'];
        }

        $message = $action === 'ban'
            ? 'Usuario baneado correctamente.'
            : 'Usuario desbaneado y notificado correctamente.';

        return ['success' => true, 'message' => $message];
    }

    // =========================================================
    // Search Ajax — búsqueda en tiempo real
    // =========================================================
    public function actionSearchAjax()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $tab    = Yii::$app->request->get('tab');
        $search = trim(Yii::$app->request->get('search', ''));
        $order  = Yii::$app->request->get('order', 'all');

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
                    ['like', 'u.username',    $search],
                    ['like', 'r.motivo',      $search],
                    ['like', 'r.descripcion', $search],
                ]);
            }

            if ($order === 'recent')  $query->orderBy(['r.created_at' => SORT_DESC]);
            elseif ($order === 'old') $query->orderBy(['r.created_at' => SORT_ASC]);
            else                      $query->orderBy(['r.id'         => SORT_DESC]);

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
                    ['like', 'rc.titulo',     $search],
                    ['like', 'r.descripcion', $search],
                ]);
            }

            if ($order === 'recent')  $query->orderBy(['r.created_at' => SORT_DESC]);
            elseif ($order === 'old') $query->orderBy(['r.created_at' => SORT_ASC]);
            else                      $query->orderBy(['r.id'         => SORT_DESC]);

            $data = $query->all();
        }

        if ($tab === 'banned') {
            $query = User::find()
                ->where(['is_active' => 0]);

            if (!empty($search)) {
                $query->andWhere(['like', 'username', $search]);
            }

            if ($order === 'recent')  $query->orderBy(['created_at' => SORT_DESC]);
            elseif ($order === 'old') $query->orderBy(['created_at' => SORT_ASC]);
            else                      $query->orderBy(['id'         => SORT_DESC]);

            $data = $query->all();
        }

        $html = $this->renderPartial('_results', [
            'tab'  => $tab,
            'data' => $data,
        ]);

        return ['success' => true, 'html' => $html];
    }
}