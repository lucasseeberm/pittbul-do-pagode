<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['usuario_id'])) {
    header('Location: loja.php');
    exit();
}

$produto_id = $_POST['produto_id'];

// Prepara o payload para a API de Estoque
$payload = json_encode([
    'usuario_id' => $_SESSION['usuario_id'],
    'usuario_nome' => $_SESSION['usuario_nome'], // Pegamos o nome da sessão
    'nota' => $_POST['nota'],
    'comentario' => $_POST['comentario']
]);

// Chama a API para salvar a avaliação
$url_api = getenv('ESTOQUE_API_URL') . "/produtos/" . $produto_id . "/avaliacoes";
$ch = curl_init($url_api);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_exec($ch);
curl_close($ch);

// Redireciona de volta para a página do produto
header('Location: produto.php?id=' . $produto_id);
exit();