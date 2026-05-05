<?php
$url = $_ENV['MYSQL_PUBLIC_URL'] ?? getenv('MYSQL_PUBLIC_URL') ?: null;

if ($url) {
    $parsed = parse_url($url);
    $host = $parsed['host'];
    $port = $parsed['port'];
    $user = $parsed['user'];
    $pass = $parsed['pass'];
    $db   = ltrim($parsed['path'], '/');
} else {
    $host = 'localhost';
    $db   = 'rh_altutex';
    $user = 'root';
    $pass = '';
    $port = '3306';
}

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