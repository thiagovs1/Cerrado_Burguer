<?php
session_start();

require_once "../crud/conexao.php";

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../crud/login.html");
    exit;
}

$id_usuario = $_SESSION['usuario_id'];

$sql = "SELECT *
        FROM pedido
        WHERE id_usuario = ?
        AND status = 'Pedido recebido'
        ORDER BY id_pedido DESC
        LIMIT 1";

$stmt = $pdo->prepare($sql);
$stmt->execute([$id_usuario]);

$pedido = $stmt->fetch();

if (!$pedido) {
    header("Location: ../html/cardapio.php");
    exit;
}

$sql = "SELECT
            produto.nome,
            itens_pedidos.quantidade,
            itens_pedidos.preco_unitario
        FROM itens_pedidos
        INNER JOIN produto
            ON produto.id_produto = itens_pedidos.id_produto
        WHERE itens_pedidos.id_pedido = ?
        ORDER BY itens_pedidos.id_item_pedidos ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute([$pedido['id_pedido']]);

$itens = $stmt->fetchAll();

$subtotal = $pedido['subtotal'];
$entrega = $pedido['valor_entrega'];
$total = $pedido['total'];

?>

<!DOCTYPE html>

<html lang="pt-br">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Pedido Confirmado - Cerrado Burguer</title>

    <link
        rel="stylesheet"
        href="../css-carrinho/confirmacao.css"
    >

</head>


<body>


<header class="topo">


    <a href="../html/tela-inicial.html">

        <img
            src="../imagens/logo.png"
            class="logo"
        >

    </a>


    <nav>

        <a href="../html/tela-inicial.html">
            Inicio
        </a>

        <a href="../html/tela-inicial.html">
            Promoções do dia
        </a>

        <a href="../html/cardapio.php">
            Cardápio
        </a>

        <a href="../html/tela-inicial.html#contato">
            Contato
        </a>

        <a href="../html/tela-inicial.html#avaliacoes">
            Avaliações
        </a>

    </nav>


    <div class="icones">

        <a href="../crud/perfil.php">

            <img
                src="../imagens/perfil.png"
                class="icone-img"
            >

        </a>


        <a href="carrinho.php">

            <img
                src="../imagens/carrinho.png"
                class="icone-img"
            >

        </a>

    </div>

</header>


<section class="status-page">


    <div class="confirmado-topo">

        <div class="check-circulo">

            <span>✓</span>

        </div>


        <h1>
            Pedido confirmado!
        </h1>


        <p>
            Obrigado por pedir no Cerrado Burguer.
        </p>

    </div>


    <div class="status-container">

        <h2>
            Status do pedido
        </h2>


        <div class="status-progresso-wrapper">

            <div class="linha-conector"></div>


            <div class="status-linha">


                <div class="status-item ativo">

                    <div class="status-circulo">

                        <span class="icon-check">
                            ✓
                        </span>

                    </div>

                    <p>
                        Pedido confirmado
                    </p>

                </div>


                <div class="status-item">

                    <div class="status-circulo">

                        <div class="relogio-ponteiro"></div>

                    </div>

                    <p>
                        Em preparação
                    </p>

                </div>


                <div class="status-item">

                    <div class="status-circulo">

                        <img src="../imagens/moto.png">

                    </div>

                    <p>
                        A caminho
                    </p>

                </div>


                <div class="status-item">

                    <div class="status-circulo">

                        <span class="icon-check">
                            ✓
                        </span>

                    </div>

                    <p>
                        Entregue
                    </p>

                </div>


            </div>

        </div>

    </div>


    <div class="pedido-info">


        <h2>

            Pedido

            <span>
                #<?= $pedido['id_pedido'] ?>
            </span>

        </h2>


        <p>

            <strong>
                Produtos:
            </strong>

        </p>


        <div id="lista-produtos">

            <?php foreach ($itens as $item): ?>

                <p>

                    <?= $item['quantidade'] ?>

                    x

                    <?= htmlspecialchars($item['nome']) ?>

                    -

                    R$

                    <?= number_format(
                        $item['quantidade'] * $item['preco_unitario'],
                        2,
                        ',',
                        '.'
                    ) ?>

                </p>

            <?php endforeach; ?>

        </div>


        <p>

            <strong>
                Tipo de entrega:
            </strong>

            <?= htmlspecialchars($pedido['tipo_entrega']) ?>

        </p>


        <?php if ($pedido['tipo_entrega'] === 'Entrega'): ?>

            <p>

                <strong>
                    Endereço:
                </strong>

                <?= htmlspecialchars($pedido['endereco']) ?>,

                <?= htmlspecialchars($pedido['setor']) ?> -

                <?= htmlspecialchars($pedido['cidade']) ?>

                /

                <?= htmlspecialchars($pedido['estado']) ?>

            </p>

        <?php endif; ?>


        <p>

            <strong>
                Forma de pagamento:
            </strong>

            <?= htmlspecialchars($pedido['forma_pagamento']) ?>

        </p>


        <?php if (
            !empty($pedido['troco'])
            && $pedido['troco'] !== 'Não'
        ): ?>

            <p>

                <strong>
                    Troco para:
                </strong>

                R$

                <?= htmlspecialchars($pedido['troco']) ?>

            </p>

        <?php endif; ?>


        <p>

            <strong>
                Subtotal:
            </strong>

            <span class="valor-destaque">

                R$

                <?= number_format(
                    $subtotal,
                    2,
                    ',',
                    '.'
                ) ?>

            </span>

        </p>


        <p>

            <strong>
                Entrega:
            </strong>

            <span class="valor-destaque">

                R$

                <?= number_format(
                    $entrega,
                    2,
                    ',',
                    '.'
                ) ?>

            </span>

        </p>


        <p>

            <strong>
                Total:
            </strong>

            <span class="valor-destaque">

                R$

                <?= number_format(
                    $total,
                    2,
                    ',',
                    '.'
                ) ?>

            </span>

        </p>


        <p>

            <strong>
                Observação:
            </strong>

            <?= !empty($pedido['observacao'])
                ? htmlspecialchars($pedido['observacao'])
                : 'Nenhuma observação.'
            ?>

        </p>


        <div id="mensagem-pagamento">

            Pagamento registrado com sucesso.

        </div>


    </div>


    <div class="voltar-inicio">

        <a
            href="../html/tela-inicial.html"
            class="btn-voltar-inicio"
        >

            Voltar para o início

        </a>

    </div>


</section>


</body>

</html>