<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

// --- LÓGICA DE CUPOM ---
$cupom_aplicado = null;
$mensagem_cupom = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao_cupom'])) {
    $codigo_cupom = trim($_POST['codigo_cupom'] ?? '');
    if (!empty($codigo_cupom)) {
        $url_api_cupom = getenv('VENDAS_API_URL') . "/cupons/validar/" . urlencode($codigo_cupom);
        $ch_cupom = curl_init($url_api_cupom);
        curl_setopt($ch_cupom, CURLOPT_RETURNTRANSFER, 1);
        $response_cupom = curl_exec($ch_cupom);
        $http_code_cupom = curl_getinfo($ch_cupom, CURLINFO_HTTP_CODE);
        curl_close($ch_cupom);
        if ($http_code_cupom == 200) {
            $_SESSION['cupom_aplicado'] = json_decode($response_cupom, true);
            $mensagem_cupom = "<div class='alert alert-success mt-2'>Cupom '" . htmlspecialchars($codigo_cupom) . "' aplicado com sucesso!</div>";
        } else {
            unset($_SESSION['cupom_aplicado']);
            $mensagem_cupom = "<div class='alert alert-danger mt-2'>Cupom inválido, expirado ou esgotado.</div>";
        }
    } else {
        unset($_SESSION['cupom_aplicado']);
    }
}

if (isset($_SESSION['cupom_aplicado'])) {
    $cupom_aplicado = $_SESSION['cupom_aplicado'];
}

// --- LÓGICA PARA BUSCAR DETALHES DOS PRODUTOS NO CARRINHO ---
$carrinho_session = $_SESSION['carrinho'] ?? [];
$itens_carrinho_detalhados = [];
$subtotal_carrinho = 0;
$carrinho_tem_controlado = false;

if (!empty($carrinho_session)) {
    foreach ($carrinho_session as $produto_id => $item) {
        $url_api_produto = getenv('ESTOQUE_API_URL') . "/produtos/" . $produto_id;
        $ch = curl_init($url_api_produto);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code == 200) {
            $produto_detalhes = json_decode($response, true);
            $subtotal = $produto_detalhes['preco'] * $item['quantidade'];
            $subtotal_carrinho += $subtotal;

            if ($produto_detalhes['controlado']) {
                $carrinho_tem_controlado = true;
            }
            $itens_carrinho_detalhados[] = [
                'id' => $produto_id,
                'nome' => $produto_detalhes['nome'],
                'preco' => $produto_detalhes['preco'],
                'quantidade' => $item['quantidade'],
                'subtotal' => $subtotal,
                'controlado' => $produto_detalhes['controlado']
            ];
        }
    }
}

// --- LÓGICA FINAL DE CÁLCULO DE TOTAL COM DESCONTO ---
$valor_desconto = 0;
if ($cupom_aplicado) {
    if ($cupom_aplicado['tipo_desconto'] == 'fixo') {
        $valor_desconto = $cupom_aplicado['valor'];
    } elseif ($cupom_aplicado['tipo_desconto'] == 'percentual') {
        $valor_desconto = ($subtotal_carrinho * $cupom_aplicado['valor']) / 100;
    }
}
$total_final = $subtotal_carrinho - $valor_desconto;
if ($total_final < 0) $total_final = 0;

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Meu Carrinho de Compras</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg bg-body-tertiary">
        <div class="container">
            <a class="navbar-brand" href="loja.php">Farmácia Digital</a>
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                 <li class="nav-item"><a class="nav-link" href="profile.php">Meu Perfil</a></li>
                 <li class="nav-item"><a class="nav-link" href="loja.php">Loja</a></li>
                 <li class="nav-item"><a class="nav-link active" href="carrinho.php">Carrinho <span class="badge bg-primary rounded-pill"><?php echo count($carrinho_session); ?></span></a></li>
                 <li class="nav-item"><a class="nav-link" href="meus_pedidos.php">Meus Pedidos</a></li>
            </ul>
            <a href="logout.php" class="btn btn-outline-danger">Sair</a>
        </div>
    </nav>

    <div class="container mt-5">
        <h1>Meu Carrinho de Compras</h1>
        <hr>
        <?php if (isset($_GET['status'])): ?>
            <div class="alert alert-danger">Ocorreu um erro. Verifique os dados e tente novamente.</div>
        <?php endif; ?>

        <?php if (!empty($itens_carrinho_detalhados)): ?>
            <table class="table align-middle">
                <thead><tr><th>Produto</th><th>Preço Unitário</th><th>Quantidade</th><th>Subtotal</th><th>Ações</th></tr></thead>
                <tbody>
                    <?php foreach ($itens_carrinho_detalhados as $item): ?>
                        <tr>
                            <td>
                                <?php echo htmlspecialchars($item['nome']); ?>
                                <?php if ($item['controlado']): ?><span class="badge bg-danger ms-2">Requer Receita</span><?php endif; ?>
                            </td>
                            <td>R$ <?php echo number_format($item['preco'], 2, ',', '.'); ?></td>
                            <td><?php echo $item['quantidade']; ?></td>
                            <td>R$ <?php echo number_format($item['subtotal'], 2, ',', '.'); ?></td>
                            <td><a href="remover_carrinho.php?produto_id=<?php echo $item['id']; ?>" class="btn btn-danger btn-sm">Remover</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="row mt-4">
                <div class="col-md-7">
                    <form action="finalizar_pedido.php" method="post" enctype="multipart/form-data">
                        <div class="card bg-light p-3 mb-4">
                            <h4>Dados de Pagamento (Simulação)</h4>
                            <div class="row">
                                <div class="col-md-12 mb-3"><label for="cartao_nome" class="form-label">Nome no Cartão</label><input type="text" class="form-control" name="cartao_nome" id="cartao_nome" required></div>
                                <div class="col-md-12 mb-3"><label for="cartao_numero" class="form-label">Número do Cartão</label><input type="text" class="form-control" name="cartao_numero" id="cartao_numero" placeholder="xxxx xxxx xxxx xxxx" required></div>
                                <div class="col-md-6 mb-3"><label for="cartao_validade" class="form-label">Validade (MM/AA)</label><input type="text" class="form-control" name="cartao_validade" id="cartao_validade" placeholder="MM/AA" required></div>
                                <div class="col-md-6 mb-3"><label for="cartao_cvv" class="form-label">CVV</label><input type="text" class="form-control" name="cartao_cvv" id="cartao_cvv" placeholder="xxx" required></div>
                            </div>
                        </div>
                        <div class="card bg-light p-3 mb-4">
                            <h4>Agendamento da Entrega</h4>
                            <div class="row">
                                <div class="col-md-6 mb-3"><label for="data_agendamento" class="form-label">Data</label><input type="date" class="form-control" name="data_agendamento" id="data_agendamento" min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" required></div>
                                <div class="col-md-6 mb-3"><label for="periodo_agendamento" class="form-label">Período</label><select name="periodo_agendamento" id="periodo_agendamento" class="form-select" required><option value="Manhã (08h-12h)">Manhã (08h-12h)</option><option value="Tarde (13h-18h)">Tarde (13h-18h)</option></select></div>
                            </div>
                        </div>
                         <?php if ($carrinho_tem_controlado): ?>
                        <div class="card bg-light p-3 mb-4">
                            <h4><span class="text-danger">Atenção:</span> Envio de Receita Obrigatório</h4>
                            <div class="mb-3"><label for="receita" class="form-label">Arquivo da Receita (PDF, JPG, PNG)</label><input class="form-control" type="file" id="receita" name="receita" required></div>
                        </div>
                        <?php endif; ?>
                        <div class="d-flex justify-content-between mt-4">
                            <a href="loja.php" class="btn btn-secondary">Continuar Comprando</a>
                            <button type="submit" class="btn btn-success btn-lg">Finalizar Pedido por R$ <?php echo number_format($total_final, 2, ',', '.'); ?></button>
                        </div>
                    </form>
                </div>

                <div class="col-md-5">
                    <div class="card p-3">
                        <h5>Cupom de Desconto</h5>
                        <form action="carrinho.php" method="POST" class="d-flex">
                            <input type="text" name="codigo_cupom" class="form-control me-2" placeholder="Digite seu código" value="<?php echo htmlspecialchars($cupom_aplicado['codigo'] ?? ''); ?>">
                            <button type="submit" name="acao_cupom" value="aplicar" class="btn btn-primary">Aplicar</button>
                        </form>
                        <?php echo $mensagem_cupom; ?>
                        <hr>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between"><span>Subtotal</span><strong>R$ <?php echo number_format($subtotal_carrinho, 2, ',', '.'); ?></strong></li>
                            <?php if ($valor_desconto > 0): ?>
                            <li class="list-group-item d-flex justify-content-between text-success"><span>Desconto (<?php echo htmlspecialchars($cupom_aplicado['codigo']); ?>)</span><strong>- R$ <?php echo number_format($valor_desconto, 2, ',', '.'); ?></strong></li>
                            <?php endif; ?>
                            <li class="list-group-item d-flex justify-content-between bg-light">
                                <h5 class="my-0">Total</h5>
                                <h5 class="my-0">R$ <?php echo number_format($total_final, 2, ',', '.'); ?></h5>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            
        <?php else: ?>
            <div class="alert alert-info">Seu carrinho está vazio.</div>
            <a href="loja.php" class="btn btn-primary">Ir para a Loja</a>
        <?php endif; ?>
    </div>
</body>
</html>