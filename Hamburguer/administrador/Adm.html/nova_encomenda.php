<?php
session_start();
require_once "../Crud/conexao.php";

if (!isset($pdo)) die("Erro na conexão com o banco.");

if (!isset($_SESSION['dados_encomenda'])) {
    $_SESSION['dados_encomenda'] = [
        'id_fornecedor' => '',
        'nome_fornecedor' => '',
        'tipo_fornecedor' => '',
        'data_encomenda' => date('Y-m-d'),
        'data_prevista' => '',
        'observacao' => ''
    ];
}

if (!isset($_SESSION['itens_encomenda'])) {
    $_SESSION['itens_encomenda'] = [];
}

$dados = $_SESSION['dados_encomenda'];

/* FORNECEDORES */
$stmt = $pdo->query("
    SELECT id_fornecedor, nome, tipo
    FROM fornecedor
    WHERE status = 'Ativo'
    ORDER BY nome
");
$fornecedores = $stmt->fetchAll();

/* AÇÕES */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $acao = $_POST['acao'] ?? '';

    /* SELECIONAR FORNECEDOR */
    if ($acao === 'selecionar_fornecedor') {

        $id = (int)($_POST['id_fornecedor'] ?? 0);

        $stmt = $pdo->prepare("
            SELECT id_fornecedor, nome, tipo
            FROM fornecedor
            WHERE id_fornecedor = ?
            AND status = 'Ativo'
            LIMIT 1
        ");
        $stmt->execute([$id]);
        $fornecedor = $stmt->fetch();

        if ($fornecedor) {
            $_SESSION['dados_encomenda']['id_fornecedor'] = $fornecedor['id_fornecedor'];
            $_SESSION['dados_encomenda']['nome_fornecedor'] = $fornecedor['nome'];
            $_SESSION['dados_encomenda']['tipo_fornecedor'] = $fornecedor['tipo'];
            $_SESSION['dados_encomenda']['data_prevista'] = '';
            unset($_SESSION['produto_selecionado']);
        }

        header("Location: nova_encomenda.php");
        exit;
    }

    /* SELECIONAR PRODUTO */
    if ($acao === 'selecionar_produto') {

        $id = (int)($_POST['id_fornecedor_item'] ?? 0);

        $stmt = $pdo->prepare("
            SELECT id_fornecedor_item, nome_item, tipo, unidade,
                   valor_unitario, prazo_dias
            FROM fornecedor_item
            WHERE id_fornecedor_item = ?
            AND id_fornecedor = ?
            AND status = 'Ativo'
            LIMIT 1
        ");
        $stmt->execute([$id, $dados['id_fornecedor']]);
        $produto = $stmt->fetch();

        if ($produto) {
            $_SESSION['produto_selecionado'] = $produto;

            $data = new DateTime($dados['data_encomenda']);
            $data->modify("+" . (int)$produto['prazo_dias'] . " days");

            $_SESSION['dados_encomenda']['data_prevista'] =
                $data->format('Y-m-d');
        }

        header("Location: nova_encomenda.php");
        exit;
    }

    /* ATUALIZAR DATA E OBSERVAÇÃO */
    if ($acao === 'atualizar_dados') {

        $_SESSION['dados_encomenda']['data_encomenda'] =
            $_POST['data_encomenda'] ?? date('Y-m-d');

        $_SESSION['dados_encomenda']['observacao'] =
            trim($_POST['observacao'] ?? '');

        if (isset($_SESSION['produto_selecionado'])) {

            $produto = $_SESSION['produto_selecionado'];
            $data = new DateTime(
                $_SESSION['dados_encomenda']['data_encomenda']
            );

            $data->modify("+" . (int)$produto['prazo_dias'] . " days");

            $_SESSION['dados_encomenda']['data_prevista'] =
                $data->format('Y-m-d');
        }

        header("Location: nova_encomenda.php");
        exit;
    }

    /* ADICIONAR ITEM */
    if ($acao === 'adicionar_item') {

        $id = (int)($_POST['id_fornecedor_item'] ?? 0);
        $quantidade = (float)($_POST['quantidade'] ?? 0);

        $stmt = $pdo->prepare("
            SELECT id_fornecedor_item, nome_item, tipo, unidade,
                   valor_unitario, prazo_dias
            FROM fornecedor_item
            WHERE id_fornecedor_item = ?
            AND id_fornecedor = ?
            AND status = 'Ativo'
            LIMIT 1
        ");
        $stmt->execute([$id, $dados['id_fornecedor']]);
        $produto = $stmt->fetch();

        if ($produto && $quantidade > 0) {

            $valor = (float)$produto['valor_unitario'];

            $_SESSION['itens_encomenda'][] = [
                'id_fornecedor_item' => $produto['id_fornecedor_item'],
                'nome_item' => $produto['nome_item'],
                'tipo' => $produto['tipo'],
                'unidade' => $produto['unidade'],
                'quantidade' => $quantidade,
                'valor_unitario' => $valor,
                'prazo_dias' => $produto['prazo_dias'],
                'subtotal' => $quantidade * $valor
            ];

            /* Maior prazo dos produtos */
            $maiorPrazo = 0;

            foreach ($_SESSION['itens_encomenda'] as $item) {
                $maiorPrazo = max(
                    $maiorPrazo,
                    (int)$item['prazo_dias']
                );
            }

            $data = new DateTime(
                $_SESSION['dados_encomenda']['data_encomenda']
            );

            $data->modify("+{$maiorPrazo} days");

            $_SESSION['dados_encomenda']['data_prevista'] =
                $data->format('Y-m-d');
        }

        header("Location: nova_encomenda.php");
        exit;
    }

    /* EDITAR ITEM */
    if ($acao === 'editar_item') {

        $indice = (int)($_POST['indice'] ?? -1);
        $quantidade = (float)($_POST['quantidade'] ?? 0);

        if (
            isset($_SESSION['itens_encomenda'][$indice]) &&
            $quantidade > 0
        ) {
            $item = $_SESSION['itens_encomenda'][$indice];

            $item['quantidade'] = $quantidade;
            $item['subtotal'] =
                $quantidade * $item['valor_unitario'];

            $_SESSION['itens_encomenda'][$indice] = $item;
        }

        header("Location: nova_encomenda.php");
        exit;
    }

    /* EXCLUIR ITEM */
    if ($acao === 'excluir_item') {

        $indice = (int)($_POST['indice'] ?? -1);

        if (isset($_SESSION['itens_encomenda'][$indice])) {
            unset($_SESSION['itens_encomenda'][$indice]);
            $_SESSION['itens_encomenda'] =
                array_values($_SESSION['itens_encomenda']);
        }

        header("Location: nova_encomenda.php");
        exit;
    }

    /* CANCELAR */
    if ($acao === 'cancelar') {

        unset(
            $_SESSION['dados_encomenda'],
            $_SESSION['itens_encomenda'],
            $_SESSION['produto_selecionado']
        );

        header("Location: encomenda.php");
        exit;
    }

    /* SALVAR */
    if ($acao === 'salvar_encomenda') {

        $itens = $_SESSION['itens_encomenda'];

        if (
            empty($dados['id_fornecedor']) ||
            empty($dados['data_encomenda']) ||
            empty($itens)
        ) {
            $_SESSION['erro_encomenda'] =
                "Selecione um fornecedor e adicione pelo menos um item.";

            header("Location: nova_encomenda.php");
            exit;
        }

        try {

            $pdo->beginTransaction();

            /* DATA PREVISTA */
            $maiorPrazo = 0;

            foreach ($itens as $item) {
                $maiorPrazo = max(
                    $maiorPrazo,
                    (int)$item['prazo_dias']
                );
            }

            $data = new DateTime($dados['data_encomenda']);
            $data->modify("+{$maiorPrazo} days");
            $dataPrevista = $data->format('Y-m-d');

            /* TOTAL */
            $total = 0;

            foreach ($itens as $item) {
                $total += $item['subtotal'];
            }

            /* ENCOMENDA */
            $stmt = $pdo->prepare("
                INSERT INTO encomenda
                (
                    id_fornecedor,
                    data_encomenda,
                    data_prevista,
                    observacao,
                    status,
                    valor_total
                )
                VALUES (?, ?, ?, ?, 'Pendente', ?)
            ");

            $stmt->execute([
                $dados['id_fornecedor'],
                $dados['data_encomenda'],
                $dataPrevista,
                $dados['observacao'],
                $total
            ]);

            $idEncomenda = $pdo->lastInsertId();

            /* ITENS */
            $stmt = $pdo->prepare("
                INSERT INTO itens_encomenda
                (
                    id_encomenda,
                    nome_item,
                    tipo,
                    unidade,
                    quantidade,
                    valor_unitario,
                    subtotal
                )
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");

            foreach ($itens as $item) {
                $stmt->execute([
                    $idEncomenda,
                    $item['nome_item'],
                    $item['tipo'],
                    $item['unidade'],
                    $item['quantidade'],
                    $item['valor_unitario'],
                    $item['subtotal']
                ]);
            }

            $pdo->commit();

            unset(
                $_SESSION['dados_encomenda'],
                $_SESSION['itens_encomenda'],
                $_SESSION['produto_selecionado'],
                $_SESSION['erro_encomenda']
            );

            header("Location: encomenda.php?sucesso=1");
            exit;

        } catch (Exception $e) {

            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            $_SESSION['erro_encomenda'] =
                "Erro ao salvar: " . $e->getMessage();

            header("Location: nova_encomenda.php");
            exit;
        }
    }
}


/* DADOS PARA EXIBIÇÃO */
$dados = $_SESSION['dados_encomenda'];
$itens = $_SESSION['itens_encomenda'];

$produtoSelecionado =
    $_SESSION['produto_selecionado'] ?? null;

$erro = $_SESSION['erro_encomenda'] ?? '';
unset($_SESSION['erro_encomenda']);


/* PRODUTOS DO FORNECEDOR */
$produtos = [];

if (!empty($dados['id_fornecedor'])) {

    $stmt = $pdo->prepare("
        SELECT id_fornecedor_item, nome_item, tipo,
               unidade, valor_unitario, prazo_dias
        FROM fornecedor_item
        WHERE id_fornecedor = ?
        AND status = 'Ativo'
        ORDER BY nome_item
    ");

    $stmt->execute([$dados['id_fornecedor']]);
    $produtos = $stmt->fetchAll();
}


/* TOTAL */
$total = 0;

foreach ($itens as $item) {
    $total += $item['subtotal'];
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Nova encomenda - CerradoBurguer</title>

    <link rel="stylesheet"
          href="../Adm.css/nova_encomenda.css">

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

</head>

<body>

<aside class="sidebar">

    <div class="logo">

        <img src="../..//imagens/logo.png"
             alt="Logo CerradoBurguer">

        <div class="logo-text">
            <h2>CerradoBurguer</h2>
            <p>ADMINISTRAÇÃO</p>
        </div>

    </div>

    <nav class="menu">

        <a href="#"><i class="fa-solid fa-house"></i><span>Início</span></a>
        <a href="#"><i class="fa-solid fa-clipboard-list"></i><span>Pedidos</span></a>
        <a href="#"><i class="fa-solid fa-user-plus"></i><span>Cadastro</span></a>
        <a href="#"><i class="fa-solid fa-layer-group"></i><span>Categorias</span></a>
        <a href="#"><i class="fa-solid fa-chart-column"></i><span>Relatório</span></a>
        <a href="#"><i class="fa-solid fa-tag"></i><span>Promoção</span></a>
        <a href="#"><i class="fa-solid fa-users"></i><span>Clientes</span></a>
        <a href="#"><i class="fa-solid fa-truck"></i><span>Entregas</span></a>
        <a href="#"><i class="fa-solid fa-star"></i><span>Avaliação</span></a>
        <a href="#"><i class="fa-solid fa-gear"></i><span>Configurações</span></a>

        <a href="#" class="ativo">
            <i class="fa-solid fa-box"></i>
            <span>Encomenda</span>
        </a>

        <a href="#">
            <i class="fa-solid fa-right-from-bracket"></i>
            <span>Sair</span>
        </a>

    </nav>

</aside>


<main class="conteudo">

    <header class="topo">

        <div>

            <h1>Nova encomenda</h1>

            <div class="caminho">
                Encomenda &gt; Nova encomenda
            </div>

        </div>

        <a href="encomenda.php" class="voltar">
            <i class="fa-solid fa-arrow-left"></i>
            Voltar
        </a>

    </header>


    <?php if ($erro): ?>

        <section class="caixa">
            <strong>
                <?= htmlspecialchars($erro) ?>
            </strong>
        </section>

    <?php endif; ?>


    <!-- INFORMAÇÕES -->

    <section class="caixa informacoes-caixa">

        <h2>Informações da Categoria</h2>

        <div class="formulario">

            <!-- FORNECEDOR -->

            <div class="campo">

                <label>Fornecedor</label>

                <form method="POST">

                    <input type="hidden"
                           name="acao"
                           value="selecionar_fornecedor">

                    <select name="id_fornecedor"
                            onchange="this.form.submit()"
                            required>

                        <option value="">
                            Selecione um fornecedor
                        </option>

                        <?php foreach ($fornecedores as $fornecedor): ?>

                            <option
                                value="<?= $fornecedor['id_fornecedor'] ?>"
                                <?= $dados['id_fornecedor']
                                    == $fornecedor['id_fornecedor']
                                    ? 'selected'
                                    : '' ?>>

                                <?= htmlspecialchars(
                                    $fornecedor['nome']
                                ) ?>

                            </option>

                        <?php endforeach; ?>

                    </select>

                </form>

            </div>


            <!-- CATEGORIA -->

            <div class="campo">

                <label>Categoria do fornecedor</label>

                <input type="text"
                       value="<?= htmlspecialchars(
                           $dados['tipo_fornecedor']
                       ) ?>"
                       readonly>

            </div>


            <!-- DATA -->

            <div class="campo">

                <label>Data da encomenda</label>

                <form method="POST">

                    <input type="hidden"
                           name="acao"
                           value="atualizar_dados">

                    <input type="date"
                           name="data_encomenda"
                           value="<?= htmlspecialchars(
                               $dados['data_encomenda']
                           ) ?>"
                           onchange="this.form.submit()">

                    <input type="hidden"
                           name="observacao"
                           value="<?= htmlspecialchars(
                               $dados['observacao']
                           ) ?>">

                </form>

            </div>


            <!-- DATA PREVISTA -->

            <div class="campo">

                <label>Data prevista da entrega</label>

                <input type="date"
                       value="<?= htmlspecialchars(
                           $dados['data_prevista']
                       ) ?>"
                       readonly>

            </div>


            <!-- OBSERVAÇÃO -->

            <div class="campo observacao">

                <label>Observação</label>

                <form method="POST">

                    <input type="hidden"
                           name="acao"
                           value="atualizar_dados">

                    <input type="hidden"
                           name="data_encomenda"
                           value="<?= htmlspecialchars(
                               $dados['data_encomenda']
                           ) ?>">

                    <textarea name="observacao"><?= htmlspecialchars(
                        $dados['observacao']
                    ) ?></textarea>

                    <button type="submit"
                            style="display:none;">
                    </button>

                </form>

            </div>

        </div>

    </section>


    <!-- ADICIONAR ITEM -->

    <section class="caixa adicionar">

        <h2>Adicionar itens</h2>

        <?php if (empty($dados['id_fornecedor'])): ?>

            <p class="fornecedor-selecionado">
                Primeiro selecione um fornecedor.
            </p>

        <?php else: ?>

            <p class="fornecedor-selecionado">

                Fornecedor:

                <strong>
                    <?= htmlspecialchars(
                        $dados['nome_fornecedor']
                    ) ?>
                </strong>

                |

                Categoria:

                <strong>
                    <?= htmlspecialchars(
                        $dados['tipo_fornecedor']
                    ) ?>
                </strong>

            </p>


            <form method="POST">

                <input type="hidden"
                       name="acao"
                       value="adicionar_item">

                <div class="form-itens">

                    <!-- PRODUTO -->

                    <div class="campo">

                        <label>
                            <?= $dados['tipo_fornecedor'] === 'Bebidas'
                                ? 'Bebida'
                                : 'Ingrediente' ?>
                        </label>

                        <select name="id_fornecedor_item"
                                required
                                onchange="selecionarProduto(this)">

                            <option value="">
                                Selecione
                            </option>

                            <?php foreach ($produtos as $produto): ?>

                                <option
                                    value="<?= $produto['id_fornecedor_item'] ?>"
                                    <?= $produtoSelecionado &&
                                        $produtoSelecionado[
                                            'id_fornecedor_item'
                                        ] == $produto[
                                            'id_fornecedor_item'
                                        ]
                                        ? 'selected'
                                        : '' ?>>

                                    <?= htmlspecialchars(
                                        $produto['nome_item']
                                    ) ?>

                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>


                    <!-- UNIDADE -->

                    <div class="campo">

                        <label>Unidade</label>

                        <input type="text"
                               value="<?= $produtoSelecionado
                                   ? htmlspecialchars(
                                       $produtoSelecionado['unidade']
                                   )
                                   : '' ?>"
                               readonly>

                    </div>


                    <!-- QUANTIDADE -->

                    <div class="campo">

                        <label>Quantidade</label>

                        <input type="number"
                               name="quantidade"
                               step="0.01"
                               min="0.01"
                               required>

                    </div>


                    <!-- PREÇO -->

                    <div class="campo">

                        <label>Valor unitário</label>

                        <input type="text"
                               value="<?= $produtoSelecionado
                                   ? 'R$ ' .
                                     number_format(
                                         $produtoSelecionado[
                                             'valor_unitario'
                                         ],
                                         2,
                                         ',',
                                         '.'
                                     )
                                   : '' ?>"
                               readonly>

                    </div>


                    <!-- BOTÃO -->

                    <button type="submit"
                            class="adicionar-item"
                            <?= !$produtoSelecionado
                                ? 'disabled'
                                : '' ?>>

                        <i class="fa-solid fa-plus"></i>

                        Adicionar item

                    </button>

                </div>

            </form>

        <?php endif; ?>

    </section>


    <!-- TABELA -->

    <section class="tabela-caixa">

        <table>

            <thead>

                <tr>

                    <th>ITEM</th>
                    <th>TIPO</th>
                    <th>UNIDADE</th>
                    <th>QUANTIDADE</th>
                    <th>VALOR UNIT.</th>
                    <th>SUBTOTAL</th>
                    <th>AÇÕES</th>

                </tr>

            </thead>

            <tbody>

                <?php if (empty($itens)): ?>

                    <tr>

                        <td colspan="7">
                            Nenhum item adicionado.
                        </td>

                    </tr>

                <?php endif; ?>


                <?php foreach ($itens as $indice => $item): ?>

                    <tr>

                        <td>
                            <?= htmlspecialchars(
                                $item['nome_item']
                            ) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                $item['tipo']
                            ) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                $item['unidade']
                            ) ?>
                        </td>

                        <td>

                            <?= htmlspecialchars(
                                $item['quantidade']
                            ) ?>

                        </td>

                        <td>

                            R$

                            <?= number_format(
                                $item['valor_unitario'],
                                2,
                                ',',
                                '.'
                            ) ?>

                        </td>

                        <td class="verde">

                            R$

                            <?= number_format(
                                $item['subtotal'],
                                2,
                                ',',
                                '.'
                            ) ?>

                        </td>

                        <td>

                            <!-- EDITAR -->

                            <form method="POST"
                                  style="display:inline;">

                                <input type="hidden"
                                       name="acao"
                                       value="editar_item">

                                <input type="hidden"
                                       name="indice"
                                       value="<?= $indice ?>">

                                <input type="number"
                                       name="quantidade"
                                       value="<?= $item['quantidade'] ?>"
                                       step="0.01"
                                       min="0.01"
                                       style="width:55px;"
                                       required>

                                <button type="submit"
                                        class="editar"
                                        title="Salvar quantidade">

                                    <i class="fa-solid fa-check"></i>

                                </button>

                            </form>


                            <!-- EXCLUIR -->

                            <form method="POST"
                                  style="display:inline;">

                                <input type="hidden"
                                       name="acao"
                                       value="excluir_item">

                                <input type="hidden"
                                       name="indice"
                                       value="<?= $indice ?>">

                                <button type="submit"
                                        class="excluir"
                                        title="Excluir">

                                    <i class="fa-solid fa-trash"></i>

                                </button>

                            </form>

                        </td>

                    </tr>

                <?php endforeach; ?>

            </tbody>

        </table>

    </section>


    <!-- TOTAL -->

    <section class="total">

        <span>
            Total da encomenda
        </span>

        <strong>

            R$

            <?= number_format(
                $total,
                2,
                ',',
                '.'
            ) ?>

        </strong>

    </section>


    <!-- BOTÕES -->

    <div class="botoes-finais">

        <form method="POST">

            <input type="hidden"
                   name="acao"
                   value="cancelar">

            <button type="submit"
                    class="cancelar">

                Cancelar

            </button>

        </form>


        <form method="POST">

            <input type="hidden"
                   name="acao"
                   value="salvar_encomenda">

            <button type="submit"
                    class="salvar">

                Salvar Encomenda

            </button>

        </form>

    </div>

</main>


<script>

function selecionarProduto(select) {

    if (!select.value) return;

    const form = select.closest('form');

    const acao = document.createElement('input');

    acao.type = 'hidden';
    acao.name = 'acao';
    acao.value = 'selecionar_produto';

    form.appendChild(acao);

    form.submit();
}

</script>

</body>
</html>