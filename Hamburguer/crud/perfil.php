<?php

session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.html");
    exit;
}

require_once "conexao.php";

$id_usuario = $_SESSION['usuario_id'];

$pagina = $_GET['pagina'] ?? 'perfil';

$mensagem = "";
$tipo_mensagem = "";


if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['salvar_perfil'])
) {

    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');
    $endereco = trim($_POST['endereco'] ?? '');

    if ($nome === '' || $email === '') {

        $mensagem = "Nome e e-mail são obrigatórios.";
        $tipo_mensagem = "erro";

    } else {

        try {

            $verifica = $pdo->prepare("
                SELECT id
                FROM usuario
                WHERE email = ?
                AND id != ?
            ");

            $verifica->execute([
                $email,
                $id_usuario
            ]);

            if ($verifica->fetch()) {

                $mensagem = "Este e-mail já está sendo usado.";
                $tipo_mensagem = "erro";

            } else {

                $update = $pdo->prepare("
                    UPDATE usuario
                    SET
                        nome = ?,
                        email = ?,
                        telefone = ?,
                        endereco = ?
                    WHERE id = ?
                ");

                $update->execute([
                    $nome,
                    $email,
                    $telefone,
                    $endereco,
                    $id_usuario
                ]);

                $mensagem = "Perfil atualizado com sucesso!";
                $tipo_mensagem = "sucesso";

                $pagina = "perfil";
            }

        } catch (PDOException $e) {

            $mensagem = "Erro ao atualizar o perfil.";
            $tipo_mensagem = "erro";
        }
    }
}

if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['finalizar_pedido'])
) {

    $id_pedido = intval($_POST['id_pedido'] ?? 0);

    if ($id_pedido > 0) {

        try {

            $finalizar = $pdo->prepare("
                UPDATE pedido
                SET status = 'finalizado'
                WHERE id_pedido = ?
                AND id_usuario = ?
                AND (
                    status IS NULL
                    OR (
                        LOWER(status) NOT LIKE '%final%'
                        AND LOWER(status) NOT LIKE '%cancel%'
                        AND LOWER(status) NOT LIKE '%entreg%'
                    )
                )
            ");

            $finalizar->execute([
                $id_pedido,
                $id_usuario
            ]);

            if ($finalizar->rowCount() > 0) {

                $mensagem = "Pedido finalizado com sucesso!";
                $tipo_mensagem = "sucesso";

            } else {

                $mensagem = "Não foi possível finalizar este pedido.";
                $tipo_mensagem = "erro";
            }

            $pagina = "pedidos";

        } catch (PDOException $e) {

            $mensagem = "Erro ao finalizar o pedido.";
            $tipo_mensagem = "erro";

            $pagina = "pedidos";
        }
    }
}

if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['cancelar_pedido'])
) {

    $id_pedido = intval($_POST['id_pedido'] ?? 0);

    if ($id_pedido > 0) {

        try {

            $cancelar = $pdo->prepare("
                UPDATE pedido
                SET status = 'cancelado'
                WHERE id_pedido = ?
                AND id_usuario = ?
                AND (
                    status IS NULL
                    OR (
                        LOWER(status) NOT LIKE '%final%'
                        AND LOWER(status) NOT LIKE '%cancel%'
                        AND LOWER(status) NOT LIKE '%entreg%'
                    )
                )
            ");

            $cancelar->execute([
                $id_pedido,
                $id_usuario
            ]);

            if ($cancelar->rowCount() > 0) {

                $mensagem = "Pedido cancelado com sucesso!";
                $tipo_mensagem = "sucesso";

            } else {

                $mensagem = "Não foi possível cancelar este pedido.";
                $tipo_mensagem = "erro";
            }

            $pagina = "pedidos";

        } catch (PDOException $e) {

            $mensagem = "Erro ao cancelar o pedido.";
            $tipo_mensagem = "erro";

            $pagina = "pedidos";
        }
    }
}

$stmt = $pdo->prepare("
    SELECT
        id,
        nome,
        email,
        telefone,
        endereco,
        cpf
    FROM usuario
    WHERE id = ?
");

$stmt->execute([$id_usuario]);

$usuario = $stmt->fetch();

if (!$usuario) {

    session_destroy();

    header("Location: login.html");
    exit;
}

$pedidos = [];
$itensPedidos = [];

if ($pagina === "pedidos") {

    $stmtPedidos = $pdo->prepare("
        SELECT
            id_pedido,
            subtotal,
            valor_entrega,
            total,
            tipo_entrega,
            forma_pagamento,
            status,
            data_pedido
        FROM pedido
        WHERE id_usuario = ?
        ORDER BY data_pedido DESC
    ");

    $stmtPedidos->execute([$id_usuario]);

    $pedidos = $stmtPedidos->fetchAll();

    if (!empty($pedidos)) {

        $ids = array_column($pedidos, 'id_pedido');

        $placeholders = implode(
            ',',
            array_fill(0, count($ids), '?')
        );

        $stmtItens = $pdo->prepare("
            SELECT
                ip.id_pedido,
                ip.quantidade,
                ip.preco_unitario,
                p.nome
            FROM itens_pedidos ip
            INNER JOIN produto p
                ON p.id_produto = ip.id_produto
            WHERE ip.id_pedido IN ($placeholders)
            ORDER BY ip.id_item_pedidos
        ");

        $stmtItens->execute($ids);

        foreach ($stmtItens->fetchAll() as $item) {

            $itensPedidos[$item['id_pedido']][] = $item;
        }
    }
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

<title>Meu Perfil - CerradoBurguer</title>

<link
    rel="stylesheet"
    href="../css/perfil.css"
>

<link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
>


</head>

<body class="perfil-body">


<div
    id="modalExcluir"
    class="modal-excluir"
>


<div class="modal-conteudo">

    <h2>Atenção!</h2>

    <p>
        Tem certeza que deseja apagar seu perfil?
        Esta ação não pode ser desfeita.
    </p>

    <div class="botoes-modal">

        <button
            type="button"
            class="btn-modal btn-cancelar"
            onclick="fecharModal()"
        >
            Cancelar
        </button>

        <a
            id="linkConfirmarExclusao"
            href="deletar.php"
            class="btn-modal btn-confirmar"
        >
            Sim, Apagar
        </a>

    </div>

</div>


</div>

<div
    id="modalAcao"
    class="modal-excluir"
>


<div class="modal-conteudo">

    <h2 id="modalAcaoTitulo">
        Atenção!
    </h2>

    <p id="modalAcaoTexto">
        Tem certeza que deseja continuar?
    </p>

    <div class="botoes-modal">

        <button
            type="button"
            class="btn-modal btn-cancelar"
            onclick="fecharModalAcao()"
        >
            Não
        </button>

        <button
            type="button"
            id="btnConfirmarAcao"
            class="btn-modal btn-confirmar"
            onclick="confirmarAcaoModal()"
        >
            Sim
        </button>

    </div>

</div>


</div>

<div class="pagina">

<header class="menu-superior">


<a
    href="../html/tela-inicial.html"
    class="logo-site"
>

    <img
        src="../imagens/logo.png"
        alt="CerradoBurguer"
    >

</a>


<nav class="links-menu">

    <a href="../html/tela-inicial.html">
        Início
    </a>

    <a href="../html/tela-inicial.html#promocoes">
        Promoções do dia
    </a>

    <a href="../html/cardapio.html">
        Cardápio
    </a>

    <a href="../html/tela-inicial.html#contato">
        Contato
    </a>

    <a href="../html/tela-inicial.html#avaliacao">
        Avaliações
    </a>

</nav>


<div class="acoes-menu">

    <a
        href="perfil.php"
        class="icone-menu perfil-ativo"
        title="Meu Perfil"
    >

        <img
            src="../imagens/perfil.png"
            alt="Perfil"
        >

    </a>


    <a
        href="../html-carrinho/carrinho.html"
        class="icone-menu"
        title="Carrinho"
    >

        <img
            src="../imagens/carrinho.png"
            alt="Carrinho"
        >

    </a>

</div>


</header>

<main class="conteudo">

<?php if ($pagina === "editar"): ?>

<div class="area-perfil">


<aside class="perfil-menu">

    <a href="perfil.php">

        <i class="fa-solid fa-user"></i>

        <span>
            Meu Perfil
        </span>

    </a>


    <a href="perfil.php?pagina=pedidos">

        <i class="fa-solid fa-receipt"></i>

        <span>
            Pedidos
        </span>

    </a>


    <a
        href="logout.php"
        onclick="return confirmarSaida(event)"
    >

        <i class="fa-solid fa-right-from-bracket"></i>

        <span>
            Sair da Conta
        </span>

    </a>


    <a
        href="#"
        class="apagar"
        onclick="abrirModal(event)"
    >

        <i class="fa-solid fa-trash"></i>

        <span>
            Apagar Perfil
        </span>

    </a>


    <div class="propaganda">

        <span>
            Peça seu<br>
            favorito<br>
            agora!
        </span>

    </div>


    <a
        href="../html/tela-inicial.html"
        class="voltar-inicio"
    >
        Voltar ao início
    </a>

</aside>


<section class="perfil-caixa editar-caixa">

    <div class="icone-perfil">

        <img
            src="../imagens/perfil.png"
            alt="Perfil"
        >

    </div>


    <h1>
        Editar Perfil
    </h1>


    <?php if ($mensagem !== ""): ?>

        <div class="mensagem <?= $tipo_mensagem ?>">

            <?= htmlspecialchars($mensagem) ?>

        </div>

    <?php endif; ?>


    <form method="POST">

        <div class="campo">

            <label>
                Nome:
            </label>

            <input
                type="text"
                name="nome"
                value="<?= htmlspecialchars($usuario['nome'] ?? '') ?>"
                required
            >

        </div>


        <div class="campo">

            <label>
                Email:
            </label>

            <input
                type="email"
                name="email"
                value="<?= htmlspecialchars($usuario['email'] ?? '') ?>"
                required
            >

        </div>


        <div class="campo">

            <label>
                Telefone:
            </label>

            <input
                type="text"
                name="telefone"
                value="<?= htmlspecialchars($usuario['telefone'] ?? '') ?>"
            >

        </div>


        <div class="campo">

            <label>
                CPF:
            </label>

            <input
                type="text"
                value="<?= htmlspecialchars($usuario['cpf'] ?? '') ?>"
                readonly
                class="bloqueado"
            >

        </div>


        <div class="campo">

            <label>
                Endereço:
            </label>

            <input
                type="text"
                name="endereco"
                value="<?= htmlspecialchars($usuario['endereco'] ?? '') ?>"
            >

        </div>


        <div class="botoes">

            <button
                type="submit"
                name="salvar_perfil"
                class="btn salvar"
            >
                Salvar Alterações
            </button>


            <a
                href="perfil.php"
                class="btn cancelar"
            >
                Cancelar
            </a>

        </div>

    </form>

</section>


</div>

<?php elseif ($pagina === "pedidos"): ?>

<div class="area-perfil">


<aside class="perfil-menu">

    <a href="perfil.php">

        <i class="fa-solid fa-user"></i>

        <span>
            Meu Perfil
        </span>

    </a>


    <a
        href="perfil.php?pagina=pedidos"
        class="selecionado"
    >

        <i class="fa-solid fa-receipt"></i>

        <span>
            Pedidos
        </span>

    </a>


    <a
        href="logout.php"
        onclick="return confirmarSaida(event)"
    >

        <i class="fa-solid fa-right-from-bracket"></i>

        <span>
            Sair da Conta
        </span>

    </a>


    <a
        href="#"
        class="apagar"
        onclick="abrirModal(event)"
    >

        <i class="fa-solid fa-trash"></i>

        <span>
            Apagar Perfil
        </span>

    </a>


    <div class="propaganda">

        <span>
            Peça seu<br>
            favorito<br>
            agora!
        </span>

    </div>


    <a
        href="../html/tela-inicial.html"
        class="voltar-inicio"
    >
        Voltar ao início
    </a>

</aside>


<section class="pedidos-caixa">

    <h1>
        Meus Pedidos
    </h1>


    <?php if ($mensagem !== ""): ?>

        <div class="mensagem <?= $tipo_mensagem ?>">

            <?= htmlspecialchars($mensagem) ?>

        </div>

    <?php endif; ?>


    <div class="filtros">

        <button onclick="filtrarPedidos('todos')">
            Todos
        </button>

        <button onclick="filtrarPedidos('aberto')">
            Abertos
        </button>

        <button onclick="filtrarPedidos('finalizado')">
            Finalizados
        </button>

        <button onclick="filtrarPedidos('cancelado')">
            Cancelados
        </button>

    </div>


    <?php if (empty($pedidos)): ?>


        <div class="sem-pedidos">

            <h2>
                Nenhum pedido encontrado
            </h2>

            <p>
                Você ainda não realizou nenhum pedido.
            </p>

            <a
                href="../html/cardapio.html"
                class="btn salvar"
            >
                Ver Cardápio
            </a>

        </div>


    <?php else: ?>


        <?php foreach ($pedidos as $pedido): ?>


            <?php

            $status = strtolower($pedido['status'] ?? '');

            if (str_contains($status, 'cancel')) {

                $classeStatus = "cancelado";
                $grupoStatus = "cancelado";

            } elseif (
                str_contains($status, 'final')
                ||
                str_contains($status, 'entreg')
            ) {

                $classeStatus = "finalizado";
                $grupoStatus = "finalizado";

            } else {

                $classeStatus = "aberto";
                $grupoStatus = "aberto";
            }

            ?>


            <article
                class="pedido-card"
                data-status="<?= $grupoStatus ?>"
            >


                <div class="pedido-topo">

                    <div>

                        <strong>
                            Pedido #<?= $pedido['id_pedido'] ?>
                        </strong>

                        <span>
                            <?= date(
                                'd/m/Y - H:i',
                                strtotime($pedido['data_pedido'])
                            ) ?>
                        </span>

                    </div>


                    <span
                        class="status <?= $classeStatus ?>"
                    >

                        <?= htmlspecialchars(
                            $pedido['status']
                        ) ?>

                    </span>

                </div>


                <div class="pedido-produtos">

                    <?php

                    $itens =
                        $itensPedidos[$pedido['id_pedido']]
                        ?? [];

                    ?>


                    <?php if (!empty($itens)): ?>


                        <?php foreach ($itens as $item): ?>

                            <div class="produto">

                                <span>

                                    <?= $item['quantidade'] ?>x

                                    <?= htmlspecialchars(
                                        $item['nome']
                                    ) ?>

                                </span>


                                <span>

                                    R$

                                    <?= number_format(
                                        $item['preco_unitario'],
                                        2,
                                        ',',
                                        '.'
                                    ) ?>

                                </span>

                            </div>

                        <?php endforeach; ?>


                    <?php else: ?>

                        <p>
                            Produtos do pedido não encontrados.
                        </p>

                    <?php endif; ?>

                </div>


                <div class="pedido-informacoes">

                    <span>

                        <?= htmlspecialchars(
                            $pedido['tipo_entrega']
                        ) ?>

                    </span>


                    <?php if (!empty($pedido['forma_pagamento'])): ?>

                        <span>

                            Pagamento:

                            <?= htmlspecialchars(
                                $pedido['forma_pagamento']
                            ) ?>

                        </span>

                    <?php endif; ?>

                </div>


                <div class="pedido-final">

                    <strong>

                        Total:

                        R$

                        <?= number_format(
                            $pedido['total'],
                            2,
                            ',',
                            '.'
                        ) ?>

                    </strong>


                    <?php if ($grupoStatus === "aberto"): ?>

                        <span class="pedido-andamento">
                            Preparando seu pedido
                        </span>

                    <?php elseif ($grupoStatus === "finalizado"): ?>

                        <span class="pedido-finalizado">
                            Pedido finalizado
                        </span>

                    <?php elseif ($grupoStatus === "cancelado"): ?>

                        <span class="pedido-cancelado">
                            Pedido cancelado
                        </span>

                    <?php endif; ?>

                </div>

                <?php if ($grupoStatus === "aberto"): ?>


                    <div class="acoes-pedido">


                        <form
                            method="POST"
                            class="form-pedido"
                        >

                            <input
                                type="hidden"
                                name="id_pedido"
                                value="<?= $pedido['id_pedido'] ?>"
                            >

                            <button
                                type="button"
                                class="btn-finalizar-pedido"
                                onclick="abrirConfirmacaoPedido(this, 'finalizar', <?= $pedido['id_pedido'] ?>)"
                            >

                                <i class="fa-solid fa-check"></i>

                                Finalizar pedido

                            </button>

                        </form>


                        <form
                            method="POST"
                            class="form-pedido"
                        >

                            <input
                                type="hidden"
                                name="id_pedido"
                                value="<?= $pedido['id_pedido'] ?>"
                            >

                            <button
                                type="button"
                                class="btn-cancelar-pedido"
                                onclick="abrirConfirmacaoPedido(this, 'cancelar', <?= $pedido['id_pedido'] ?>)"
                            >

                                <i class="fa-solid fa-xmark"></i>

                                Cancelar pedido

                            </button>

                        </form>


                    </div>


                <?php endif; ?>


            </article>


        <?php endforeach; ?>


    <?php endif; ?>


</section>


</div>

<?php else: ?>

<div class="area-perfil">


<aside class="perfil-menu">

    <a
        href="perfil.php"
        class="selecionado"
    >

        <i class="fa-solid fa-user"></i>

        <span>
            Meu Perfil
        </span>

    </a>


    <a href="perfil.php?pagina=pedidos">

        <i class="fa-solid fa-receipt"></i>

        <span>
            Pedidos
        </span>

    </a>


    <a
        href="logout.php"
        onclick="return confirmarSaida(event)"
    >

        <i class="fa-solid fa-right-from-bracket"></i>

        <span>
            Sair da Conta
        </span>

    </a>


    <a
        href="#"
        class="apagar"
        onclick="abrirModal(event)"
    >

        <i class="fa-solid fa-trash"></i>

        <span>
            Apagar Perfil
        </span>

    </a>


    <div class="propaganda">

        <span>
            Peça seu<br>
            favorito<br>
            agora!
        </span>

    </div>


    <a
        href="../html/tela-inicial.html"
        class="voltar-inicio"
    >
        Voltar ao início
    </a>

</aside>


<section class="perfil-caixa">

    <div class="icone-perfil">

        <img
            src="../imagens/perfil.png"
            alt="Perfil"
        >

    </div>


    <h1>
        Meu Perfil
    </h1>


    <?php if ($mensagem !== ""): ?>

        <div class="mensagem <?= $tipo_mensagem ?>">

            <?= htmlspecialchars($mensagem) ?>

        </div>

    <?php endif; ?>


    <div class="dados-perfil">


        <div class="dado">

            <strong>
                Nome:
            </strong>

            <span>
                <?= htmlspecialchars(
                    $usuario['nome'] ?? ''
                ) ?>
            </span>

        </div>


        <div class="dado">

            <strong>
                Email:
            </strong>

            <span>
                <?= htmlspecialchars(
                    $usuario['email'] ?? ''
                ) ?>
            </span>

        </div>


        <div class="dado">

            <strong>
                Telefone:
            </strong>

            <span>
                <?= htmlspecialchars(
                    $usuario['telefone'] ?? ''
                ) ?>
            </span>

        </div>


        <div class="dado">

            <strong>
                CPF:
            </strong>

            <span>
                <?= htmlspecialchars(
                    $usuario['cpf'] ?? ''
                ) ?>
            </span>

        </div>


        <div class="dado">

            <strong>
                Endereço:
            </strong>

            <span>
                <?= htmlspecialchars(
                    $usuario['endereco'] ?? ''
                ) ?>
            </span>

        </div>


    </div>


    <div class="botoes">

        <a
            href="perfil.php?pagina=editar"
            class="btn salvar"
        >
            ✎ Editar perfil
        </a>

    </div>


</section>


</div>

<?php endif; ?>

</main>

</div>

<script>

function abrirModal(event) {

    event.preventDefault();

    const modal =
        document.getElementById("modalExcluir");

    modal.style.display = "flex";
}


function fecharModal() {

    const modal =
        document.getElementById("modalExcluir");

    modal.style.display = "none";
}

let formularioAcao = null;
let tipoAcao = null;
let linkSaida = null;

function abrirConfirmacaoPedido(botao, tipo, idPedido) {

    formularioAcao = botao.closest("form");

    tipoAcao = tipo;

    linkSaida = null;

    const modal =
        document.getElementById("modalAcao");

    const titulo =
        document.getElementById("modalAcaoTitulo");

    const texto =
        document.getElementById("modalAcaoTexto");

    const confirmar =
        document.getElementById("btnConfirmarAcao");


    if (tipo === "finalizar") {

        titulo.textContent =
            "Finalizar pedido";

        texto.textContent =
            "Tem certeza que deseja finalizar o pedido #" +
            idPedido +
            "?";

        confirmar.textContent =
            "Finalizar";

        confirmar.className =
            "btn-modal btn-confirmar";

    } else {

        titulo.textContent =
            "Cancelar pedido";

        texto.textContent =
            "Tem certeza que deseja cancelar o pedido #" +
            idPedido +
            "?";

        confirmar.textContent =
            "Cancelar";

        confirmar.className =
            "btn-modal btn-cancelar-pedido-modal";
    }


    modal.style.display = "flex";
}

function confirmarSaida(event) {

    event.preventDefault();

    linkSaida =
        event.currentTarget;

    formularioAcao = null;

    tipoAcao = "saida";


    const modal =
        document.getElementById("modalAcao");

    const titulo =
        document.getElementById("modalAcaoTitulo");

    const texto =
        document.getElementById("modalAcaoTexto");

    const confirmar =
        document.getElementById("btnConfirmarAcao");


    titulo.textContent =
        "Sair da conta";

    texto.textContent =
        "Tem certeza que deseja sair da sua conta?";

    confirmar.textContent =
        "Sair";

    confirmar.className =
        "btn-modal btn-confirmar";


    modal.style.display = "flex";

    return false;
}

function confirmarAcaoModal() {

    /* SAIR DA CONTA */

    if (linkSaida) {

        window.location.href =
            linkSaida.href;

        return;
    }


    /* FINALIZAR OU CANCELAR PEDIDO */

    if (
        formularioAcao
        &&
        tipoAcao
    ) {

        const input =
            document.createElement("input");

        input.type = "hidden";

        if (tipoAcao === "finalizar") {

            input.name =
                "finalizar_pedido";

        } else if (tipoAcao === "cancelar") {

            input.name =
                "cancelar_pedido";

        }

        input.value = "1";

        formularioAcao.appendChild(input);

        formularioAcao.submit();
    }
}

function fecharModalAcao() {

    const modal =
        document.getElementById("modalAcao");

    modal.style.display = "none";

    formularioAcao = null;

    tipoAcao = null;

    linkSaida = null;
}

window.addEventListener("click", function(event) {

    const modalExcluir =
        document.getElementById("modalExcluir");

    const modalAcao =
        document.getElementById("modalAcao");


    if (event.target === modalExcluir) {

        fecharModal();
    }


    if (event.target === modalAcao) {

        fecharModalAcao();
    }

});

function filtrarPedidos(filtro) {

    const pedidos =
        document.querySelectorAll(".pedido-card");

    pedidos.forEach(function(pedido) {

        if (
            filtro === "todos"
            ||
            pedido.dataset.status === filtro
        ) {

            pedido.style.display = "block";

        } else {

            pedido.style.display = "none";
        }

    });

}


</script>

</body>

</html>
