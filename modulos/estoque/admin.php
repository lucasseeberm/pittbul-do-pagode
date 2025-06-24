<?php
require_once 'config/database.php'; // Usa o $pdo do estoque

// Busca todos os produtos para listar na tabela
$produtos = [];
try {
    $stmt = $pdo->query("SELECT id, nome, preco, estoque FROM produtos ORDER BY nome");
    $produtos = $stmt->fetchAll();
} catch (\PDOException $e) {
    $erro = "Erro ao buscar produtos: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Admin - Gerenciar Produtos</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center">
    <h1>Gerenciar Produtos</h1>
    <div> <a href="alertas.php" class="btn btn-warning">Ver Alertas</a>
        <a href="relatorio_movimentacoes.php" class="btn btn-info">Ver Relatórios</a>
        <a href="form_produto.php" class="btn btn-success">Adicionar Produto</a>
    </div>
</div>
    <hr>
    <?php if (isset($erro)): ?>
        <div class="alert alert-danger"><?php echo $erro; ?></div>
    <?php endif; ?>
    <table class="table table-bordered table-striped">
        <thead class="table-dark">
        <tr>
            <th>ID</th>
            <th>Nome</th>
            <th>Preço</th>
            <th>Estoque</th>
            <th>Ações</th>
        </tr>
        </thead>
        <tbody>
        <?php if (count($produtos) > 0): ?>
            <?php foreach ($produtos as $produto): ?>
                <tr>
                    <td><?php echo $produto['id']; ?></td>
                    <td><?php echo htmlspecialchars($produto['nome']); ?></td>
                    <td>R$ <?php echo number_format($produto['preco'], 2, ',', '.'); ?></td>
                    <td><?php echo $produto['estoque']; ?></td>
                    <td>
                        <a href="form_produto.php?id=<?php echo $produto['id']; ?>" class="btn btn-primary btn-sm">Editar</a>
                        <a href="excluir_produto.php?id=<?php echo $produto['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Tem certeza que deseja excluir este produto?');">Excluir</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="5" class="text-center">Nenhum produto cadastrado.</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
    <a href="relatorio_movimentacoes.php" class="btn btn-info">Ver Relatório de Movimentações</a>
</div>
</body>
</html>