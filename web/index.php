<?php

require __DIR__ . '/../vendor/autoload.php'; //funcion require para carga clases automáticamente

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

// comment out the following two lines when deployed to production
defined('YII_DEBUG') or define('YII_DEBUG', getenv('YII_DEBUG') === 'true');
defined('YII_ENV') or define('YII_ENV', getenv('YII_ENV') ?: 'prod');

require __DIR__ . '/../vendor/yiisoft/yii2/Yii.php'; //inicia el framework Yii

$config = require __DIR__ . '/../config/web.php'; //carga configuración en $config

(new yii\web\Application($config))->run();
