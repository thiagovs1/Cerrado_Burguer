<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../html/login.html");
    exit;
}

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

$id = $_SESSION['usuario_id'];

$stmt = $pdo->prepare("DELETE FROM usuario WHERE id = ?");

if ($stmt->execute([$id])) {
    session_destroy();
    header("Location: login.html?sucesso=Perfil excluído com sucesso");
} else {
    header("Location: ../html/tela-inicial.html?erro=Erro ao excluir perfil");
}
exit;
?>
