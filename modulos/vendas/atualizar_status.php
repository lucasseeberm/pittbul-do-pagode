<?php
// Verifica se os dados foram enviados via POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['pedido_id']) || !isset($_POST['novo_status'])) {
    header('Location: validacao_receitas.php');
    exit();
}

$pedido_id = $_POST['pedido_id'];
$novo_status = $_POST['novo_status'];

// Prepara os dados para enviar para a API
$payload = json_encode([
    'pedido_id' => $pedido_id,
    'novo_status' => $novo_status
]);

// Chama a própria API interna do serviço de Vendas para atualizar o status
$url_api = 'http://localhost/pedidos/status'; // 'localhost' aqui funciona pois está no mesmo container

$ch = curl_init($url_api);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_exec($ch);
curl_close($ch);

// Redireciona de volta para a página de validação com uma mensagem de sucesso
header('Location: validacao_receitas.php?status=success');
exit();