<?php

header('Content-Type: application/json; charset=utf-8');

require_once "../conexao.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    http_response_code(405);

    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Método não permitido."
    ]);

    exit;
}

$id = intval($_POST['id_categoria'] ?? 0);

if ($id <= 0) {

    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Categoria inválida."
    ]);

    exit;
}

/*
|--------------------------------------------------------------------------
| Verifica se existem produtos nessa categoria
|--------------------------------------------------------------------------
*/

$verificar = $pdo->prepare("
    SELECT COUNT(*) AS total
    FROM produto
    WHERE id_categoria = ?
");

$verificar->execute([$id]);

$total = $verificar->fetch()['total'];

if ($total > 0) {

    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Não é possível excluir esta categoria porque existem produtos vinculados a ela."
    ]);

    exit;
}

/*
|--------------------------------------------------------------------------
| Exclui
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    DELETE FROM categoria
    WHERE id_categoria = ?
");

$stmt->execute([$id]);

echo json_encode([
    "sucesso" => true,
    "mensagem" => "Categoria excluída com sucesso!"
]);