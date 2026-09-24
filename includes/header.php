<?php
require_once __DIR__ . '/auth.php';
requireLogin();
$user = currentUser();
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= SISTEMA_NOME ?></title>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<div class="layout">
  <!-- SIDEBAR -->
  <aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
      <i class="fa-solid fa-solar-panel"></i>
      <span>Solar Gestão</span>
    </div>
    <nav class="sidebar-nav">
      <a href="<?= BASE_URL ?>/index.php" class="nav-item <?= $currentPage==='index'?'active':'' ?>">
        <i class="fa-solid fa-gauge-high"></i><span>Dashboard</span>
      </a>
      <a href="<?= BASE_URL ?>/modules/clientes/index.php" class="nav-item <?= strpos($currentPage,'cliente')!==false?'active':'' ?>">
        <i class="fa-solid fa-users"></i><span>Clientes</span>
      </a>
      <a href="<?= BASE_URL ?>/modules/estoque/index.php" class="nav-item <?= strpos($currentPage,'produto')!==false||strpos($currentPage,'estoque')!==false?'active':'' ?>">
        <i class="fa-solid fa-boxes-stacked"></i><span>Estoque</span>
      </a>
      <a href="<?= BASE_URL ?>/modules/os/index.php" class="nav-item <?= strpos($currentPage,'os')!==false?'active':'' ?>">
        <i class="fa-solid fa-file-invoice"></i><span>Ordens de Serviço</span>
      </a>
      <a href="<?= BASE_URL ?>/modules/propostas/index.php" class="nav-item <?= strpos($_SERVER['PHP_SELF'],'/propostas/')!==false?'active':'' ?>">
        <i class="fa-solid fa-file-signature"></i><span>Propostas</span>
      </a>
      <a href="<?= BASE_URL ?>/modules/relatorios/index.php" class="nav-item <?= strpos($currentPage,'relatorio')!==false?'active':'' ?>">
        <i class="fa-solid fa-chart-bar"></i><span>Relatórios</span>
      </a>
      <?php if(hasRole('admin','gerente','financeiro')): ?>
      <a href="<?= BASE_URL ?>/modules/financeiro/index.php" class="nav-item <?= strpos($currentPage,'financeiro')!==false?'active':'' ?>">
        <i class="fa-solid fa-dollar-sign"></i><span>Financeiro</span>
      </a>
      <?php endif; ?>
      <?php if(hasRole('admin','gerente')): ?>
      <a href="<?= BASE_URL ?>/modules/usuarios/index.php" class="nav-item <?= strpos($currentPage,'usuario')!==false?'active':'' ?>">
        <i class="fa-solid fa-user-gear"></i><span>Usuários</span>
      </a>
      <?php endif; ?>
    </nav>
    <div class="sidebar-footer">
      <a href="<?= BASE_URL ?>/modules/ponto/index.php" class="nav-item" target="_blank">
        <i class="fa-solid fa-clock"></i><span>Ponto (Terminal)</span>
      </a>
      <?php if(hasRole('admin')): ?><a href="<?= BASE_URL ?>/modules/ponto/relatorio.php" class="nav-item"><i class="fa-solid fa-clipboard-list"></i><span>Relatório de Ponto</span></a><?php endif; ?>
    </div>
  </aside>

  <!-- MAIN -->
  <div class="main-wrapper">
    <header class="topbar">
      <button class="btn-menu" onclick="document.getElementById('sidebar').classList.toggle('collapsed')">
        <i class="fa-solid fa-bars"></i>
      </button>
      <div class="topbar-right">
        <span class="topbar-user">
          <i class="fa-solid fa-circle-user"></i>
          <?= htmlspecialchars($user['nome']) ?>
          <small>(<?= $user['perfil'] ?>)</small>
        </span>
        <a href="<?= BASE_URL ?>/logout.php" class="btn-logout" title="Sair">
          <i class="fa-solid fa-right-from-bracket"></i>
        </a>
      </div>
    </header>
    <main class="main-content">
