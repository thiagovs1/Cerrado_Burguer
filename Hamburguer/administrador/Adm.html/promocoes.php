<?php
require_once "../../crud/conexao.php";

if (!isset($pdo)) {
    die("Erro na conexão com o banco.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $acao = $_POST['acao'] ?? '';
    $id = (int)($_POST['id_produto'] ?? 0);

    if ($id > 0) {

        if ($acao === 'excluir') {

            $stmt = $pdo->prepare("
                UPDATE produto
                SET promocao = 'Não'
                WHERE id_produto = ?
            ");

            $stmt->execute([$id]);

            header("Location: promocoes.php");
            exit;
        }

        if ($acao === 'editar') {

            $nome = trim($_POST['nome'] ?? '');
            $descricao = trim($_POST['descricao'] ?? '');
            $preco = (float)($_POST['preco'] ?? 0);
            $status = $_POST['status'] ?? 'Ativo';

            $stmt = $pdo->prepare("
                UPDATE produto
                SET nome = ?, descricao = ?, preco = ?, status = ?
                WHERE id_produto = ?
            ");

            $stmt->execute([
                $nome,
                $descricao,
                $preco,
                $status,
                $id
            ]);

            header("Location: promocoes.php");
            exit;
        }
    }
}

$busca = trim($_GET['busca'] ?? '');
$status = $_GET['status'] ?? '';

$where = ["p.promocao = 'Sim'"];
$params = [];

if ($busca !== '') {

    $where[] = "
        (
            p.nome LIKE ?
            OR p.descricao LIKE ?
            OR c.nome LIKE ?
        )
    ";

    $termo = "%$busca%";

    $params[] = $termo;
    $params[] = $termo;
    $params[] = $termo;
}

if ($status === 'Ativa') {
    $where[] = "p.status = 'Ativo'";
}

if ($status === 'Inativa') {
    $where[] = "p.status = 'Inativo'";
}

$sql = "
    SELECT
        p.id_produto,
        p.nome,
        p.descricao,
        p.preco,
        p.status,
        p.imagem,
        c.nome AS categoria
    FROM produto p
    INNER JOIN categoria c
        ON c.id_categoria = p.id_categoria
    WHERE " . implode(" AND ", $where) . "
    ORDER BY p.id_produto DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$promocoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->query("
    SELECT
        COUNT(*) AS total,
        SUM(status = 'Ativo') AS ativas,
        SUM(status = 'Inativo') AS inativas
    FROM produto
    WHERE promocao = 'Sim'
");

$resumo = $stmt->fetch(PDO::FETCH_ASSOC);

$total = (int)($resumo['total'] ?? 0);
$ativas = (int)($resumo['ativas'] ?? 0);
$inativas = (int)($resumo['inativas'] ?? 0);

$percentual = $total > 0
    ? round(($ativas / $total) * 100)
    : 0;

$editar = null;

if (isset($_GET['editar'])) {

    $idEditar = (int)$_GET['editar'];

    $stmt = $pdo->prepare("
        SELECT *
        FROM produto
        WHERE id_produto = ?
        AND promocao = 'Sim'
    ");

    $stmt->execute([$idEditar]);

    $editar = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Promoções</title>

    <link rel="stylesheet" href="../Adm.css/promocoes.css">

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

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

        <a href="adm-inicio.html">
            <i class="fa-solid fa-house"></i>
            <span>Início</span>
        </a>

        <a href="pedidos.php">
            <i class="fa-solid fa-clipboard-list"></i>
            <span>Pedidos</span>
        </a>

        <a href="cadastro.php">
            <i class="fa-solid fa-user-plus"></i>
            <span>Cadastro</span>
        </a>

        <a href="categorias.php">
            <i class="fa-solid fa-layer-group"></i>
            <span>Categorias</span>
        </a>

        <a href="relatorio.html">
            <i class="fa-solid fa-chart-column"></i>
            <span>Relatório</span>
        </a>

        <a href="promocoes.php" class="ativo">
            <i class="fa-solid fa-tag"></i>
            <span>Promoção</span>
        </a>

        <a href="clientes.php">
            <i class="fa-solid fa-users"></i>
            <span>Clientes</span>
        </a>

        <a href="entregas.html">
            <i class="fa-solid fa-truck"></i>
            <span>Entregas</span>
        </a>

        <a href="avaliacoes.html">
            <i class="fa-solid fa-star"></i>
            <span>Avaliação</span>
        </a>

        <a href="configuracoes.html">
            <i class="fa-solid fa-gear"></i>
            <span>Configurações</span>
        </a>

        <a href="encomenda.php">
            <i class="fa-solid fa-box"></i>
            <span>Encomenda</span>
        </a>

        <a href="sair.html">
            <i class="fa-solid fa-right-from-bracket"></i>
            <span>Sair</span>
        </a>

    </nav>

</aside>


<main class="conteudo">


    <div class="cabecalho-promocoes">

        <h1>Promoções</h1>

        <button class="btn-nova-promocao" type="button">

            <i class="fa-solid fa-plus"></i>

            Criar nova promoção

        </button>

    </div>

    <section class="resumo-promocoes">


        <div class="card-resumo">

            <div class="icone-resumo">
                <i class="fa-solid fa-tags"></i>
            </div>

            <div class="info-resumo">

                <p>Total de Promoções</p>

                <strong><?= $total ?></strong>

                <span>Produtos cadastrados</span>

            </div>

        </div>


        <div class="card-resumo">

            <div class="icone-resumo">
                <i class="fa-solid fa-circle-check"></i>
            </div>

            <div class="info-resumo">

                <p>Promoções ativas</p>

                <strong><?= $ativas ?></strong>

                <span><?= $percentual ?>% do total</span>

            </div>

        </div>


        <div class="card-resumo">

            <div class="icone-resumo">
                <i class="fa-solid fa-circle-xmark"></i>
            </div>

            <div class="info-resumo">

                <p>Promoções inativas</p>

                <strong><?= $inativas ?></strong>

                <span>Produtos inativos</span>

            </div>

        </div>


        <div class="card-resumo">

            <div class="icone-resumo">
                <i class="fa-solid fa-burger"></i>
            </div>

            <div class="info-resumo">

                <p>Produtos em promoção</p>

                <strong><?= $total ?></strong>

                <span>Cadastrados no sistema</span>

            </div>

        </div>


    </section>

    <section class="area-promocoes">


        <form method="GET" class="filtros">


            <div class="busca-container">

                <i class="fa-solid fa-magnifying-glass"></i>

                <input
                    type="text"
                    name="busca"
                    class="campo-busca"
                    placeholder="Buscar produto ou categoria"
                    value="<?= htmlspecialchars($busca) ?>"
                >

            </div>


            <select
                class="filtro-status"
                name="status"
                onchange="this.form.submit()"
            >

                <option value="">
                    Todos os status
                </option>

                <option
                    value="Ativa"
                    <?= $status === 'Ativa' ? 'selected' : '' ?>
                >
                    Ativa
                </option>

                <option
                    value="Inativa"
                    <?= $status === 'Inativa' ? 'selected' : '' ?>
                >
                    Inativa
                </option>

            </select>


        </form>

        <table class="tabela-promocoes">


            <thead>

                <tr>

                    <th>PRODUTO</th>

                    <th>PREÇO</th>

                    <th>CATEGORIA</th>

                    <th>STATUS</th>

                    <th>AÇÕES</th>

                </tr>

            </thead>


            <tbody>


                <?php if (!$promocoes): ?>

                    <tr>

                        <td colspan="5" style="text-align:center;">

                            Nenhuma promoção encontrada.

                        </td>

                    </tr>


                <?php else: ?>


                    <?php foreach ($promocoes as $p): ?>


                        <tr>


                            <td>

                                <div class="produto">


                                    <?php if (!empty($p['imagem'])): ?>

                                        <img
                                            src="../../imagens/<?= htmlspecialchars($p['imagem']) ?>"
                                            alt="<?= htmlspecialchars($p['nome']) ?>"
                                        >

                                    <?php endif; ?>


                                    <div class="produto-info">

                                        <strong>
                                            <?= htmlspecialchars($p['nome']) ?>
                                        </strong>


                                        <?php if (!empty($p['descricao'])): ?>

                                            <span>
                                                <?= htmlspecialchars($p['descricao']) ?>
                                            </span>

                                        <?php endif; ?>


                                    </div>


                                </div>

                            </td>


                            <td class="preco">

                                R$
                                <?= number_format(
                                    $p['preco'],
                                    2,
                                    ',',
                                    '.'
                                ) ?>

                            </td>


                            <td>

                                <?= htmlspecialchars($p['categoria']) ?>

                            </td>


                            <td class="<?= $p['status'] === 'Ativo' ? 'status-ativa' : '' ?>">

                                <?= $p['status'] === 'Ativo' ? 'Ativa' : 'Inativa' ?>

                            </td>


                            <td>


                                <div class="acoes">


                                    <!-- EDITAR -->

                                    <a
                                        href="promocoes.php?editar=<?= $p['id_produto'] ?>"
                                        class="btn-editar"
                                    >

                                        <i class="fa-solid fa-pen"></i>

                                        Editar

                                    </a>


                                    <!-- EXCLUIR -->

                                    <form
                                        method="POST"
                                        onsubmit="return confirm('Deseja retirar este produto das promoções?')"
                                    >

                                        <input
                                            type="hidden"
                                            name="acao"
                                            value="excluir"
                                        >

                                        <input
                                            type="hidden"
                                            name="id_produto"
                                            value="<?= $p['id_produto'] ?>"
                                        >

                                        <button
                                            class="btn-excluir"
                                            type="submit"
                                        >

                                            <i class="fa-solid fa-trash"></i>

                                        </button>

                                    </form>


                                </div>


                            </td>


                        </tr>


                    <?php endforeach; ?>


                <?php endif; ?>


            </tbody>


        </table>


    </section>


</main>


<?php if ($editar): ?>

    <div class="modal">


        <div class="modal-conteudo">


            <h2>Editar promoção</h2>


            <form method="POST">


                <input
                    type="hidden"
                    name="acao"
                    value="editar"
                >


                <input
                    type="hidden"
                    name="id_produto"
                    value="<?= $editar['id_produto'] ?>"
                >


                <label>Nome</label>

                <input
                    type="text"
                    name="nome"
                    value="<?= htmlspecialchars($editar['nome']) ?>"
                    required
                >


                <label>Descrição</label>

                <input
                    type="text"
                    name="descricao"
                    value="<?= htmlspecialchars($editar['descricao'] ?? '') ?>"
                >


                <label>Preço</label>

                <input
                    type="number"
                    name="preco"
                    step="0.01"
                    min="0"
                    value="<?= $editar['preco'] ?>"
                    required
                >


                <label>Status</label>

                <select name="status">

                    <option
                        value="Ativo"
                        <?= $editar['status'] === 'Ativo' ? 'selected' : '' ?>
                    >
                        Ativo
                    </option>

                    <option
                        value="Inativo"
                        <?= $editar['status'] === 'Inativo' ? 'selected' : '' ?>
                    >
                        Inativo
                    </option>

                </select>


                <div class="acoes-modal">


                    <button
                        type="submit"
                        class="btn-editar"
                    >

                        Salvar

                    </button>


                    <a
                        href="promocoes.php"
                        class="btn-excluir"
                    >

                        Cancelar

                    </a>


                </div>


            </form>


        </div>


    </div>

<?php endif; ?>


</body>

</html>