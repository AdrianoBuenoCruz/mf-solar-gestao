<?php
require_once __DIR__ . '/../../includes/header.php';
$id = (int)($_GET['id'] ?? 0);
if ($id) { $db = getDB(); $db->prepare("UPDATE clientes SET ativo=0 WHERE id=?")->execute([$id]); }
$_SESSION['msg'] = 'Cliente desativado.';
header('Location: index.php'); exit;
