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
        AND status = 'Carrinho'
        LIMIT 1";

$stmt = $pdo->prepare($sql);
$stmt->execute([$id_usuario]);

$pedido = $stmt->fetch();

if (!$pedido) {
    header("Location: carrinho.php");
    exit;
}


/* CALCULA O SUBTOTAL */

$sql = "SELECT SUM(quantidade * preco_unitario) AS subtotal
        FROM itens_pedidos
        WHERE id_pedido = ?";

$stmt = $pdo->prepare($sql);
$stmt->execute([$pedido['id_pedido']]);

$subtotal = $stmt->fetch()['subtotal'] ?? 0;


/* VALOR DA ENTREGA */

$entrega = ($pedido['tipo_entrega'] === 'Entrega') ? 5 : 0;

$total = $subtotal + $entrega;


/* ENDEREÇO */

$endereco = $pedido['endereco'] ?? '';
$cidade = $pedido['cidade'] ?? '';

?>

<!DOCTYPE html>

<html lang="pt-br">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Forma de Pagamento</title>

    <link rel="stylesheet" href="../css-carrinho/forma-pagamento.css">

</head>

<body>

<header class="topo">

    <a href="../html/tela-inicial.html">
        <img src="../imagens/logo.png" class="logo">
    </a>

    <nav>

        <a href="../html/tela-inicial.html">Inicio</a>

        <a href="../html/tela-inicial.html">
            Promoções do dia
        </a>

        <a href="../html/cardapio.html">
            Cardápio
        </a>

        <a href="../html/contato.html">
            Contato
        </a>

        <a href="../html/avaliacoes.html">
            Avaliações
        </a>

    </nav>

    <div class="icones">

        <a href="../crud/perfil.php">
            <img src="../imagens/perfil.png" class="icone-img">
        </a>

        <a href="carrinho.php">
            <img src="../imagens/carrinho.png" class="icone-img">
        </a>

    </div>

</header>


<section class="pagamento-section">


<form action="../php/forma-pagamento.php" method="POST">


    <div class="pagamento-esquerda">

        <h1>Selecione a Forma de pagamento:</h1>


        <div class="pagamento-box">


            <label class="forma-pagamento">

                <div class="lado-esquerdo">

                    <input
                        type="radio"
                        name="pagamento"
                        value="Dinheiro"
                        required
                    >

                    <span>Dinheiro</span>

                </div>

                <img src="../imagens/dinheiro.png">

            </label>


            <label class="forma-pagamento">

                <div class="lado-esquerdo">

                    <input
                        type="radio"
                        name="pagamento"
                        value="Pix"
                    >

                    <span>Pix</span>

                </div>

                <img src="../imagens/pix.png">

            </label>


            <label class="forma-pagamento">

                <div class="lado-esquerdo">

                    <input
                        type="radio"
                        name="pagamento"
                        value="Cartão"
                    >

                    <span>Cartão</span>

                </div>

                <img src="../imagens/cart.png">

            </label>


        </div>


        <div class="troco-box">

            <h3>Precisa de Troco?</h3>


            <div class="troco-input">

                <label>

                    <input
                        type="radio"
                        name="troco"
                        value="Sim"
                    >

                    Sim

                </label>


                <label>

                    <input
                        type="radio"
                        name="troco"
                        value="Não"
                        checked
                    >

                    Não

                </label>


                <input
                    type="text"
                    name="valor_troco"
                    placeholder="R$ 0,00"
                >

            </div>

        </div>

    </div>


    <div class="pagamento-direita">


        <div class="resumo-pagamento">

            <h2>Resumo do Pedido</h2>


            <div class="endereco-box">

                <img src="../imagens/loja.png">

                <div>

                    <p id="endereco-resumo">

                        <?= htmlspecialchars($endereco ?: 'Retirada no estabelecimento') ?>

                    </p>

                    <p id="cidade-resumo">

                        <?= htmlspecialchars($cidade) ?>

                    </p>

                </div>

            </div>


            <hr>


            <div class="linha-pagamento">

                <span>Subtotal:</span>

                <span>

                    R$ <?= number_format($subtotal, 2, ',', '.') ?>

                </span>

            </div>


            <div class="linha-pagamento">

                <span>Entrega:</span>

                <span>

                    R$ <?= number_format($entrega, 2, ',', '.') ?>

                </span>

            </div>


            <hr>


            <div class="total-pagamento">

                <span>Total:</span>

                <span>

                    R$ <?= number_format($total, 2, ',', '.') ?>

                </span>

            </div>


        </div>


        <div class="botoes-pagamento">


            <button
                type="button"
                class="btn-cancelar"
                onclick="window.location.href='endereco.php'"
            >
                Cancelar
            </button>


            <button
                type="submit"
                class="btn-confirmar"
            >
                Confirmar
            </button>


        </div>


    </div>


</form>


</section>

</body>

</html>