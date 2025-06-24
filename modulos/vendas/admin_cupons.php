<?php
require_once 'config/database.php';

// Lógica para salvar um novo cupom
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['codigo'])) {
    try {
        $sql = "INSERT INTO cupons (codigo, tipo_desconto, valor, data_validade, usos_restantes) VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $_POST['codigo'],
            $_POST['tipo_desconto'],
            $_POST['valor'],
            $_POST['data_validade'],
            $_POST['usos_restantes']
        ]);
        $mensagem_sucesso = "Cupom criado com sucesso!";
    } catch (\PDOException $e) {
        $mensagem_erro = "Erro ao criar cupom: " . $e->getMessage();
    }
}

// Lógica para buscar os cupons existentes
$cupons = $pdo->query("SELECT * FROM cupons ORDER BY id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Admin - Gerenciar Cupons</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center">
        <h1>Gerenciar Cupons de Desconto</h1>
        <a href="relatorios.php" class="btn btn-secondary">Ver Relatórios</a>
    </div>
    <hr>
    <div class="card mb-4">
        <div class="card-header">Criar Novo Cupom</div>
        <div class="card-body">
            <?php if(isset($mensagem_sucesso)) echo "<div class='alert alert-success'>$mensagem_sucesso</div>"; ?>
            <?php if(isset($mensagem_erro)) echo "<div class='alert alert-danger'>$mensagem_erro</div>"; ?>
            <form action="admin_cupons.php" method="POST">
                <div class="row">
                    <div class="col-md-3"><input type="text" name="codigo" class="form-control" placeholder="Código (ex: NATAL20)" required></div>
                    <div class="col-md-3">
                        <select name="tipo_desconto" class="form-select">
                            <option value="fixo">Valor Fixo (R$)</option>
                            <option value="percentual">Percentual (%)</option>
                        </select>
                    </div>
                    <div class="col-md-2"><input type="number" step="0.01" name="valor" class="form-control" placeholder="Valor" required></div>
                    <div class="col-md-2"><input type="date" name="data_validade" class="form-control" required></div>
                    <div class="col-md-1"><input type="number" name="usos_restantes" class="form-control" placeholder="Usos" value="1" required></div>
                    <div class="col-md-1"><button type="submit" class="btn btn-primary w-100">Criar</button></div>
                </div>
            </form>
        </div>
    </div>

    <table class="table table-bordered">
        <thead><tr><th>ID</th><th>Código</th><th>Tipo</th><th>Valor</th><th>Validade</th><th>Usos Restantes</th><th>Ativo</th></tr></thead>
        <tbody>
            <?php foreach($cupons as $cupom): ?>
            <tr>
                <td><?php echo $cupom['id']; ?></td>
                <td><?php echo htmlspecialchars($cupom['codigo']); ?></td>
                <td><?php echo $cupom['tipo_desconto']; ?></td>
                <td><?php echo $cupom['tipo_desconto'] == 'fixo' ? 'R$ ' : ''; ?><?php echo number_format($cupom['valor'], 2, ',', '.'); ?><?php echo $cupom['tipo_desconto'] == 'percentual' ? '%' : ''; ?></td>
                <td><?php echo date('d/m/Y', strtotime($cupom['data_validade'])); ?></td>
                <td><?php echo $cupom['usos_restantes']; ?></td>
                <td><?php echo $cupom['ativo'] ? 'Sim' : 'Não'; ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>