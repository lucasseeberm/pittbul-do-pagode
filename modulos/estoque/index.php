<?php
// /modulos/estoque/index.php - VERSÃO FINAL E ORGANIZADA

header('Content-Type: application/json');
require_once 'config/database.php';

$method = $_SERVER['REQUEST_METHOD'];
$requestUri = $_SERVER['REQUEST_URI'];

// ROTA: LER as avaliações de um produto (Ex: GET /produtos/1/avaliacoes)
if (preg_match('/\/produtos\/(\d+)\/avaliacoes/', $requestUri, $matches) && $method === 'GET') {
    $produto_id = $matches[1];
    try {
        $stmt = $pdo->prepare("SELECT * FROM avaliacoes WHERE produto_id = ? ORDER BY data_avaliacao DESC");
        $stmt->execute([$produto_id]);
        echo json_encode($stmt->fetchAll(), JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Erro ao buscar avaliações.']);
    }
    exit();
}

// ROTA: SALVAR uma nova avaliação (Ex: POST /produtos/1/avaliacoes)
if (preg_match('/\/produtos\/(\d+)\/avaliacoes/', $requestUri, $matches) && $method === 'POST') {
    $produto_id = $matches[1];
    $dados = json_decode(file_get_contents('php://input'), true);

    if (!isset($dados['usuario_id'], $dados['usuario_nome'], $dados['nota'], $dados['comentario'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Dados da avaliação incompletos.']);
        exit();
    }
    
    try {
        $sql = "INSERT INTO avaliacoes (produto_id, usuario_id, usuario_nome, nota, comentario) VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$produto_id, $dados['usuario_id'], $dados['usuario_nome'], $dados['nota'], $dados['comentario']]);
        http_response_code(201);
        echo json_encode(['message' => 'Avaliação salva com sucesso.']);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Erro ao salvar avaliação.']);
    }
    exit();
}

// ROTA: Buscar um produto específico pelo ID (Ex: GET /produtos/1)
// Usamos '$' no final para garantir que a URL termina com o número.
if (preg_match('/\/produtos\/(\d+)$/', $requestUri, $matches) && $method === 'GET') {
    $produtoId = $matches[1];
    try {
        $stmt = $pdo->prepare("SELECT * FROM produtos WHERE id = ?");
        $stmt->execute([$produtoId]);
        $produto = $stmt->fetch();
        if ($produto) {
            echo json_encode($produto, JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Produto não encontrado.']);
        }
    } catch (\PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Erro ao buscar o produto.']);
    }
    exit();
}

// ROTA: Listar todos os produtos (COM FILTRO E PESQUISA) (Ex: GET /produtos?search=...)
// Esta é a rota mais geral, por isso vem por último entre as de GET /produtos.
if (strpos($requestUri, '/produtos') === 0 && $method === 'GET') {
    $sql = "SELECT id, nome, descricao, preco, estoque, categoria, controlado, estoque_minimo FROM produtos";
    $where = [];
    $params = [];

    if (isset($_GET['search']) && !empty($_GET['search'])) {
        $where[] = "(nome LIKE ? OR descricao LIKE ?)";
        $params[] = '%' . $_GET['search'] . '%';
        $params[] = '%' . $_GET['search'] . '%';
    }
    if (isset($_GET['categoria']) && !empty($_GET['categoria'])) {
        $where[] = "categoria = ?";
        $params[] = $_GET['categoria'];
    }
    if (!empty($where)) {
        $sql .= " WHERE " . implode(" AND ", $where);
    }
    $sql .= " ORDER BY nome ASC";
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $produtos = $stmt->fetchAll();
        echo json_encode($produtos, JSON_UNESCAPED_UNICODE);
    } catch (\PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Erro ao buscar produtos.', 'details' => $e->getMessage()]);
    }
    exit();
}

// ROTA: Dar baixa no estoque após uma venda (Ex: POST /produtos/dar-baixa)
if ($requestUri === '/produtos/dar-baixa' && $method === 'POST') {
    $dados_json = file_get_contents('php://input');
    $payload = json_decode($dados_json, true);

    if (!isset($payload['pedido_id']) || !isset($payload['itens']) || empty($payload['itens'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Payload inválido para baixa no estoque.']);
        exit();
    }

    $pedido_id_venda = $payload['pedido_id'];
    $itens = $payload['itens'];

    $pdo->beginTransaction();
    try {
        $stmt_update = $pdo->prepare("UPDATE produtos SET estoque = estoque - ? WHERE id = ?");
        $stmt_log = $pdo->prepare("INSERT INTO movimentacoes_estoque (produto_id, tipo_movimentacao, quantidade_alterada, observacao) VALUES (?, ?, ?, ?)");
        $stmt_alerta = $pdo->prepare("INSERT INTO alertas_estoque (produto_id, mensagem, status) VALUES (?, ?, ?)");

        foreach ($itens as $item) {
            if (isset($item['id']) && isset($item['quantidade'])) {
                $stmt_update->execute([$item['quantidade'], $item['id']]);
                
                $observacao = "Saída para o Pedido #" . $pedido_id_venda;
                $quantidade_negativa = -1 * abs($item['quantidade']); 
                $stmt_log->execute([$item['id'], 'saida_venda', $quantidade_negativa, $observacao]);
                
                $stmt_check = $pdo->prepare("SELECT nome, estoque, estoque_minimo FROM produtos WHERE id = ?");
                $stmt_check->execute([$item['id']]);
                $produto_atualizado = $stmt_check->fetch();

                if ($produto_atualizado && $produto_atualizado['estoque'] <= $produto_atualizado['estoque_minimo']) {
                    $mensagem_alerta = "Produto '" . $produto_atualizado['nome'] . "' atingiu o estoque baixo. Quantidade atual: " . $produto_atualizado['estoque'];
                    $stmt_alerta->execute([$item['id'], $mensagem_alerta, 'novo']);
                }
            }
        }
        $pdo->commit();
        echo json_encode(['message' => 'Estoque atualizado e movimentação registrada com sucesso.']);
    } catch (Exception $e) {
        $pdo->rollBack();
        http_response_code(500);
        echo json_encode(['error' => 'Falha ao atualizar o estoque.', 'details' => $e->getMessage()]);
    }
    exit();
}

// Resposta padrão para rotas não encontradas
http_response_code(404);
echo json_encode(['error' => 'Endpoint não encontrado no serviço de estoque.']);