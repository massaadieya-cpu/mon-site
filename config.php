<?php
if (getenv('MYSQLHOST')) {
    $host = 'trolley.proxy.rlwy.net';
    $port = '12388';
    $db   = 'railway';
    $user = 'root';
    $pass = 'dXRLbawXJqRwzuRyciEhabPhIbuzbUsP';
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