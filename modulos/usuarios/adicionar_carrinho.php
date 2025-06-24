<?php
session_start();

// Garante que o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

// Garante que um ID de produto foi passado
if (!isset($_GET['produto_id'])) {
    // Se não houver ID, apenas redireciona de volta para a loja
    header('Location: loja.php');
    exit();
}

$produto_id = (int)$_GET['produto_id'];

// Se o carrinho ainda não existe na sessão, cria um array vazio
if (!isset($_SESSION['carrinho'])) {
    $_SESSION['carrinho'] = [];
}

// Verifica se o produto já está no carrinho
if (isset($_SESSION['carrinho'][$produto_id])) {
    // Se sim, incrementa a quantidade
    $_SESSION['carrinho'][$produto_id]['quantidade']++;
} else {
    // Se não, adiciona o produto ao carrinho com quantidade 1
    $_SESSION['carrinho'][$produto_id] = ['quantidade' => 1];
}

// Redireciona de volta para a loja com uma mensagem de sucesso
header('Location: loja.php?status=adicionado');
exit();