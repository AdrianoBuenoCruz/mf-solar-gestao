<div align="center">

# ☀️ MF Solar Gestão

### Da proposta à instalação: a operação solar em um só lugar.

Sistema web para apoiar a gestão comercial e administrativa de uma empresa de energia fotovoltaica.

![Status: concluído](https://img.shields.io/badge/status-conclu%C3%ADdo-16803c?style=flat-square)
![PHP 8+](https://img.shields.io/badge/PHP-8%2B-777bb4?style=flat-square&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/banco-MySQL-4479a1?style=flat-square&logo=mysql&logoColor=white)

**Desenvolvimento e autoria: [Adriano Bueno](https://github.com/AdrianoBuenoCruz)**

</div>

---

## Sobre o projeto

O **MF Solar Gestão** reúne informações de clientes, dimensionamento e propostas de sistemas fotovoltaicos, ordens de serviço, estoque e financeiro. A ideia é acompanhar o trabalho desde o primeiro atendimento até a execução e a administração do serviço, com os dados organizados em um mesmo sistema.

> **Status do desenvolvimento:** concluído. Para utilizar o sistema, é necessário configurar um servidor PHP e um banco MySQL/MariaDB. Este repositório contém o código-fonte; o GitHub não executa a aplicação PHP.

## Como o sistema se encaixa na operação

1. **Cadastre o cliente** e os dados do local da instalação.
2. **Monte a proposta** com dimensionamento, estimativas de geração, equipamentos e valores.
3. **Apresente a proposta** em um modelo preparado para impressão ou para salvar em PDF pelo navegador.
4. **Converta a proposta aprovada em ordem de serviço** e acompanhe sua execução.
5. **Controle a operação** com estoque, contas, registros de ponto, relatórios e indicadores no painel.

## Funcionalidades

| Área | O que oferece |
| --- | --- |
| **Painel** | Visão de ordens de serviço, indicadores do mês, gráficos e produtos com estoque crítico. |
| **Clientes** | Cadastro de pessoas físicas e jurídicas, contato, endereço e dados técnicos da instalação. |
| **Propostas** | Dimensionamento fotovoltaico, estimativa de geração, itens, valores, impressão e conversão em ordem de serviço. |
| **Ordens de serviço** | Registro e acompanhamento do serviço, seus itens, dados técnicos e status. |
| **Estoque** | Cadastro de produtos, movimentações e comparação com o estoque mínimo. |
| **Financeiro** | Contas a receber e a pagar, vencimentos, filtros e resumo mensal. |
| **Ponto** | Registro de entradas e saídas com relatório. |
| **Usuários e relatórios** | Cadastro de usuários com perfis de acesso e consultas operacionais por período. |

## Tecnologias

- **Back-end:** PHP 8+ com PDO.
- **Banco de dados:** MySQL ou MariaDB.
- **Interface:** HTML, CSS e JavaScript, com Font Awesome e Chart.js.
- **Integração auxiliar:** consulta de CEP via ViaCEP no cadastro de clientes.

## Instalação

### Requisitos

PHP 8+ com a extensão `pdo_mysql`, MySQL/MariaDB e um servidor web configurado para executar PHP. Em ambiente local, Apache e phpMyAdmin são uma opção.

### Passo a passo

1. Baixe o repositório e coloque os arquivos no diretório servido pelo PHP.
2. Importe `sql/schema.sql` no MySQL. Ele cria o banco **`solar_gestao`**, as tabelas iniciais e alguns produtos de demonstração.
3. Selecione o banco `solar_gestao` e importe `sql/INSTALACAO_UNICA_MF_SOLAR.sql` para garantir a estrutura dos módulos mais recentes. Os demais scripts em `sql/` são referência para atualizações de instalações anteriores.
4. Copie `includes/config.example.php` para `includes/config.php`. Ajuste `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS` e `BASE_URL` conforme seu ambiente. Por exemplo, `BASE_URL` pode ser `http://localhost/solar_gestao` quando essa for a URL real da instalação.
5. Crie a pasta `uploads/` e conceda permissão de escrita ao servidor web.
6. Crie a primeira conta administradora conforme as instruções abaixo e abra `login.php` na URL configurada.

### Primeiro administrador

Esta versão publicada **não inclui senha padrão nem conta administrativa pronta**. Gere um hash para uma senha escolhida por você no terminal com PHP:

```bash
php -r '$senha = readline("Senha inicial: "); echo password_hash($senha, PASSWORD_DEFAULT), PHP_EOL;'
```

No banco `solar_gestao`, insira um registro na tabela `usuarios` com seu `nome` e `email`, o hash gerado na coluna `senha`, `perfil` igual a `admin` e `ativo` igual a `1`. A inserção pode ser feita pelo phpMyAdmin. Depois, entre pelo `login.php` com o e-mail e a senha que escolheu.

> **Ao publicar na internet:** use HTTPS; mantenha `includes/config.php` fora do Git; restrinja o acesso HTTP às pastas `includes/` e `sql/`; e limite as permissões de `uploads/` ao necessário. Há regras `.htaccess` para Apache no repositório; em Nginx, configure proteções equivalentes.

## Estrutura do repositório

```text
assets/              Estilos, scripts e imagens das propostas
includes/            Configuração de exemplo, banco, autenticação e layout
modules/             Clientes, propostas, OS, estoque, financeiro, ponto e relatórios
sql/                 Estrutura inicial e scripts de atualização
index.php            Painel principal
login.php            Acesso ao sistema
```

---

Desenvolvido por **Adriano Bueno** · [GitHub @AdrianoBuenoCruz](https://github.com/AdrianoBuenoCruz)
