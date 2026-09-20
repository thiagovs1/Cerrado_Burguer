
<?php
session_start();
require_once "../Crud/conexao.php";

if (!isset($pdo)) die("Erro na conexão com o banco.");

$_SESSION['dados_encomenda'] ??= [
    'nome_fornecedor'=>'',
    'tipo_fornecedor'=>'',
    'data_encomenda'=>date('Y-m-d'),
    'data_prevista'=>'',
    'observacao'=>''
];

$_SESSION['itens_encomenda'] ??= [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';

    if ($acao === 'atualizar_dados') {
        $_SESSION['dados_encomenda'] = [
            'nome_fornecedor'=>trim($_POST['nome_fornecedor'] ?? ''),
            'tipo_fornecedor'=>trim($_POST['tipo_fornecedor'] ?? ''),
            'data_encomenda'=>$_POST['data_encomenda'] ?? date('Y-m-d'),
            'data_prevista'=>$_POST['data_prevista'] ?? '',
            'observacao'=>trim($_POST['observacao'] ?? '')
        ];
        header("Location: nova_encomenda.php"); exit;
    }

    if ($acao === 'adicionar_item') {
        $nome=trim($_POST['nome_item']??'');
        $tipo=trim($_POST['tipo']??'');
        $unidade=trim($_POST['unidade']??'');
        $qtd=(float)($_POST['quantidade']??0);
        $preco=(float)($_POST['valor_unitario']??0);

        $unidades=['Unidade','Kg','Grama','Litro','Mililitro','Caixa','Pacote','Fardo','Dúzia'];

        if (!$nome) $erro="Informe o nome do item.";
        elseif (!in_array($tipo,['Bebida','Ingrediente'],true)) $erro="Selecione o tipo do item.";
        elseif (!in_array($unidade,$unidades,true)) $erro="Selecione uma unidade válida.";
        elseif ($qtd<=0) $erro="A quantidade deve ser maior que zero.";
        elseif ($preco<=0) $erro="O preço unitário deve ser maior que zero.";

        if (isset($erro)) {
            $_SESSION['erro_encomenda']=$erro;
            header("Location: nova_encomenda.php"); exit;
        }

        $_SESSION['itens_encomenda'][]=[
            'nome_item'=>$nome,
            'tipo'=>$tipo,
            'unidade'=>$unidade,
            'quantidade'=>$qtd,
            'valor_unitario'=>$preco,
            'subtotal'=>$qtd*$preco
        ];

        header("Location: nova_encomenda.php"); exit;
    }

    if ($acao === 'editar_item') {
        $i=(int)($_POST['indice']??-1);
        $qtd=(float)($_POST['quantidade']??0);
        $preco=(float)($_POST['valor_unitario']??0);

        if (isset($_SESSION['itens_encomenda'][$i]) && $qtd>0 && $preco>0) {
            $_SESSION['itens_encomenda'][$i]['quantidade']=$qtd;
            $_SESSION['itens_encomenda'][$i]['valor_unitario']=$preco;
            $_SESSION['itens_encomenda'][$i]['subtotal']=$qtd*$preco;
        }

        header("Location: nova_encomenda.php"); exit;
    }

    if ($acao === 'excluir_item') {
        $i=(int)($_POST['indice']??-1);

        if (isset($_SESSION['itens_encomenda'][$i])) {
            unset($_SESSION['itens_encomenda'][$i]);
            $_SESSION['itens_encomenda']=array_values($_SESSION['itens_encomenda']);
        }

        header("Location: nova_encomenda.php"); exit;
    }

    if ($acao === 'cancelar') {
        unset($_SESSION['dados_encomenda'],$_SESSION['itens_encomenda'],$_SESSION['erro_encomenda']);
        header("Location: encomenda.php"); exit;
    }

    if ($acao === 'salvar_encomenda') {
        $d=$_SESSION['dados_encomenda'];
        $itens=$_SESSION['itens_encomenda'];

        if (!$d['nome_fornecedor'] || !$d['tipo_fornecedor'])
            $erro="Informe o nome e o tipo do fornecedor.";
        elseif (!$d['data_encomenda'] || !$d['data_prevista'])
            $erro="Informe a data da encomenda e a data de entrega.";
        elseif (!$itens)
            $erro="Adicione pelo menos um item.";
        elseif (!in_array($d['tipo_fornecedor'],['Bebidas','Ingredientes'],true))
            $erro="Selecione o tipo do fornecedor.";

        if (isset($erro)) {
            $_SESSION['erro_encomenda']=$erro;
            header("Location: nova_encomenda.php"); exit;
        }

        try {
            $pdo->beginTransaction();

            $total=array_sum(array_column($itens,'subtotal'));

            $stmt=$pdo->prepare("
                INSERT INTO encomenda
                (nome_fornecedor,tipo_fornecedor,data_encomenda,data_prevista,observacao,status,valor_total)
                VALUES (?,?,?,?,?,'Pendente',?)
            ");

            $stmt->execute([
                $d['nome_fornecedor'],
                $d['tipo_fornecedor'],
                $d['data_encomenda'],
                $d['data_prevista'],
                $d['observacao'],
                $total
            ]);

            $id=$pdo->lastInsertId();

            $stmt=$pdo->prepare("
                INSERT INTO itens_encomenda
                (id_encomenda,nome_item,tipo,unidade,quantidade,valor_unitario,subtotal)
                VALUES (?,?,?,?,?,?,?)
            ");

            foreach ($itens as $item) {
                $stmt->execute([
                    $id,
                    $item['nome_item'],
                    $item['tipo'],
                    $item['unidade'],
                    $item['quantidade'],
                    $item['valor_unitario'],
                    $item['subtotal']
                ]);
            }

            $pdo->commit();

            unset($_SESSION['dados_encomenda'],$_SESSION['itens_encomenda'],$_SESSION['erro_encomenda']);
            header("Location: encomenda.php?sucesso=1"); exit;

        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $_SESSION['erro_encomenda']="Erro ao salvar: ".$e->getMessage();
            header("Location: nova_encomenda.php"); exit;
        }
    }
}

$dados=$_SESSION['dados_encomenda'];
$itens=$_SESSION['itens_encomenda'];
$erro=$_SESSION['erro_encomenda']??'';
unset($_SESSION['erro_encomenda']);
$total=array_sum(array_column($itens,'subtotal'));
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Nova encomenda - CerradoBurguer</title>
<link rel="stylesheet" href="../Adm.css/nova_encomenda.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>

<body>

<aside class="sidebar">
    <div class="logo">
        <img src="../..//imagens/logo.png" alt="Logo CerradoBurguer">
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
        <a href="encomenda.php" class="ativo"><i class="fa-solid fa-box"></i><span>Encomenda</span></a>
        <a href="#"><i class="fa-solid fa-right-from-bracket"></i><span>Sair</span></a>
    </nav>
</aside>

<main class="conteudo">

<header class="topo">
    <div>
        <h1>Nova encomenda</h1>
        <div class="caminho">Encomenda &gt; Nova encomenda</div>
    </div>
    <a href="encomenda.php" class="voltar"><i class="fa-solid fa-arrow-left"></i> Voltar</a>
</header>

<?php if ($erro): ?>
<section class="caixa"><strong><?= htmlspecialchars($erro) ?></strong></section>
<?php endif; ?>

<section class="caixa informacoes-caixa">
<h2>Informações da encomenda</h2>

<form method="POST">
<input type="hidden" name="acao" value="atualizar_dados">

<div class="formulario">

<div class="campo">
<label>Nome do fornecedor</label>
<input type="text" name="nome_fornecedor" value="<?= htmlspecialchars($dados['nome_fornecedor']) ?>" placeholder="Ex.: Distribuidora Silva" required>
</div>

<div class="campo">
<label>Tipo do fornecedor</label>
<select name="tipo_fornecedor" required>
<option value="">Selecione</option>
<option value="Bebidas" <?= $dados['tipo_fornecedor']==='Bebidas'?'selected':'' ?>>Bebidas</option>
<option value="Ingredientes" <?= $dados['tipo_fornecedor']==='Ingredientes'?'selected':'' ?>>Ingredientes</option>
</select>
</div>

<div class="campo">
<label>Data da encomenda</label>
<input type="date" name="data_encomenda" value="<?= htmlspecialchars($dados['data_encomenda']) ?>" required>
</div>

<div class="campo">
<label>Data da entrega</label>
<input type="date" name="data_prevista" value="<?= htmlspecialchars($dados['data_prevista']) ?>" required>
</div>

<div class="campo observacao">
<label>Observação</label>
<textarea name="observacao" placeholder="Observações sobre a encomenda..."><?= htmlspecialchars($dados['observacao']) ?></textarea>
</div>

<div class="campo">
<button type="submit" class="adicionar-item">
<i class="fa-solid fa-floppy-disk"></i> Atualizar dados
</button>
</div>

</div>
</form>
</section>

<section class="caixa adicionar">
<h2>Adicionar item</h2>

<?php if (!$dados['nome_fornecedor'] || !$dados['tipo_fornecedor']): ?>

<p class="fornecedor-selecionado">Primeiro preencha o nome e o tipo do fornecedor.</p>

<?php else: ?>

<p class="fornecedor-selecionado">
Fornecedor: <strong><?= htmlspecialchars($dados['nome_fornecedor']) ?></strong> |
Tipo: <strong><?= htmlspecialchars($dados['tipo_fornecedor']) ?></strong>
</p>

<form method="POST">
<input type="hidden" name="acao" value="adicionar_item">

<div class="form-itens">

<div class="campo">
<label>Nome do item</label>
<input type="text" name="nome_item" placeholder="Ex.: Carne bovina" required>
</div>

<div class="campo">
<label>Tipo</label>
<select name="tipo" required>
<option value="">Selecione</option>
<option value="Ingrediente">Ingrediente</option>
<option value="Bebida">Bebida</option>
</select>
</div>

<div class="campo">
<label>Unidade</label>
<select name="unidade" required>
<option value="">Selecione</option>
<option>Unidade</option>
<option>Kg</option>
<option>Grama</option>
<option>Litro</option>
<option>Mililitro</option>
<option>Caixa</option>
<option>Pacote</option>
<option>Fardo</option>
<option>Dúzia</option>
</select>
</div>

<div class="campo">
<label>Quantidade</label>
<input type="number" name="quantidade" step="0.01" min="0.01" placeholder="0" required>
</div>

<div class="campo">
<label>Preço por unidade</label>
<input type="number" name="valor_unitario" step="0.01" min="0.01" placeholder="0,00" required>
</div>

<button type="submit" class="adicionar-item">
<i class="fa-solid fa-plus"></i> Adicionar item
</button>

</div>
</form>
<?php endif; ?>
</section>

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

<?php if (!$itens): ?>
<tr><td colspan="7">Nenhum item adicionado.</td></tr>
<?php endif; ?>

<?php foreach ($itens as $i=>$item): ?>
<tr>

<td><?= htmlspecialchars($item['nome_item']) ?></td>
<td><?= htmlspecialchars($item['tipo']) ?></td>
<td><?= htmlspecialchars($item['unidade']) ?></td>
<td><?= number_format($item['quantidade'],2,',','.') ?></td>
<td>R$ <?= number_format($item['valor_unitario'],2,',','.') ?></td>
<td class="verde">R$ <?= number_format($item['subtotal'],2,',','.') ?></td>

<td class="acoes">

<button type="button" class="editar" title="Editar item"
onclick='abrirEdicao(
<?= $i ?>,
<?= json_encode($item["nome_item"]) ?>,
<?= json_encode($item["quantidade"]) ?>,
<?= json_encode($item["valor_unitario"]) ?>
)'>
<i class="fa-solid fa-pen"></i>
</button>

<form method="POST" style="display:inline">
<input type="hidden" name="acao" value="excluir_item">
<input type="hidden" name="indice" value="<?= $i ?>">
<button type="submit" class="excluir" title="Excluir item" onclick="return confirm('Deseja realmente excluir este item?')">
<i class="fa-solid fa-trash"></i>
</button>
</form>

</td>
</tr>
<?php endforeach; ?>

</tbody>
</table>
</section>

<section class="total">
<span>Total da encomenda</span>
<strong>R$ <?= number_format($total,2,',','.') ?></strong>
</section>

<div class="botoes-finais">

<form method="POST">
<input type="hidden" name="acao" value="cancelar">
<button type="submit" class="cancelar">Cancelar</button>
</form>

<form method="POST">
<input type="hidden" name="acao" value="salvar_encomenda">
<button type="submit" class="salvar">Salvar Encomenda</button>
</form>

</div>

</main>

<div id="modalEdicao" class="modal">
<div class="modal-conteudo">

<div class="modal-cabecalho">
<h2>Editar item</h2>
<button type="button" class="fechar-modal" onclick="fecharEdicao()">&times;</button>
</div>

<form method="POST">

<input type="hidden" name="acao" value="editar_item">
<input type="hidden" name="indice" id="editarIndice">

<div class="campo">
<label>Item</label>
<input type="text" id="editarNome" readonly>
</div>

<div class="campo">
<label>Quantidade</label>
<input type="number" name="quantidade" id="editarQuantidade" step="0.01" min="0.01" required>
</div>

<div class="campo">
<label>Preço por unidade</label>
<input type="number" name="valor_unitario" id="editarPreco" step="0.01" min="0.01" required>
</div>

<div class="modal-botoes">
<button type="button" class="cancelar" onclick="fecharEdicao()">Cancelar</button>
<button type="submit" class="salvar">Salvar alterações</button>
</div>

</form>
</div>
</div>

<script>
function abrirEdicao(i,n,q,p){
    document.getElementById('editarIndice').value=i;
    document.getElementById('editarNome').value=n;
    document.getElementById('editarQuantidade').value=q;
    document.getElementById('editarPreco').value=p;
    document.getElementById('modalEdicao').style.display='flex';
}

function fecharEdicao(){
    document.getElementById('modalEdicao').style.display='none';
}

window.onclick=function(e){
    const m=document.getElementById('modalEdicao');
    if(e.target===m) fecharEdicao();
};
</script>

</body>
</html>

