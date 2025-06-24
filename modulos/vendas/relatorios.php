<?php
// /modulos/vendas/relatorios.php

require_once 'config/database.php'; // Conecta ao $pdo do serviço de vendas

$metricas = null;
$ultimos_pedidos = [];
$erro = null;

try {
    // Query 1: Busca as métricas gerais
    $stmt_metricas = $pdo->query(
        "SELECT
            COUNT(*) as total_pedidos,
            SUM(valor_total) as faturamento_total,
            AVG(valor_total) as ticket_medio
         FROM pedidos"
    );
    $metricas = $stmt_metricas->fetch();

    // Query 2: Busca os 10 últimos pedidos
    $stmt_pedidos = $pdo->query(
        "SELECT * FROM pedidos ORDER BY data_pedido DESC LIMIT 10"
    );
    $ultimos_pedidos = $stmt_pedidos->fetchAll();

} catch (\PDOException $e) {
    $erro = "Erro ao buscar os dados do relatório: " . $e->getMessage();
}

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Admin - Relatórios de Vendas</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0">Relatórios de Vendas</h1>
        <a href="admin_cupons.php" class="btn btn-info">Gerenciar Cupons</a>
        <a href="validacao_receitas.php" class="btn btn-primary">Ir para Validação de Receitas</a>
    </div>

    <?php if ($erro): ?>
        <div class="alert alert-danger"><?php echo $erro; ?></div>
    <?php else: ?>
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-header">Total de Pedidos</div>
                    <div class="card-body">
                        <h3 class="card-title"><?php echo $metricas['total_pedidos'] ?? 0; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center text-bg-success">
                    <div class="card-header">Faturamento Total</div>
                    <div class="card-body">
                        <h3 class="card-title">R$ <?php echo number_format($metricas['faturamento_total'] ?? 0, 2, ',', '.'); ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-header">Ticket Médio</div>
                    <div class="card-body">
                        <h3 class="card-title">R$ <?php echo number_format($metricas['ticket_medio'] ?? 0, 2, ',', '.'); ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <h3 class="mt-5">Últimos Pedidos Realizados</h3>
        <table class="table table-striped table-hover">
            <thead class="table-dark">
                <tr>
                    <th>ID do Pedido</th>
                    <th>ID do Usuário</th>
                    <th>Valor Total</th>
                    <th>Status</th>
                    <th>Data</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($ultimos_pedidos as $pedido): ?>
                <tr>
                    <td><?php echo $pedido['id']; ?></td>
                    <td><?php echo $pedido['usuario_id']; ?></td>
                    <td>R$ <?php echo number_format($pedido['valor_total'], 2, ',', '.'); ?></td>
                    <td><span class="badge bg-info"><?php echo htmlspecialchars($pedido['status_pedido']); ?></span></td>
                    <td><?php echo date('d/m/Y H:i', strtotime($pedido['data_pedido'])); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
</body>
</html>