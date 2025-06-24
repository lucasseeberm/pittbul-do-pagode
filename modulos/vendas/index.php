<?php
// /modulos/vendas/index.php

header('Content-Type: application/json');
require_once 'config/database.php'; // Conecta ao $pdo de vendas

$method = $_SERVER['REQUEST_METHOD'];
$requestUri = $_SERVER['REQUEST_URI'];

// ROTA 1: Buscar todos os pedidos de um usuário específico
if (preg_match('/\/pedidos\/usuario\/(\d+)/', $requestUri, $matches) && $method === 'GET') {
    $usuario_id = $matches[1];
    try {
        $stmt = $pdo->prepare("SELECT * FROM pedidos WHERE usuario_id = ? ORDER BY data_pedido DESC");
        $stmt->execute([$usuario_id]);
        $pedidos = $stmt->fetchAll();
        echo json_encode($pedidos);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Erro ao buscar pedidos do usuário.']);
    }
    exit();
}

// ROTA 2: Buscar os itens de um pedido específico
if (preg_match('/\/pedidos\/itens\/(\d+)/', $requestUri, $matches) && $method === 'GET') {
    $pedido_id = $matches[1];
    try {
        $stmt = $pdo->prepare("SELECT produto_nome, quantidade, preco_unitario FROM pedido_itens WHERE pedido_id = ?");
        $stmt->execute([$pedido_id]);
        $itens = $stmt->fetchAll();
        echo json_encode($itens);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Erro ao buscar itens do pedido.']);
    }
    exit();
}

// ROTA 3: Criar um novo pedido e dar baixa no estoque
if ($requestUri === '/pedidos' && $method === 'POST') {
    $dados_json = file_get_contents('php://input');
    $dados = json_decode($dados_json, true);

    if (!isset($dados['usuario_id']) || !isset($dados['itens']) || empty($dados['itens'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Dados do pedido incompletos ou inválidos.']);
        exit();
    }

    $usuario_id = $dados['usuario_id'];
    $itens_pedido = $dados['itens'];
    $receita = $dados['receita'] ?? null;
    $data_agendamento = $dados['data_agendamento'] ?? null;
    $periodo_agendamento = $dados['periodo_agendamento'] ?? null;
    // --- ALTERAÇÃO 1: Pega o método de pagamento dos dados recebidos ---
    $metodo_pagamento = $dados['metodo_pagamento'] ?? 'N/A';

    $valor_total = 0;
    foreach($itens_pedido as $item) {
        if (isset($item['subtotal'])) {
            $valor_total += $item['subtotal'];
        }
    }

    $pdo->beginTransaction();
    try {
        $status_pedido = $receita ? 'Aguardando Validação' : 'Processando';
        
        // --- ALTERAÇÃO 2: Adiciona a coluna 'metodo_pagamento' no INSERT ---
        $stmt_pedido = $pdo->prepare(
            "INSERT INTO pedidos (usuario_id, valor_total, status_pedido, metodo_pagamento, data_agendamento, periodo_agendamento) VALUES (?, ?, ?, ?, ?, ?)"
        );
        // --- ALTERAÇÃO 3: Adiciona a variável $metodo_pagamento no execute() ---
        $stmt_pedido->execute([$usuario_id, $valor_total, $status_pedido, $metodo_pagamento, $data_agendamento, $periodo_agendamento]);
        
        $pedido_id = $pdo->lastInsertId();

        $stmt_item = $pdo->prepare("INSERT INTO pedido_itens (pedido_id, produto_id, produto_nome, quantidade, preco_unitario) VALUES (?, ?, ?, ?, ?)");
        $itens_para_baixa = [];
        foreach ($itens_pedido as $item) {
            $stmt_item->execute([$pedido_id, $item['id'], $item['nome'], $item['quantidade'], $item['preco']]);
            $itens_para_baixa[] = ['id' => $item['id'], 'quantidade' => $item['quantidade']];
        }

        if ($receita) {
            $stmt_receita = $pdo->prepare("INSERT INTO receitas_medicas (usuario_id, pedido_id, nome_arquivo_original, path_arquivo_salvo, status) VALUES (?, ?, ?, ?, ?)");
            $stmt_receita->execute([$usuario_id, $pedido_id, $receita['nome_original'], $receita['path_salvo'], 'pendente']);
        }
        
        $pdo->commit();
        
        $payload_estoque = ['pedido_id' => $pedido_id, 'itens' => $itens_para_baixa];
        $url_api_estoque = getenv('ESTOQUE_API_URL') . '/produtos/dar-baixa';
        $ch = curl_init($url_api_estoque);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload_estoque));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_exec($ch);
        curl_close($ch);

        http_response_code(201);
        echo json_encode(['message' => 'Pedido criado com sucesso!', 'pedido_id' => $pedido_id]);

    } catch (Exception $e) {
        $pdo->rollBack();
        http_response_code(500);
        echo json_encode(['error' => 'Erro ao processar o pedido.', 'details' => $e->getMessage()]);
    }
    exit();
}

// Rota para atualizar o status de um pedido (ex: aprovar/rejeitar receita)
if ($requestUri === '/pedidos/status' && $method === 'POST') {
    $dados_json = file_get_contents('php://input');
    $dados = json_decode($dados_json, true);

    if (!isset($dados['pedido_id']) || !isset($dados['novo_status'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Dados incompletos para atualizar status.']);
        exit();
    }

    $pedido_id = $dados['pedido_id'];
    $novo_status = $dados['novo_status'];

    try {
        $stmt = $pdo->prepare("UPDATE pedidos SET status_pedido = ? WHERE id = ?");
        $stmt->execute([$novo_status, $pedido_id]);

        echo json_encode(['message' => 'Status do pedido atualizado com sucesso.']);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Erro ao atualizar o status do pedido.']);
    }
    exit();
}

if (preg_match('/\/cupons\/validar\/([a-zA-Z0-9]+)/', $requestUri, $matches) && $method === 'GET') {
    $codigo_cupom = $matches[1];

    try {
        $stmt = $pdo->prepare("SELECT * FROM cupons WHERE codigo = ? AND ativo = TRUE AND data_validade >= CURDATE() AND usos_restantes > 0");
        $stmt->execute([$codigo_cupom]);
        $cupom = $stmt->fetch();

        if ($cupom) {
            // Se encontrou, retorna os dados do cupom
            echo json_encode($cupom);
        } else {
            // Se não, retorna erro 404
            http_response_code(404);
            echo json_encode(['error' => 'Cupom inválido, expirado ou esgotado.']);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Erro ao validar o cupom.']);
    }
    exit();
}


// Resposta padrão
http_response_code(404);
echo json_encode(['error' => 'Endpoint não encontrado no serviço de vendas.']);