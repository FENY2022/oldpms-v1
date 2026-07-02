<?php

  require_once __DIR__ . '/../processphp/local_config.php';

  $requiredConfig = ['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASSWORD'];
  foreach ($requiredConfig as $configName) {
    if (!defined($configName)) {
      error_log("Missing required configuration: {$configName}");
      exit('Application configuration error.');
    }
  }

  class Config {
    private $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . '';

    protected $conn = null;



    // Method for connection to the database
    public function __construct() {
      try {
        $this->conn = new PDO($this->dsn, DB_USER, DB_PASSWORD);
        $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      } catch (PDOException $e) {
        error_log('PDO connection failed: ' . $e->getMessage());
        exit('Database connection failed.');
      }
    }
  }
  

?>
