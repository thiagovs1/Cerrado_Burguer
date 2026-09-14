<?php

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../conexao.php';

try {

    $id =
        (int) ($_POST['id'] ?? 0);


    if ($id <= 0) {

        throw new Exception(
            'Cliente inválido.'
        );

    }


    $stmt =
        $pdo->prepare("
            SELECT id
            FROM usuario
            WHERE id = ?
        ");

    $stmt->execute([$id]);


    if (!$stmt->fetch()) {

        throw new Exception(
            'Cliente não encontrado.'
        );

    }


    $stmt =
        $pdo->prepare("
            SELECT COUNT(*) AS total
            FROM pedido
            WHERE id_usuario = ?
        ");

    $stmt->execute([$id]);


    $pedidos =
        (int) $stmt->fetch()['total'];


    if ($pedidos > 0) {

        echo json_encode([

            'sucesso' => false,

            'mensagem' =>
                'Este cliente possui pedidos registrados e não pode ser excluído. Altere o status para Inativo.'

        ], JSON_UNESCAPED_UNICODE);

        exit;

    }


    $stmt =
        $pdo->prepare("
            DELETE FROM usuario
            WHERE id = ?
        ");

    $stmt->execute([$id]);


    echo json_encode([

        'sucesso' => true,

        'mensagem' =>
            'Cliente excluído com sucesso.'

    ], JSON_UNESCAPED_UNICODE);


} catch (PDOException $e) {

    http_response_code(500);

    echo json_encode([

        'sucesso' => false,

        'mensagem' =>
            'Não foi possível excluir o cliente.'

    ], JSON_UNESCAPED_UNICODE);


} catch (Exception $e) {

    echo json_encode([

        'sucesso' => false,

        'mensagem' =>
            $e->getMessage()

    ], JSON_UNESCAPED_UNICODE);

}