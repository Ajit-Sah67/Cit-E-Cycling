<?php
function getDbConnection(): PDO{
    $servername = 'localhost';
    $username = 'root';
    $password = '';
    $database = 'cycling';
    $port = 3306;

    $dsn = 'mysql:host=' . $servername . ';port=' . $port . ';dbname=' . $database . ';charset=utf8mb4';

    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    return $pdo;
}
?>