<?php
if (getenv('MYSQLHOST')) {
    $host = getenv('MYSQLHOST');
    $port = getenv('MYSQLPORT') ?: '3306';
    $db   = getenv('MYSQLDATABASE') ?: getenv('MYSQL_DATABASE') ?: 'railway';
    $user = getenv('MYSQLUSER') ?: 'root';
    $pass = getenv('MYSQLPASSWORD') ?: '';
} else {
    $host = 'localhost';
    $port = '3306';
    $db   = 'rh_altutex';
    $user = 'root';
    $pass = '';
}
$charset = 'utf8mb4';
$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";
try {
     $pdo = new PDO($dsn, $user, $pass, [
         PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
         PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
         PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
     ]);
} catch (\PDOException $e) {
     die("Erreur de connexion : " . $e->getMessage());
}
?>