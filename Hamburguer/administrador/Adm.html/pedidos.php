<?php
require_once "../../crud/conexao.php";

$mensagem = $erro = "";
$statusOpcoes = ["Pedido recebido", "Em preparo", "Saiu para entrega", "Entregue", "Cancelado"];

function statusTela($status) {
    return match (strtolower(trim($status))) {
        "carrinho", "pedido recebido" => "Pedido recebido",
        "em preparo" => "Em preparo",
        "saiu para entrega", "em rota" => "Saiu para entrega",
        "entregue", "finalizado" => "Entregue",
        "cancelado" => "Cancelado",
        default => ucfirst($status)
    };
}

function classeStatus($status) {
    return match (statusTela($status)) {
        "Pedido recebido" => "recebido",
        "Em preparo" => "preparo",
        "Saiu para entrega" => "entrega",
        "Entregue" => "entregue",
        "Cancelado" => "cancelado",
        default => ""
    };
}

function dinheiro($valor) {
    return "R$ " . number_format((float)$valor, 2, ",", ".");
}

function calcularValores($pedido, $itens) {
    $subtotal = (float)$pedido["subtotal"];

    if ($subtotal <= 0) {
        $subtotal = array_sum(array_map(
            fn($item) => (int)$item["quantidade"] * (float)$item["preco_unitario"],
            $itens
        ));
    }

    $entrega = (float)$pedido["valor_entrega"];
    $total = (float)$pedido["total"] ?: $subtotal + $entrega;

    $pedido["_subtotal"] = $subtotal;
    $pedido["_total"] = $total;

    return $pedido;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $idPedido = (int)($_POST["id_pedido"] ?? 0);

    if (isset($_POST["alterar_status"])) {
        $novoStatus = trim($_POST["status"] ?? "");

        if ($idPedido > 0 && in_array($novoStatus, $statusOpcoes, true)) {
            try {
                $stmt = $pdo->prepare("UPDATE pedido SET status=:status WHERE id_pedido=:id");
                $stmt->execute(["status" => $novoStatus, "id" => $idPedido]);

                header("Location: pedidos.php?sucesso=status");
                exit;
            } catch (PDOException $e) {
                $erro = "Não foi possível atualizar o status do pedido.";
            }
        }
    }

    if (isset($_POST["cancelar_pedido"]) && $idPedido > 0) {
        $motivo = trim($_POST["motivo"] ?? "");

        try {
            $sql = "
                UPDATE pedido
                SET status='Cancelado',
                    observacao=CASE
                        WHEN :motivo='' THEN observacao
                        WHEN observacao IS NULL OR observacao='' THEN CONCAT('Cancelamento: ', :motivo2)
                        ELSE CONCAT(observacao, ' | Cancelamento: ', :motivo3)
                    END
                WHERE id_pedido=:id
            ";

            $pdo->prepare($sql)->execute([
                "motivo" => $motivo,
                "motivo2" => $motivo,
                "motivo3" => $motivo,
                "id" => $idPedido
            ]);

            header("Location: pedidos.php?sucesso=cancelado");
            exit;
        } catch (PDOException $e) {
            $erro = "Não foi possível cancelar o pedido.";
        }
    }
}
if (isset($_GET["sucesso"])) {
    $mensagem = match ($_GET["sucesso"]) {
        "status" => "Status do pedido atualizado com sucesso.",
        "cancelado" => "Pedido cancelado com sucesso.",
        default => ""
    };
}

$statusFiltro = $_GET["status"] ?? "todos";
$dataFiltro = $_GET["data"] ?? "";
$busca = trim($_GET["busca"] ?? "");

try {
    $sql = "
        SELECT
            p.id_pedido, p.id_usuario, p.subtotal, p.valor_entrega, p.total,
            p.tipo_entrega, p.cidade, p.estado, p.endereco, p.setor,
            p.informacao_adicional, p.forma_pagamento, p.troco,
            p.observacao, p.status, p.data_pedido,
            u.nome AS cliente, u.telefone, u.email, u.cpf
        FROM pedido p
        INNER JOIN usuario u ON u.id=p.id_usuario
        WHERE 1=1
    ";

    $params = [];

    if ($statusFiltro !== "todos") {
        $mapaStatus = [
            "Pedido recebido" => ["Pedido recebido", "Carrinho"],
            "Entregue" => ["Entregue", "finalizado", "Finalizado"],
            "Cancelado" => ["Cancelado", "cancelado"],
            "Saiu para entrega" => ["Saiu para entrega", "Em rota"]
        ];

        if (isset($mapaStatus[$statusFiltro])) {
            $placeholders = implode(",", array_fill(0, count($mapaStatus[$statusFiltro]), "?"));
            $sql .= " AND p.status IN ($placeholders)";
            $params = array_merge($params, $mapaStatus[$statusFiltro]);
        } else {
            $sql .= " AND p.status=?";
            $params[] = $statusFiltro;
        }
    }

    if ($dataFiltro !== "") {
        $sql .= " AND DATE(p.data_pedido)=?";
        $params[] = $dataFiltro;
    }

    if ($busca !== "") {
        $sql .= "
            AND (
                u.nome LIKE ?
                OR u.email LIKE ?
                OR u.telefone LIKE ?
                OR p.id_pedido LIKE ?
                OR p.forma_pagamento LIKE ?
                OR EXISTS (
                    SELECT 1
                    FROM itens_pedidos ipx
                    INNER JOIN produto prx ON prx.id_produto=ipx.id_produto
                    WHERE ipx.id_pedido=p.id_pedido
                    AND prx.nome LIKE ?
                )
            )
        ";

        $termo = "%$busca%";
        $params = array_merge($params, array_fill(0, 6, $termo));
    }

    $sql .= " ORDER BY p.data_pedido DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $pedidos = [];
    $erro = "Não foi possível carregar os pedidos.";
}

$itensPorPedido = [];

if ($pedidos) {
    $ids = array_column($pedidos, "id_pedido");
    $placeholders = implode(",", array_fill(0, count($ids), "?"));

    try {
        $sql = "
            SELECT
                ip.id_pedido, ip.id_item_pedidos, ip.id_produto,
                ip.quantidade, ip.preco_unitario,
                pr.nome AS produto, pr.imagem
            FROM itens_pedidos ip
            INNER JOIN produto pr ON pr.id_produto=ip.id_produto
            WHERE ip.id_pedido IN ($placeholders)
            ORDER BY ip.id_pedido, ip.id_item_pedidos
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($ids);

        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $item) {
            $itensPorPedido[$item["id_pedido"]][] = $item;
        }
    } catch (PDOException $e) {
    }
}

foreach ($pedidos as &$pedido) {
    $pedido = calcularValores(
        $pedido,
        $itensPorPedido[$pedido["id_pedido"]] ?? []
    );
}
unset($pedido);

$pedidoDetalhes = null;
$itensDetalhes = [];

if (!empty($_GET["ver"])) {
    $idVer = (int)$_GET["ver"];

    if ($idVer > 0) {
        try {
            $stmt = $pdo->prepare("
                SELECT p.*, u.nome AS cliente, u.email, u.telefone, u.cpf
                FROM pedido p
                INNER JOIN usuario u ON u.id=p.id_usuario
                WHERE p.id_pedido=?
                LIMIT 1
            ");
            $stmt->execute([$idVer]);
            $pedidoDetalhes = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($pedidoDetalhes) {
                $stmt = $pdo->prepare("
                    SELECT
                        ip.id_item_pedidos, ip.id_produto,
                        ip.quantidade, ip.preco_unitario,
                        pr.nome AS produto, pr.imagem
                    FROM itens_pedidos ip
                    INNER JOIN produto pr ON pr.id_produto=ip.id_produto
                    WHERE ip.id_pedido=?
                    ORDER BY ip.id_item_pedidos
                ");

                $stmt->execute([$idVer]);
                $itensDetalhes = $stmt->fetchAll(PDO::FETCH_ASSOC);

                $pedidoDetalhes = calcularValores(
                    $pedidoDetalhes,
                    $itensDetalhes
                );
            }
        } catch (PDOException $e) {
            $erro = "Não foi possível carregar os detalhes do pedido.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciamento de Pedidos</title>
    <link rel="stylesheet" href="../Adm.css/pedidos.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>

<body>

<aside class="sidebar">
    <div class="logo">
        <img src="../../imagens/logo.png" alt="Logo CerradoBurguer">
        <div class="logo-text">
            <h2>CerradoBurguer</h2>
            <p>ADMINISTRAÇÃO</p>
        </div>
    </div>

    <nav class="menu">
        <a href="adm-inicio.html"><i class="fa-solid fa-house"></i><span>Início</span></a>
        <a href="pedidos.php" class="ativo"><i class="fa-solid fa-clipboard-list"></i><span>Pedidos</span></a>
        <a href="cadastro.php"><i class="fa-solid fa-user-plus"></i><span>Cadastro</span></a>
        <a href="categorias.php"><i class="fa-solid fa-layer-group"></i><span>Categorias</span></a>
        <a href="relatorio.html"><i class="fa-solid fa-chart-column"></i><span>Relatório</span></a>
        <a href="promocoes.php"><i class="fa-solid fa-tag"></i><span>Promoção</span></a>
        <a href="clientes.php"><i class="fa-solid fa-users"></i><span>Clientes</span></a>
        <a href="entregas.html"><i class="fa-solid fa-truck"></i><span>Entregas</span></a>
        <a href="avaliacoes.html"><i class="fa-solid fa-star"></i><span>Avaliação</span></a>
        <a href="configuracoes.html"><i class="fa-solid fa-gear"></i><span>Configurações</span></a>
        <a href="encomenda.php"><i class="fa-solid fa-box"></i><span>Encomenda</span></a>
        <a href="sair.html" class="sair"><i class="fa-solid fa-right-from-bracket"></i><span>Sair</span></a>
    </nav>
</aside>

<main class="main">
    <header class="topo">
        <div>
            <h1>Gerenciamento de Pedidos</h1>
            <p>Visualize e gerencie os pedidos realizados.</p>
        </div>
    </header>

    <?php if ($mensagem): ?>
        <div class="mensagem sucesso">
            <i class="fa-solid fa-circle-check"></i>
            <?= htmlspecialchars($mensagem) ?>
        </div>
    <?php endif; ?>

    <?php if ($erro): ?>
        <div class="mensagem erro">
            <i class="fa-solid fa-circle-exclamation"></i>
            <?= htmlspecialchars($erro) ?>
        </div>
    <?php endif; ?>

    <section class="filtros">
        <form method="GET">
            <div class="filtro-grupo">
                <label for="busca">Pesquisar</label>
                <input type="text" id="busca" name="busca"
                    placeholder="Cliente, pedido, telefone..."
                    value="<?= htmlspecialchars($busca) ?>">
            </div>

            <div class="filtro-grupo">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="todos">Todos</option>

                    <?php foreach ($statusOpcoes as $status): ?>
                        <option value="<?= htmlspecialchars($status) ?>"
                            <?= $statusFiltro === $status ? "selected" : "" ?>>
                            <?= htmlspecialchars($status) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filtro-grupo">
                <label for="data">Data</label>
                <input type="date" id="data" name="data"
                    value="<?= htmlspecialchars($dataFiltro) ?>">
            </div>

            <button type="submit" class="btn-filtrar">
                <i class="fa-solid fa-filter"></i> Filtrar
            </button>

            <a href="pedidos.php" class="btn-limpar">
                <i class="fa-solid fa-rotate-left"></i> Limpar
            </a>
        </form>
    </section>

    <section class="tabela-container">
        <div class="tabela-cabecalho">
            <div>
                <h2>Pedidos</h2>
                <p><?= count($pedidos) ?> pedido(s)</p>
            </div>

            <a href="pedidos.php" class="btn-atualizar">
                <i class="fa-solid fa-rotate"></i> Atualizar
            </a>
        </div>

        <div class="tabela-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Cliente</th>
                        <th>Tipo</th>
                        <th>Endereço</th>
                        <th>Pagamento</th>
                        <th>Subtotal</th>
                        <th>Entrega</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Data</th>
                        <th>Ações</th>
                    </tr>
                </thead>

                <tbody>
                <?php if (!$pedidos): ?>
                    <tr>
                        <td colspan="11" class="vazio">
                            <i class="fa-solid fa-box-open"></i>
                            <strong>Nenhum pedido encontrado.</strong>
                            <span>Tente alterar os filtros.</span>
                        </td>
                    </tr>

                <?php else: ?>
                    <?php foreach ($pedidos as $pedido): ?>
                        <tr>
                            <td><strong>#<?= $pedido["id_pedido"] ?></strong></td>

                            <td>
                                <div class="cliente">
                                    <span><i class="fa-solid fa-user"></i></span>
                                    <div>
                                        <strong><?= htmlspecialchars($pedido["cliente"]) ?></strong>
                                        <small>ID <?= $pedido["id_usuario"] ?></small>
                                    </div>
                                </div>
                            </td>

                            <td>
                                <span class="tipo">
                                    <i class="fa-solid <?= $pedido["tipo_entrega"] === "Retirada" ? "fa-store" : "fa-motorcycle" ?>"></i>
                                    <?= htmlspecialchars($pedido["tipo_entrega"]) ?>
                                </span>
                            </td>

                            <td>
                                <?php if ($pedido["tipo_entrega"] === "Retirada"): ?>
                                    <span class="retirada">Retirada</span>
                                <?php else: ?>
                                    <div class="endereco">
                                        <?= htmlspecialchars($pedido["endereco"] ?? "-") ?>

                                        <?php if (!empty($pedido["setor"])): ?>
                                            <small>Setor: <?= htmlspecialchars($pedido["setor"]) ?></small>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </td>

                            <td><?= htmlspecialchars($pedido["forma_pagamento"] ?? "-") ?></td>
                            <td><strong><?= dinheiro($pedido["_subtotal"]) ?></strong></td>
                            <td><?= dinheiro($pedido["valor_entrega"]) ?></td>
                            <td><strong><?= dinheiro($pedido["_total"]) ?></strong></td>

                            <td>
                                <span class="status <?= classeStatus($pedido["status"]) ?>">
                                    <?= htmlspecialchars(statusTela($pedido["status"])) ?>
                                </span>
                            </td>

                            <td>
                                <?= date("d/m/Y", strtotime($pedido["data_pedido"])) ?>
                                <small><?= date("H:i", strtotime($pedido["data_pedido"])) ?></small>
                            </td>

                            <td class="acoes">
                                <a href="pedidos.php?ver=<?= $pedido["id_pedido"] ?>" title="Visualizar pedido">
                                    <i class="fa-solid fa-eye"></i>
                                </a>

                                <?php if (!in_array(statusTela($pedido["status"]), ["Cancelado", "Entregue"], true)): ?>
                                    <button type="button"
                                        title="Cancelar pedido"
                                        onclick="abrirCancelar(<?= $pedido["id_pedido"] ?>)">
                                        <i class="fa-solid fa-ban"></i>
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>

<?php if ($pedidoDetalhes): ?>
<div class="modal-fundo" id="modalDetalhes">
    <div class="modal">

        <div class="modal-topo">
            <div>
                <h2>Pedido #<?= $pedidoDetalhes["id_pedido"] ?></h2>
                <p><?= date("d/m/Y H:i", strtotime($pedidoDetalhes["data_pedido"])) ?></p>
            </div>

            <button type="button" onclick="fecharModal()">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div class="detalhe-bloco">
            <h3><i class="fa-solid fa-user"></i> Cliente</h3>

            <div class="dados-grid">
                <div>
                    <small>Nome</small>
                    <strong><?= htmlspecialchars($pedidoDetalhes["cliente"]) ?></strong>
                </div>

                <div>
                    <small>Telefone</small>
                    <strong><?= htmlspecialchars($pedidoDetalhes["telefone"] ?? "-") ?></strong>
                </div>

                <div>
                    <small>E-mail</small>
                    <strong><?= htmlspecialchars($pedidoDetalhes["email"] ?? "-") ?></strong>
                </div>

                <div>
                    <small>CPF</small>
                    <strong><?= htmlspecialchars($pedidoDetalhes["cpf"] ?? "-") ?></strong>
                </div>
            </div>
        </div>

        <div class="detalhe-bloco">
            <h3><i class="fa-solid fa-burger"></i> Produtos do pedido</h3>

            <?php if (!$itensDetalhes): ?>
                <p class="sem-itens">Nenhum produto encontrado neste pedido.</p>
            <?php else: ?>
                <div class="itens-pedido">
                    <?php foreach ($itensDetalhes as $item): ?>
                        <div class="item-pedido">
                            <div class="item-info">
                                <strong><?= htmlspecialchars($item["produto"]) ?></strong>
                                <small>
                                    <?= $item["quantidade"] ?>x <?= dinheiro($item["preco_unitario"]) ?>
                                </small>
                            </div>

                            <strong>
                                <?= dinheiro((int)$item["quantidade"] * (float)$item["preco_unitario"]) ?>
                            </strong>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="detalhe-bloco">
            <h3><i class="fa-solid fa-money-bill"></i> Valores</h3>

            <div class="valores">
                <div>
                    <span>Subtotal</span>
                    <strong><?= dinheiro($pedidoDetalhes["_subtotal"]) ?></strong>
                </div>

                <div>
                    <span>Taxa de entrega</span>
                    <strong><?= dinheiro($pedidoDetalhes["valor_entrega"]) ?></strong>
                </div>

                <div class="valor-total">
                    <span>Total</span>
                    <strong><?= dinheiro($pedidoDetalhes["_total"]) ?></strong>
                </div>
            </div>
        </div>

        <div class="detalhe-bloco">
            <h3><i class="fa-solid fa-location-dot"></i> Entrega</h3>

            <div class="dados-grid">
                <div>
                    <small>Tipo</small>
                    <strong><?= htmlspecialchars($pedidoDetalhes["tipo_entrega"]) ?></strong>
                </div>

                <div>
                    <small>Cidade</small>
                    <strong><?= htmlspecialchars($pedidoDetalhes["cidade"] ?? "-") ?></strong>
                </div>

                <div>
                    <small>Estado</small>
                    <strong><?= htmlspecialchars($pedidoDetalhes["estado"] ?? "-") ?></strong>
                </div>

                <div>
                    <small>Setor</small>
                    <strong><?= htmlspecialchars($pedidoDetalhes["setor"] ?? "-") ?></strong>
                </div>

                <div class="campo-grande">
                    <small>Endereço</small>
                    <strong><?= htmlspecialchars($pedidoDetalhes["endereco"] ?? "-") ?></strong>
                </div>

                <div class="campo-grande">
                    <small>Informação adicional</small>
                    <strong><?= htmlspecialchars($pedidoDetalhes["informacao_adicional"] ?? "-") ?></strong>
                </div>
            </div>
        </div>

        <div class="detalhe-bloco">
            <h3><i class="fa-solid fa-credit-card"></i> Pagamento</h3>

            <div class="dados-grid">
                <div>
                    <small>Forma de pagamento</small>
                    <strong><?= htmlspecialchars($pedidoDetalhes["forma_pagamento"] ?? "-") ?></strong>
                </div>

                <div>
                    <small>Troco</small>
                    <strong><?= htmlspecialchars($pedidoDetalhes["troco"] ?? "-") ?></strong>
                </div>
            </div>
        </div>

        <?php if (!empty($pedidoDetalhes["observacao"])): ?>
            <div class="detalhe-bloco">
                <h3><i class="fa-solid fa-note-sticky"></i> Observação</h3>
                <p class="observacao"><?= htmlspecialchars($pedidoDetalhes["observacao"]) ?></p>
            </div>
        <?php endif; ?>

        <div class="detalhe-bloco">
            <h3><i class="fa-solid fa-list-check"></i> Status do pedido</h3>

            <form method="POST">
                <input type="hidden" name="id_pedido" value="<?= $pedidoDetalhes["id_pedido"] ?>">

                <div class="status-edicao">
                    <select name="status">
                        <?php foreach ($statusOpcoes as $status): ?>
                            <option value="<?= htmlspecialchars($status) ?>"
                                <?= statusTela($pedidoDetalhes["status"]) === $status ? "selected" : "" ?>>
                                <?= htmlspecialchars($status) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <button type="submit" name="alterar_status">
                        <i class="fa-solid fa-check"></i> Atualizar status
                    </button>
                </div>
            </form>
        </div>

        <div class="modal-acoes">
            <button type="button" class="btn-fechar" onclick="fecharModal()">Fechar</button>

            <?php if (!in_array(statusTela($pedidoDetalhes["status"]), ["Cancelado", "Entregue"], true)): ?>
                <button type="button"
                    class="btn-cancelar-modal"
                    onclick="abrirCancelar(<?= $pedidoDetalhes["id_pedido"] ?>)">
                    <i class="fa-solid fa-ban"></i> Cancelar pedido
                </button>
            <?php endif; ?>
        </div>

    </div>
</div>
<?php endif; ?>

<div class="modal-fundo modal-cancelamento" id="modalCancelar" style="display:none;">
    <div class="modal modal-excluir">

        <div class="icone-excluir">
            <i class="fa-solid fa-trash-can"></i>
        </div>

        <h2>Cancelar pedido?</h2>

        <p class="texto-excluir">
            Tem certeza que deseja cancelar este pedido?<br>
            O pedido continuará registrado no sistema,
            mas ficará com status <strong>Cancelado</strong>.
        </p>

        <div class="pedido-excluir">
            <span>Pedido</span>
            <strong>#<span id="numeroCancelar"></span></strong>
        </div>

        <form method="POST">
            <input type="hidden" name="id_pedido" id="idPedidoCancelar">

            <label for="motivo">
                Motivo do cancelamento
                <small>opcional</small>
            </label>

            <textarea name="motivo" id="motivo" rows="3"
                placeholder="Ex.: Cliente solicitou o cancelamento..."></textarea>

            <div class="acoes-excluir">
                <button type="button" class="btn-voltar" onclick="fecharCancelar()">
                    Voltar
                </button>

                <button type="submit" name="cancelar_pedido" class="btn-confirmar-excluir">
                    <i class="fa-solid fa-trash-can"></i>
                    Confirmar cancelamento
                </button>
            </div>
        </form>

    </div>
</div>

<script>
function fecharModal() {
    window.location.href = "pedidos.php";
}

function abrirCancelar(id) {
    document.getElementById("idPedidoCancelar").value = id;
    document.getElementById("numeroCancelar").textContent = id;
    document.getElementById("modalCancelar").style.display = "flex";
}

function fecharCancelar() {
    document.getElementById("modalCancelar").style.display = "none";
}

window.addEventListener("click", e => {
    if (e.target === document.getElementById("modalCancelar")) {
        fecharCancelar();
    }
});
</script>

</body>
</html>