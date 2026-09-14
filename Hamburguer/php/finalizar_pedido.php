<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../crud/conexao.php';
if (empty($_SESSION['carrinho'])) {
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'O carrinho está vazio.'
    ]);
    exit;
}
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Usuário não está logado.'
    ]);
    exit;
}
$idUsuario = (int) $_SESSION['usuario_id'];
$tipoEntrega = $_POST['tipo_entrega'] ?? 'Retirada';
$valorEntrega = $tipoEntrega === 'Entrega'
    ? 5.00
    : 0.00;
$formaPagamento = $_POST['forma_pagamento'] ?? null;
$troco = isset($_POST['troco'])
    ? (float) $_POST['troco']
    : 0;
$observacao = trim($_POST['observacao'] ?? '');
$cidade = $_POST['cidade'] ?? null;
$estado = $_POST['estado'] ?? null;
$endereco = $_POST['endereco'] ?? null;
$setor = $_POST['setor'] ?? null;
$informacaoAdicional = $_POST['informacao_adicional'] ?? null;
$subtotal = 0;
foreach ($_SESSION['carrinho'] as $produto) {
    $subtotal +=
        (float) $produto['preco'] *
        (int) $produto['quantidade'];
}
$total = $subtotal + $valorEntrega;
try {
    $pdo->beginTransaction();
    $stmtPedido = $pdo->prepare("
        INSERT INTO pedido (
            id_usuario,
            subtotal,
            valor_entrega,
            total,
            tipo_entrega,
            cidade,
            estado,
            endereco,
            setor,
            informacao_adicional,
            forma_pagamento,
            troco,
            observacao
        )
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmtPedido->execute([
        $idUsuario,
        $subtotal,
        $valorEntrega,
        $total,
        $tipoEntrega,
        $cidade,
        $estado,
        $endereco,
        $setor,
        $informacaoAdicional,
        $formaPagamento,
        $troco,
        $observacao
    ]);
    $idPedido = (int) $pdo->lastInsertId();
    $stmtItem = $pdo->prepare("
        INSERT INTO itens_pedidos (
            id_produto,
            id_pedido,
            quantidade,
            preco_unitario
        )
        VALUES (?, ?, ?, ?)
    ");
    foreach ($_SESSION['carrinho'] as $produto) {
        if (!isset($produto['id_produto'])) {
            throw new Exception(
                'Produto sem ID no carrinho.'
            );
        }
        $stmtItem->execute([
            $produto['id_produto'],
            $idPedido,
            $produto['quantidade'],
            $produto['preco']
        ]);
    }
    $pdo->commit();
    // Limpa o carrinho
    $_SESSION['carrinho'] = [];
    $_SESSION['observacao'] = '';
    echo json_encode([
        'sucesso' => true,
        'mensagem' => 'Pedido realizado com sucesso!',
        'id_pedido' => $idPedido,
        'subtotal' => $subtotal,
        'valor_entrega' => $valorEntrega,
        'total' => $total
    ]);
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Erro ao finalizar pedido.',
        'erro' => $e->getMessage()
    ]);
}
exit;