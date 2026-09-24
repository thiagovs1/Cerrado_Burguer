
<?php

$dbname = "cerrado_burguer";
$user = "root";
$password = "";

// Detecta se o PHP está rodando dentro do Docker
if (getenv("DOCKER_ENV") === "true") {
    $host = "mysql";
} else {
    $host = "localhost";
}

try {

    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $user,
        $password
    );

    $pdo->setAttribute(
        PDO::ATTR_ERRMODE,
        PDO::ERRMODE_EXCEPTION
    );

    $pdo->setAttribute(
        PDO::ATTR_DEFAULT_FETCH_MODE,
        PDO::FETCH_ASSOC
    );

    $pdo->exec("SET NAMES utf8mb4");

} catch (PDOException $e) {

    die("Erro na conexão: " . $e->getMessage());
}

