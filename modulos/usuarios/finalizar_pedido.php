<?php
session_start();

// --- ETAPA 1: COLETA DE TODOS OS DADOS DO FORMULÁRIO ---
$data_agendamento = $_POST['data_agendamento'] ?? null;
$periodo_agendamento = $_POST['periodo_agendamento'] ?? null;
$cartao_nome = trim($_POST['cartao_nome'] ?? '');
$cartao_numero = trim($_POST['cartao_numero'] ?? '');
$cartao_validade = trim($_POST['cartao_validade'] ?? '');
$cartao_cvv = trim($_POST['cartao_cvv'] ?? '');


// --- ETAPA 2: VALIDAÇÕES INICIAIS ---
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

if (empty($_SESSION['carrinho'])) {
    header('Location: carrinho.php');
    exit();
}


// --- ETAPA 3: SIMULAÇÃO DE VALIDAÇÃO DE PAGAMENTO ---
// Verificamos se todos os campos do "cartão" foram preenchidos.
if (empty($cartao_nome) || empty($cartao_numero) || empty($cartao_validade) || empty($cartao_cvv)) {
    // Se algum campo estiver vazio, recusa o pagamento e volta para o carrinho.
    header('Location: carrinho.php?status=pagamento_recusado');
    exit();
}
// Se a validação passou, o fluxo continua...


// --- ETAPA 4: PROCESSAMENTO DO UPLOAD DA RECEITA (se houver) ---
$dados_receita = null;
if (isset($_FILES['receita']) && $_FILES['receita']['error'] !== UPLOAD_ERR_NO_FILE) {
    // ----> INÍCIO DA SEÇÃO DE VERIFICAÇÃO DE ERRO <----
    if ($_FILES['receita']['error'] !== UPLOAD_ERR_OK) {
        $upload_errors = array(
            UPLOAD_ERR_INI_SIZE   => "O arquivo excede o limite de tamanho definido no servidor (upload_max_filesize).",
            UPLOAD_ERR_FORM_SIZE  => "O arquivo excede o limite de tamanho definido no formulário.",
            UPLOAD_ERR_PARTIAL    => "O upload do arquivo foi feito apenas parcialmente.",
            UPLOAD_ERR_NO_TMP_DIR => "Faltando uma pasta temporária no servidor.",
            UPLOAD_ERR_CANT_WRITE => "Falha ao escrever o arquivo no disco.",
            UPLOAD_ERR_EXTENSION  => "Uma extensão do PHP interrompeu o upload do arquivo.",
        );
        $error_message = $upload_errors[$_FILES['receita']['error']] ?? "Ocorreu um erro desconhecido no upload.";
        // Redireciona com uma mensagem de erro clara
        header('Location: carrinho.php?status=erro_upload&msg=' . urlencode($error_message));
        exit();
    }
    // ----> FIM DA SEÇÃO DE VERIFICAÇÃO DE ERRO <----
    
    $receita_info = $_FILES['receita'];
    
    $allowed_types = ['image/jpeg', 'image/png', 'application/pdf'];
    if (!in_array($receita_info['type'], $allowed_types)) {
        header('Location: carrinho.php?status=erro_tipo_arquivo');
        exit();
    }

    $upload_dir = 'uploads/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    $nome_original = $receita_info['name'];
    $extensao = pathinfo($nome_original, PATHINFO_EXTENSION);
    $nome_unico = uniqid('receita_', true) . '.' . $extensao;
    $caminho_salvo_local = $upload_dir . $nome_unico;

    if (move_uploaded_file($receita_info['tmp_name'], $caminho_salvo_local)) {
        $base_url = getenv('APP_BASE_URL'); 
        $url_completa = $base_url . '/' . $caminho_salvo_local;
        $dados_receita = [
            'nome_original' => $nome_original,
            'path_salvo' => $url_completa
        ];
    } else {
        header('Location: carrinho.php?status=erro_mover_arquivo');
        exit();
    }
}


// --- ETAPA 5: COLETA DOS DETALHES DOS PRODUTOS ---
$carrinho_session = $_SESSION['carrinho'];
$itens_para_api = [];
foreach ($carrinho_session as $produto_id => $item) {
    $url_api_produto = getenv('ESTOQUE_API_URL') . "/produtos/" . $produto_id;
    $ch_prod = curl_init($url_api_produto);
    curl_setopt($ch_prod, CURLOPT_RETURNTRANSFER, 1);
    $response_prod = curl_exec($ch_prod);
    $http_code_prod = curl_getinfo($ch_prod, CURLINFO_HTTP_CODE);
    curl_close($ch_prod);

    if ($http_code_prod == 200) {
        $produto_detalhes = json_decode($response_prod, true);
        $itens_para_api[] = [
            'id' => $produto_id,
            'nome' => $produto_detalhes['nome'],
            'preco' => $produto_detalhes['preco'],
            'quantidade' => $item['quantidade'],
            'subtotal' => $produto_detalhes['preco'] * $item['quantidade']
        ];
    }
}


// --- ETAPA 6: PREPARAÇÃO DO PACOTE DE DADOS FINAL PARA A API DE VENDAS ---
$dados_pedido = [
    'usuario_id' => $_SESSION['usuario_id'],
    'itens' => $itens_para_api,
    'receita' => $dados_receita,
    'data_agendamento' => $data_agendamento,
    'periodo_agendamento' => $periodo_agendamento,
    'metodo_pagamento' => 'Cartão de Crédito (Simulado)'
];

$dados_json = json_encode($dados_pedido);


// --- ETAPA 7: CHAMADA PARA A API DE VENDAS ---
$url_api_vendas = getenv('VENDAS_API_URL') . '/pedidos';
$ch = curl_init($url_api_vendas);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $dados_json);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Content-Length: ' . strlen($dados_json)
]);

$response_vendas = curl_exec($ch);
$http_code_vendas = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);


// --- ETAPA 8: TRATAMENTO DA RESPOSTA E REDIRECIONAMENTO ---
if ($http_code_vendas == 201) {
    unset($_SESSION['carrinho']);
    $resposta_api = json_decode($response_vendas, true);
    $pedido_id = $resposta_api['pedido_id'] ?? 'novo';
    header('Location: pedido_sucesso.php?pedido_id=' . $pedido_id);
    exit();
} else {
    header('Location: carrinho.php?status=erro_finalizar');
    exit();
}