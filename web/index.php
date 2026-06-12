<?php

require __DIR__ . '/../vendor/autoload.php'; //funcion require para carga clases automáticamente

// Cargar variables de entorno - DEBE ser lo primero
require __DIR__ . '/../init-env.php';

// Usar la función env() para obtener valores (usa $_ENV internamente)
$yii_debug = env('YII_DEBUG', 'false');
$yii_env = env('YII_ENV', 'prod');

// comment out the following two lines when deployed to production
defined('YII_DEBUG') or define('YII_DEBUG', $yii_debug === 'true');
defined('YII_ENV') or define('YII_ENV', $yii_env);

require __DIR__ . '/../vendor/yiisoft/yii2/Yii.php'; //inicia el framework Yii

$config = require __DIR__ . '/../config/web.php'; //carga configuración en $config

(new yii\web\Application($config))->run();
