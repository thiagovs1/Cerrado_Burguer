<?php

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../conexao.php';

try {

    $stmt = $pdo->query("
        SELECT
            u.id,
            u.nome,
            u.email,
            u.telefone,
            u.cpf,
            u.endereco,
            u.data_cadastro,
            u.status,

            COUNT(p.id_pedido) AS total_pedidos,

            COALESCE(
                SUM(p.total),
                0
            ) AS total_gasto,

            MAX(p.data_pedido) AS ultimo_pedido

        FROM usuario u

        LEFT JOIN pedido p
            ON p.id_usuario = u.id

        GROUP BY
            u.id,
            u.nome,
            u.email,
            u.telefone,
            u.cpf,
            u.endereco,
            u.data_cadastro,
            u.status

        ORDER BY
            u.data_cadastro DESC
    ");

    $clientes = $stmt->fetchAll();


    $stmtTotal =
        $pdo->query("
            SELECT COUNT(*) AS total
            FROM usuario
        ");

    $totalClientes =
        (int) $stmtTotal->fetch()['total'];


    $stmtAtivos =
        $pdo->query("
            SELECT COUNT(*) AS total
            FROM usuario
            WHERE status = 'Ativo'
        ");

    $clientesAtivos =
        (int) $stmtAtivos->fetch()['total'];


    $stmtNovos =
        $pdo->query("
            SELECT COUNT(*) AS total
            FROM usuario
            WHERE MONTH(data_cadastro) = MONTH(CURRENT_DATE())
              AND YEAR(data_cadastro) = YEAR(CURRENT_DATE())
        ");

    $novosClientes =
        (int) $stmtNovos->fetch()['total'];


    $stmtPedidos =
        $pdo->query("
            SELECT COUNT(*) AS total
            FROM pedido
        ");

    $totalPedidos =
        (int) $stmtPedidos->fetch()['total'];


    echo json_encode([

        'sucesso' => true,

        'clientes' => $clientes,

        'resumo' => [

            'total_clientes' =>
                $totalClientes,

            'clientes_ativos' =>
                $clientesAtivos,

            'novos_clientes' =>
                $novosClientes,

            'total_pedidos' =>
                $totalPedidos

        ]

    ], JSON_UNESCAPED_UNICODE);


} catch (PDOException $e) {

    http_response_code(500);

    echo json_encode([

        'sucesso' => false,

        'mensagem' =>
            'Erro ao carregar clientes.'

    ], JSON_UNESCAPED_UNICODE);

}