<?php
require_once 'config/database.php';

$alertas = [];
try {
    // Seleciona os alertas e junta com a tabela de produtos para pegar o nome
    $stmt = $pdo->query(
        "SELECT a.*, p.nome as produto_nome 
         FROM alertas_estoque a
         JOIN produtos p ON a.produto_id = p.id
         ORDER BY a.data_alerta DESC"
    );
    $alertas = $stmt->fetchAll();
} catch (\PDOException $e) {
    $erro = "Erro ao buscar alertas: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Admin - Alertas de Estoque</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center">
        <h1>Alertas de Estoque Baixo</h1>
        <a href="admin.php" class="btn btn-secondary">Voltar para Produtos</a>
    </div>
    <hr>
    <?php if(!empty($alertas)): ?>
        <div class="list-group">
        <?php foreach($alertas as $alerta): ?>
            <div class="list-group-item list-group-item-action bg-warning-subtle">
                <div class="d-flex w-100 justify-content-between">
                    <h5 class="mb-1">Alerta para: <?php echo htmlspecialchars($alerta['produto_nome']); ?></h5>
                    <small><?php echo date('d/m/Y H:i', strtotime($alerta['data_alerta'])); ?></small>
                </div>
                <p class="mb-1"><?php echo htmlspecialchars($alerta['mensagem']); ?></p>
            </div>
        <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-success">Nenhum alerta de estoque baixo no momento.</div>
    <?php endif; ?>
</div>
</body>
</html>