<?php
/**
 * Setup script: creates the database and tables if they don't exist.
 * Run once via browser: http://localhost/elearning/php/setup_database.php
 * Or via CLI: php setup_database.php
 */

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "elearning_platform";

// Connect without selecting a database
$conn = new mysqli($servername, $username, $password);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

$sqlFile = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'elearning_platform.sql';
if (!is_file($sqlFile)) {
    die("SQL file not found: " . $sqlFile);
}

$sql = file_get_contents($sqlFile);
// Remove SQL comments (-- ...) to avoid parsing issues
$sql = preg_replace('/--[^\n]*\n/', "\n", $sql);

if ($conn->multi_query($sql)) {
    do {
        if ($result = $conn->store_result()) {
            $result->free();
        }
    } while ($conn->next_result());
}

if ($conn->errno) {
    die("SQL Error: " . $conn->error . " (errno: " . $conn->errno . ")");
}

$conn->close();

// Output success
$cli = (php_sapi_name() === 'cli');
if ($cli) {
    echo "Database '$dbname' created successfully.\n";
    echo "You can now run the project. Admin: username=admin, password=admin123\n";
} else {
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Setup OK</title></head><body>';
    echo '<h1>Database setup complete</h1>';
    echo '<p>Database <strong>' . htmlspecialchars($dbname) . '</strong> has been created with all tables and default data.</p>';
    echo '<p>You can now <a href="../index.php">open the project</a>. Admin login: <strong>admin</strong> / <strong>admin123</strong></p>';
    echo '</body></html>';
}
