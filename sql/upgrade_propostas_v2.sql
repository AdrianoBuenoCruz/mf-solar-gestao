-- Execute somente se a primeira versão do módulo de propostas já foi instalada.
-- MySQL/MariaDB: adiciona os campos do assistente de dimensionamento.
ALTER TABLE propostas
  ADD COLUMN grupo_consumidor VARCHAR(30) DEFAULT 'Grupo B' AFTER localidade,
  ADD COLUMN modo_dimensionamento VARCHAR(30) DEFAULT 'kwh' AFTER grupo_consumidor,
  ADD COLUMN consumo_mensal_kwh DECIMAL(12,2) NULL AFTER modo_dimensionamento,
  ADD COLUMN tarifa_energia DECIMAL(10,4) DEFAULT 1.20 AFTER consumo_mensal_kwh,
  ADD COLUMN inclui_iluminacao TINYINT(1) DEFAULT 0 AFTER tarifa_energia,
  ADD COLUMN valor_iluminacao DECIMAL(10,2) DEFAULT 0 AFTER inclui_iluminacao,
  ADD COLUMN tusd_percentual DECIMAL(6,2) DEFAULT 41.51 AFTER valor_iluminacao,
  ADD COLUMN simultaneidade_percentual DECIMAL(6,2) DEFAULT 30 AFTER tusd_percentual,
  ADD COLUMN tipo_conexao VARCHAR(30) NULL AFTER simultaneidade_percentual,
  ADD COLUMN tensao_rede VARCHAR(30) NULL AFTER tipo_conexao,
  ADD COLUMN eficiencia_percentual DECIMAL(6,2) DEFAULT 80 AFTER tensao_rede,
  ADD COLUMN hsp DECIMAL(6,2) DEFAULT 5.25 AFTER eficiencia_percentual,
  ADD COLUMN estado VARCHAR(50) NULL AFTER hsp,
  ADD COLUMN cidade VARCHAR(100) NULL AFTER estado,
  ADD COLUMN fornecedor_nome VARCHAR(150) NULL AFTER cidade,
  ADD COLUMN tipo_dimensionamento VARCHAR(50) NULL AFTER fornecedor_nome,
  ADD COLUMN painel_descricao VARCHAR(180) NULL AFTER tipo_dimensionamento,
  ADD COLUMN painel_potencia_wp DECIMAL(10,2) NULL AFTER painel_descricao,
  ADD COLUMN inversor_descricao VARCHAR(180) NULL AFTER painel_potencia_wp;
