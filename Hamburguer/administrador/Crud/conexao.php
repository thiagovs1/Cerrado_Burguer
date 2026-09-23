<?php

$host = "mysql";
$usuario = "root";
$senha = "";
$banco = "cerrado_burguer";

try {

    $pdo = new PDO(
        "mysql:host=$host;port=3306;dbname=$banco;charset=utf8mb4",
        $usuario,
        $senha,
        [
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
        ]
    );

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    http_response_code(500);

    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Erro ao conectar ao banco de dados."
    ]);

    exit;
}