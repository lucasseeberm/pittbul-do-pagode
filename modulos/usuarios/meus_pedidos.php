<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$pedidos = [];

// 1. Primeira chamada à API: Busca a lista de todos os pedidos do usuário
$url_api_pedidos = getenv('VENDAS_API_URL') . "/pedidos/usuario/" . $usuario_id;
$ch = curl_init($url_api_pedidos);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$response_pedidos = curl_exec($ch);
$http_code_pedidos = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code_pedidos == 200) {
    $pedidos = json_decode($response_pedidos, true);

    // --- INÍCIO DA MODIFICAÇÃO ---

    // 2. Para cada pedido encontrado, busca seus itens específicos
    // Usamos o '&' antes de $pedido para modificar o array original diretamente
    foreach ($pedidos as &$pedido) {
        $pedido_id = $pedido['id'];
        $url_api_itens = getenv('VENDAS_API_URL') . "/pedidos/itens/" . $pedido_id;

        $ch_itens = curl_init($url_api_itens);
        curl_setopt($ch_itens, CURLOPT_RETURNTRANSFER, 1);
        $response_itens = curl_exec($ch_itens);
        curl_close($ch_itens);

        // Adiciona os itens encontrados ao array do pedido correspondente
        $pedido['itens'] = json_decode($response_itens, true);
    }
    unset($pedido); // Boa prática ao usar referências em loops

    // --- FIM DA MODIFICAÇÃO ---
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Meus Pedidos</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg bg-body-tertiary">
        <div class="container">
            <a class="navbar-brand" href="#">Farmácia Digital</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="profile.php">Meu Perfil</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="loja.php">Loja</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="carrinho.php">Carrinho</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="meus_pedidos.php">Meus Pedidos</a>
                    </li>
                </ul>
            </div>
            <a href="logout.php" class="btn btn-outline-danger">Sair</a>
        </div>
    </nav>

    <div class="container mt-5">
        <h1>Meus Pedidos</h1>
        <hr>
        <?php if (!empty($pedidos)): ?>
            <div class="accordion" id="accordionPedidos">
                <?php foreach ($pedidos as $pedido): ?>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="heading<?php echo $pedido['id']; ?>">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $pedido['id']; ?>">
                                Pedido #<?php echo $pedido['id']; ?> - 
                                Data: <?php echo date('d/m/Y H:i', strtotime($pedido['data_pedido'])); ?> - 
                                Status: <?php echo htmlspecialchars($pedido['status_pedido']); ?>
                                <?php if (!empty($pedido['data_agendamento'])): ?>
                                    <strong class="ms-3">Entrega Agendada: <?php echo date('d/m/Y', strtotime($pedido['data_agendamento'])) . ' (' . htmlspecialchars($pedido['periodo_agendamento']) . ')'; ?></strong>
                                <?php endif; ?>
                                Total: R$ <?php echo number_format($pedido['valor_total'], 2, ',', '.'); ?>
                            </button>
                        </h2>
                        <div id="collapse<?php echo $pedido['id']; ?>" class="accordion-collapse collapse" data-bs-parent="#accordionPedidos">
                            <div class="accordion-body">
                                
                                <h5>Itens deste Pedido:</h5>
                                <?php if (!empty($pedido['itens'])): ?>
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Produto</th>
                                                <th>Quantidade</th>
                                                <th>Preço Unitário</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($pedido['itens'] as $item): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($item['produto_nome']); ?></td>
                                                <td><?php echo $item['quantidade']; ?></td>
                                                <td>R$ <?php echo number_format($item['preco_unitario'], 2, ',', '.'); ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                <?php else: ?>
                                    <p>Não foi possível carregar os itens deste pedido.</p>
                                <?php endif; ?>
                                </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php elseif ($http_code_pedidos != 200): ?>
             <div class="alert alert-danger">
                Houve um erro ao buscar seu histórico de pedidos. Por favor, tente novamente mais tarde.
            </div>
        <?php else: ?>
            <div class="alert alert-info">Você ainda não realizou nenhum pedido.</div>
        <?php endif; ?>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>