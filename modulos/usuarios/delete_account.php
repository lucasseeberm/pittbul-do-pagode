<?php
// delete_account.php

// 1. Iniciar a sessão e verificar se o usuário está logado
session_start();

if (!isset($_SESSION['usuario_id'])) {
    // Se não estiver logado, não pode deletar nada. Redireciona para o login.
    header('Location: login.php');
    exit();
}

// 2. Incluir a conexão com o banco de dados
require_once 'config/database.php'; // Garante que $pdo_usuarios está disponível

try {
    // 3. Preparar e executar o comando SQL para deletar o usuário
    $id_para_deletar = $_SESSION['usuario_id'];

    $stmt = $pdo_usuarios->prepare("DELETE FROM usuarios WHERE id = ?");
    $stmt->execute([$id_para_deletar]);

    // 4. Limpar e destruir a sessão (fazer logout)
    $_SESSION = array(); // Limpa todas as variáveis da sessão
    session_destroy();   // Destrói a sessão

    // 5. Redirecionar para uma página de confirmação ou para a home com uma mensagem
    // Por simplicidade, vamos redirecionar para a página de registro com uma mensagem de sucesso.
    header('Location: register.php?status=deleted'); // Podemos usar um parâmetro na URL
    exit();

} catch (\PDOException $e) {
    // Em caso de erro, não destrua a sessão. Apenas mostre uma mensagem.
    // Você pode criar uma página de erro mais amigável.
    die("Erro ao deletar a conta. Por favor, tente novamente. Erro: " . $e->getMessage());
}
?>