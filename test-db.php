<?php
/**
 * Script de prueba de conexión a Base de Datos
 * Ejecutar desde: http://localhost/blogrecetas/test-db.php
 */

// Cargar variables de entorno
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/init-env.php';

echo "<h1>🧪 Test de Conexión a Base de Datos</h1>";
echo "<hr>";

// Mostrar variables cargadas
echo "<h2>1️⃣ Variables de Entorno Cargadas:</h2>";
echo "<pre>";
echo "DB_HOST: " . ($_ENV['DB_HOST'] ?? '[VACÍA]') . "\n";
echo "DB_PORT: " . ($_ENV['DB_PORT'] ?? '[VACÍA]') . "\n";
echo "DB_NAME: " . ($_ENV['DB_NAME'] ?? '[VACÍA]') . "\n";
echo "DB_USERNAME: " . ($_ENV['DB_USERNAME'] ?? '[VACÍA]') . "\n";
echo "DB_PASSWORD: " . (($_ENV['DB_PASSWORD'] ?? '') ? '***[Oculta]***' : '[VACÍA]') . "\n";
echo "</pre>";

// Intentar conexión
echo "<h2>2️⃣ Intentando Conexión a Aiven Cloud MySQL:</h2>";

try {
    $host     = $_ENV['DB_HOST'] ?? 'localhost';
    $port     = $_ENV['DB_PORT'] ?? '3306';
    $database = $_ENV['DB_NAME'] ?? 'recetas_db';
    $user     = $_ENV['DB_USERNAME'] ?? 'root';
    $pass     = $_ENV['DB_PASSWORD'] ?? '';
    
    $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";
    
    echo "<p><strong>DSN:</strong> " . htmlspecialchars($dsn) . "</p>";
    
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_TIMEOUT => 5,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    
    echo "<p style='color: green;'><strong>✅ Conexión exitosa!</strong></p>";
    
    // Test de tabla
    echo "<h2>3️⃣ Verificando Tablas:</h2>";
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (count($tables) > 0) {
        echo "<p><strong>Tablas encontradas:</strong></p>";
        echo "<ul>";
        foreach ($tables as $table) {
            echo "<li>" . htmlspecialchars($table) . "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p style='color: orange;'><strong>⚠️ No hay tablas en la base de datos</strong></p>";
    }
    
    // Test de usuarios
    echo "<h2>4️⃣ Test de Tabla 'users':</h2>";
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p>Total de usuarios: <strong>" . $result['total'] . "</strong></p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'><strong>❌ Error de conexión:</strong></p>";
    echo "<pre style='color: red;'>";
    echo htmlspecialchars($e->getMessage());
    echo "</pre>";
    echo "<p><strong>Código de error:</strong> " . $e->getCode() . "</p>";
}

echo "<hr>";
echo "<p><a href='/blogrecetas/'>← Volver al inicio</a></p>";
?>
