<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

if (isLoggedIn()) { header('Location: ' . BASE_URL . '/index.php'); exit; }

$erro = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (login($_POST['email'] ?? '', $_POST['senha'] ?? '')) {
        header('Location: ' . BASE_URL . '/index.php');
        exit;
    }
    $erro = 'E-mail ou senha incorretos.';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login – Solar Gestão</title>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<div class="login-page">
  <div class="login-box">
    <div class="login-logo">
      <i class="fa-solid fa-solar-panel"></i>
      <h1>Solar Gestão</h1>
      <p>Sistema de Gestão Fotovoltaica</p>
    </div>
    <?php if($erro): ?>
    <div class="alert alert-danger"><i class="fa-solid fa-circle-exclamation"></i> <?= $erro ?></div>
    <?php endif; ?>
    <form method="POST">
      <div class="form-group" style="margin-bottom:14px">
        <label>E-mail</label>
        <input type="email" name="email" placeholder="seu@email.com" required autofocus>
      </div>
      <div class="form-group" style="margin-bottom:24px">
        <label>Senha</label>
        <input type="password" name="senha" placeholder="••••••••" required>
      </div>
      <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;padding:12px">
        <i class="fa-solid fa-right-to-bracket"></i> Entrar
      </button>
    </form>
  </div>
</div>
</body>
</html>
