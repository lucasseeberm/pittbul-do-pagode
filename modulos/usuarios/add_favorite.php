<?php
session_start();

// Garante que o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

// Garante que um ID de produto foi passado
if (!isset($_GET['produto_id'])) {
    header('Location: loja.php?status=erro_favorito');
    exit();
}

require_once 'config/database.php'; // Conecta ao $pdo_usuarios

$usuario_id = $_SESSION['usuario_id'];
$produto_id = $_GET['produto_id'];

try {
    // INSERT IGNORE não insere se o par (usuario_id, produto_id) já existir, evitando duplicatas
    $stmt = $pdo_usuarios->prepare("INSERT IGNORE INTO favoritos (usuario_id, produto_id) VALUES (?, ?)");
    $stmt->execute([$usuario_id, $produto_id]);
    
    // Redireciona de volta para a loja com uma mensagem de sucesso
    header('Location: loja.php?status=favorito_add');
    exit();

} catch (\PDOException $e) {
    // Em caso de erro, redireciona com uma mensagem de erro
    header('Location: loja.php?status=erro_favorito');
    exit();
}