<?php
require_once "../Crud/conexao.php";

if (!isset($pdo)) {
    die("Erro na conexão com o banco.");
}

function numeroEncomenda($id){
    return '#ENQ-' . str_pad($id, 4, '0', STR_PAD_LEFT);
}

function moeda($valor){
    return 'R$ ' . number_format((float)$valor, 2, ',', '.');
}

function classeStatus($status){
    return match($status){
        'Pendente' => 'pendente',
        'Em Andamento' => 'andamento',
        'Recebida' => 'recebida',
        'Cancelada' => 'cancelado',
        default => ''
    };
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';
    $id = (int)($_POST['id_encomenda'] ?? 0);

    if ($id > 0) {
        if ($acao === 'receber') {
            $stmt = $pdo->prepare("UPDATE encomenda SET status = 'Recebida' WHERE id_encomenda = ?");
            $stmt->execute([$id]);
        }

        if ($acao === 'cancelar') {
            $stmt = $pdo->prepare("UPDATE encomenda SET status = 'Cancelada' WHERE id_encomenda = ?");
            $stmt->execute([$id]);
        }

        if ($acao === 'excluir') {
            $stmt = $pdo->prepare("DELETE FROM encomenda WHERE id_encomenda = ?");
            $stmt->execute([$id]);
        }
    }

    header("Location: encomenda.php");
    exit;
}

$busca = trim($_GET['busca'] ?? '');
$status = $_GET['status'] ?? '';

$where = [];
$params = [];

if ($busca !== '') {
    $where[] = "(e.nome_fornecedor LIKE ? OR CONCAT('#ENQ-', LPAD(e.id_encomenda, 4, '0')) LIKE ?)";
    $params[] = "%$busca%";
    $params[] = "%$busca%";
}

if ($status !== '') {
    $where[] = "e.status = ?";
    $params[] = $status;
}

$sql = "
    SELECT e.*,
    (
        SELECT COUNT(*)
        FROM itens_encomenda ie
        WHERE ie.id_encomenda = e.id_encomenda
    ) AS quantidade_itens
    FROM encomenda e
";

if ($where) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

$sql .= " ORDER BY e.id_encomenda DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$encomendas = $stmt->fetchAll(PDO::FETCH_ASSOC);

function card($pdo, $condicao = ''){
    $sql = "
        SELECT COUNT(*) AS quantidade,
        COALESCE(SUM(valor_total), 0) AS total
        FROM encomenda
    ";

    if ($condicao !== '') {
        $sql .= " WHERE " . $condicao;
    }

    return $pdo->query($sql)->fetch(PDO::FETCH_ASSOC);
}

$hoje = card($pdo, "data_encomenda = CURDATE()");
$pendentes = card($pdo, "status = 'Pendente'");
$andamento = card($pdo, "status = 'Em Andamento'");
$recebidas = card($pdo, "status = 'Recebida'");
$canceladas = card($pdo, "status = 'Cancelada'");

$visualizar = (int)($_GET['visualizar'] ?? 0);
$detalhe = null;
$itens = [];

if ($visualizar > 0) {
    $stmt = $pdo->prepare("SELECT * FROM encomenda WHERE id_encomenda = ?");
    $stmt->execute([$visualizar]);
    $detalhe = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($detalhe) {
        $stmt = $pdo->prepare("SELECT * FROM itens_encomenda WHERE id_encomenda = ? ORDER BY id_item");
        $stmt->execute([$visualizar]);
        $itens = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Encomenda</title>
    <link rel="stylesheet" href="../Adm.css/encomenda.css">
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
        <a href="pedidos.html"><i class="fa-solid fa-clipboard-list"></i><span>Pedidos</span></a>
        <a href="cadastro.php"><i class="fa-solid fa-user-plus"></i><span>Cadastro</span></a>
        <a href="categorias.php"><i class="fa-solid fa-layer-group"></i><span>Categorias</span></a>
        <a href="relatorio.html"><i class="fa-solid fa-chart-column"></i><span>Relatório</span></a>
        <a href="promocoes.html"><i class="fa-solid fa-tag"></i><span>Promoção</span></a>
        <a href="clientes.php"><i class="fa-solid fa-users"></i><span>Clientes</span></a>
        <a href="entregas.html"><i class="fa-solid fa-truck"></i><span>Entregas</span></a>
        <a href="avaliacoes.html"><i class="fa-solid fa-star"></i><span>Avaliação</span></a>
        <a href="configuracoes.html"><i class="fa-solid fa-gear"></i><span>Configurações</span></a>
        <a href="encomenda.php" class="ativo"><i class="fa-solid fa-box"></i><span>Encomenda</span></a>
        <a href="sair.html"><i class="fa-solid fa-right-from-bracket"></i><span>Sair</span></a>
    </nav>
</aside>

<main class="conteudo">
    <header class="topo">
        <div>
            <h1>Encomenda</h1>
            <p>Gerenciamento de encomendas</p>
        </div>
        <a href="nova_encomenda.php" class="nova-encomenda">+ Nova Encomenda</a>
    </header>

    <section class="cards">
        <div class="card">
            <h3>Encomenda de Hoje</h3>
            <strong><?= $hoje['quantidade'] ?></strong>
            <span><?= moeda($hoje['total']) ?></span>
        </div>

        <div class="card">
            <h3>Pendente</h3>
            <strong><?= $pendentes['quantidade'] ?></strong>
            <span><?= moeda($pendentes['total']) ?></span>
        </div>

        <div class="card">
            <h3>Em Andamento</h3>
            <strong><?= $andamento['quantidade'] ?></strong>
            <span><?= moeda($andamento['total']) ?></span>
        </div>

        <div class="card">
            <h3>Recebidas</h3>
            <strong><?= $recebidas['quantidade'] ?></strong>
            <span><?= moeda($recebidas['total']) ?></span>
        </div>

        <div class="card">
            <h3>Canceladas</h3>
            <strong><?= $canceladas['quantidade'] ?></strong>
            <span><?= moeda($canceladas['total']) ?></span>
        </div>
    </section>

    <form method="GET" class="filtros">
        <div class="campo pesquisa">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" name="busca" placeholder="Número ou fornecedor" value="<?= htmlspecialchars($busca) ?>">
        </div>

        <select name="status">
            <option value="">Todos os status</option>
            <?php
            $statusLista = ['Pendente', 'Em Andamento', 'Recebida', 'Cancelada'];
            foreach ($statusLista as $s):
            ?>
                <option value="<?= htmlspecialchars($s) ?>" <?= $status === $s ? 'selected' : '' ?>>
                    <?= htmlspecialchars($s) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit">Filtrar</button>
        <a href="encomenda.php">Limpar</a>
    </form>

    <section class="area-principal">
        <div class="tabela-container">
            <div class="tabela-scroll">
                <table>
                    <thead>
                        <tr>
                            <th>Número</th>
                            <th>Fornecedor</th>
                            <th>Tipo</th>
                            <th>Data</th>
                            <th>Itens</th>
                            <th>Valor</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php if (!$encomendas): ?>
                        <tr>
                            <td colspan="8">Nenhuma encomenda encontrada.</td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($encomendas as $e): ?>
                        <tr>
                            <td><?= numeroEncomenda($e['id_encomenda']) ?></td>
                            <td><?= htmlspecialchars($e['nome_fornecedor']) ?></td>
                            <td><?= htmlspecialchars($e['tipo_fornecedor']) ?></td>
                            <td><?= date('d/m/Y', strtotime($e['data_encomenda'])) ?></td>
                            <td><?= $e['quantidade_itens'] ?></td>
                            <td><?= moeda($e['valor_total']) ?></td>
                            <td>
                                <span class="status <?= classeStatus($e['status']) ?>">
                                    <?= htmlspecialchars($e['status']) ?>
                                </span>
                            </td>

                            <td class="acoes">
                                <a href="?visualizar=<?= $e['id_encomenda'] ?>" class="editar">Ver</a>

                                <?php if ($e['status'] !== 'Recebida' && $e['status'] !== 'Cancelada'): ?>
                                    <form method="POST" style="display:inline">
                                        <input type="hidden" name="acao" value="receber">
                                        <input type="hidden" name="id_encomenda" value="<?= $e['id_encomenda'] ?>">
                                        <button type="submit" class="editar">Receber</button>
                                    </form>

                                    <form method="POST" style="display:inline">
                                        <input type="hidden" name="acao" value="cancelar">
                                        <input type="hidden" name="id_encomenda" value="<?= $e['id_encomenda'] ?>">
                                        <button type="submit" class="editar">Cancelar</button>
                                    </form>
                                <?php endif; ?>

                                <form method="POST" style="display:inline">
                                    <input type="hidden" name="acao" value="excluir">
                                    <input type="hidden" name="id_encomenda" value="<?= $e['id_encomenda'] ?>">
                                    <button type="submit" class="excluir">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php if ($detalhe): ?>
            <section class="detalhes">
                <div class="detalhes-topo">
                    <div>
                        <h2><?= numeroEncomenda($detalhe['id_encomenda']) ?></h2>
                        <strong><?= htmlspecialchars($detalhe['nome_fornecedor']) ?></strong>
                    </div>
                </div>

                <div class="informacoes">
                    <div>
                        <b>Tipo:</b>
                        <span><?= htmlspecialchars($detalhe['tipo_fornecedor']) ?></span>
                    </div>

                    <div>
                        <b>Data da encomenda:</b>
                        <span><?= date('d/m/Y', strtotime($detalhe['data_encomenda'])) ?></span>
                    </div>

                    <div>
                        <b>Data prevista:</b>
                        <span><?= date('d/m/Y', strtotime($detalhe['data_prevista'])) ?></span>
                    </div>

                    <div>
                        <b>Status:</b>
                        <span><?= htmlspecialchars($detalhe['status']) ?></span>
                    </div>

                    <div>
                        <b>Observação:</b>
                        <span><?= htmlspecialchars($detalhe['observacao'] ?: 'Nenhuma') ?></span>
                    </div>
                </div>

                <h3>Itens</h3>

                <div class="itens-scroll">
                    <table class="tabela-itens">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Tipo</th>
                                <th>Unidade</th>
                                <th>Quantidade</th>
                                <th>Valor unitário</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>

                        <tbody>
                        <?php if (!$itens): ?>
                            <tr>
                                <td colspan="6">Nenhum item encontrado.</td>
                            </tr>
                        <?php endif; ?>

                        <?php foreach ($itens as $item): ?>
                            <tr>
                                <td><?= htmlspecialchars($item['nome_item']) ?></td>
                                <td><?= htmlspecialchars($item['tipo']) ?></td>
                                <td><?= htmlspecialchars($item['unidade']) ?></td>
                                <td><?= htmlspecialchars($item['quantidade']) ?></td>
                                <td><?= moeda($item['valor_unitario']) ?></td>
                                <td><?= moeda($item['subtotal']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="total">
                    <b>Total:</b>
                    <strong><?= moeda($detalhe['valor_total']) ?></strong>
                </div>
            </section>
        <?php endif; ?>
    </section>
</main>
</body>
</html>