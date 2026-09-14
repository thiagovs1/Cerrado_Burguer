<?php
session_start();

$host = 'localhost';
$db   = 'cerrado_burguer';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;port=3306;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    die("Erro na conexão: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';

    if (empty($email) || empty($senha)) {
        header("Location: login.html?erro=Preencha todos os campos");
        exit;
    }

if ($email === "admin123@hamburgueria.com" && $senha === "CerradoBurguer") {

    $_SESSION['admin'] = true;
    $_SESSION['usuario_email'] = $email;

    header("Location: ../administrador/Adm.html/clientes.html");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM usuario WHERE email = ?");
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


    if ($usuario && password_verify($senha, $usuario['senha'])) {
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['usuario_email'] = $usuario['email'];
        header("Location: login.html?sucesso=Login realizado");
    } else {
        header("Location: login.html?erro=E-mail ou senha incorretos");
    }
    exit;
}
?>
