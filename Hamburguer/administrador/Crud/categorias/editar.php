<?php

header('Content-Type: application/json; charset=utf-8');

require_once "../conexao.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    http_response_code(405);

    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Método não permitido."
    ]);

    exit;
}

$id = intval($_POST['id_categoria'] ?? 0);
$nome = trim($_POST['nome'] ?? '');
$descricao = trim($_POST['descricao'] ?? '');
$status = $_POST['status'] ?? 'Ativa';

if ($id <= 0) {

    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Categoria inválida."
    ]);

    exit;
}

if ($nome === '') {

    echo json_encode([
        "sucesso" => false,
        "mensagem" => "O nome da categoria é obrigatório."
    ]);

    exit;
}

if (!in_array($status, ['Ativa', 'Inativa'])) {
    $status = 'Ativa';
}

/*
|--------------------------------------------------------------------------
| Define a imagem novamente pelo nome
|--------------------------------------------------------------------------
*/

function normalizarTexto($texto)
{
    $texto = mb_strtolower(trim($texto), 'UTF-8');

    return str_replace(
        ['á', 'à', 'ã', 'â', 'ä', 'é', 'è', 'ê', 'ë', 'í', 'ì', 'î', 'ï', 'ó', 'ò', 'õ', 'ô', 'ö', 'ú', 'ù', 'û', 'ü', 'ç'],
        ['a', 'a', 'a', 'a', 'a', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'c'],
        $texto
    );
}

$nomeNormalizado = normalizarTexto($nome);

$imagem = null;

if (str_contains($nomeNormalizado, 'hamburguer')) {

    $imagem = 'icon-hamburguerpng.png';

} elseif (
    str_contains($nomeNormalizado, 'bebida') ||
    str_contains($nomeNormalizado, 'refrigerante')
) {

    $imagem = 'categorias-bebidas.png';

} elseif (
    str_contains($nomeNormalizado, 'acompanhamento') ||
    str_contains($nomeNormalizado, 'batata')
) {

    $imagem = 'categorias-acompanhamentos.png';

} elseif (str_contains($nomeNormalizado, 'combo')) {

    $imagem = 'categorias-combos.png';
}

/*
|--------------------------------------------------------------------------
| Verifica duplicidade
|--------------------------------------------------------------------------
*/

$verificar = $pdo->prepare("
    SELECT id_categoria
    FROM categoria
    WHERE LOWER(nome) = LOWER(?)
    AND id_categoria != ?
");

$verificar->execute([
    $nome,
    $id
]);

if ($verificar->fetch()) {

    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Já existe outra categoria com esse nome."
    ]);

    exit;
}

/*
|--------------------------------------------------------------------------
| Atualiza
|--------------------------------------------------------------------------
*/

$sql = "
    UPDATE categoria
    SET
        nome = ?,
        descricao = ?,
        status = ?,
        imagem = ?
    WHERE id_categoria = ?
";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    $nome,
    $descricao,
    $status,
    $imagem,
    $id
]);

echo json_encode([
    "sucesso" => true,
    "mensagem" => "Categoria atualizada com sucesso!"
]);