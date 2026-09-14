<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../crud/conexao.php';
if (!isset($_SESSION['carrinho'])) {
    $_SESSION['carrinho'] = [];
}
if (!isset($_SESSION['observacao'])) {
    $_SESSION['observacao'] = '';
}
$acao = $_POST['acao'] ?? $_GET['acao'] ?? '';
if ($acao === 'adicionar') {
    $idProduto = (int) ($_POST['id_produto'] ?? 0);
    if ($idProduto <= 0) {
        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'Produto não identificado.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    $stmt = $pdo->prepare("
        SELECT
            id_produto,
            nome,
            preco,
            imagem
        FROM produto
        WHERE id_produto = ?
          AND status = 'Ativo'
        LIMIT 1
    ");
    $stmt->execute([$idProduto]);
    $produtoBanco = $stmt->fetch();
    if (!$produtoBanco) {
        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'Produto não encontrado no banco de dados.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    $produtoEncontrado = false;
    foreach ($_SESSION['carrinho'] as &$produto) {
        if (
            isset($produto['id_produto']) &&
            $produto['id_produto'] == $produtoBanco['id_produto']
        ) {
            $produto['quantidade']++;
            $produtoEncontrado = true;
            break;
        }
    }
    unset($produto);
    if (!$produtoEncontrado) {
        $_SESSION['carrinho'][] = [
            'id_produto' => (int) $produtoBanco['id_produto'],
            'nome' => $produtoBanco['nome'],
            'preco' => (float) $produtoBanco['preco'],
            'imagem' => $produtoBanco['imagem'],
            'quantidade' => 1
        ];
    }
    echo json_encode([
        'sucesso' => true,
        'mensagem' => 'Produto adicionado ao carrinho.',
        'nome' => $produtoBanco['nome'],
        'carrinho' => $_SESSION['carrinho']
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
if ($acao === 'aumentar') {
    $indice = isset($_POST['indice'])
        ? (int) $_POST['indice']
        : -1;
    if (isset($_SESSION['carrinho'][$indice])) {
        $_SESSION['carrinho'][$indice]['quantidade']++;
    }
    echo json_encode([
        'sucesso' => true,
        'carrinho' => $_SESSION['carrinho']
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
if ($acao === 'diminuir') {
    $indice = isset($_POST['indice'])
        ? (int) $_POST['indice']
        : -1;
    if (isset($_SESSION['carrinho'][$indice])) {
        $_SESSION['carrinho'][$indice]['quantidade']--;
        if ($_SESSION['carrinho'][$indice]['quantidade'] <= 0) {
            array_splice(
                $_SESSION['carrinho'],
                $indice,
                1
            );
        }
    }
    echo json_encode([
        'sucesso' => true,
        'carrinho' => $_SESSION['carrinho']
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
if ($acao === 'excluir') {
    $indice = isset($_POST['indice'])
        ? (int) $_POST['indice']
        : -1;
    if (isset($_SESSION['carrinho'][$indice])) {
        array_splice(
            $_SESSION['carrinho'],
            $indice,
            1
        );
    }
    echo json_encode([
        'sucesso' => true,
        'carrinho' => $_SESSION['carrinho']
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
if ($acao === 'observacao') {
    $observacao = trim(
        $_POST['observacao'] ?? ''
    );
    $_SESSION['observacao'] = $observacao;
    echo json_encode([
        'sucesso' => true
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
if ($acao === 'limpar') {
    $_SESSION['carrinho'] = [];
    $_SESSION['observacao'] = '';
    echo json_encode([
        'sucesso' => true,
        'carrinho' => []
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
if ($acao === 'listar' || $acao === '') {
    $subtotal = 0;
    foreach ($_SESSION['carrinho'] as $produto) {
        $subtotal +=
            (float) $produto['preco'] *
            (int) $produto['quantidade'];
    }
    $valorEntrega = 5.00;
    $entrega =
        count($_SESSION['carrinho']) > 0
            ? $valorEntrega
            : 0;
    $total = $subtotal + $entrega;
    echo json_encode([
        'sucesso' => true,
        'carrinho' =>
            $_SESSION['carrinho'],
        'subtotal' =>
            $subtotal,
        'entrega' =>
            $entrega,
        'total' =>
            $total,
        'observacao' =>
            $_SESSION['observacao']
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
