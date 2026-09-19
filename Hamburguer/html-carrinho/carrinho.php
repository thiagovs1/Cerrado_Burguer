<?php

session_start();

require_once "../crud/conexao.php";


/*
|--------------------------------------------------------------------------
| VERIFICA LOGIN
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION['usuario_id'])) {

    header("Location: ../crud/login.html");
    exit;
}

$id_usuario = $_SESSION['usuario_id'];


/*
|--------------------------------------------------------------------------
| PROCURA O CARRINHO DO USUÁRIO
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        id_pedido,
        observacao
    FROM pedido
    WHERE id_usuario = ?
    AND status = 'Carrinho'
    LIMIT 1
";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    $id_usuario
]);

$carrinho = $stmt->fetch();


$produtos = [];

$subtotal = 0;

$entrega = 0;

$total = 0;

$observacao = '';


/*
|--------------------------------------------------------------------------
| SE EXISTIR CARRINHO
|--------------------------------------------------------------------------
*/

if ($carrinho) {

    $id_pedido = $carrinho['id_pedido'];

    $observacao = $carrinho['observacao'] ?? '';


    /*
    |--------------------------------------------------------------------------
    | BUSCA OS PRODUTOS NO MYSQL
    |--------------------------------------------------------------------------
    */

    $sql = "
        SELECT
            ip.id_produto,
            ip.quantidade,
            ip.preco_unitario,
            p.nome,
            p.imagem
        FROM itens_pedidos ip

        INNER JOIN produto p
            ON p.id_produto = ip.id_produto

        WHERE ip.id_pedido = ?

        ORDER BY ip.id_item_pedidos ASC
    ";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        $id_pedido
    ]);

    $produtos = $stmt->fetchAll();


    /*
    |--------------------------------------------------------------------------
    | CALCULA SUBTOTAL
    |--------------------------------------------------------------------------
    */

    foreach ($produtos as &$produto) {

        $produto['subtotal'] =
            $produto['preco_unitario']
            *
            $produto['quantidade'];

        $subtotal += $produto['subtotal'];
    }

    unset($produto);


    /*
    |--------------------------------------------------------------------------
    | POR ENQUANTO A ENTREGA FICA ZERO
    |--------------------------------------------------------------------------
    |
    | O valor da entrega será definido
    | na próxima página.
    |
    */

    $entrega = 0;

    $total = $subtotal + $entrega;
}

?>

<!DOCTYPE html>

<html lang="pt-br">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Carrinho - Cerrado Burguer</title>

    <link
        rel="stylesheet"
        href="../css-carrinho/carrinho.css"
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

        <a href="../html/cardapio.html">
            Cardápio
        </a>

        <a href="../html/tela-inicial.html">
            Contato
        </a>

        <a href="../html/avaliacoes.html">
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


<section class="pedidos">


    <div class="produtos" id="lista-carrinho">


        <?php if (empty($produtos)): ?>


            <h2>
                Seu carrinho está vazio.
            </h2>


            <a href="../html/cardapio.html">

                Voltar para o cardápio

            </a>


        <?php else: ?>


            <?php foreach ($produtos as $produto): ?>


                <div class="produto-carrinho">


                    <img
                        src="../imagens/<?= htmlspecialchars($produto['imagem']) ?>"
                        alt="<?= htmlspecialchars($produto['nome']) ?>"
                    >


                    <div class="informacoes-produto">


                        <h3>

                            <?= htmlspecialchars(
                                $produto['nome']
                            ) ?>

                        </h3>


                        <p>

                            R$

                            <?= number_format(
                                $produto['preco_unitario'],
                                2,
                                ',',
                                '.'
                            ) ?>

                        </p>


                    </div>


                    <div class="quantidade">


                        <!-- DIMINUIR -->

                        <form
                            action="../php/carrinho.php"
                            method="POST"
                        >

                            <input
                                type="hidden"
                                name="acao"
                                value="diminuir"
                            >

                            <input
                                type="hidden"
                                name="id_produto"
                                value="<?= $produto['id_produto'] ?>"
                            >

                            <button type="submit">

                                -

                            </button>

                        </form>


                        <span>

                            <?= $produto['quantidade'] ?>

                        </span>


                        <!-- AUMENTAR -->

                        <form
                            action="../php/carrinho.php"
                            method="POST"
                        >

                            <input
                                type="hidden"
                                name="acao"
                                value="aumentar"
                            >

                            <input
                                type="hidden"
                                name="id_produto"
                                value="<?= $produto['id_produto'] ?>"
                            >

                            <button type="submit">

                                +

                            </button>

                        </form>


                    </div>


                    <div class="preco-produto">

                        <strong>

                            R$

                            <?= number_format(
                                $produto['subtotal'],
                                2,
                                ',',
                                '.'
                            ) ?>

                        </strong>

                    </div>


                    <!-- EXCLUIR -->

                    <form
                        action="../php/carrinho.php"
                        method="POST"
                    >

                        <input
                            type="hidden"
                            name="acao"
                            value="excluir"
                        >

                        <input
                            type="hidden"
                            name="id_produto"
                            value="<?= $produto['id_produto'] ?>"
                        >

                        <button type="submit">

                            Excluir

                        </button>

                    </form>


                </div>


            <?php endforeach; ?>


        <?php endif; ?>


    </div>


    <div class="resumo">


        <div class="box">


            <h2>
                Resumo do Pedido
            </h2>


            <div class="linha">

                <span>
                    Subtotal:
                </span>

                <span>

                    R$

                    <?= number_format(
                        $subtotal,
                        2,
                        ',',
                        '.'
                    ) ?>

                </span>

            </div>


            <div class="linha">

                <span>
                    Entrega:
                </span>

                <span>

                    R$

                    <?= number_format(
                        $entrega,
                        2,
                        ',',
                        '.'
                    ) ?>

                </span>

            </div>


            <div class="linha total">

                <span>
                    Total:
                </span>

                <span>

                    R$

                    <?= number_format(
                        $total,
                        2,
                        ',',
                        '.'
                    ) ?>

                </span>

            </div>


        </div>


        <div class="box observacao-box">


            <h2>
                Observação:
            </h2>


            <form
                action="../php/carrinho.php"
                method="POST"
            >


                <input
                    type="hidden"
                    name="acao"
                    value="observacao"
                >


                <div class="obs">


                    <textarea
                        id="observacao"
                        name="observacao"
                        rows="4"
                        maxlength="100"
                        placeholder="Digite alguma observação para o pedido..."
                    ><?= htmlspecialchars($observacao) ?></textarea>


                </div>


                <div class="botoes">


                    <button
                        type="submit"
                        class="confirmar"
                    >

                        Confirmar

                    </button>


                </div>


            </form>


            <form
                action="../php/carrinho.php"
                method="POST"
            >

                <input
                    type="hidden"
                    name="acao"
                    value="limpar"
                >


                <button
                    type="submit"
                    id="btn-cancelar"
                    class="cancelar"
                >

                    Cancelar

                </button>

            </form>


        </div>


    </div>


</section>


</body>

</html>