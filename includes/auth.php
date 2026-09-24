<?php
require_once __DIR__ . '/db.php';

function isLoggedIn(): bool {
    return isset($_SESSION['usuario_id']);
}

function requireLogin(): void {
    if (!isLoggedIn()) {
        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }
}

function currentUser(): ?array {
    if (!isLoggedIn()) return null;
    static $user = null;
    if ($user === null) {
        $db = getDB();
        $st = $db->prepare("SELECT * FROM usuarios WHERE id = ?");
        $st->execute([$_SESSION['usuario_id']]);
        $user = $st->fetch() ?: null;
    }
    return $user;
}

function hasRole(string ...$roles): bool {
    $u = currentUser();
    return $u && in_array($u['perfil'], $roles);
}

function login(string $email, string $senha): bool {
    $db = getDB();
    $st = $db->prepare("SELECT * FROM usuarios WHERE email = ? AND ativo = 1");
    $st->execute([$email]);
    $user = $st->fetch();
    if ($user && password_verify($senha, $user['senha'])) {
        $_SESSION['usuario_id'] = $user['id'];
        $_SESSION['usuario_nome'] = $user['nome'];
        $_SESSION['usuario_perfil'] = $user['perfil'];
        return true;
    }
    return false;
}

function logout(): void {
    session_destroy();
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}
