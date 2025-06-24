CREATE TABLE produtos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    descricao TEXT,
    preco DECIMAL(10, 2) NOT NULL,
    estoque INT NOT NULL,
    estoque_minimo INT DEFAULT 5,
    categoria VARCHAR(50),
    principio_ativo VARCHAR(100),
    controlado BOOLEAN DEFAULT FALSE,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE movimentacoes_estoque (
    id INT AUTO_INCREMENT PRIMARY KEY,
    produto_id INT NOT NULL,
    tipo_movimentacao VARCHAR(50) NOT NULL, -- ex: 'saida_venda', 'entrada_manual'
    quantidade_alterada INT NOT NULL, -- ex: -2 para uma venda, +10 para uma entrada
    observacao VARCHAR(255), -- ex: 'Venda referente ao Pedido #3'
    data_movimentacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (produto_id) REFERENCES produtos(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE alertas_estoque (
    id INT AUTO_INCREMENT PRIMARY KEY,
    produto_id INT NOT NULL,
    mensagem VARCHAR(255) NOT NULL,
    status VARCHAR(50) NOT NULL DEFAULT 'novo',
    data_alerta TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (produto_id) REFERENCES produtos(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE avaliacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    produto_id INT NOT NULL,
    usuario_id INT NOT NULL,
    usuario_nome VARCHAR(100) NOT NULL, -- Duplicamos o nome para não precisar chamar a API de usuários
    nota INT NOT NULL, -- Nota de 1 a 5
    comentario TEXT,
    data_avaliacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (produto_id) REFERENCES produtos(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;




INSERT INTO produtos (nome, descricao, preco, estoque, categoria, principio_ativo, controlado) VALUES
('Dipirona 500mg', 'Analgésico e antitérmico.', 15.50, 100, 'Analgésicos', 'Dipirona Sódica', FALSE),
('Amoxicilina 500mg', 'Antibiótico para infecções bacterianas.', 45.00, 50, 'Antibióticos', 'Amoxicilina', TRUE),
('Paracetamol 750mg', 'Indicado para o alívio da dor e febre.', 12.00, 200, 'Analgésicos', 'Paracetamol', FALSE),
('Losartana Potássica 50mg', 'Anti-hipertensivo.', 25.80, 80, 'Cardiovascular', 'Losartana', TRUE);