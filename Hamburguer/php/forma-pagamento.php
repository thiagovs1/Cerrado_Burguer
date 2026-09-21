<?php
session_start();

require_once "../crud/conexao.php";

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../crud/login.html");
    exit;
}

$id_usuario = $_SESSION['usuario_id'];

$pagamento = $_POST['pagamento'] ?? '';
$troco = $_POST['troco'] ?? 'Não';
$valor_troco = trim($_POST['valor_troco'] ?? '');

if ($pagamento === '') {
    header("Location: ../html-carrinho/forma-pagamento.php");
    exit;
}

if ($troco === 'Sim') {
    $troco_final = $valor_troco;
} else {
    $troco_final = 'Não';
}

$sql = "SELECT id_pedido, tipo_entrega
        FROM pedido
        WHERE id_usuario = ?
        AND status = 'Carrinho'
        LIMIT 1";

$stmt = $pdo->prepare($sql);
$stmt->execute([$id_usuario]);

$pedido = $stmt->fetch();

if (!$pedido) {
    header("Location: ../html/cardapio.php");
    exit;
}

$id_pedido = $pedido['id_pedido'];

$sql = "SELECT SUM(quantidade * preco_unitario) AS subtotal
        FROM itens_pedidos
        WHERE id_pedido = ?";

$stmt = $pdo->prepare($sql);
$stmt->execute([$id_pedido]);

$resultado = $stmt->fetch();

$subtotal = $resultado['subtotal'] ?? 0;

if ($pedido['tipo_entrega'] === 'Entrega') {
    $entrega = 5;
} else {
    $entrega = 0;
}

$total = $subtotal + $entrega;

$sql = "UPDATE pedido
        SET forma_pagamento = ?,
            troco = ?,
            subtotal = ?,
            valor_entrega = ?,
            total = ?,
            status = 'Pedido recebido'
        WHERE id_pedido = ?
        AND id_usuario = ?
        AND status = 'Carrinho'";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    $pagamento,
    $troco_final,
    $subtotal,
    $entrega,
    $total,
    $id_pedido,
    $id_usuario
]);

header("Location: ../html-carrinho/confirmacao.php");
exit;