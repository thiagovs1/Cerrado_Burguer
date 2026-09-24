<?php
require_once "../../crud/conexao.php";

$mensagem="";$modal=false;$editar=null;

if($_SERVER['REQUEST_METHOD']==='POST'){
    $acao=$_POST['acao']??'';
    $id=intval($_POST['id_categoria']??0);

    if($acao==='salvar'){
        $nome=trim($_POST['nome']??'');
        $descricao=trim($_POST['descricao']??'');
        $status=$_POST['status']??'Ativa';
        $cor=$_POST['cor']??'#8B0000';

        if(!preg_match('/^#[0-9A-Fa-f]{6}$/',$cor))$cor='#8B0000';

        if($nome===''){
            $mensagem="O nome da categoria é obrigatório.";
            $modal=true;
        }else{
            if(!in_array($status,['Ativa','Inativa'],true))$status='Ativa';

            $sql="SELECT id_categoria FROM categoria WHERE LOWER(nome)=LOWER(?)";
            $params=[$nome];

            if($id>0){
                $sql.=" AND id_categoria!=?";
                $params[]=$id;
            }

            $stmt=$pdo->prepare($sql);
            $stmt->execute($params);

            if($stmt->fetch()){
                $mensagem="Essa categoria já está cadastrada.";
                $modal=true;
            }else{
                $imagem=null;

                if($id>0){
                    $stmt=$pdo->prepare("SELECT imagem FROM categoria WHERE id_categoria=?");
                    $stmt->execute([$id]);
                    $atual=$stmt->fetch();
                    $imagem=$atual['imagem']??null;
                }

                if(!empty($_FILES['imagem']['name'])){
                    $ext=strtolower(pathinfo($_FILES['imagem']['name'],PATHINFO_EXTENSION));
                    $permitidas=['jpg','jpeg','png','webp','gif'];

                    if(in_array($ext,$permitidas,true)){
                        $nomeImagem=uniqid().".".$ext;
                        move_uploaded_file($_FILES['imagem']['tmp_name'],"../../imagens/".$nomeImagem);
                        $imagem=$nomeImagem;
                    }
                }

                if($id>0){
                    $stmt=$pdo->prepare("UPDATE categoria SET nome=?,descricao=?,status=?,imagem=?,cor=? WHERE id_categoria=?");
                    $stmt->execute([$nome,$descricao,$status,$imagem,$cor,$id]);
                    header("Location: categorias.php?mensagem=Categoria+atualizada+com+sucesso");
                }else{
                    $stmt=$pdo->prepare("INSERT INTO categoria(nome,descricao,status,imagem,cor) VALUES(?,?,?,?,?)");
                    $stmt->execute([$nome,$descricao,$status,$imagem,$cor]);
                    header("Location: categorias.php?mensagem=Categoria+cadastrada+com+sucesso");
                }
                exit;
            }
        }
    }

    if($acao==='excluir'&&$id>0){
        $stmt=$pdo->prepare("SELECT COUNT(*) FROM produto WHERE id_categoria=?");
        $stmt->execute([$id]);

        if($stmt->fetchColumn()>0){
            $mensagem="Não é possível excluir esta categoria porque existem produtos vinculados a ela.";
        }else{
            $stmt=$pdo->prepare("DELETE FROM categoria WHERE id_categoria=?");
            $stmt->execute([$id]);
            header("Location: categorias.php?mensagem=Categoria+excluída+com+sucesso");
            exit;
        }
    }
}

if(isset($_GET['mensagem']))$mensagem=$_GET['mensagem'];

if(isset($_GET['editar'])){
    $stmt=$pdo->prepare("SELECT * FROM categoria WHERE id_categoria=?");
    $stmt->execute([intval($_GET['editar'])]);
    $editar=$stmt->fetch();
    if($editar)$modal=true;
}

if(isset($_GET['nova']))$modal=true;

$busca=trim($_GET['busca']??'');
$statusFiltro=$_GET['status']??'';

$sql="SELECT c.id_categoria,c.nome,c.descricao,c.status,c.imagem,c.cor,
COUNT(p.id_produto) AS produtos
FROM categoria c
LEFT JOIN produto p ON c.id_categoria=p.id_categoria";

$condicoes=[];$params=[];

if($busca!==''){
    $condicoes[]="(c.nome LIKE ? OR c.descricao LIKE ?)";
    $params[]="%$busca%";
    $params[]="%$busca%";
}

if(in_array($statusFiltro,['Ativa','Inativa'],true)){
    $condicoes[]="c.status=?";
    $params[]=$statusFiltro;
}

if($condicoes)$sql.=" WHERE ".implode(" AND ",$condicoes);

$sql.=" GROUP BY c.id_categoria,c.nome,c.descricao,c.status,c.imagem,c.cor ORDER BY c.id_categoria ASC";

$stmt=$pdo->prepare($sql);
$stmt->execute($params);
$categorias=$stmt->fetchAll();

$total=count($categorias);$ativas=0;$inativas=0;$produtos=0;

foreach($categorias as $categoria){
    $categoria['status']==='Ativa'?$ativas++:$inativas++;
    $produtos+=$categoria['produtos'];
}

$porcentagemAtivas=$total?round(($ativas/$total)*100):0;
$porcentagemInativas=$total?round(($inativas/$total)*100):0;
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Categorias - Painel Administrativo</title>
<link rel="stylesheet" href="../Adm.css/categorias.css">
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
        <a href="pedidos.php"><i class="fa-solid fa-clipboard-list"></i><span>Pedidos</span></a>
        <a href="cadastro.php"><i class="fa-solid fa-user-plus"></i><span>Cadastro</span></a>
        <a href="categorias.php" class="ativo"><i class="fa-solid fa-layer-group"></i><span>Categorias</span></a>
        <a href="relatorio.html"><i class="fa-solid fa-chart-column"></i><span>Relatório</span></a>
        <a href="promocoes.php"><i class="fa-solid fa-tag"></i><span>Promoção</span></a>
        <a href="clientes.php"><i class="fa-solid fa-users"></i><span>Clientes</span></a>
        <a href="entregas.html"><i class="fa-solid fa-truck"></i><span>Entregas</span></a>
        <a href="avaliacoes.html"><i class="fa-solid fa-star"></i><span>Avaliação</span></a>
        <a href="configuracoes.html"><i class="fa-solid fa-gear"></i><span>Configurações</span></a>
        <a href="encomenda.php"><i class="fa-solid fa-box"></i><span>Encomenda</span></a>
        <a href="sair.html"><i class="fa-solid fa-right-from-bracket"></i><span>Sair</span></a>
    </nav>
</aside>

<main class="conteudo">

<div class="topo">
    <h1>Categorias</h1>
    <a href="?nova=1" class="btn-nova">+ Criar nova categoria</a>
</div>

<?php if($mensagem): ?>
<p class="mensagem"><?=htmlspecialchars($mensagem)?></p>
<?php endif; ?>

<section class="cards">

<div class="card">
<div class="icone"><img src="../../imagens/icon-categorias.png"></div>
<div class="info">
<span>Total de Categorias</span>
<h2><?=$total?></h2>
<p>Categorias Cadastradas</p>
</div>
</div>

<div class="card">
<div class="icone"><img src="../../imagens/icon-categorias.png"></div>
<div class="info">
<span>Categorias ativas</span>
<h2><?=$ativas?></h2>
<p><?=$porcentagemAtivas?>% do total</p>
</div>
</div>

<div class="card">
<div class="icone"><img src="../../imagens/icon-categorias.png"></div>
<div class="info">
<span>Categorias inativas</span>
<h2><?=$inativas?></h2>
<p><?=$porcentagemInativas?>% do total</p>
</div>
</div>

<div class="card">
<div class="icone"><img src="../../imagens/icon-categorias.png"></div>
<div class="info">
<span>Produtos vinculados</span>
<h2><?=$produtos?></h2>
<p>Em todas as categorias</p>
</div>
</div>

</section>

<section class="painel">

<form method="GET" class="barra-superior">

<div class="pesquisa">
<img src="../../imagens/icon-pesquisar.png">
<input type="text" name="busca" value="<?=htmlspecialchars($busca)?>" placeholder="Buscar categoria">
</div>

<select name="status" onchange="this.form.submit()">
<option value="">Todos os status</option>
<option value="Ativa" <?=$statusFiltro==='Ativa'?'selected':''?>>Ativa</option>
<option value="Inativa" <?=$statusFiltro==='Inativa'?'selected':''?>>Inativa</option>
</select>

<button type="submit">Pesquisar</button>

</form>

<table>
<thead>
<tr>
<th>CATEGORIA</th>
<th>DESCRIÇÃO</th>
<th>PRODUTOS</th>
<th>STATUS</th>
<th>AÇÕES</th>
</tr>
</thead>

<tbody>

<?php foreach($categorias as $categoria): ?>
<tr>

<td class="categoria">
<?php if(!empty($categoria['imagem'])): ?>
<img src="../../imagens/<?=htmlspecialchars($categoria['imagem'])?>" alt="">
<?php else: ?>
<div class="imagem-interrogacao">?</div>
<?php endif; ?>
<strong><?=htmlspecialchars($categoria['nome'])?></strong>
</td>

<td><?=htmlspecialchars($categoria['descricao'])?></td>

<td class="numero"><?=$categoria['produtos']?></td>

<td>
<span class="<?=$categoria['status']==='Ativa'?'ativo-status':'inativo-status'?>">
<?=htmlspecialchars($categoria['status'])?>
</span>
</td>

<td>
<div class="acoes">

<a href="?editar=<?=$categoria['id_categoria']?>" class="editar">
<i class="fa-solid fa-pen"></i> Editar
</a>

<form method="POST">
<input type="hidden" name="acao" value="excluir">
<input type="hidden" name="id_categoria" value="<?=$categoria['id_categoria']?>">
<button type="submit" class="lixeira" title="Excluir">
<i class="fa-solid fa-trash"></i>
</button>
</form>

</div>
</td>

</tr>
<?php endforeach; ?>

</tbody>
</table>

</section>
</main>

<?php if($modal): ?>

<div class="modal" style="display:flex">

<div class="modal-conteudo">

<div class="modal-topo">
<h2><?=$editar?'Editar categoria':'Nova categoria'?></h2>
<a href="categorias.php" class="fechar-modal">×</a>
</div>

<form method="POST" enctype="multipart/form-data">

<input type="hidden" name="acao" value="salvar">
<input type="hidden" name="id_categoria" value="<?=$editar['id_categoria']??''?>">

<div class="campo">
<label>Nome da categoria</label>
<input type="text" name="nome" id="nome" value="<?=htmlspecialchars($editar['nome']??'')?>" placeholder="Ex: Hambúrgueres" maxlength="100" required>
</div>

<div class="campo">
<label>Descrição</label>
<textarea name="descricao" placeholder="Digite a descrição da categoria" maxlength="500"><?=htmlspecialchars($editar['descricao']??'')?></textarea>
</div>

<div class="campo">
<label>Imagem da categoria</label>
<input type="file" name="imagem" id="imagem" accept="image/*">
</div>

<div class="campo">
<label>Cor da categoria</label>
<input type="color" name="cor" id="cor" value="<?=htmlspecialchars($editar['cor']??'#8B0000')?>">
</div>

<div class="campo">
<label>Status</label>
<select name="status">
<option value="Ativa" <?=($editar['status']??'Ativa')==='Ativa'?'selected':''?>>Ativa</option>
<option value="Inativa" <?=($editar['status']??'')==='Inativa'?'selected':''?>>Inativa</option>
</select>
</div>

<div class="preview-area">
<label>Pré-visualização</label>

<div class="preview-categoria" id="previewCategoria" style="background:<?=htmlspecialchars($editar['cor']??'#8B0000')?>">

<div class="preview-imagem">
<?php if(!empty($editar['imagem'])): ?>
<img id="previewImagem" src="../../imagens/<?=htmlspecialchars($editar['imagem'])?>" alt="Preview">
<?php else: ?>
<img id="previewImagem" src="" style="display:none" alt="Preview">
<?php endif; ?>
</div>

<h3 id="previewNome"><?=htmlspecialchars($editar['nome']??'Nome da categoria')?></h3>

</div>
</div>

<div class="botoes-modal">
<a href="categorias.php" class="btn-cancelar">Cancelar</a>
<button type="submit" class="btn-salvar">Salvar categoria</button>
</div>

</form>
</div>
</div>

<?php endif; ?>

<script>
const nome=document.getElementById('nome');
const cor=document.getElementById('cor');
const imagem=document.getElementById('imagem');
const preview=document.getElementById('previewCategoria');
const previewNome=document.getElementById('previewNome');
const previewImagem=document.getElementById('previewImagem');

if(nome){
nome.addEventListener('input',()=>previewNome.textContent=nome.value||'Nome da categoria');
cor.addEventListener('input',()=>preview.style.background=cor.value);

imagem.addEventListener('change',()=>{
const arquivo=imagem.files[0];
if(!arquivo)return;
const leitor=new FileReader();
leitor.onload=e=>{
previewImagem.src=e.target.result;
previewImagem.style.display='block';
};
leitor.readAsDataURL(arquivo);
});
}
</script>

</body>
</html>