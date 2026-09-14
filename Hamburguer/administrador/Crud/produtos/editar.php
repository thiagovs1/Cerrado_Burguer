<?php

header('Content-Type: application/json; charset=utf-8');

require_once "../conexao.php";

try {
    if (
        $_SERVER["REQUEST_METHOD"] === "GET" &&
        isset($_GET["id"])
    ) {

        $id = intval($_GET["id"]);


        $sql = "
            SELECT
                id_produto,
                id_categoria,
                nome,
                descricao,
                preco,
                status,
                imagem,
                tempo_preparo,
                promocao,
                disponivel_entrega

            FROM produto

            WHERE id_produto = :id
        ";


        $stmt =
            $pdo->prepare($sql);


        $stmt->execute([
            ":id" => $id
        ]);


        $produto =
            $stmt->fetch(PDO::FETCH_ASSOC);


        if (!$produto) {

            echo json_encode([

                "sucesso" => false,

                "mensagem" =>
                    "Produto não encontrado."

            ]);

            exit;
        }


        echo json_encode([

            "sucesso" => true,

            "produto" => $produto

        ]);

        exit;
    }

    if ($_SERVER["REQUEST_METHOD"] !== "POST") {

        http_response_code(405);

        echo json_encode([

            "sucesso" => false,

            "mensagem" =>
                "Método não permitido."

        ]);

        exit;
    }


    $id = intval(
        $_POST["id_produto"] ?? 0
    );


    if ($id <= 0) {

        echo json_encode([

            "sucesso" => false,

            "mensagem" =>
                "Produto inválido."

        ]);

        exit;
    }


    $nome =
        trim($_POST["nome"] ?? "");


    $descricao =
        trim($_POST["descricao"] ?? "");


    $id_categoria =
        $_POST["id_categoria"] ?? "";


    $status =
        $_POST["status"] ?? "Ativo";


    $tempo_preparo =
        intval(
            $_POST["tempo_preparo"] ?? 0
        );


    $promocao =
        isset($_POST["promocao"])
            ? "Sim"
            : "Não";


    $disponivel_entrega =
        isset($_POST["disponivel_entrega"])
            ? "Sim"
            : "Não";
    if ($nome === "") {

        echo json_encode([
            "sucesso" => false,
            "mensagem" => "Informe o nome do produto."
        ]);

        exit;
    }


    if ($id_categoria === "") {

        echo json_encode([
            "sucesso" => false,
            "mensagem" => "Selecione uma categoria."
        ]);

        exit;
    }


    $preco =
        $_POST["preco"] ?? "";


    $preco =
        str_replace(".", "", $preco);

    $preco =
        str_replace(",", ".", $preco);


    if (!is_numeric($preco)) {

        echo json_encode([
            "sucesso" => false,
            "mensagem" => "Informe um preço válido."
        ]);

        exit;
    }


    $preco =
        floatval($preco);

    $stmt =
        $pdo->prepare("
            SELECT imagem
            FROM produto
            WHERE id_produto = :id
        ");


    $stmt->execute([
        ":id" => $id
    ]);


    $produtoAtual =
        $stmt->fetch(PDO::FETCH_ASSOC);


    if (!$produtoAtual) {

        echo json_encode([
            "sucesso" => false,
            "mensagem" => "Produto não encontrado."
        ]);

        exit;
    }


    $nomeImagem =
        $produtoAtual["imagem"];

    if (
        isset($_FILES["imagem"]) &&
        $_FILES["imagem"]["error"] !== UPLOAD_ERR_NO_FILE
    ) {

        if ($_FILES["imagem"]["error"] !== UPLOAD_ERR_OK) {

            echo json_encode([
                "sucesso" => false,
                "mensagem" => "Erro ao enviar a imagem."
            ]);

            exit;
        }


        if (
            $_FILES["imagem"]["size"]
            > 5 * 1024 * 1024
        ) {

            echo json_encode([
                "sucesso" => false,
                "mensagem" => "A imagem deve ter no máximo 5MB."
            ]);

            exit;
        }


        $extensao =
            strtolower(
                pathinfo(
                    $_FILES["imagem"]["name"],
                    PATHINFO_EXTENSION
                )
            );


        $permitidas = [
            "jpg",
            "jpeg",
            "png",
            "webp"
        ];


        if (!in_array(
            $extensao,
            $permitidas
        )) {

            echo json_encode([
                "sucesso" => false,
                "mensagem" => "Formato de imagem inválido."
            ]);

            exit;
        }


        $novaImagem =
            uniqid("produto_", true)
            . "."
            . $extensao;


        $pastaImagem =
            __DIR__
            . "/../../../imagens/";


        if (!is_dir($pastaImagem)) {

            mkdir(
                $pastaImagem,
                0777,
                true
            );
        }


        $destino =
            $pastaImagem
            . $novaImagem;


        if (!move_uploaded_file(
            $_FILES["imagem"]["tmp_name"],
            $destino
        )) {

            echo json_encode([
                "sucesso" => false,
                "mensagem" => "Não foi possível salvar a imagem."
            ]);

            exit;
        }


        // Apaga imagem antiga
        if (
            !empty($nomeImagem) &&
            file_exists(
                $pastaImagem . $nomeImagem
            )
        ) {

            unlink(
                $pastaImagem . $nomeImagem
            );
        }


        $nomeImagem =
            $novaImagem;
    }
    $sql = "
        UPDATE produto

        SET
            id_categoria = :id_categoria,
            nome = :nome,
            descricao = :descricao,
            preco = :preco,
            status = :status,
            imagem = :imagem,
            tempo_preparo = :tempo_preparo,
            promocao = :promocao,
            disponivel_entrega = :disponivel_entrega

        WHERE id_produto = :id
    ";


    $stmt =
        $pdo->prepare($sql);


    $stmt->execute([

        ":id_categoria" =>
            $id_categoria,

        ":nome" =>
            $nome,

        ":descricao" =>
            $descricao,

        ":preco" =>
            $preco,

        ":status" =>
            $status,

        ":imagem" =>
            $nomeImagem,

        ":tempo_preparo" =>
            $tempo_preparo,

        ":promocao" =>
            $promocao,

        ":disponivel_entrega" =>
            $disponivel_entrega,

        ":id" =>
            $id

    ]);


    echo json_encode([

        "sucesso" => true,

        "mensagem" =>
            "Produto atualizado com sucesso!"

    ]);


} catch (PDOException $e) {

    http_response_code(500);

    echo json_encode([

        "sucesso" => false,

        "mensagem" =>
            "Erro no banco de dados.",

        "erro" =>
            $e->getMessage()

    ]);

}