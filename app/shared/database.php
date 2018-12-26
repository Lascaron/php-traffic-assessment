<?php

    // Get the credentials.
    require 'connection.php';

    class Database {
 
        // Credentials.
        private $host = MYSQL_HOST;
        private $db_name = MYSQL_SCHEMA;
        private $username = MYSQL_USER;
        private $password = MYSQL_PASSWORD;
        public $conn;
 
        // Database connection.
        public function getConnection() {
 
            $this->conn = null;
 
            try {

                $this->conn = new PDO("mysql:host={$this->host};dbname={$this->db_name};charset=utf8", $this->username, $this->password);
                $this->conn->exec('set names utf8');
            } catch(PDOException $exception) {

                echo 'Connection error: '.$exception->getMessage();
            }

            return $this->conn;
        }
    }

?>
