<?php

header('Content-Type: application/json; charset=utf-8');

require_once "../conexao.php";

try {

    if ($_SERVER["REQUEST_METHOD"] !== "POST") {

        http_response_code(405);

        echo json_encode([
            "sucesso" => false,
            "mensagem" => "Método não permitido."
        ]);

        exit;
    }


    $id =
        intval(
            $_POST["id_produto"] ?? 0
        );


    if ($id <= 0) {

        echo json_encode([
            "sucesso" => false,
            "mensagem" => "Produto inválido."
        ]);

        exit;
    }

    $stmt =
        $pdo->prepare("
            SELECT imagem
            FROM produto
            WHERE id_produto = :id
        ");


    $stmt->execute([
        ":id" => $id
    ]);


    $produto =
        $stmt->fetch(PDO::FETCH_ASSOC);


    if (!$produto) {

        echo json_encode([
            "sucesso" => false,
            "mensagem" => "Produto não encontrado."
        ]);

        exit;
    }

    $stmt =
        $pdo->prepare("
            DELETE FROM produto
            WHERE id_produto = :id
        ");


    $stmt->execute([
        ":id" => $id
    ]);

    if (!empty($produto["imagem"])) {

        $arquivo =
            __DIR__
            . "/../../../imagens/"
            . $produto["imagem"];


        if (file_exists($arquivo)) {

            unlink($arquivo);

        }
    }


    echo json_encode([

        "sucesso" => true,

        "mensagem" =>
            "Produto excluído com sucesso!"

    ]);


} catch (PDOException $e) {

    if ($e->getCode() === "23000") {

        echo json_encode([

            "sucesso" => false,

            "mensagem" =>
                "Este produto não pode ser excluído porque já está sendo utilizado em pedidos."

        ]);

        exit;
    }


    http_response_code(500);

    echo json_encode([

        "sucesso" => false,

        "mensagem" =>
            "Erro ao excluir produto.",

        "erro" =>
            $e->getMessage()

    ]);

}