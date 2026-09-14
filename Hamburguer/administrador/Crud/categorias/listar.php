<?php

header('Content-Type: application/json; charset=utf-8');

require_once "../conexao.php";

try {

    $busca = trim($_GET['busca'] ?? '');
    $status = $_GET['status'] ?? '';

    $sql = "
        SELECT
            c.id_categoria,
            c.nome,
            c.descricao,
            c.status,
            c.imagem,
            COUNT(p.id_produto) AS produtos
        FROM categoria c
        LEFT JOIN produto p
            ON c.id_categoria = p.id_categoria
    ";

    $condicoes = [];
    $parametros = [];

    if ($busca !== '') {

        $condicoes[] = "
            (
                c.nome LIKE ?
                OR c.descricao LIKE ?
            )
        ";

        $parametros[] = "%$busca%";
        $parametros[] = "%$busca%";
    }

    if ($status === 'Ativa' || $status === 'Inativa') {

        $condicoes[] = "c.status = ?";
        $parametros[] = $status;
    }

    if (!empty($condicoes)) {
        $sql .= " WHERE " . implode(" AND ", $condicoes);
    }

    $sql .= "
        GROUP BY
            c.id_categoria,
            c.nome,
            c.descricao,
            c.status,
            c.imagem
        ORDER BY c.id_categoria ASC
    ";

    $stmt = $pdo->prepare($sql);

    $stmt->execute($parametros);

    $categorias = $stmt->fetchAll();

    echo json_encode([
        "sucesso" => true,
        "categorias" => $categorias
    ]);

} catch (PDOException $e) {

    http_response_code(500);

    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Erro ao listar categorias."
    ]);
}