<?php
require_once "../administrador/Crud/conexao.php";

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    die("Categoria inválida.");
}

$stmt = $pdo->prepare("
    SELECT id_categoria, nome, descricao, imagem
    FROM categoria
    WHERE id_categoria = ? AND status = 'Ativa'
");
$stmt->execute([$id]);
$categoria = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$categoria) {
    die("Categoria não encontrada.");
}

$stmt = $pdo->prepare("
    SELECT id_produto, nome, descricao, preco, imagem
    FROM produto
    WHERE id_categoria = ? AND status = 'Ativo'
    ORDER BY id_produto DESC
");
$stmt->execute([$id]);
$produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= htmlspecialchars($categoria['nome']) ?> - Cerrado Burguer</title>

    <link rel="stylesheet" href="../css-cardapio-tela-inicial/hamburguer.css">
    <link rel="stylesheet" href="../css/menu_perfil.css">

</head>

<body>

<header class="topo">

    <a href="tela-inicial.html">
        <img src="../imagens/logo.png" class="logo" alt="Cerrado Burguer">
    </a>

    <nav class="menu">

        <a href="tela-inicial.html">
            Inicio
        </a>

        <a href="tela-inicial.html#promocoes">
            Promoções
        </a>

        <a href="cardapio.php">
            Cardápio
        </a>

        <a href="tela-inicial.html">
            Contato
        </a>

        <a href="tela-inicial.html#avaliacao">
            Avaliações
        </a>

    </nav>

    <div class="icons">

        <a href="../crud/perfil.php">
            <img src="../imagens/Perfil.png" alt="Perfil">
        </a>

        <a href="../html-carrinho/carrinho.php">
            <img src="../imagens/carrinho.png" alt="Carrinho">
        </a>

    </div>

</header>

<main>

    <h1><?= htmlspecialchars($categoria['nome']) ?></h1>

    <div class="produtos">

        <?php if (!$produtos): ?>

            <p class="sem-produtos">
                Nenhum produto disponível nesta categoria.
            </p>

        <?php else: ?>

            <?php foreach ($produtos as $produto): ?>

                <div class="item">

                    <?php if (!empty($produto['imagem'])): ?>

                        <img
                            src="../imagens/<?= htmlspecialchars($produto['imagem']) ?>"
                            alt="<?= htmlspecialchars($produto['nome']) ?>">

                    <?php endif; ?>

                    <div>

                        <h2>
                            <?= htmlspecialchars($produto['nome']) ?>
                        </h2>

                        <p>
                            <?= htmlspecialchars($produto['descricao']) ?>
                        </p>

                        <span>
                            R$ <?= number_format($produto['preco'], 2, ',', '.') ?>
                        </span>

                    </div>

                    <form action="../php/carrinho.php" method="POST">

                        <input
                            type="hidden"
                            name="acao"
                            value="adicionar">

                        <input
                            type="hidden"
                            name="id_produto"
                            value="<?= $produto['id_produto'] ?>">

                         <input type="hidden" name="voltar" value="<?= htmlspecialchars($_SERVER['REQUEST_URI']) ?>">

                         <button type="submit" class="add">+</button>

                    </form>

                </div>

            <?php endforeach; ?>

        <?php endif; ?>

    </div>

    <div class="area-voltar">

        <a href="cardapio.php" class="btn-voltar">
            ← Voltar
        </a>

    </div>

</main>

</body>
</html>