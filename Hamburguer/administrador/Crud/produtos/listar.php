<?php

header('Content-Type: application/json; charset=utf-8');

require_once "../conexao.php";

try {

    $sql = "
        SELECT
            p.id_produto,
            p.id_categoria,
            p.nome,
            p.descricao,
            p.preco,
            p.status,
            p.imagem,
            p.tempo_preparo,
            p.promocao,
            p.disponivel_entrega,

            c.nome AS categoria

        FROM produto p

        INNER JOIN categoria c
            ON c.id_categoria = p.id_categoria

        ORDER BY p.id_produto DESC
    ";


    $stmt = $pdo->query($sql);

    $produtos =
        $stmt->fetchAll(PDO::FETCH_ASSOC);


    echo json_encode([

        "sucesso" => true,

        "produtos" => $produtos

    ]);


} catch (PDOException $e) {

    http_response_code(500);

    echo json_encode([

        "sucesso" => false,

        "mensagem" =>
            "Erro ao listar produtos.",

        "erro" =>
            $e->getMessage()

    ]);

}