<?php
$host = $_ENV['MYSQLHOST'] ?? getenv('MYSQLHOST') ?: 'localhost';
$db = $_ENV['MYSQLDATABASE'] ?? getenv('MYSQLDATABASE') ?? $_ENV['MYSQL_DATABASE'] ?? getenv('MYSQL_DATABASE') ?: 'rh_altutex';
$user = $_ENV['MYSQLUSER'] ?? getenv('MYSQLUSER') ?: 'root';
$pass = $_ENV['MYSQLPASSWORD'] ?? getenv('MYSQLPASSWORD') ?: '';
$port = $_ENV['MYSQLPORT'] ?? getenv('MYSQLPORT') ?: '3306';
$charset = 'utf8mb4';
$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";
try {
     $pdo = new PDO($dsn, $user, $pass, [
         PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
         PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
     ]);
} catch (\PDOException $e) {
     die("Erreur de connexion : " . $e->getMessage());
}
?>