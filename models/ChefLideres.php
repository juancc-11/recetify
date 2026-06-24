<?php

namespace app\models;

use Yii;
use yii\base\Model;

/**
 * ChefLideres Model
 *
 * Obtiene los mejores chefs (usuarios con recetas publicadas)
 * según distintos criterios: calificación total, mes actual y semana actual.
 *
 * Cada "chef" retornado incluye:
 *   - user_id, username, full_name, avatar_url
 *   - receta_titulo, receta_descripcion, receta_imagen (portada de su mejor receta)
 *   - total_views    (suma de vistas de sus recetas)
 *   - avg_score      (promedio de calificaciones recibidas)
 *   - total_recipes  (cantidad de recetas publicadas)
 */
class ChefLideres extends Model
{
    /**
     * Top $limit chefs por promedio de calificación (todos los tiempos).
     */
    public function getMejoresCalificados(int $limit = 3): array
    {
        $sql = <<<SQL
            SELECT
                u.id            AS user_id,
                u.username,
                u.full_name,
                u.avatar_url,
                ROUND(AVG(c.score), 1)          AS avg_score,
                COUNT(DISTINCT r.id)             AS total_recipes,
                COALESCE(SUM(rv.views), 0)       AS total_views,
                -- receta con mayor promedio de score del chef
                (
                    SELECT r2.titulo
                    FROM recipes r2
                    LEFT JOIN comments c2 ON c2.recipe_id = r2.id
                    WHERE r2.user_id = u.id AND r2.is_published = 1 AND r2.is_deleted = 0
                    GROUP BY r2.id
                    ORDER BY AVG(c2.score) DESC
                    LIMIT 1
                ) AS receta_titulo,
                (
                    SELECT r2.descripcion
                    FROM recipes r2
                    LEFT JOIN comments c2 ON c2.recipe_id = r2.id
                    WHERE r2.user_id = u.id AND r2.is_published = 1 AND r2.is_deleted = 0
                    GROUP BY r2.id
                    ORDER BY AVG(c2.score) DESC
                    LIMIT 1
                ) AS receta_descripcion,
                (
                    SELECT COALESCE(r2.imagen_portada_url, 'https://picsum.photos/seed/' || CAST(r2.id AS CHAR) || '/400/300')
                    FROM recipes r2
                    LEFT JOIN comments c2 ON c2.recipe_id = r2.id
                    WHERE r2.user_id = u.id AND r2.is_published = 1 AND r2.is_deleted = 0
                    GROUP BY r2.id
                    ORDER BY AVG(c2.score) DESC
                    LIMIT 1
                ) AS receta_imagen
            FROM users u
            INNER JOIN recipes r    ON r.user_id = u.id AND r.is_published = 1 AND r.is_deleted = 0
            LEFT  JOIN comments c   ON c.recipe_id = r.id
            LEFT  JOIN recipe_views rv ON rv.recipe_id = r.id
            WHERE u.is_active = 1 AND u.rol = 'usuario'
            GROUP BY u.id, u.username, u.full_name, u.avatar_url
            HAVING COUNT(c.id) > 0
            ORDER BY avg_score DESC, total_views DESC
            LIMIT :limit
        SQL;

        return $this->query($sql, [':limit' => $limit]);
    }

    /**
     * Top $limit chefs con más vistas en el mes actual.
     */
    public function getMejoresDelMes(int $limit = 3): array
    {
        $sql = <<<SQL
            SELECT
                u.id            AS user_id,
                u.username,
                u.full_name,
                u.avatar_url,
                ROUND(AVG(c.score), 1)          AS avg_score,
                COUNT(DISTINCT r.id)             AS total_recipes,
                COALESCE(SUM(rv.views), 0)       AS total_views,
                (
                    SELECT r2.titulo
                    FROM recipes r2
                    LEFT JOIN recipe_views rv2 ON rv2.recipe_id = r2.id
                        AND MONTH(rv2.view_date) = MONTH(CURDATE())
                        AND YEAR(rv2.view_date)  = YEAR(CURDATE())
                    WHERE r2.user_id = u.id AND r2.is_published = 1 AND r2.is_deleted = 0
                    GROUP BY r2.id
                    ORDER BY COALESCE(SUM(rv2.views), 0) DESC
                    LIMIT 1
                ) AS receta_titulo,
                (
                    SELECT r2.descripcion
                    FROM recipes r2
                    LEFT JOIN recipe_views rv2 ON rv2.recipe_id = r2.id
                        AND MONTH(rv2.view_date) = MONTH(CURDATE())
                        AND YEAR(rv2.view_date)  = YEAR(CURDATE())
                    WHERE r2.user_id = u.id AND r2.is_published = 1 AND r2.is_deleted = 0
                    GROUP BY r2.id
                    ORDER BY COALESCE(SUM(rv2.views), 0) DESC
                    LIMIT 1
                ) AS receta_descripcion,
                (
                    SELECT COALESCE(r2.imagen_portada_url, 'https://picsum.photos/seed/' || CAST(r2.id AS CHAR) || '/400/300')
                    FROM recipes r2
                    LEFT JOIN recipe_views rv2 ON rv2.recipe_id = r2.id
                        AND MONTH(rv2.view_date) = MONTH(CURDATE())
                        AND YEAR(rv2.view_date)  = YEAR(CURDATE())
                    WHERE r2.user_id = u.id AND r2.is_published = 1 AND r2.is_deleted = 0
                    GROUP BY r2.id
                    ORDER BY COALESCE(SUM(rv2.views), 0) DESC
                    LIMIT 1
                ) AS receta_imagen
            FROM users u
            INNER JOIN recipes r    ON r.user_id = u.id AND r.is_published = 1 AND r.is_deleted = 0
            LEFT  JOIN comments c   ON c.recipe_id = r.id
            INNER JOIN recipe_views rv ON rv.recipe_id = r.id
                AND MONTH(rv.view_date) = MONTH(CURDATE())
                AND YEAR(rv.view_date)  = YEAR(CURDATE())
            WHERE u.is_active = 1 AND u.rol = 'usuario'
            GROUP BY u.id, u.username, u.full_name, u.avatar_url
            ORDER BY total_views DESC, avg_score DESC
            LIMIT :limit
        SQL;

        return $this->query($sql, [':limit' => $limit]);
    }

    /**
     * Top $limit chefs con más vistas en la semana actual (lunes–domingo).
     */
    public function getMejoresDeLaSemana(int $limit = 3): array
    {
        $sql = <<<SQL
            SELECT
                u.id            AS user_id,
                u.username,
                u.full_name,
                u.avatar_url,
                ROUND(AVG(c.score), 1)          AS avg_score,
                COUNT(DISTINCT r.id)             AS total_recipes,
                COALESCE(SUM(rv.views), 0)       AS total_views,
                (
                    SELECT r2.titulo
                    FROM recipes r2
                    LEFT JOIN recipe_views rv2 ON rv2.recipe_id = r2.id
                        AND rv2.view_date >= DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY)
                    WHERE r2.user_id = u.id AND r2.is_published = 1 AND r2.is_deleted = 0
                    GROUP BY r2.id
                    ORDER BY COALESCE(SUM(rv2.views), 0) DESC
                    LIMIT 1
                ) AS receta_titulo,
                (
                    SELECT r2.descripcion
                    FROM recipes r2
                    LEFT JOIN recipe_views rv2 ON rv2.recipe_id = r2.id
                        AND rv2.view_date >= DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY)
                    WHERE r2.user_id = u.id AND r2.is_published = 1 AND r2.is_deleted = 0
                    GROUP BY r2.id
                    ORDER BY COALESCE(SUM(rv2.views), 0) DESC
                    LIMIT 1
                ) AS receta_descripcion,
                (
                    SELECT COALESCE(r2.imagen_portada_url, 'https://picsum.photos/seed/' || CAST(r2.id AS CHAR) || '/400/300')
                    FROM recipes r2
                    LEFT JOIN recipe_views rv2 ON rv2.recipe_id = r2.id
                        AND rv2.view_date >= DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY)
                    WHERE r2.user_id = u.id AND r2.is_published = 1 AND r2.is_deleted = 0
                    GROUP BY r2.id
                    ORDER BY COALESCE(SUM(rv2.views), 0) DESC
                    LIMIT 1
                ) AS receta_imagen
            FROM users u
            INNER JOIN recipes r    ON r.user_id = u.id AND r.is_published = 1 AND r.is_deleted = 0
            LEFT  JOIN comments c   ON c.recipe_id = r.id
            INNER JOIN recipe_views rv ON rv.recipe_id = r.id
                AND rv.view_date >= DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY)
            WHERE u.is_active = 1 AND u.rol = 'usuario'
            GROUP BY u.id, u.username, u.full_name, u.avatar_url
            ORDER BY total_views DESC, avg_score DESC
            LIMIT :limit
        SQL;

        return $this->query($sql, [':limit' => $limit]);
    }

    // ----------------------------------------------------------------
    // Helper privado
    // ----------------------------------------------------------------

    /**
     * Ejecuta un query y retorna array de resultados.
     * Si no hay datos reales, retorna datos de ejemplo para no mostrar vacío.
     */
    private function query(string $sql, array $params = []): array
    {
        try {
            $rows = Yii::$app->db->createCommand($sql, $params)->queryAll();
            return !empty($rows) ? $rows : [];
        } catch (\Exception $e) {
            Yii::error($e->getMessage(), 'chef-lideres');
            return [];
        }
    }
}
