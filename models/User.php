<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

class User extends ActiveRecord implements IdentityInterface
{
    // CAMPOS EXTRA (no están en BD)
    public $password;
    public $repeat_password;
    public $avatar_file;

    public static function tableName()
    {
        return 'users';
    }

    /* ================= VALIDACIONES ================= */
    public function rules()
{
    return [
        [['username', 'email'], 'required'],
        ['email', 'email'],

        ['username', 'string', 'max' => 50],
        ['email', 'string', 'max' => 255],

        [['password', 'repeat_password'], 'required', 'on' => 'register'],

        ['password', 'string', 'min' => 6],

        ['repeat_password', 'compare',
            'compareAttribute' => 'password',
            'message' => 'Las contraseñas no coinciden'
        ],

        ['avatar_file', 'file',
            'extensions' => 'png, jpg, jpeg',
            'skipOnEmpty' => true
        ],

        [['username', 'email'], 'unique'],
    ];
}

    /* ================= IDENTITY ================= */

    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'is_active' => 1]);
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
        return null;
    }

    public static function findByUsername($username)
    {
        return static::findOne([
            'username' => $username,
            'is_active' => 1
        ]);
    }

    public function getId()
    {
        return $this->id;
    }

    public function getAuthKey()
    {
        return null;
    }

    public function validateAuthKey($authKey)
    {
        return true;
    }

    /* ================= PASSWORD ================= */

    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword(
            $password,
            $this->password_hash
        );
    }
}