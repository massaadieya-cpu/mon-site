<?php
$host = getenv('MYSQLHOST') ?: 'localhost';
$db   = getenv('MYSQLDATABASE') ?: getenv('MYSQL_DATABASE') ?: 'rh_altutex';
$user = getenv('MYSQLUSER') ?: 'root';
$pass = getenv('MYSQLPASSWORD') ?: '';
$port = getenv('MYSQLPORT') ?: '3306';

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