# ☀️ MF Solar — Sistema de Gestão

**Status: concluído** · **Autoria e desenvolvimento: Adriano Bueno**

Sistema web desenvolvido para organizar a operação de uma empresa de energia fotovoltaica, da proposta comercial ao acompanhamento de clientes, ordens de serviço e financeiro.

## Funcionalidades

| Módulo | Recursos |
| --- | --- |
| Painel | Indicadores da operação, ordens de serviço e estoque. |
| Clientes | Cadastro de pessoas físicas e jurídicas e dados da instalação. |
| Propostas | Dimensionamento fotovoltaico, itens, geração estimada, versão para impressão/PDF e conversão em OS. |
| Ordens de serviço | Cadastro, acompanhamento, itens e dados técnicos. |
| Estoque | Produtos, movimentações e alertas de estoque crítico. |
| Financeiro | Contas a receber e a pagar, filtros e resumo do mês. |
| Ponto | Registro de entradas e saídas e relatório. |
| Usuários e relatórios | Perfis de acesso e consultas por período. |

## Tecnologias

PHP 8, PDO, MySQL/MariaDB, HTML, CSS e JavaScript. A interface usa Font Awesome e Chart.js; o cadastro de clientes pode consultar o ViaCEP.

## Instalação local

1. Prepare PHP 8 com `pdo_mysql`, MySQL ou MariaDB e um servidor web configurado para PHP. Para o uso local, pode utilizar Apache com phpMyAdmin.
2. Importe `sql/schema.sql` no MySQL. Esse arquivo cria o banco `solar_gestao` e inclui alguns produtos de demonstração.
3. No banco `solar_gestao`, importe `sql/INSTALACAO_UNICA_MF_SOLAR.sql` para garantir a estrutura dos módulos mais recentes. Os outros arquivos em `sql/` servem de referência para atualizações de instalações antigas.
4. Copie `includes/config.example.php` para `includes/config.php` e configure as credenciais do banco e a `BASE_URL` do ambiente. O arquivo `includes/config.php` é ignorado pelo Git.
5. Crie um administrador inicial na tabela `usuarios`. **Não há senha padrão** nesta versão publicada. Gere um hash para sua senha em um terminal com PHP:

   ```bash
   php -r '$senha = readline("Senha inicial: "); echo password_hash($senha, PASSWORD_DEFAULT), PHP_EOL;'
   ```

   Insira o hash gerado na coluna `senha`, com `nome` e `email` próprios, `perfil` igual a `admin` e `ativo` igual a `1`. Você pode fazer isso pelo phpMyAdmin. Não armazene a senha em texto puro nem publique o arquivo local de configuração.
6. Crie a pasta `uploads/` com permissão de escrita para o servidor e acesse `login.php` pela URL configurada.

> Para instalação na internet, restrinja o acesso HTTP às pastas `includes/` e `sql/`, use HTTPS e configure as permissões de `uploads/` para o servidor. O repositório inclui regras `.htaccess` para Apache; em Nginx, configure as restrições equivalentes.

## Organização

- `modules/propostas/`: elaboração, visualização, impressão e conversão de propostas.
- `modules/clientes/`, `modules/os/`, `modules/estoque/`: operação comercial e atendimento.
- `modules/financeiro/`, `modules/ponto/`, `modules/relatorios/`: gestão administrativa.
- `includes/`: conexão, autenticação e componentes compartilhados.
- `sql/`: estrutura inicial e scripts de atualização.

**Autor:** Adriano Bueno · [Perfil no GitHub](https://github.com/AdrianoBuenoCruz)
