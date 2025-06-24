<?php
session_start(); // Inicia a sessão para gerenciar o carrinho
require_once 'config/database.php';

// Busca todos os produtos no banco
$stmt = $pdo->query("SELECT id, nome, descricao, preco, estoque FROM produtos WHERE estoque > 0");
$produtos = $stmt->fetchAll();
?>

<?php include 'templates/header.php'; ?>

<div class="container">
    <h2>Nossos Produtos</h2>
    <div class="product-grid">
        <?php foreach ($produtos as $produto): ?>
            <div class="product-card">
                <h3><?php echo htmlspecialchars($produto['nome']); ?></h3>
                <p><?php echo htmlspecialchars($produto['descricao']); ?></p>
                <p class="price">R$ <?php echo number_format($produto['preco'], 2, ',', '.'); ?></p>
                
                <form action="/modulos/vendas/adicionar_carrinho.php" method="post">
                    <input type="hidden" name="produto_id" value="<?php echo $produto['id']; ?>">
                    <input type="number" name="quantidade" value="1" min="1" max="<?php echo $produto['estoque']; ?>">
                    <button type="submit">Adicionar ao Carrinho</button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include 'templates/footer.php'; ?>