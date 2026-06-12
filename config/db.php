<?php

// Variables de entorno cargadas por init-env.php
// Usa $_ENV directamente (no getenv()) porque es más confiable en web
$db_host     = $_ENV['DB_HOST']     ?? 'localhost';
$db_port     = $_ENV['DB_PORT']     ?? '3306';
$db_name     = $_ENV['DB_NAME']     ?? 'recetas_db';
$db_username = $_ENV['DB_USERNAME'] ?? 'root';
$db_password = $_ENV['DB_PASSWORD'] ?? '';

// Debug logging (solo en desarrollo)
if (($_ENV['YII_DEBUG'] ?? 'false') === 'true') {
    error_log('DB Config - Host: ' . $db_host);
    error_log('DB Config - Port: ' . $db_port);
    error_log('DB Config - Name: ' . $db_name);
    error_log('DB Config - User: ' . $db_username);
}

return [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=' . $db_host . ';port=' . $db_port . ';dbname=' . $db_name,
    'username' => $db_username,
    'password' => $db_password,
    'charset' => 'utf8mb4',
    
    // Opciones de conexión para Aiven/Cloud
    'attributes' => [
        \PDO::ATTR_TIMEOUT => 10,
        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
    ],
    
    // Enable pooling for better performance
    'enableSlaves' => false,

    // Schema cache options (for production environment)
    //'enableSchemaCache' => true,
    //'schemaCacheDuration' => 60,
    //'schemaCache' => 'cache',
];
