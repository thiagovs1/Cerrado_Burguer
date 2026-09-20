<?php
require_once "../administrador/Crud/conexao.php";

$categorias = $pdo->query("
    SELECT id_categoria, nome, imagem, cor
    FROM categoria
    WHERE status = 'Ativa'
    ORDER BY id_categoria
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cardápio - Cerrado Burguer</title>

    <link rel="stylesheet" href="../css-cardapio-tela-inicial/cardapio.css">
    <link rel="stylesheet" href="../css/menu_perfil.css">
</head>

<body>

<header class="topo">

    <a href="tela-inicial.html">
        <img src="../imagens/logo.png" class="logo" alt="Cerrado Burguer">
    </a>

    <nav class="menu">
        <a href="tela-inicial.html">Inicio</a>
        <a href="tela-inicial.html#promocoes">Promoções</a>
        <a href="cardapio.php">Cardápio</a>
        <a href="tela-inicial.html">Contato</a>
        <a href="tela-inicial.html#avaliacao">Avaliações</a>
    </nav>

    <div class="icons" id="area-perfil">

        <a href="../crud/perfil.php">
            <img src="../imagens/Perfil.png" alt="Perfil">
        </a>

        <a href="../html-carrinho/carrinho.php">
            <img src="../imagens/carrinho.png" class="icone-img" alt="Carrinho">
        </a>

    </div>

</header>

<section id="cardapio">

    <div class="cards">

        <?php foreach ($categorias as $categoria): ?>

            <a href="categoria.php?id=<?= $categoria['id_categoria'] ?>"
               class="card"
               style="background-color: <?= htmlspecialchars($categoria['cor']) ?>;">

                <img
                    src="<?= !empty($categoria['imagem'])
                        ? '../imagens/' . htmlspecialchars($categoria['imagem'])
                        : '../imagens/amini.png' ?>"
                    alt="<?= htmlspecialchars($categoria['nome']) ?>"
                >

                <h3><?= htmlspecialchars($categoria['nome']) ?></h3>

            </a>

        <?php endforeach; ?>

    </div>

    <div class="linha-voltar">

        <a href="tela-inicial.html" class="btn-voltar">
            ← Voltar
        </a>

    </div>

</section>

</body>
</html>