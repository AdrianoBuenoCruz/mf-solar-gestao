-- ============================================================
--  SOLAR GESTÃO - Sistema de Gestão para Energia Fotovoltaica
--  Schema MySQL completo
-- ============================================================

CREATE DATABASE IF NOT EXISTS solar_gestao CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE solar_gestao;

-- ============================================================
-- USUÁRIOS DO SISTEMA
-- ============================================================
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    perfil ENUM('admin','gerente','tecnico','vendedor','financeiro') DEFAULT 'vendedor',
    ativo TINYINT(1) DEFAULT 1,
    foto VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ============================================================
-- CLIENTES
-- ============================================================
CREATE TABLE clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo_pessoa ENUM('fisica','juridica') DEFAULT 'fisica',
    -- Pessoa Física
    nome VARCHAR(150),
    cpf VARCHAR(14),
    rg VARCHAR(20),
    data_nascimento DATE,
    -- Pessoa Jurídica
    razao_social VARCHAR(150),
    nome_fantasia VARCHAR(150),
    cnpj VARCHAR(18),
    inscricao_estadual VARCHAR(30),
    responsavel VARCHAR(100),
    -- Contato
    email VARCHAR(150),
    telefone VARCHAR(20),
    celular VARCHAR(20),
    whatsapp VARCHAR(20),
    -- Endereço
    cep VARCHAR(10),
    logradouro VARCHAR(200),
    numero VARCHAR(20),
    complemento VARCHAR(100),
    bairro VARCHAR(100),
    cidade VARCHAR(100),
    estado CHAR(2),
    -- Dados da Instalação
    distribuidora VARCHAR(100),
    numero_uc VARCHAR(50),
    classe_tarifaria ENUM('residencial','comercial','industrial','rural','poder_publico','iluminacao_publica'),
    tensao_rede ENUM('monofasico','bifasico','trifasico'),
    consumo_medio_kwh DECIMAL(10,2),
    demanda_contratada DECIMAL(10,2),
    coordenadas_lat DECIMAL(10,7),
    coordenadas_lng DECIMAL(10,7),
    area_disponivel DECIMAL(10,2),
    tipo_telhado ENUM('fibrocimento','ceramica','metalico','laje','outro'),
    orientacao_telhado VARCHAR(50),
    inclinacao_telhado DECIMAL(5,2),
    -- Documentos e obs
    observacoes TEXT,
    origem ENUM('indicacao','site','instagram','facebook','google','ligacao','outro'),
    ativo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_cpf (cpf),
    INDEX idx_cnpj (cnpj),
    INDEX idx_cidade (cidade)
);

CREATE TABLE clientes_documentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    tipo VARCHAR(100),
    arquivo VARCHAR(255),
    observacao TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE
);

-- ============================================================
-- CATEGORIAS E PRODUTOS (ESTOQUE)
-- ============================================================
CREATE TABLE categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    descricao TEXT,
    ativo TINYINT(1) DEFAULT 1
);

CREATE TABLE fornecedores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    razao_social VARCHAR(150) NOT NULL,
    nome_fantasia VARCHAR(150),
    cnpj VARCHAR(18),
    contato VARCHAR(100),
    email VARCHAR(150),
    telefone VARCHAR(20),
    celular VARCHAR(20),
    cep VARCHAR(10),
    logradouro VARCHAR(200),
    numero VARCHAR(20),
    cidade VARCHAR(100),
    estado CHAR(2),
    observacoes TEXT,
    ativo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE produtos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    categoria_id INT,
    fornecedor_id INT,
    codigo VARCHAR(50),
    nome VARCHAR(200) NOT NULL,
    descricao TEXT,
    marca VARCHAR(100),
    modelo VARCHAR(100),
    unidade VARCHAR(20) DEFAULT 'un',
    -- Dimensões/Especificações técnicas
    potencia_wp DECIMAL(10,2) COMMENT 'Para painéis',
    tensao_voc DECIMAL(10,2),
    corrente_isc DECIMAL(10,2),
    eficiencia DECIMAL(5,2),
    garantia_produto INT COMMENT 'anos',
    garantia_desempenho INT COMMENT 'anos',
    -- Estoque
    estoque_atual DECIMAL(10,2) DEFAULT 0,
    estoque_minimo DECIMAL(10,2) DEFAULT 0,
    estoque_maximo DECIMAL(10,2) DEFAULT 0,
    localizacao_estoque VARCHAR(100),
    -- Financeiro
    preco_custo DECIMAL(10,2),
    preco_venda DECIMAL(10,2),
    margem_padrao DECIMAL(5,2),
    -- Controle
    ativo TINYINT(1) DEFAULT 1,
    foto VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id),
    FOREIGN KEY (fornecedor_id) REFERENCES fornecedores(id),
    INDEX idx_codigo (codigo),
    INDEX idx_estoque_minimo (estoque_atual, estoque_minimo)
);

CREATE TABLE movimentacoes_estoque (
    id INT AUTO_INCREMENT PRIMARY KEY,
    produto_id INT NOT NULL,
    usuario_id INT,
    tipo ENUM('entrada','saida','ajuste','devolucao','transferencia') NOT NULL,
    quantidade DECIMAL(10,2) NOT NULL,
    estoque_anterior DECIMAL(10,2),
    estoque_posterior DECIMAL(10,2),
    preco_unitario DECIMAL(10,2),
    nota_fiscal VARCHAR(50),
    fornecedor_id INT,
    ordem_servico_id INT,
    observacao TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (produto_id) REFERENCES produtos(id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    FOREIGN KEY (fornecedor_id) REFERENCES fornecedores(id)
);

-- ============================================================
-- ORDENS DE SERVIÇO / PROJETOS
-- ============================================================
CREATE TABLE ordens_servico (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero VARCHAR(20) UNIQUE,
    cliente_id INT NOT NULL,
    usuario_responsavel_id INT,
    tecnico_id INT,
    status ENUM('orcamento','aprovado','em_andamento','instalado','vistoria','concluido','cancelado') DEFAULT 'orcamento',
    tipo ENUM('instalacao','manutencao','ampliacao','vistoria','outro') DEFAULT 'instalacao',
    -- Projeto
    potencia_projeto DECIMAL(10,2),
    quantidade_paineis INT,
    area_instalacao DECIMAL(10,2),
    geracao_estimada_kwh DECIMAL(10,2),
    -- Datas
    data_orcamento DATE,
    data_aprovacao DATE,
    data_instalacao_prevista DATE,
    data_instalacao_real DATE,
    data_vistoria DATE,
    data_conclusao DATE,
    -- Financeiro
    valor_total DECIMAL(10,2),
    valor_materiais DECIMAL(10,2),
    valor_servicos DECIMAL(10,2),
    desconto DECIMAL(10,2) DEFAULT 0,
    forma_pagamento VARCHAR(100),
    -- Obs
    observacoes TEXT,
    observacoes_tecnicas TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id),
    FOREIGN KEY (usuario_responsavel_id) REFERENCES usuarios(id),
    FOREIGN KEY (tecnico_id) REFERENCES usuarios(id)
);

CREATE TABLE os_itens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ordem_servico_id INT NOT NULL,
    produto_id INT,
    descricao VARCHAR(255),
    quantidade DECIMAL(10,2),
    preco_unitario DECIMAL(10,2),
    desconto DECIMAL(5,2) DEFAULT 0,
    total DECIMAL(10,2),
    FOREIGN KEY (ordem_servico_id) REFERENCES ordens_servico(id) ON DELETE CASCADE,
    FOREIGN KEY (produto_id) REFERENCES produtos(id)
);

-- ============================================================
-- PONTO ELETRÔNICO
-- ============================================================
CREATE TABLE registros_ponto (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    data DATE NOT NULL,
    entrada1 TIME,
    saida1 TIME,
    entrada2 TIME,
    saida2 TIME,
    entrada3 TIME,
    saida3 TIME,
    horas_trabalhadas DECIMAL(5,2),
    horas_extras DECIMAL(5,2) DEFAULT 0,
    tipo_dia ENUM('normal','feriado','folga','falta','meio_periodo') DEFAULT 'normal',
    justificativa TEXT,
    aprovado TINYINT(1) DEFAULT 0,
    aprovado_por INT,
    ip_registro VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    UNIQUE KEY uk_usuario_data (usuario_id, data)
);

CREATE TABLE batidas_ponto (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    tipo ENUM('entrada','saida') NOT NULL,
    data_hora DATETIME NOT NULL,
    ip VARCHAR(45),
    dispositivo VARCHAR(100),
    observacao VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    INDEX idx_usuario_data (usuario_id, data_hora)
);

-- ============================================================
-- FINANCEIRO
-- ============================================================
CREATE TABLE contas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ordem_servico_id INT,
    cliente_id INT,
    tipo ENUM('receber','pagar') NOT NULL,
    descricao VARCHAR(255),
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
    FOREIGN KEY (cliente_id) REFERENCES clientes(id)
);

-- ============================================================
-- DADOS INICIAIS
-- ============================================================
INSERT INTO categorias (nome, descricao) VALUES
('Painéis Solares', 'Módulos fotovoltaicos'),
('Inversores', 'Inversores string e micro inversores'),
('Estruturas', 'Estruturas de fixação e suportes'),
('Cabos e Conectores', 'Cabos solares, conectores MC4'),
('Proteção Elétrica', 'Disjuntores, fusíveis, aterramentos, DPS'),
('Monitoramento', 'Dataloggers e sistemas de monitoramento'),
('Baterias', 'Sistemas de armazenamento'),
('Ferramentas', 'Ferramentas e equipamentos de instalação'),
('EPI', 'Equipamentos de Proteção Individual');

INSERT INTO produtos (categoria_id, codigo, nome, unidade, potencia_wp, estoque_atual, estoque_minimo, preco_custo, preco_venda) VALUES
(1, 'PAI-550-MONO', 'Painel Solar 550Wp Monocristalino', 'un', 550, 48, 10, 580.00, 780.00),
(1, 'PAI-400-POLI', 'Painel Solar 400Wp Policristalino', 'un', 400, 20, 8, 420.00, 580.00),
(2, 'INV-5K-STR', 'Inversor String 5kW Monofásico', 'un', NULL, 8, 3, 1800.00, 2600.00),
(2, 'INV-10K-TRI', 'Inversor String 10kW Trifásico', 'un', NULL, 5, 2, 3200.00, 4500.00),
(3, 'EST-ALU-TELHADO', 'Estrutura Alumínio Telhado Cerâmico', 'kit', NULL, 15, 5, 280.00, 450.00),
(4, 'CAB-SOL-6MM', 'Cabo Solar 6mm² preto (rolo 100m)', 'rolo', NULL, 10, 3, 320.00, 480.00),
(5, 'DPS-CC-1000V', 'DPS Corrente Contínua 1000V', 'un', NULL, 20, 5, 85.00, 140.00);

-- ============================================================
-- PROPOSTAS COMERCIAIS
-- ============================================================
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
    FOREIGN KEY (ordem_servico_id) REFERENCES ordens_servico(id)
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
    FOREIGN KEY (produto_id) REFERENCES produtos(id)
);
