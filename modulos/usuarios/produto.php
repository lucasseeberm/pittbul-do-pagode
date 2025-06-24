<?php
session_start();
if (!isset($_GET['id'])) { header('Location: loja.php'); exit(); }

$produto_id = $_GET['id'];
$produto = null;
$avaliacoes = [];

// API Call 1: Buscar detalhes do produto
$ch_prod = curl_init(getenv('ESTOQUE_API_URL') . "/produtos/" . $produto_id);
curl_setopt($ch_prod, CURLOPT_RETURNTRANSFER, 1);
$response_prod = curl_exec($ch_prod);
if (curl_getinfo($ch_prod, CURLINFO_HTTP_CODE) == 200) {
    $produto = json_decode($response_prod, true);
}
curl_close($ch_prod);

// API Call 2: Buscar avaliações do produto
if ($produto) { // Só busca avaliações se o produto foi encontrado
    $ch_aval = curl_init(getenv('ESTOQUE_API_URL') . "/produtos/" . $produto_id . "/avaliacoes");
    curl_setopt($ch_aval, CURLOPT_RETURNTRANSFER, 1);
    $response_aval = curl_exec($ch_aval);
    if (curl_getinfo($ch_aval, CURLINFO_HTTP_CODE) == 200) {
        $avaliacoes = json_decode($response_aval, true);
    }
    curl_close($ch_aval);
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($produto['nome'] ?? 'Produto'); ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg bg-body-tertiary">
        <div class="container">
            <a class="navbar-brand" href="loja.php">Farmácia Digital</a>
            <a href="loja.php" class="btn btn-outline-primary">Voltar para a Loja</a>
        </div>
    </nav>

    <div class="container mt-5">
        <?php if ($produto): ?>
            <div class="row">
                <div class="col-md-8">
                    <h1><?php echo htmlspecialchars($produto['nome']); ?></h1>
                    <p class="lead"><?php echo htmlspecialchars($produto['descricao']); ?></p>
                    <hr>
                    
                    <?php if (isset($_SESSION['usuario_id'])): ?>
                    <div class="card my-4">
                        <div class="card-header">Deixe sua avaliação</div>
                        <div class="card-body">
                            <form action="submit_review.php" method="POST">
                                <input type="hidden" name="produto_id" value="<?php echo $produto_id; ?>">
                                <div class="mb-3">
                                    <label for="nota" class="form-label">Sua Nota (de 1 a 5)</label>
                                    <select name="nota" id="nota" class="form-select" required>
                                        <option value="5">5 Estrelas</option><option value="4">4 Estrelas</option><option value="3">3 Estrelas</option><option value="2">2 Estrelas</option><option value="1">1 Estrela</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="comentario" class="form-label">Seu Comentário</label>
                                    <textarea name="comentario" id="comentario" class="form-control" rows="3" required></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary">Enviar Avaliação</button>
                            </form>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <h3 class="mt-4">Avaliações de Clientes</h3>
                    <?php if (!empty($avaliacoes)): ?>
                        <?php foreach($avaliacoes as $aval): ?>
                        <div class="card mb-3">
                            <div class="card-body">
                                <strong><?php echo htmlspecialchars($aval['usuario_nome']); ?></strong> - Nota: <?php echo $aval['nota']; ?>/5
                                <p class="card-text mt-2"><?php echo nl2br(htmlspecialchars($aval['comentario'])); ?></p>
                                <small class="text-muted">Em <?php echo date('d/m/Y', strtotime($aval['data_avaliacao'])); ?></small>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>Este produto ainda não tem avaliações. Seja o primeiro a avaliar!</p>
                    <?php endif; ?>
                </div>

                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">R$ <?php echo number_format($produto['preco'], 2, ',', '.'); ?></h4>
                            <p>Em estoque: <?php echo $produto['estoque']; ?></p>
                            <a href="adicionar_carrinho.php?produto_id=<?php echo $produto['id']; ?>" class="btn btn-success w-100">Adicionar ao Carrinho</a>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-danger">Produto não encontrado.</div>
        <?php endif; ?>
    </div>
</body>
</html>