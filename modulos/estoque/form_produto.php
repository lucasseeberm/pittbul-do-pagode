<?php
require_once 'config/database.php';

$produto = [
    'id' => '', 'nome' => '', 'descricao' => '', 'preco' => '', 
    'estoque' => '', 'categoria' => '', 'principio_ativo' => '', 'controlado' => 0
];
$titulo = "Adicionar Novo Produto";

// Se um ID for passado pela URL, estamos em modo de edição
if (isset($_GET['id'])) {
    $titulo = "Editar Produto";
    try {
        $stmt = $pdo->prepare("SELECT * FROM produtos WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        $produto = $stmt->fetch();
        if (!$produto) {
            die("Produto não encontrado!");
        }
    } catch (\PDOException $e) {
        die("Erro ao buscar o produto: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title><?php echo $titulo; ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <h1><?php echo $titulo; ?></h1>
    <hr>
    <form action="salvar_produto.php" method="post">
        <input type="hidden" name="id" value="<?php echo $produto['id']; ?>">

        <div class="row">
            <div class="col-md-8 mb-3">
                <label for="nome" class="form-label">Nome do Produto</label>
                <input type="text" class="form-control" id="nome" name="nome" value="<?php echo htmlspecialchars($produto['nome']); ?>" required>
            </div>
            <div class="col-md-4 mb-3">
                <label for="preco" class="form-label">Preço (R$)</label>
                <input type="number" step="0.01" class="form-control" id="preco" name="preco" value="<?php echo htmlspecialchars($produto['preco']); ?>" required>
            </div>
        </div>
        <div class="mb-3">
            <label for="descricao" class="form-label">Descrição</label>
            <textarea class="form-control" id="descricao" name="descricao" rows="3"><?php echo htmlspecialchars($produto['descricao']); ?></textarea>
        </div>
        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="estoque" class="form-label">Quantidade em Estoque</label>
                <input type="number" class="form-control" id="estoque" name="estoque" value="<?php echo htmlspecialchars($produto['estoque']); ?>" required>
            </div>
            <div class="col-md-4 mb-3">
                <label for="categoria" class="form-label">Categoria</label>
                <input type="text" class="form-control" id="categoria" name="categoria" value="<?php echo htmlspecialchars($produto['categoria']); ?>">
            </div>
             <div class="col-md-4 mb-3">
                <label for="principio_ativo" class="form-label">Princípio Ativo</label>
                <input type="text" class="form-control" id="principio_ativo" name="principio_ativo" value="<?php echo htmlspecialchars($produto['principio_ativo']); ?>">
            </div>
        </div>
        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="controlado" id="controlado" value="1" <?php echo $produto['controlado'] ? 'checked' : ''; ?>>
            <label class="form-check-label" for="controlado">
                É um medicamento controlado?
            </label>
        </div>

        <button type="submit" class="btn btn-primary">Salvar</button>
        <a href="admin.php" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
</body>
</html>