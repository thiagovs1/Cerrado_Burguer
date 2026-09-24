
<?php
session_start();

require_once "../crud/conexao.php";

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../crud/login.html");
    exit;
}

$id_usuario = $_SESSION['usuario_id'];

$tipo_entrega = $_POST['tipo_entrega'] ?? '';

/*
|--------------------------------------------------------------------------
| RETIRADA
|--------------------------------------------------------------------------
*/

if ($tipo_entrega === 'Retirada') {

    /*
     * Localiza o carrinho atual do usuário.
     */

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


    /*
     * Calcula o subtotal dos produtos.
     */

    $sql = "SELECT SUM(quantidade * preco_unitario) AS subtotal
            FROM itens_pedidos
            WHERE id_pedido = ?";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_pedido]);

    $resultado = $stmt->fetch();

    $subtotal = (float) ($resultado['subtotal'] ?? 0);

    /*
     * Retirada não possui taxa de entrega.
     */

    $entrega = 0.00;

    /*
     * Total = subtotal + entrega.
     */

    $total = $subtotal + $entrega;


    /*
     * Finaliza o pedido como Retirada.
     */

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


    /*
     * Vai direto para a confirmação.
     */

    header("Location: ../html-carrinho/confirmacao.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| ENTREGA
|--------------------------------------------------------------------------
*/

if ($tipo_entrega === 'Entrega') {

    $cidade = trim($_POST['cidade'] ?? '');
    $estado = trim($_POST['estado'] ?? '');
    $endereco = trim($_POST['endereco'] ?? '');
    $setor = trim($_POST['setor'] ?? '');
    $informacao = trim($_POST['informacao'] ?? '');

    /*
     * Verifica os campos obrigatórios.
     */

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


    /*
     * Salva os dados de entrega no pedido.
     */

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


    /*
     * Entrega continua seguindo para a forma de pagamento.
     */

    header("Location: ../html-carrinho/forma-pagamento.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| NENHUMA OPÇÃO SELECIONADA
|--------------------------------------------------------------------------
*/

echo "
<script>
    alert('Selecione uma opção de entrega.');
    window.history.back();
</script>
";

?>
