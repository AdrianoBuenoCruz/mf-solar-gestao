-- MF SOLAR - INSTALAÇÃO ÚNICA
-- Execute uma única vez no banco do sistema pelo phpMyAdmin.
-- Compatível tanto com instalação nova quanto com a primeira versão de Propostas.

CREATE TABLE IF NOT EXISTS propostas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero VARCHAR(30) NOT NULL UNIQUE,
    cliente_id INT NOT NULL,
    usuario_id INT,
    status ENUM('rascunho','enviada','aprovada','recusada','vencida','convertida') DEFAULT 'rascunho',
    titulo VARCHAR(150) DEFAULT 'Usina Fotovoltaica',
    data_proposta DATE NOT NULL,
    validade DATE,
    localidade VARCHAR(150),
    grupo_consumidor VARCHAR(30) DEFAULT 'Grupo B',
    modo_dimensionamento VARCHAR(30) DEFAULT 'kwh',
    consumo_mensal_kwh DECIMAL(12,2),
    tarifa_energia DECIMAL(10,4) DEFAULT 1.20,
    inclui_iluminacao TINYINT(1) DEFAULT 0,
    valor_iluminacao DECIMAL(10,2) DEFAULT 0,
    tusd_percentual DECIMAL(6,2) DEFAULT 41.51,
    simultaneidade_percentual DECIMAL(6,2) DEFAULT 30,
    tipo_conexao VARCHAR(30),
    tensao_rede VARCHAR(30),
    eficiencia_percentual DECIMAL(6,2) DEFAULT 80,
    hsp DECIMAL(6,2) DEFAULT 5.25,
    estado VARCHAR(50),
    cidade VARCHAR(100),
    fornecedor_nome VARCHAR(150),
    tipo_dimensionamento VARCHAR(50),
    painel_descricao VARCHAR(180),
    painel_potencia_wp DECIMAL(10,2),
    inversor_descricao VARCHAR(180),
    tipo_estrutura VARCHAR(100),
    potencia_kwp DECIMAL(10,2),
    quantidade_modulos INT,
    geracao_anual_kwh DECIMAL(12,2),
    geracao_mensal_kwh DECIMAL(12,2),
    area_necessaria_m2 DECIMAL(10,2),
    geracao_meses TEXT,
    consumo_meses TEXT,
    valor_equipamentos DECIMAL(12,2) DEFAULT 0,
    valor_servicos DECIMAL(12,2) DEFAULT 0,
    desconto DECIMAL(12,2) DEFAULT 0,
    valor_total DECIMAL(12,2) DEFAULT 0,
    entrada DECIMAL(12,2) DEFAULT 0,
    saldo DECIMAL(12,2) DEFAULT 0,
    condicoes_pagamento VARCHAR(255),
    itens_inclusos TEXT,
    observacoes TEXT,
    ordem_servico_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    FOREIGN KEY (ordem_servico_id) REFERENCES ordens_servico(id),
    INDEX idx_proposta_cliente (cliente_id),
    INDEX idx_proposta_status (status),
    INDEX idx_proposta_data (data_proposta)
);

CREATE TABLE IF NOT EXISTS proposta_itens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    proposta_id INT NOT NULL,
    produto_id INT,
    descricao VARCHAR(255) NOT NULL,
    quantidade DECIMAL(10,2) DEFAULT 1,
    unidade VARCHAR(20) DEFAULT 'un',
    preco_unitario DECIMAL(12,2) DEFAULT 0,
    total DECIMAL(12,2) DEFAULT 0,
    ordem INT DEFAULT 0,
    FOREIGN KEY (proposta_id) REFERENCES propostas(id) ON DELETE CASCADE,
    FOREIGN KEY (produto_id) REFERENCES produtos(id),
    INDEX idx_item_proposta (proposta_id)
);

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

-- Acrescenta os campos abaixo somente quando uma versão antiga de propostas já existe.
DELIMITER $$
DROP PROCEDURE IF EXISTS mf_add_column$$
CREATE PROCEDURE mf_add_column(IN p_table VARCHAR(64),IN p_column VARCHAR(64),IN p_definition TEXT)
BEGIN
    IF NOT EXISTS (SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=p_table AND COLUMN_NAME=p_column) THEN
        SET @mf_sql=CONCAT('ALTER TABLE `',p_table,'` ADD COLUMN `',p_column,'` ',p_definition);
        PREPARE mf_stmt FROM @mf_sql;
        EXECUTE mf_stmt;
        DEALLOCATE PREPARE mf_stmt;
    END IF;
END$$
DELIMITER ;

CALL mf_add_column('propostas','grupo_consumidor','VARCHAR(30) DEFAULT ''Grupo B''');
CALL mf_add_column('propostas','modo_dimensionamento','VARCHAR(30) DEFAULT ''kwh''');
CALL mf_add_column('propostas','consumo_mensal_kwh','DECIMAL(12,2) NULL');
CALL mf_add_column('propostas','tarifa_energia','DECIMAL(10,4) DEFAULT 1.20');
CALL mf_add_column('propostas','inclui_iluminacao','TINYINT(1) DEFAULT 0');
CALL mf_add_column('propostas','valor_iluminacao','DECIMAL(10,2) DEFAULT 0');
CALL mf_add_column('propostas','tusd_percentual','DECIMAL(6,2) DEFAULT 41.51');
CALL mf_add_column('propostas','simultaneidade_percentual','DECIMAL(6,2) DEFAULT 30');
CALL mf_add_column('propostas','tipo_conexao','VARCHAR(30) NULL');
CALL mf_add_column('propostas','tensao_rede','VARCHAR(30) NULL');
CALL mf_add_column('propostas','eficiencia_percentual','DECIMAL(6,2) DEFAULT 80');
CALL mf_add_column('propostas','hsp','DECIMAL(6,2) DEFAULT 5.25');
CALL mf_add_column('propostas','estado','VARCHAR(50) NULL');
CALL mf_add_column('propostas','cidade','VARCHAR(100) NULL');
CALL mf_add_column('propostas','fornecedor_nome','VARCHAR(150) NULL');
CALL mf_add_column('propostas','tipo_dimensionamento','VARCHAR(50) NULL');
CALL mf_add_column('propostas','painel_descricao','VARCHAR(180) NULL');
CALL mf_add_column('propostas','painel_potencia_wp','DECIMAL(10,2) NULL');
CALL mf_add_column('propostas','inversor_descricao','VARCHAR(180) NULL');
DROP PROCEDURE IF EXISTS mf_add_column;
