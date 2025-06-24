<?php
require_once 'config/database.php';

if (!isset($_GET['id'])) {
    header('Location: admin.php');
    exit();
}

$id = (int)$_GET['id'];

try {
    $stmt = $pdo->prepare("DELETE FROM produtos WHERE id = ?");
    $stmt->execute([$id]);
} catch (\PDOException $e) {
    die("Erro ao excluir o produto: " . $e->getMessage());
}

// Redireciona de volta para a página de admin
header('Location: admin.php?status=excluido');
exit();