<?php

$dbPassword = getenv('MARIADB_ROOT_PASSWORD') ?: '';

try {
    $db = new PDO('mysql:host=db;dbname=lognow;charset=utf8mb4', 'root', $dbPassword, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    echo '<p>No se ha encontrado la base de datos lognow.</p>';
    exit;
}

$tablas = $db->query('SHOW TABLES')->fetchAll();

if (!$tablas) {
    echo '<p>No se han encontrado tablas en la base de datos lognow.</p>';
    exit;
}
