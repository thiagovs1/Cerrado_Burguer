<?php

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../conexao.php';

try {

    $id =
        (int) ($_POST['id'] ?? 0);

    $nome =
        trim($_POST['nome'] ?? '');

    $email =
        trim($_POST['email'] ?? '');

    $telefone =
        trim($_POST['telefone'] ?? '');

    $cpf =
        trim($_POST['cpf'] ?? '');

    $endereco =
        trim($_POST['endereco'] ?? '');


    if ($id <= 0) {

        throw new Exception(
            'Cliente inválido.'
        );

    }


    if ($nome === '') {

        throw new Exception(
            'O nome é obrigatório.'
        );

    }


    if ($email === '') {

        throw new Exception(
            'O e-mail é obrigatório.'
        );

    }


    $stmt = $pdo->prepare("
        UPDATE usuario
        SET
            nome = ?,
            email = ?,
            telefone = ?,
            cpf = ?,
            endereco = ?
        WHERE id = ?
    ");


    $stmt->execute([

        $nome,

        $email,

        $telefone !== ''
            ? $telefone
            : null,

        $cpf !== ''
            ? $cpf
            : null,

        $endereco !== ''
            ? $endereco
            : null,

        $id

    ]);


    echo json_encode([

        'sucesso' => true,

        'mensagem' =>
            'Cliente atualizado com sucesso.'

    ], JSON_UNESCAPED_UNICODE);


} catch (PDOException $e) {

    http_response_code(500);

    echo json_encode([

        'sucesso' => false,

        'mensagem' =>
            'Não foi possível atualizar o cliente. Verifique se o e-mail ou CPF já está sendo usado.'

    ], JSON_UNESCAPED_UNICODE);


} catch (Exception $e) {

    echo json_encode([

        'sucesso' => false,

        'mensagem' =>
            $e->getMessage()

    ], JSON_UNESCAPED_UNICODE);

}