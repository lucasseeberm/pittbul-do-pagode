<?php
require_once 'config/database.php';

// Verifica se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: admin.php');
    exit();
}

// Coleta os dados do formulário
$id = $_POST['id'] ?? null;
$nome = $_POST['nome'];
$descricao = $_POST['descricao'];
$preco = $_POST['preco'];
$estoque = $_POST['estoque'];
$categoria = $_POST['categoria'];
$principio_ativo = $_POST['principio_ativo'];
$controlado = isset($_POST['controlado']) ? 1 : 0;

try {
    // Se há um ID, é uma atualização (UPDATE)
    if (!empty($id)) {
        $stmt = $pdo->prepare(
            "UPDATE produtos SET nome = ?, descricao = ?, preco = ?, estoque = ?, categoria = ?, principio_ativo = ?, controlado = ? WHERE id = ?"
        );
        $stmt->execute([$nome, $descricao, $preco, $estoque, $categoria, $principio_ativo, $controlado, $id]);
    } 
    // Se não há ID, é uma criação (INSERT)
    else {
        $stmt = $pdo->prepare(
            "INSERT INTO produtos (nome, descricao, preco, estoque, categoria, principio_ativo, controlado) VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([$nome, $descricao, $preco, $estoque, $categoria, $principio_ativo, $controlado]);
    }
} catch (\PDOException $e) {
    die("Erro ao salvar o produto: " . $e->getMessage());
}

// Redireciona de volta para a página de admin
header('Location: admin.php?status=salvo');
exit();