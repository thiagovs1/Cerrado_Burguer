
<?php

session_start();

require_once __DIR__ . '/conexao.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $email = $_POST['email'] ?? '';
    $novaSenha = $_POST['senha1'] ?? '';
    $confirmaSenha = $_POST['senha2'] ?? '';

    if (
        empty($email) ||
        empty($novaSenha) ||
        empty($confirmaSenha)
    ) {
        header("Location: recuperar.html?erro=Preencha todos os campos");
        exit;
    }

    if ($novaSenha !== $confirmaSenha) {
        header("Location: recuperar.html?erro=As senhas não coincidem");
        exit;
    }

    $stmt = $pdo->prepare(
        "SELECT id FROM usuario WHERE email = ?"
    );

    $stmt->execute([$email]);

    if (!$stmt->fetch()) {
        header("Location: recuperar.html?erro=E-mail não encontrado");
        exit;
    }

    $senhaHash = password_hash(
        $novaSenha,
        PASSWORD_DEFAULT
    );

    $stmt = $pdo->prepare(
        "UPDATE usuario SET senha = ? WHERE email = ?"
    );

    if ($stmt->execute([$senhaHash, $email])) {

        header(
            "Location: login.html?sucesso=Senha alterada com sucesso"
        );

    } else {

        header(
            "Location: recuperar.html?erro=Erro ao alterar senha"
        );
    }

    exit;
}
?>

