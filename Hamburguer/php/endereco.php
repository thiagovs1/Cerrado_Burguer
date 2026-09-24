php
<?php

session_start();

require_once "../crud/conexao.php";

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../crud/login.html");
    exit;
}

$id_usuario = $_SESSION['usuario_id'];
$tipo_entrega = $_POST['tipo_entrega'] ?? '';


if ($tipo_entrega === 'Retirada') {

    $sql = "SELECT id_pedido
            FROM pedido
            WHERE id_usuario = ?
            AND status = 'Carrinho'
            LIMIT 1";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_usuario]);

    $pedido = $stmt->fetch();

    if (!$pedido) {
        header("Location: ../html/cardapio.php");
        exit;
    }

    $id_pedido = $pedido['id_pedido'];

    $sql = "SELECT SUM(quantidade * preco_unitario) AS subtotal
            FROM itens_pedidos
            WHERE id_pedido = ?";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_pedido]);

    $resultado = $stmt->fetch();

    $subtotal = (float) ($resultado['subtotal'] ?? 0);
    $entrega = 0.00;
    $total = $subtotal;

    $sql = "UPDATE pedido
            SET tipo_entrega = 'Retirada',
                cidade = NULL,
                estado = NULL,
                endereco = NULL,
                setor = NULL,
                informacao_adicional = NULL,
                subtotal = ?,
                valor_entrega = ?,
                total = ?,
                status = 'Pedido recebido'
            WHERE id_pedido = ?
            AND id_usuario = ?
            AND status = 'Carrinho'";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        $subtotal,
        $entrega,
        $total,
        $id_pedido,
        $id_usuario
    ]);

    header("Location: ../html-carrinho/confirmacao.php");
    exit;
}


if ($tipo_entrega === 'Entrega') {

    $cidade = trim($_POST['cidade'] ?? '');
    $estado = trim($_POST['estado'] ?? '');
    $endereco = trim($_POST['endereco'] ?? '');
    $setor = trim($_POST['setor'] ?? '');
    $informacao = trim($_POST['informacao'] ?? '');

    if (
        $cidade === '' ||
        $estado === '' ||
        $endereco === '' ||
        $setor === ''
    ) {
        echo "
        <script>
            alert('Preencha todos os campos obrigatórios.');
            window.history.back();
        </script>
        ";

        exit;
    }

    $sql = "UPDATE pedido
            SET tipo_entrega = 'Entrega',
                cidade = ?,
                estado = ?,
                endereco = ?,
                setor = ?,
                informacao_adicional = ?
            WHERE id_usuario = ?
            AND status = 'Carrinho'";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        $cidade,
        $estado,
        $endereco,
        $setor,
        $informacao,
        $id_usuario
    ]);

    header("Location: ../html-carrinho/forma-pagamento.php");
    exit;
}

echo "
<script>
    alert('Selecione uma opção de entrega.');
    window.history.back();
</script>
";

?>

