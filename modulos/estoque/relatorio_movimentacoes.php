<?php
require_once 'config/database.php';

$movimentacoes = [];
try {
    // Seleciona as movimentações e junta com a tabela de produtos para pegar o nome
    $stmt = $pdo->query(
        "SELECT m.*, p.nome as produto_nome 
         FROM movimentacoes_estoque m
         JOIN produtos p ON m.produto_id = p.id
         ORDER BY m.data_movimentacao DESC"
    );
    $movimentacoes = $stmt->fetchAll();
} catch (\PDOException $e) {
    $erro = "Erro ao buscar movimentações: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Admin - Relatório de Movimentações</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center">
        <h1>Relatório de Movimentações de Estoque</h1>
        <a href="admin.php" class="btn btn-secondary">Voltar para Produtos</a>
    </div>
    <hr>
    <table class="table table-sm table-striped">
        <thead>
            <tr>
                <th>Data</th>
                <th>Produto</th>
                <th>Tipo</th>
                <th>Quantidade</th>
                <th>Observação</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($movimentacoes as $mov): ?>
            <tr>
                <td><?php echo date('d/m/Y H:i', strtotime($mov['data_movimentacao'])); ?></td>
                <td><?php echo htmlspecialchars($mov['produto_nome']); ?></td>
                <td><?php echo htmlspecialchars($mov['tipo_movimentacao']); ?></td>
                <td><strong><?php echo $mov['quantidade_alterada']; ?></strong></td>
                <td><?php echo htmlspecialchars($mov['observacao']); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>