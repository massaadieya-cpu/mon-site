<?php
$host = getenv('MYSQLHOST');
$db   = getenv('MYSQL_DATABASE');
$user = getenv('MYSQLUSER');
$pass = getenv('MYSQLPASSWORD');
$port = getenv('MYSQLPORT');
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