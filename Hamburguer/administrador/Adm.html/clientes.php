<?php
require_once __DIR__.'/../Crud/conexao.php';

if($_SERVER['REQUEST_METHOD']==='POST'){
    $acao=$_POST['acao']??'';
    $id=(int)($_POST['id']??0);

    if($acao==='editar'){
        $nome=trim($_POST['nome']??'');
        $email=trim($_POST['email']??'');
        $telefone=trim($_POST['telefone']??'')?:null;
        $cpf=trim($_POST['cpf']??'')?:null;
        $endereco=trim($_POST['endereco']??'')?:null;
        $status=($_POST['status']??'Ativo')==='Inativo'?'Inativo':'Ativo';

        $stmt=$pdo->prepare("
            UPDATE usuario
            SET nome=?,email=?,telefone=?,cpf=?,endereco=?,status=?
            WHERE id=?
        ");

        $stmt->execute([
            $nome,$email,$telefone,$cpf,$endereco,$status,$id
        ]);

        header('Location: clientes.php');
        exit;
    }

    if($acao==='excluir'){
        $stmt=$pdo->prepare("
            SELECT COUNT(*)
            FROM pedido
            WHERE id_usuario=?
            AND status<>'Carrinho'
        ");

        $stmt->execute([$id]);

        if($stmt->fetchColumn()>0){
            die('Este cliente possui pedidos e não pode ser excluído. Altere o status para Inativo.');
        }

        $stmt=$pdo->prepare("DELETE FROM usuario WHERE id=?");
        $stmt->execute([$id]);

        header('Location: clientes.php');
        exit;
    }
}

$stmt=$pdo->query("
    SELECT
        u.*,
        COUNT(p.id_pedido) AS total_pedidos,
        COALESCE(SUM(p.total),0) AS total_gasto,
        MAX(p.data_pedido) AS ultimo_pedido
    FROM usuario u
    LEFT JOIN pedido p
        ON p.id_usuario=u.id
        AND p.status<>'Carrinho'
    GROUP BY u.id
    ORDER BY u.data_cadastro DESC
");

$clientes=$stmt->fetchAll(PDO::FETCH_ASSOC);


$totalClientes=$pdo->query("
    SELECT COUNT(*) FROM usuario
")->fetchColumn();

$clientesAtivos=$pdo->query("
    SELECT COUNT(*) FROM usuario WHERE status='Ativo'
")->fetchColumn();

$novosClientes=$pdo->query("
    SELECT COUNT(*)
    FROM usuario
    WHERE MONTH(data_cadastro)=MONTH(CURRENT_DATE())
    AND YEAR(data_cadastro)=YEAR(CURRENT_DATE())
")->fetchColumn();

$totalPedidos=$pdo->query("
    SELECT COUNT(*)
    FROM pedido
    WHERE status<>'Carrinho'
")->fetchColumn();

$clienteEditar=null;

if(isset($_GET['editar'])){
    $stmt=$pdo->prepare("
        SELECT * FROM usuario WHERE id=?
    ");

    $stmt->execute([(int)$_GET['editar']]);
    $clienteEditar=$stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">

<title>Clientes - Painel Administrativo</title>

<link rel="stylesheet" href="../Adm.css/cliente.css">

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

        <a href="pedidos.html">
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

        <a href="promocoes.php">
            <i class="fa-solid fa-tag"></i>
            <span>Promoção</span>
        </a>

        <a href="clientes.php" class="ativo">
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

<h1>Clientes</h1>

<section class="cards-resumo">

    <div class="card-resumo">

        <div class="card-icone">
            <img src="../../imagens/icon-clientes.png" alt="">
        </div>

        <div class="card-info">
            <span>Total de Clientes</span>
            <strong><?=$totalClientes?></strong>
            <small>Clientes cadastrados</small>
        </div>

    </div>

    <div class="card-resumo">

        <div class="card-icone">
            <img src="../../imagens/icon-clientes.png" alt="">
        </div>

        <div class="card-info">
            <span>Clientes Ativos</span>
            <strong><?=$clientesAtivos?></strong>
        </div>

    </div>

    <div class="card-resumo">

        <div class="card-icone">
            <img src="../../imagens/icon-clientes.png" alt="">
        </div>

        <div class="card-info">
            <span>Novos Clientes este Mês</span>
            <strong><?=$novosClientes?></strong>
        </div>

    </div>

    <div class="card-resumo">

        <div class="card-icone">
            <img src="../../imagens/icon-pedidos.png" alt="">
        </div>

        <div class="card-info">
            <span>Pedidos Realizados</span>
            <strong><?=$totalPedidos?></strong>
            <small>Total de pedidos</small>
        </div>

    </div>

</section>

<section class="area-clientes">

<div class="tabela-container">

<table>

<thead>

<tr>
    <th>PERFIL/CLIENTE</th>
    <th>CONTATO</th>
    <th>PEDIDOS</th>
    <th>TOTAL GASTO</th>
    <th>ÚLTIMO PEDIDO</th>
    <th>STATUS</th>
    <th>AÇÕES</th>
</tr>

</thead>

<tbody>

<?php if(empty($clientes)): ?>

<tr>
    <td colspan="7" class="vazio">
        Nenhum cliente cadastrado.
    </td>
</tr>

<?php endif; ?>

<?php foreach($clientes as $cliente): ?>

<tr>

<td>

    <div class="cliente">

        <div class="avatar">
            <?=strtoupper(substr($cliente['nome']?:'C',0,1))?>
        </div>

        <div>

            <strong>
                <?=htmlspecialchars($cliente['nome']?:'Sem nome')?>
            </strong>

            <small>
                Cadastro:
                <?=date('d/m/Y',strtotime($cliente['data_cadastro']))?>
            </small>

        </div>

    </div>

</td>

<td>

    <div class="contato">

        <span>
            <?=htmlspecialchars($cliente['email'])?>
        </span>

        <small>
            <?=htmlspecialchars($cliente['telefone']??'')?>
        </small>

    </div>

</td>

<td style="text-align:center">
    <?=$cliente['total_pedidos']?>
</td>

<td>
    R$ <?=number_format($cliente['total_gasto'],2,',','.')?>
</td>

<td>

<?php if($cliente['ultimo_pedido']): ?>

    <?=date('d/m/Y',strtotime($cliente['ultimo_pedido']))?>

<?php else: ?>

    Nenhum

<?php endif; ?>

</td>

<td>

    <span class="status <?=strtolower($cliente['status'])?>">
        <?=htmlspecialchars($cliente['status'])?>
    </span>

</td>

<td>

    <div class="acoes">

        <a
            href="clientes.php?editar=<?=$cliente['id']?>"
            class="btn-editar"
        >
            Editar
        </a>

        <form method="POST" style="margin:0">

            <input
                type="hidden"
                name="acao"
                value="excluir"
            >

            <input
                type="hidden"
                name="id"
                value="<?=$cliente['id']?>"
            >

            <button
                type="submit"
                class="btn-excluir"
            >
                <i class="fa-solid fa-trash"></i>
            </button>

        </form>

    </div>

</td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

</div>

<div class="ver-todos">
    <span>Ver todos</span>
    <span>▼</span>
</div>

</section>

</main>

<?php if($clienteEditar): ?>

<div class="modal" style="display:flex">

<div class="modal-conteudo">

<a href="clientes.php" class="fechar-modal">×</a>

<h2>Editar Cliente</h2>

<form method="POST">

<input type="hidden" name="acao" value="editar">

<input
    type="hidden"
    name="id"
    value="<?=$clienteEditar['id']?>"
>

<label>Nome</label>

<input
    type="text"
    name="nome"
    value="<?=htmlspecialchars($clienteEditar['nome']??'')?>"
    required
>

<label>E-mail</label>

<input
    type="email"
    name="email"
    value="<?=htmlspecialchars($clienteEditar['email'])?>"
    required
>

<label>Telefone</label>

<input
    type="text"
    name="telefone"
    value="<?=htmlspecialchars($clienteEditar['telefone']??'')?>"
>

<label>CPF</label>

<input
    type="text"
    name="cpf"
    value="<?=htmlspecialchars($clienteEditar['cpf']??'')?>"
>

<label>Endereço</label>

<input
    type="text"
    name="endereco"
    value="<?=htmlspecialchars($clienteEditar['endereco']??'')?>"
>

<label>Status</label>

<select name="status">

<option
    value="Ativo"
    <?=$clienteEditar['status']==='Ativo'?'selected':''?>
>
    Ativo
</option>

<option
    value="Inativo"
    <?=$clienteEditar['status']==='Inativo'?'selected':''?>
>
    Inativo
</option>

</select>

<div class="modal-botoes">

<a href="clientes.php" class="btn-cancelar">
    Cancelar
</a>

<button type="submit" class="btn-salvar">
    Salvar alterações
</button>

</div>

</form>

</div>

</div>

<?php endif; ?>

</body>
</html>