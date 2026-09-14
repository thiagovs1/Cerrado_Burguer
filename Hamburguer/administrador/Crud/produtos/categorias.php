<?php

header('Content-Type: application/json; charset=utf-8');

require_once "../conexao.php";

try {

    $sql = "
        SELECT
            id_categoria,
            nome
        FROM categoria
        ORDER BY nome ASC
    ";

    $stmt = $pdo->query($sql);

    $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "sucesso" => true,
        "categorias" => $categorias
    ]);

} catch (PDOException $e) {

    http_response_code(500);

    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Erro ao carregar categorias.",
        "erro" => $e->getMessage()
    ]);

}