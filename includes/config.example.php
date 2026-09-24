<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'solar_gestao');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

define('SISTEMA_NOME', 'Solar Gestão');
define('SISTEMA_VERSAO', '1.0.0');
define('BASE_URL', 'http://localhost/solar_gestao');
define('UPLOAD_DIR', __DIR__ . '/../uploads/');

date_default_timezone_set('America/Sao_Paulo');
session_start();
