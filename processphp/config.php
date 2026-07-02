<?php

$localConfig = __DIR__ . '/local_config.php';
if (file_exists($localConfig)) {
    require_once $localConfig;
}

$requiredConfig = ['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASSWORD'];
foreach ($requiredConfig as $configName) {
    if (!defined($configName)) {
        error_log("Missing required configuration: {$configName}");
        exit('Application configuration error.');
    }
}

try {
    // PDO connection
    $connection = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASSWORD);
    $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // MySQLi connection
    $con = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    if (!$con) {
        error_log('MySQLi connection failed: ' . mysqli_connect_error());
        exit('Database connection failed.');
    }
} catch (PDOException $e) {
    error_log('PDO connection failed: ' . $e->getMessage());
    exit('Database connection failed.');
}

// // Ensure session is started only once
// if (session_status() == PHP_SESSION_NONE) {
//     session_start();
// }

// Set timezone
date_default_timezone_set('Asia/Manila');


    ?>
    
