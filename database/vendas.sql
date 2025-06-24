CREATE TABLE pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL, -- Este ID virá do serviço de usuários
    valor_total DECIMAL(10, 2),
    status_pedido VARCHAR(50) DEFAULT 'Pendente',
    data_pedido TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE pedido_itens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT,
    produto_id INT NOT NULL, -- Este ID virá do serviço de estoque
    produto_nome VARCHAR(150), -- Duplicamos o nome para histórico
    quantidade INT,
    preco_unitario DECIMAL(10, 2),
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE receitas_medicas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    pedido_id INT, -- Será preenchido quando o pedido for criado
    nome_arquivo_original VARCHAR(255) NOT NULL,
    path_arquivo_salvo VARCHAR(255) NOT NULL,
    status ENUM('pendente', 'aprovada', 'rejeitada') NOT NULL DEFAULT 'pendente',
    data_upload TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE cupons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(50) UNIQUE NOT NULL,
    tipo_desconto ENUM('percentual', 'fixo') NOT NULL,
    valor DECIMAL(10, 2) NOT NULL,
    data_validade DATE NOT NULL,
    usos_restantes INT NOT NULL DEFAULT 1,
    ativo BOOLEAN NOT NULL DEFAULT TRUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE pedidos
ADD COLUMN data_agendamento DATE NULL,
ADD COLUMN periodo_agendamento VARCHAR(50) NULL;
ALTER TABLE pedidos
ADD COLUMN metodo_pagamento VARCHAR(50) NULL AFTER status_pedido;