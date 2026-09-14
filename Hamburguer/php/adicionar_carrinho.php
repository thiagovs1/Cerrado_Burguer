<?php
session_start();
if (!isset($_SESSION['carrinho'])) {
    $_SESSION['carrinho'] = [];
}
$nome = $_POST['nome'] ?? '';
$preco = $_POST['preco'] ?? 0;
$imagem = $_POST['imagem'] ?? '';
if ($nome === '' || $preco <= 0) {
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Produto inválido.'
    ]);
    exit;
}
$produtoEncontrado = false;
foreach ($_SESSION['carrinho'] as &$produto) {
    if ($produto['nome'] === $nome) {
        $produto['quantidade']++;
        $produtoEncontrado = true;
        break;
    }
}
unset($produto);
if (!$produtoEncontrado) {
    $_SESSION['carrinho'][] = [
        'nome' => $nome,
        'preco' => (float) $preco,
        'imagem' => $imagem,
        'quantidade' => 1
    ];
}
echo json_encode([
    'sucesso' => true,
    'carrinho' => $_SESSION['carrinho']
]);