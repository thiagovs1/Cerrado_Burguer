
<?php

session_start();

require_once __DIR__ . '/conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';

    if (empty($email) || empty($senha)) {
        header("Location: login.html?erro=Preencha todos os campos");
        exit;
    }

    if ($email === "admin123@hamburgueria.com" && $senha === "CerradoBurguer") {

        $_SESSION['admin'] = true;
        $_SESSION['usuario_email'] = $email;

        header("Location: ../administrador/Adm.html/adm-inicio.html");
        exit;
    }

    $stmt = $pdo->prepare(
        "SELECT * FROM usuario WHERE email = ?"
    );

    $stmt->execute([$email]);

    $usuario = $stmt->fetch();

    if ($usuario && password_verify($senha, $usuario['senha'])) {

        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['usuario_email'] = $usuario['email'];

        header("Location: ../html/tela-inicial.html");
        exit;

    } else {

        header("Location: login.html?erro=E-mail ou senha incorretos");
        exit;
    }
}
?>

