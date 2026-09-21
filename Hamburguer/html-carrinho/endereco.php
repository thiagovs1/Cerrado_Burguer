<?php
session_start();
require_once "../crud/conexao.php";

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../crud/login.html");
    exit;
}

$id_usuario = $_SESSION['usuario_id'];

$sql = "SELECT * FROM pedido WHERE id_usuario = ? AND status = 'Carrinho' LIMIT 1";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id_usuario]);
$pedido = $stmt->fetch();

if (!$pedido) {
    header("Location: carrinho.php");
    exit;
}

$tipoEntrega = $pedido['tipo_entrega'] ?? 'Entrega';
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Endereço de Entrega</title>
<link rel="stylesheet" href="../css-carrinho/endereco.css">
<link rel="stylesheet" href="../css-carrinho/carrinho.css">

<style>
.campo-desabilitado{
    background-color:#d3d3d3!important;
    color:#777!important;
    cursor:not-allowed;
}
</style>
</head>

<body>

<header class="topo">
    <a href="../html/tela-inicial.html">
        <img src="../imagens/logo.png" class="logo">
    </a>

    <nav>
        <a href="../html/tela-inicial.html">Inicio</a>
        <a href="../html/tela-inicial.html">Promoções do dia</a>
        <a href="../html/cardapio.php">Cardápio</a>
        <a href="../html/tela-inicial.html#contato">Contato</a>
        <a href="../html/tela-inicial.html#avaliacoes">Avaliações</a>
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

<section class="entrega-section">
<div class="entrega-container">

<form action="../php/endereco.php" method="POST">

    <div class="form-endereco">

        <label>Cidade:</label>
        <input type="text" name="cidade" id="cidade" placeholder="Ex.: Colinas - TO" value="<?= htmlspecialchars($pedido['cidade'] ?? '') ?>">

        <label>Estado:</label>
        <input type="text" name="estado" id="estado" placeholder="Ex.: Tocantins" value="<?= htmlspecialchars($pedido['estado'] ?? '') ?>">

        <label>Endereço:</label>
        <input type="text" name="endereco" id="endereco" placeholder="Ex.: Rua Alegria, 1234" value="<?= htmlspecialchars($pedido['endereco'] ?? '') ?>">

        <label>Setor:</label>
        <input type="text" name="setor" id="setor" placeholder="Ex.: Norte" value="<?= htmlspecialchars($pedido['setor'] ?? '') ?>">

        <label>Informação Adicional:</label>
        <textarea name="informacao" id="informacao" maxlength="500" placeholder="Ex.: Portão Vermelho"><?= htmlspecialchars($pedido['informacao_adicional'] ?? '') ?></textarea>

    </div>

    <div class="entrega-opcoes">

        <div class="opcao-box">

            <div class="opcao">
                <label>
                    <input type="radio" name="tipo_entrega" value="Retirada" id="retirada" <?= $tipoEntrega === 'Retirada' ? 'checked' : '' ?>>
                    Retirada
                </label>
                <img src="../imagens/moto.png">
            </div>

            <div class="opcao">
                <label>
                    <input type="radio" name="tipo_entrega" value="Entrega" id="entrega" <?= $tipoEntrega !== 'Retirada' ? 'checked' : '' ?>>
                    Entrega
                </label>
                <img src="../imagens/moto.png">
            </div>

        </div>

        <div class="botoes-entrega">
            <button type="button" class="btn-cancelar" onclick="window.location.href='carrinho.php'">
                Cancelar
            </button>

            <button type="submit" class="btn-confirmar">
                Confirmar
            </button>
        </div>

    </div>

</form>

</div>
</section>

<script>
const retirada = document.getElementById("retirada");
const entrega = document.getElementById("entrega");

const campos = [
    document.getElementById("cidade"),
    document.getElementById("estado"),
    document.getElementById("endereco"),
    document.getElementById("setor"),
    document.getElementById("informacao")
];

function atualizarCampos(){
    const retiradaSelecionada = retirada.checked;

    campos.forEach(campo => {
        campo.disabled = retiradaSelecionada;
        campo.required = !retiradaSelecionada;
        campo.classList.toggle("campo-desabilitado", retiradaSelecionada);
    });
}

retirada.addEventListener("change", atualizarCampos);
entrega.addEventListener("change", atualizarCampos);

atualizarCampos();
</script>

</body>
</html>