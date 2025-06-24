<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

require_once 'config/database.php'; // Conecta ao $pdo_usuarios

$produtos_favoritos = [];
try {
    // 1. Busca no banco de usuários os IDs dos produtos favoritados
    $stmt = $pdo_usuarios->prepare("SELECT produto_id FROM favoritos WHERE usuario_id = ?");
    $stmt->execute([$_SESSION['usuario_id']]);
    $favoritos_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // 2. Se houver favoritos, busca os detalhes de cada um na API de Estoque
    if (!empty($favoritos_ids)) {
        foreach ($favoritos_ids as $produto_id) {
            $url_api_produto = getenv('ESTOQUE_API_URL') . "/produtos/" . $produto_id;
            $ch = curl_init($url_api_produto);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $response = curl_exec($ch);
            if (curl_getinfo($ch, CURLINFO_HTTP_CODE) == 200) {
                $produtos_favoritos[] = json_decode($response, true);
            }
            curl_close($ch);
        }
    }
} catch (\PDOException $e) {
    // Tratar erro de banco de dados se necessário
}

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Meu Perfil</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg bg-body-tertiary">
        <div class="container">
            <a class="navbar-brand" href="loja.php">Farmácia Digital</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link active" href="profile.php">Meu Perfil</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="loja.php">Loja</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="carrinho.php">Carrinho</a>
                    </li>
                     <li class="nav-item">
                        <a class="nav-link" href="meus_pedidos.php">Meus Pedidos</a>
                    </li>
                </ul>
            </div>
            <a href="logout.php" class="btn btn-outline-danger">Sair</a>
        </div>
    </nav>
    <div class="container mt-5">
        <h1>Bem-vindo, <?php echo htmlspecialchars($_SESSION['usuario_nome']); ?>!</h1>
        <p>Este é o seu perfil. Você está logado com sucesso.</p>

        <div class="my-4">
            <a href="edit_profile.php" class="btn btn-primary">Editar Perfil</a>
            <a href="delete_account.php" class="btn btn-outline-danger" onclick="return confirm('Tem certeza que deseja deletar sua conta? Esta ação não pode ser desfeita.');">
                Deletar Minha Conta
            </a>
        </div>
        <hr>

        <h3>Meus Produtos Favoritos</h3>
        <?php if (!empty($produtos_favoritos)): ?>
            <div class="list-group">
                <?php foreach ($produtos_favoritos as $produto): ?>
                <a href="loja.php" class="list-group-item list-group-item-action">
                    <div class="d-flex w-100 justify-content-between">
                        <h5 class="mb-1"><?php echo htmlspecialchars($produto['nome']); ?></h5>
                        <small>Preço: R$ <?php echo number_format($produto['preco'], 2, ',', '.'); ?></small>
                    </div>
                    <p class="mb-1"><?php echo htmlspecialchars($produto['descricao']); ?></p>
                </a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>Você ainda não favoritou nenhum produto.</p>
        <?php endif; ?>

    </div>
</body>
</html>