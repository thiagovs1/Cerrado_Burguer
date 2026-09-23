<?php

header('Content-Type: text/html; charset=UTF-8');

require_once "../Crud/conexao.php";

$mensagem = "";
$editar = null;
$modalExcluir = null;
$visualizar = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';
    $id = intval($_POST['id_produto'] ?? 0);
    $nome = trim($_POST['nome'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');
    $preco = str_replace(',', '.', $_POST['preco'] ?? '');
    $id_categoria = intval($_POST['id_categoria'] ?? 0);
    $status = $_POST['status'] ?? 'Ativo';
    $tempo = intval($_POST['tempo_preparo'] ?? 0);
    $promocao = isset($_POST['promocao']) ? 'Sim' : 'Não';
    $entrega = isset($_POST['disponivel_entrega']) ? 'Sim' : 'Não';

    if ($acao === 'visualizar') {
        if (!$nome || !$descricao || !$id_categoria) $mensagem = "Preencha nome, descrição e categoria.";
        elseif (!is_numeric($preco) || $preco < 0) $mensagem = "Informe um preço válido.";
        else {
            if ($id) {
                $stmt = $pdo->prepare("SELECT * FROM produto WHERE id_produto=?");
                $stmt->execute([$id]);
                $editar = $stmt->fetch(PDO::FETCH_ASSOC);
            }
            $editar = $editar ?: [];
            $editar = array_merge($editar, [
                'id_produto' => $id,
                'nome' => $nome,
                'descricao' => $descricao,
                'preco' => $preco,
                'id_categoria' => $id_categoria,
                'status' => $status,
                'tempo_preparo' => $tempo,
                'promocao' => $promocao,
                'disponivel_entrega' => $entrega
            ]);
            $visualizar = true;
        }
    }

    if ($acao === 'salvar') {
        if (!$nome || !$descricao || !$id_categoria) $mensagem = "Preencha todos os campos obrigatórios.";
        elseif (!is_numeric($preco) || $preco < 0) $mensagem = "Informe um preço válido.";
        else {
            $imagem = null;

            if ($id) {
                $stmt = $pdo->prepare("SELECT imagem FROM produto WHERE id_produto=?");
                $stmt->execute([$id]);
                $imagem = $stmt->fetchColumn();
            }

            if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] !== UPLOAD_ERR_NO_FILE) {
                if ($_FILES['imagem']['error'] !== UPLOAD_ERR_OK) {
                    $mensagem = "Erro ao enviar a imagem.";
                } elseif ($_FILES['imagem']['size'] > 5 * 1024 * 1024) {
                    $mensagem = "A imagem deve ter no máximo 5MB.";
                } else {
                    $ext = strtolower(pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION));

                    if (!in_array($ext, ['jpg','jpeg','png','webp'])) {
                        $mensagem = "Formato de imagem inválido.";
                    } else {
                        $novaImagem = uniqid('produto_', true) . '.' . $ext;
                        $pasta = __DIR__ . '/../../../imagens/';

                        if (!is_dir($pasta)) mkdir($pasta, 0777, true);

                        if (move_uploaded_file($_FILES['imagem']['tmp_name'], $pasta . $novaImagem)) {
                            if ($imagem && file_exists($pasta . $imagem)) unlink($pasta . $imagem);
                            $imagem = $novaImagem;
                        } else {
                            $mensagem = "Não foi possível salvar a imagem.";
                        }
                    }
                }
            }

            if (!$mensagem) {
                if ($id) {
                    $sql = "UPDATE produto SET id_categoria=?,nome=?,descricao=?,preco=?,status=?,imagem=?,tempo_preparo=?,promocao=?,disponivel_entrega=? WHERE id_produto=?";
                    $pdo->prepare($sql)->execute([$id_categoria,$nome,$descricao,$preco,$status,$imagem,$tempo,$promocao,$entrega,$id]);
                    header("Location: cadastro.php?msg=Produto+atualizado+com+sucesso");
                } else {
                    $sql = "INSERT INTO produto (id_categoria,nome,descricao,preco,status,imagem,tempo_preparo,promocao,disponivel_entrega) VALUES (?,?,?,?,?,?,?,?,?)";
                    $pdo->prepare($sql)->execute([$id_categoria,$nome,$descricao,$preco,$status,$imagem,$tempo,$promocao,$entrega]);
                    header("Location: cadastro.php?msg=Produto+cadastrado+com+sucesso");
                }
                exit;
            }
        }
    }

    if ($acao === 'excluir' && $id) {
        try {
            $stmt = $pdo->prepare("SELECT imagem FROM produto WHERE id_produto=?");
            $stmt->execute([$id]);
            $produto = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$produto) {
                $mensagem = "Produto não encontrado.";
            } else {
                $pdo->prepare("DELETE FROM produto WHERE id_produto=?")->execute([$id]);

                if ($produto['imagem']) {
                    $arquivo = __DIR__ . '/../../../imagens/' . $produto['imagem'];
                    if (file_exists($arquivo)) unlink($arquivo);
                }

                header("Location: cadastro.php?msg=Produto+excluído+com+sucesso");
                exit;
            }
        } catch (PDOException $e) {
            $mensagem = $e->getCode() === '23000'
                ? "Este produto não pode ser excluído porque está sendo utilizado em pedidos."
                : "Erro ao excluir produto.";
        }
    }
}

if (isset($_GET['msg'])) $mensagem = $_GET['msg'];

$categorias = $pdo->query("SELECT id_categoria,nome,cor FROM categoria ORDER BY nome ASC")->fetchAll(PDO::FETCH_ASSOC);

if (isset($_GET['editar'])) {
    $stmt = $pdo->prepare("SELECT * FROM produto WHERE id_produto=?");
    $stmt->execute([intval($_GET['editar'])]);
    $editar = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$editar) $mensagem = "Produto não encontrado.";
}

if (isset($_GET['excluir'])) {
    $stmt = $pdo->prepare("SELECT id_produto,nome FROM produto WHERE id_produto=?");
    $stmt->execute([intval($_GET['excluir'])]);
    $modalExcluir = $stmt->fetch(PDO::FETCH_ASSOC);
}

$produtos = $pdo->query("
    SELECT p.*,c.nome AS categoria
    FROM produto p
    INNER JOIN categoria c ON c.id_categoria=p.id_categoria
    ORDER BY p.id_produto DESC
")->fetchAll(PDO::FETCH_ASSOC);

$categoriaPreview = null;

if ($visualizar && !empty($editar['id_categoria'])) {
    $stmt = $pdo->prepare("SELECT id_categoria,nome,cor FROM categoria WHERE id_categoria=?");
    $stmt->execute([$editar['id_categoria']]);
    $categoriaPreview = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Cadastro de Produtos</title>
<link rel="stylesheet" href="../Adm.css/cadastro.css">
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
        <a href="cadastro.php" class="ativo"><i class="fa-solid fa-user-plus"></i><span>Cadastro</span></a>
        <a href="categorias.php"><i class="fa-solid fa-layer-group"></i><span>Categorias</span></a>
        <a href="relatorio.html"><i class="fa-solid fa-chart-column"></i><span>Relatório</span></a>
        <a href="promocoes.html"><i class="fa-solid fa-tag"></i><span>Promoção</span></a>
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
    <h1><?= $editar ? 'Editar Produto' : 'Cadastrar Produto' ?></h1>
    <a href="cadastro.php" class="voltar">← Voltar</a>
</div>

<?php if ($mensagem): ?>
<div class="mensagem"><?= htmlspecialchars($mensagem) ?></div>
<?php endif; ?>

<div class="area-cadastro">

<section class="formulario">
<h2>Informações Básicas</h2>

<form method="POST" enctype="multipart/form-data">

<input type="hidden" name="id_produto" value="<?= $editar['id_produto'] ?? '' ?>">

<div class="campo">
<label>Nome do Produto <b>*</b></label>
<input type="text" name="nome" value="<?= htmlspecialchars($editar['nome'] ?? '') ?>" placeholder="Ex: Burguer Clássico" maxlength="100" required>
</div>

<div class="campo">
<label>Descrição <b>*</b></label>
<textarea name="descricao" maxlength="500" required><?= htmlspecialchars($editar['descricao'] ?? '') ?></textarea>
</div>

<div class="linha">

<div class="campo">
<label>Preço <b>*</b></label>
<input type="text" name="preco" value="<?= isset($editar['preco']) ? number_format($editar['preco'],2,',','') : '' ?>" placeholder="0,00" required>
</div>

<div class="campo">
<label>Categoria <b>*</b></label>
<select name="id_categoria" required>
<option value="">Selecione a categoria</option>
<?php foreach ($categorias as $categoria): ?>
<option value="<?= $categoria['id_categoria'] ?>" <?= isset($editar['id_categoria']) && $editar['id_categoria'] == $categoria['id_categoria'] ? 'selected' : '' ?>>
<?= htmlspecialchars($categoria['nome']) ?>
</option>
<?php endforeach; ?>
</select>
</div>

</div>

<div class="campo status-campo">
<label>Status <b>*</b></label>
<div class="status-opcoes">
<label><input type="radio" name="status" value="Ativo" <?= ($editar['status'] ?? 'Ativo') === 'Ativo' ? 'checked' : '' ?>> Ativo</label>
<label><input type="radio" name="status" value="Inativo" <?= ($editar['status'] ?? '') === 'Inativo' ? 'checked' : '' ?>> Inativo</label>
</div>
</div>

<div class="campo imagem-campo">
<label>Imagem do Produto</label>
<small>Adicione uma imagem atraente ao produto</small>

<div class="upload">
<input type="file" id="imagem" name="imagem" accept="image/png,image/jpeg,image/webp">
<label for="imagem" class="arquivo">Escolher arquivo</label>
<span><?= !empty($editar['imagem']) ? htmlspecialchars($editar['imagem']) : 'Nenhum arquivo escolhido' ?></span>
</div>

<small class="formatos">PNG, JPG, WEBP até 5MB</small>

<?php if (!empty($editar['imagem'])): ?>
<div class="imagem-atual">
<img src="../../imagens/<?= htmlspecialchars($editar['imagem']) ?>" alt="Imagem atual">
<span>Imagem atual</span>
</div>
<?php endif; ?>
</div>

<div class="adicionais">
<h3>Informações Adicionais</h3>

<div class="tempo">
<label>Tempo de preparo (min)</label>
<input type="number" name="tempo_preparo" min="0" value="<?= $editar['tempo_preparo'] ?? 0 ?>">
</div>

<div class="opcoes">
<label class="check">
<input type="checkbox" name="promocao" <?= ($editar['promocao'] ?? 'Não') === 'Sim' ? 'checked' : '' ?>>
Produto em Promoção
</label>

<label class="check">
<input type="checkbox" name="disponivel_entrega" <?= ($editar['disponivel_entrega'] ?? 'Sim') === 'Sim' ? 'checked' : '' ?>>
Disponível para entrega
</label>
</div>
</div>

<div class="botoes">
<a href="cadastro.php" class="limpar">🗑 Limpar</a>

<button type="submit" name="acao" value="visualizar" class="botao-visualizar">
👁 Visualizar produto
</button>

<button type="submit" name="acao" value="salvar" class="salvar">
<?= $editar ? 'Atualizar Produto' : 'Salvar Produto' ?>
</button>
</div>

</form>
</section>

<?php if ($visualizar): ?>
<section class="preview-produto-area">

<h2>Pré-visualização</h2>
<span class="subtitulo">Assim o produto aparecerá na categoria.</span>

<?php if ($categoriaPreview): ?>
<div class="preview-categoria-nome" style="background:<?= htmlspecialchars($categoriaPreview['cor']) ?>;color:#fff">
Categoria: <?= htmlspecialchars($categoriaPreview['nome']) ?>
</div>
<?php endif; ?>

<div class="preview-card">

<div class="preview-card-imagem">
<?php if (!empty($editar['imagem'])): ?>
<img src="../../imagens/<?= htmlspecialchars($editar['imagem']) ?>" alt="<?= htmlspecialchars($editar['nome']) ?>">
<?php else: ?>
<span class="preview-sem-imagem">Sem imagem</span>
<?php endif; ?>
</div>

<?php if (($editar['promocao'] ?? 'Não') === 'Sim'): ?>
<span class="preview-promocao">PROMOÇÃO</span>
<?php endif; ?>

<h3><?= htmlspecialchars($editar['nome'] ?: 'Nome do produto') ?></h3>

<p><?= htmlspecialchars($editar['descricao'] ?: 'Descrição do produto') ?></p>

<span class="preco">
R$ <?= number_format((float)($editar['preco'] ?? 0),2,',','.') ?>
</span>

<?php if (($editar['status'] ?? 'Ativo') === 'Ativo' && ($editar['disponivel_entrega'] ?? 'Sim') === 'Sim'): ?>
<div class="add-preview">+</div>
<?php else: ?>
<div class="preview-inativo">Produto indisponível</div>
<?php endif; ?>

</div>
</section>
<?php endif; ?>

<section class="produtos">

<h2>Produtos Cadastrados</h2>

<div class="tabela-cabecalho">
<span>Nome</span>
<span>Categoria</span>
<span>Preço</span>
<span>Status</span>
<span>Ações</span>
</div>

<?php foreach ($produtos as $produto): ?>
<div class="produto">

<span><?= htmlspecialchars($produto['nome']) ?></span>
<span><?= htmlspecialchars($produto['categoria']) ?></span>
<span>R$ <?= number_format($produto['preco'],2,',','.') ?></span>
<span><?= htmlspecialchars($produto['status']) ?></span>

<div class="acoes">
<a href="?editar=<?= $produto['id_produto'] ?>" class="editar">Editar</a>
<a href="?excluir=<?= $produto['id_produto'] ?>" class="excluir">Excluir</a>
</div>

</div>
<?php endforeach; ?>

<?php if (!$produtos): ?>
<p class="sem-produtos">Nenhum produto cadastrado.</p>
<?php endif; ?>

</section>

</div>
</main>

<?php if ($modalExcluir): ?>
<div class="modal-excluir ativo">

<div class="caixa-excluir">

<h2>Excluir produto?</h2>

<p>
Tem certeza que deseja excluir
<strong><?= htmlspecialchars($modalExcluir['nome']) ?></strong>?
</p>

<div class="botoes-excluir">

<a href="cadastro.php" class="btn-cancelar-exclusao">
Cancelar
</a>

<form method="POST">
<input type="hidden" name="acao" value="excluir">
<input type="hidden" name="id_produto" value="<?= $modalExcluir['id_produto'] ?>">
<button type="submit" class="btn-confirmar-exclusao">Excluir</button>
</form>

</div>
</div>
</div>
<?php endif; ?>

</body>
</html>