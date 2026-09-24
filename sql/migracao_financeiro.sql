-- MF Solar - instalação/garantia do módulo financeiro
CREATE TABLE IF NOT EXISTS contas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ordem_servico_id INT,
    cliente_id INT,
    tipo ENUM('receber','pagar') NOT NULL,
    descricao VARCHAR(255) NOT NULL,
    valor DECIMAL(10,2) NOT NULL,
    data_vencimento DATE,
    data_pagamento DATE,
    status ENUM('pendente','pago','atrasado','cancelado') DEFAULT 'pendente',
    forma_pagamento ENUM('dinheiro','pix','boleto','cartao_credito','cartao_debito','transferencia','cheque','outro'),
    parcela_atual INT DEFAULT 1,
    total_parcelas INT DEFAULT 1,
    observacoes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ordem_servico_id) REFERENCES ordens_servico(id),
    FOREIGN KEY (cliente_id) REFERENCES clientes(id),
    INDEX idx_conta_tipo_status (tipo,status),
    INDEX idx_conta_vencimento (data_vencimento)
);
