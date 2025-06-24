<?php
require_once 'config/database.php';

$pedidos_pendentes = [];
try {
    // Busca pedidos que estão aguardando validação e que possuem uma receita associada
    $stmt = $pdo->query(
        "SELECT p.id, p.data_pedido, r.path_arquivo_salvo 
         FROM pedidos p
         JOIN receitas_medicas r ON p.id = r.pedido_id
         WHERE p.status_pedido = 'Aguardando Validação'
         ORDER BY p.data_pedido ASC"
    );
    $pedidos_pendentes = $stmt->fetchAll();
} catch (\PDOException $e) {
    $erro = "Erro ao buscar pedidos pendentes: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Admin - Validação de Receitas</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="mb-0">Validação de Receitas Pendentes</h1>
        <a href="relatorios.php" class="btn btn-secondary">Ver Relatórios de Vendas</a>
    </div>
    <p>Pedidos que necessitam de aprovação da receita médica para serem processados.</p>
    <hr>
    <?php if (isset($_GET['status']) && $_GET['status'] == 'success'): ?>
        <div class="alert alert-success">Status do pedido atualizado com sucesso!</div>
    <?php endif; ?>

    <table class="table table-bordered">
        <thead class="table-light">
            <tr>
                <th>ID do Pedido</th>
                <th>Data</th>
                <th>Receita</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($pedidos_pendentes as $pedido): ?>
            <tr>
                <td><?php echo $pedido['id']; ?></td>
                <td><?php echo date('d/m/Y H:i', strtotime($pedido['data_pedido'])); ?></td>
                <td>
                    <a href="<?php echo $pedido['path_arquivo_salvo']; ?>" target="_blank" class="btn btn-info btn-sm">Ver Receita</a>
                </td>
                <td>
                    <form action="atualizar_status.php" method="POST" style="display: inline-block;">
                        <input type="hidden" name="pedido_id" value="<?php echo $pedido['id']; ?>">
                        <input type="hidden" name="novo_status" value="Processando">
                        <button type="submit" class="btn btn-success btn-sm">Aprovar</button>
                    </form>
                    <form action="atualizar_status.php" method="POST" style="display: inline-block;">
                        <input type="hidden" name="pedido_id" value="<?php echo $pedido['id']; ?>">
                        <input type="hidden" name="novo_status" value="Rejeitado">
                        <button type="submit" class="btn btn-danger btn-sm">Rejeitar</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($pedidos_pendentes)): ?>
                <tr><td colspan="4" class="text-center">Nenhum pedido aguardando validação.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
</body>
</html>