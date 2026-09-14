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

    $nome = trim($_POST["nome"] ?? "");
    $descricao = trim($_POST["descricao"] ?? "");
    $preco = $_POST["preco"] ?? "";
    $id_categoria = $_POST["id_categoria"] ?? "";
    $status = $_POST["status"] ?? "Ativo";

    $tempo_preparo = intval(
        $_POST["tempo_preparo"] ?? 0
    );

    $promocao = isset($_POST["promocao"])
        ? "Sim"
        : "Não";

    $disponivel_entrega = isset($_POST["disponivel_entrega"])
        ? "Sim"
        : "Não";

    if ($nome === "") {

        echo json_encode([
            "sucesso" => false,
            "mensagem" => "Informe o nome do produto."
        ]);

        exit;
    }


    if ($descricao === "") {

        echo json_encode([
            "sucesso" => false,
            "mensagem" => "Informe a descrição do produto."
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


    // Converte 10,50 para 10.50
    $preco = str_replace(".", "", $preco);
    $preco = str_replace(",", ".", $preco);

    if (!is_numeric($preco)) {

        echo json_encode([
            "sucesso" => false,
            "mensagem" => "Informe um preço válido."
        ]);

        exit;
    }

    $preco = floatval($preco);


    if ($preco < 0) {

        echo json_encode([
            "sucesso" => false,
            "mensagem" => "O preço não pode ser negativo."
        ]);

        exit;
    }


    if (!in_array($status, ["Ativo", "Inativo"])) {

        echo json_encode([
            "sucesso" => false,
            "mensagem" => "Status inválido."
        ]);

        exit;
    }

    $nomeImagem = null;

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


        // Limite de 5 MB
        if ($_FILES["imagem"]["size"] > 5 * 1024 * 1024) {

            echo json_encode([
                "sucesso" => false,
                "mensagem" => "A imagem deve ter no máximo 5MB."
            ]);

            exit;
        }


        $extensao = strtolower(
            pathinfo(
                $_FILES["imagem"]["name"],
                PATHINFO_EXTENSION
            )
        );


        $extensoesPermitidas = [
            "jpg",
            "jpeg",
            "png",
            "webp"
        ];


        if (!in_array($extensao, $extensoesPermitidas)) {

            echo json_encode([
                "sucesso" => false,
                "mensagem" => "Formato de imagem inválido."
            ]);

            exit;
        }


        $nomeImagem =
            uniqid("produto_", true)
            . "."
            . $extensao;


        /*
         * cadastro.php está dentro de:
         *
         * Hamburgue/administrador/Crud/produtos/
         *
         * Para chegar em:
         *
         * Hamburgue/imagens/
         *
         * precisamos voltar 3 níveis.
         */

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
            . $nomeImagem;


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
    }

    $sql = "
        INSERT INTO produto (
            id_categoria,
            nome,
            descricao,
            preco,
            status,
            imagem,
            tempo_preparo,
            promocao,
            disponivel_entrega
        )

        VALUES (
            :id_categoria,
            :nome,
            :descricao,
            :preco,
            :status,
            :imagem,
            :tempo_preparo,
            :promocao,
            :disponivel_entrega
        )
    ";


    $stmt = $pdo->prepare($sql);


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
            $disponivel_entrega

    ]);


    echo json_encode([

        "sucesso" => true,

        "mensagem" =>
            "Produto cadastrado com sucesso!",

        "id_produto" =>
            $pdo->lastInsertId()

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