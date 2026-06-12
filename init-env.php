<?php
/**
 * init-env.php - Cargador de Variables de Entorno
 * 
 * Este archivo carga variables desde .env GARANTIZANDO que estén disponibles.
 * Se llama desde web/index.php ANTES de todo.
 * 
 * IMPORTANTE: Usa $_ENV directamente, NO getenv() (que puede fallar en web)
 */

// Ruta al archivo .env - SIEMPRE en la raíz del proyecto
$envFile = __DIR__ . '/.env';

// Cargar .env de forma GARANTIZADA
if (file_exists($envFile)) {
    // Leer el archivo línea por línea (método más confiable)
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    if ($lines !== false) {
        foreach ($lines as $line) {
            $line = trim($line);
            
            // Saltar líneas vacías y comentarios
            if (empty($line) || strpos($line, '#') === 0) {
                continue;
            }
            
            // Parsear línea: KEY=VALUE
            if (strpos($line, '=') !== false) {
                list($name, $value) = explode('=', $line, 2);
                $name = trim($name);
                $value = trim($value);
                
                // Remover comillas si existen
                if (preg_match('/^"(.*)"$/', $value, $matches)) {
                    $value = $matches[1];
                } elseif (preg_match("/^'(.*)'$/", $value, $matches)) {
                    $value = $matches[1];
                }
                
                // Establecer en TODOS los lugares
                putenv("$name=$value");           // Para getenv()
                $_ENV[$name] = $value;            // Para $_ENV['VAR']
                $_SERVER[$name] = $value;         // Para $_SERVER['VAR']
                
                // Debug: loguear carga
                if (strpos($name, 'PASSWORD') === false) {
                    error_log("[ENV-LOADED] $name = $value");
                } else {
                    error_log("[ENV-LOADED] $name = ***");
                }
            }
        }
    }
} else {
    error_log("ERROR: No se encontró archivo .env en: $envFile");
}

// Función helper para obtener variable de entorno de forma segura
function env($key, $default = null) {
    if (isset($_ENV[$key]) && $_ENV[$key] !== '') {
        return $_ENV[$key];
    }
    if (isset($_SERVER[$key]) && $_SERVER[$key] !== '') {
        return $_SERVER[$key];
    }
    $value = getenv($key);
    if ($value !== false && $value !== '') {
        return $value;
    }
    return $default;
}

// Verificar que las variables críticas se cargaron
$required = ['DB_HOST', 'DB_PORT', 'DB_NAME', 'DB_USERNAME', 'YII_DEBUG', 'YII_ENV'];
foreach ($required as $var) {
    $value = env($var);
    if (empty($value)) {
        error_log("⚠️  WARNING: Variable no cargada: $var");
    } else {
        error_log("✅ Variable cargada: $var");
    }
}
