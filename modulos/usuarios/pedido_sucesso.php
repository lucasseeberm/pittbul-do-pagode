<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

$pedido_id = isset($_GET['pedido_id']) ? htmlspecialchars($_GET['pedido_id']) : 'desconhecido';

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Pedido Realizado com Sucesso!</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5 text-center">
        <div class="alert alert-success" role="alert">
            <h4 class="alert-heading">Obrigado pela sua compra!</h4>
            <p>Seu pedido foi realizado com sucesso e já está sendo processado.</p>
            <hr>
            <p class="mb-0">O número do seu pedido é: <strong><?php echo $pedido_id; ?></strong></p>
        </div>
        <a href="loja.php" class="btn btn-primary mt-3">Continuar Comprando</a>
        <a href="meus_pedidos.php" class="btn btn-secondary mt-3">Ver Meus Pedidos</a> </div>
</body>
</html>