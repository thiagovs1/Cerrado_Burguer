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
    $senha1 = $_POST['senha1'] ?? '';
    $senha2 = $_POST['senha2'] ?? '';
    $nome = $_POST['nome'] ?? '';
    $cpf = $_POST['cpf'] ?? '';
    $telefone = $_POST['telefone'] ?? '';

    if (empty($email) || empty($senha1) || empty($senha2) || empty($nome) || empty($cpf) || empty($telefone)) {
        header("Location: cadastra.html?erro=Preencha todos os campos");
        exit;
    }

    if ($senha1 !== $senha2) {
        header("Location: cadastra.html?erro=As senhas não coincidem");
        exit;
    }

    $stmt = $pdo->prepare("SELECT id FROM usuario WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        header("Location: cadastra.html?erro=E-mail já cadastrado");
        exit;
    }

    $senhaHash = password_hash($senha1, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("INSERT INTO usuario (email, senha, nome, cpf, telefone) VALUES (?, ?, ?, ?, ?)");
    if ($stmt->execute([$email, $senhaHash, $nome, $cpf, $telefone])) {
        $_SESSION['usuario_id'] = $pdo->lastInsertId();
        $_SESSION['usuario_email'] = $email;
        $_SESSION['usuario_nome'] = $nome;
        $_SESSION['usuario_cpf'] = $cpf;
        $_SESSION['usuario_telefone'] = $telefone;

        header("Location: login.html?sucesso=Cadastro realizado com sucesso");
    } else {
        header("Location: cadastra.html?erro=Erro ao cadastrar");
    }
    exit;
}
?>
