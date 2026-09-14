
<?php

header('Content-Type: application/json; charset=utf-8');

require_once "../conexao.php";

try {

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

        http_response_code(405);

        echo json_encode([
            "sucesso" => false,
            "mensagem" => "Método não permitido."
        ]);

        exit;
    }

    $nome = trim($_POST['nome'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');
    $status = trim($_POST['status'] ?? 'Ativa');


    if ($nome === '') {

        echo json_encode([
            "sucesso" => false,
            "mensagem" => "O nome da categoria é obrigatório."
        ]);

        exit;
    }

    if (!in_array($status, ['Ativa', 'Inativa'], true)) {
        $status = 'Ativa';
    }

    function normalizarTexto($texto)
    {
        $texto = mb_strtolower(trim($texto), 'UTF-8');

        $texto = str_replace(
            [
                'á', 'à', 'ã', 'â', 'ä',
                'é', 'è', 'ê', 'ë',
                'í', 'ì', 'î', 'ï',
                'ó', 'ò', 'õ', 'ô', 'ö',
                'ú', 'ù', 'û', 'ü',
                'ç'
            ],
            [
                'a', 'a', 'a', 'a', 'a',
                'e', 'e', 'e', 'e',
                'i', 'i', 'i', 'i',
                'o', 'o', 'o', 'o', 'o',
                'u', 'u', 'u', 'u',
                'c'
            ],
            $texto
        );

        return $texto;
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

    } elseif (
        str_contains($nomeNormalizado, 'combo')
    ) {

        $imagem = 'categorias-combos.png';
    }

    $verificar = $pdo->prepare("
        SELECT id_categoria
        FROM categoria
        WHERE LOWER(nome) = LOWER(?)
        LIMIT 1
    ");

    $verificar->execute([$nome]);

    if ($verificar->fetch(PDO::FETCH_ASSOC)) {

        echo json_encode([
            "sucesso" => false,
            "mensagem" => "Essa categoria já está cadastrada."
        ]);

        exit;
    }

    $sql = "
        INSERT INTO categoria
        (nome, descricao, status, imagem)
        VALUES
        (:nome, :descricao, :status, :imagem)
    ";

    $stmt = $pdo->prepare($sql);

    $stmt->bindValue(':nome', $nome);
    $stmt->bindValue(':descricao', $descricao);
    $stmt->bindValue(':status', $status);
    $stmt->bindValue(':imagem', $imagem);

    $stmt->execute();

    echo json_encode([
        "sucesso" => true,
        "mensagem" => "Categoria cadastrada com sucesso!",
        "id_categoria" => $pdo->lastInsertId()
    ]);

} catch (PDOException $e) {

    http_response_code(500);

    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Erro no banco de dados.",
        "erro" => $e->getMessage()
    ]);

} catch (Throwable $e) {

    http_response_code(500);

    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Erro interno no servidor.",
        "erro" => $e->getMessage()
    ]);
}

