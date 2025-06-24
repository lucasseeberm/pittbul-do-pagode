<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

// --- INÍCIO DA MODIFICAÇÃO NO PHP ---

// 1. Pega os parâmetros de busca da URL (enviados pelo formulário)
$search_term = $_GET['search'] ?? '';
$category = $_GET['categoria'] ?? '';

// 2. Monta o array de parâmetros para a API
$params_api = [];
if (!empty($search_term)) {
    $params_api['search'] = $search_term;
}
if (!empty($category)) {
    $params_api['categoria'] = $category;
}

// 3. Constrói a URL da API com os parâmetros de busca
$url_api_estoque = getenv('ESTOQUE_API_URL') . '/produtos';
if (!empty($params_api)) {
    $url_api_estoque .= '?' . http_build_query($params_api);
}
// --- FIM DA MODIFICAÇÃO NO PHP ---


// Faz a chamada para a API de Estoque (agora com a URL dinâmica)
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url_api_estoque);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$produtos = [];
if ($http_code == 200 && $response) {
    $produtos = json_decode($response, true);
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Loja - Nossos Produtos</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg bg-body-tertiary">
        <div class="container">
            <a class="navbar-brand" href="loja.php">Farmácia Digital</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="profile.php">Meu Perfil</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="loja.php">Loja</a>
                    </li>
                    <li class="nav-item">
                        <?php $itens_no_carrinho = isset($_SESSION['carrinho']) ? count($_SESSION['carrinho']) : 0; ?>
                        <a class="nav-link" href="carrinho.php">
                            Carrinho <span class="badge bg-primary rounded-pill"><?php echo $itens_no_carrinho; ?></span>
                        </a>
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
        <h1>Nossos Produtos</h1>
        <p>Veja os produtos disponíveis em nosso estoque.</p>

        <div class="card card-body bg-light mb-4">
            <form action="loja.php" method="GET" class="row g-3 align-items-center">
                <div class="col-md-6">
                    <input type="text" class="form-control" name="search" placeholder="Buscar por nome ou descrição..." value="<?php echo htmlspecialchars($search_term); ?>">
                </div>
                <div class="col-md-4">
                    <select name="categoria" class="form-select">
                        <option value="">Todas as categorias</option>
                        <option value="Analgésicos" <?php echo ($category === 'Analgésicos') ? 'selected' : ''; ?>>Analgésicos</option>
                        <option value="Antibióticos" <?php echo ($category === 'Antibióticos') ? 'selected' : ''; ?>>Antibióticos</option>
                        <option value="Cardiovascular" <?php echo ($category === 'Cardiovascular') ? 'selected' : ''; ?>>Cardiovascular</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Filtrar</button>
                </div>
            </form>
        </div>
        <?php if (isset($_GET['status']) && $_GET['status'] == 'adicionado'): ?>
            <div class="alert alert-success">Produto adicionado ao carrinho com sucesso!</div>
        <?php endif; ?>

        <hr>
        
        <table class="table table-striped table-hover">
            <thead class="table-dark">
                <tr>
                    <th>Nome</th>
                    <th>Descrição</th>
                    <th>Preço</th>
                    <th>Estoque</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($produtos)): ?>
                    <?php foreach ($produtos as $produto): ?>
                        <tr>
                            <td><a href="produto.php?id=<?php echo $produto['id']; ?>"><?php echo htmlspecialchars($produto['nome']); ?></a></td>
                            <td><?php echo htmlspecialchars($produto['descricao']); ?></td>
                            <td>R$ <?php echo number_format($produto['preco'], 2, ',', '.'); ?></td>
                            <td><?php echo (int)$produto['estoque']; ?></td>
                            <td>
                                <a href="adicionar_carrinho.php?produto_id=<?php echo $produto['id']; ?>" class="btn btn-success btn-sm">Comprar</a>
                                <a href="add_favorite.php?produto_id=<?php echo $produto['id']; ?>" class="btn btn-outline-primary btn-sm">Favoritar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center">Nenhum produto encontrado com os filtros aplicados.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>