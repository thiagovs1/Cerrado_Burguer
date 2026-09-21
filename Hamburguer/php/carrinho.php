<?php
session_start();
require_once __DIR__ . '/../crud/conexao.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../crud/login.html");
    exit;
}

$id_usuario = $_SESSION['usuario_id'];
$acao = $_POST['acao'] ?? '';

$stmt = $pdo->prepare("SELECT id_pedido FROM pedido WHERE id_usuario = ? AND status = 'Carrinho' LIMIT 1");
$stmt->execute([$id_usuario]);
$carrinho = $stmt->fetch();

if ($acao === 'adicionar') {
    $id_produto = $_POST['id_produto'] ?? null;

    if (!$id_produto) {
        header("Location: ../html/cardapio.php");
        exit;
    }

    $stmt = $pdo->prepare("SELECT id_produto, preco FROM produto WHERE id_produto = ? AND status = 'Ativo'");
    $stmt->execute([$id_produto]);
    $produto = $stmt->fetch();

    if (!$produto) {
        die("Produto não encontrado.");
    }

    if (!$carrinho) {
        $stmt = $pdo->prepare("INSERT INTO pedido (id_usuario, subtotal, valor_entrega, total, status) VALUES (?, 0, 0, 0, 'Carrinho')");
        $stmt->execute([$id_usuario]);
        $id_pedido = $pdo->lastInsertId();
    } else {
        $id_pedido = $carrinho['id_pedido'];
    }

    $stmt = $pdo->prepare("SELECT id_item_pedidos FROM itens_pedidos WHERE id_pedido = ? AND id_produto = ?");
    $stmt->execute([$id_pedido, $id_produto]);
    $item = $stmt->fetch();

    if ($item) {
        $stmt = $pdo->prepare("UPDATE itens_pedidos SET quantidade = quantidade + 1 WHERE id_item_pedidos = ?");
        $stmt->execute([$item['id_item_pedidos']]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO itens_pedidos (id_produto, id_pedido, quantidade, preco_unitario) VALUES (?, ?, 1, ?)");
        $stmt->execute([$id_produto, $id_pedido, $produto['preco']]);
    }

    $voltar = $_POST['voltar'] ?? '../html/cardapio.php';
    header("Location: " . $voltar);
    exit;
}

if ($acao === 'aumentar') {
    if ($carrinho) {
        $stmt = $pdo->prepare("UPDATE itens_pedidos SET quantidade = quantidade + 1 WHERE id_pedido = ? AND id_produto = ?");
        $stmt->execute([$carrinho['id_pedido'], $_POST['id_produto']]);
    }

    header("Location: ../html-carrinho/carrinho.php");
    exit;
}

if ($acao === 'diminuir') {
    if ($carrinho) {
        $stmt = $pdo->prepare("UPDATE itens_pedidos SET quantidade = quantidade - 1 WHERE id_pedido = ? AND id_produto = ? AND quantidade > 1");
        $stmt->execute([$carrinho['id_pedido'], $_POST['id_produto']]);

        if ($stmt->rowCount() === 0) {
            $stmt = $pdo->prepare("DELETE FROM itens_pedidos WHERE id_pedido = ? AND id_produto = ?");
            $stmt->execute([$carrinho['id_pedido'], $_POST['id_produto']]);
        }
    }

    header("Location: ../html-carrinho/carrinho.php");
    exit;
}

if ($acao === 'excluir') {
    if ($carrinho) {
        $stmt = $pdo->prepare("DELETE FROM itens_pedidos WHERE id_pedido = ? AND id_produto = ?");
        $stmt->execute([$carrinho['id_pedido'], $_POST['id_produto']]);
    }

    header("Location: ../html-carrinho/carrinho.php");
    exit;
}

if ($acao === 'observacao') {
    if ($carrinho) {
        $stmt = $pdo->prepare("UPDATE pedido SET observacao = ? WHERE id_pedido = ? AND id_usuario = ?");
        $stmt->execute([
            trim($_POST['observacao'] ?? ''),
            $carrinho['id_pedido'],
            $id_usuario
        ]);
    }

    header("Location: ../html-carrinho/endereco.php");
    exit;
}

if ($acao === 'limpar') {
    if ($carrinho) {
        $id_pedido = $carrinho['id_pedido'];

        $pdo->prepare("DELETE FROM itens_pedidos WHERE id_pedido = ?")->execute([$id_pedido]);

        $pdo->prepare("DELETE FROM pedido WHERE id_pedido = ? AND id_usuario = ? AND status = 'Carrinho'")
            ->execute([$id_pedido, $id_usuario]);
    }

    header("Location: ../html/cardapio.php");
    exit;
}
