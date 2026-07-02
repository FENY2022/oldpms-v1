<?php
    require_once __DIR__ . '/../processphp/local_config.php';

    $requiredConfig = ['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASSWORD'];
    foreach ($requiredConfig as $configName) {
        if (!defined($configName)) {
            error_log("Missing required configuration: {$configName}");
            exit('Application configuration error.');
        }
    }

    try {
        $connection = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASSWORD);
        $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


        // IN NI REPLICATE INTO OTHER CONNECTED DATABASE IN TERM NAG LAHI

        $con = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);


        // EXECUTION
    } catch (PDOException $e) {
        error_log('PDO connection failed: ' . $e->getMessage());
        exit('Database connection failed.');
    }
?>
