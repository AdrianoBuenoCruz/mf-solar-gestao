<?php
require_once __DIR__ . '/../../includes/header.php';
if(!hasRole('admin','gerente')){header('Location: '.BASE_URL.'/index.php');exit;}
$db = getDB();
$usuarios = $db->query("SELECT * FROM usuarios ORDER BY nome")->fetchAll();
$msg=$_SESSION['msg']??''; unset($_SESSION['msg']);
?>
<div class="page-header">
  <div class="page-title"><i class="fa-solid fa-user-gear"></i> Usuários</div>
  <a href="form.php" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Novo Usuário</a>
</div>
<?php if($msg): ?><div class="alert alert-success" data-dismiss><i class="fa-solid fa-check"></i> <?= htmlspecialchars($msg) ?></div><?php endif; ?>
<div class="card">
  <div class="table-wrap">
    <table>
      <thead><tr><th>#</th><th>Nome</th><th>E-mail</th><th>Perfil</th><th>Status</th><th></th></tr></thead>
      <tbody>
      <?php foreach($usuarios as $u): ?>
      <tr>
        <td><?= $u['id'] ?></td>
        <td><?= htmlspecialchars($u['nome']) ?></td>
        <td><?= htmlspecialchars($u['email']) ?></td>
        <td><span class="badge badge-primary"><?= ucfirst($u['perfil']) ?></span></td>
        <td><span class="badge <?= $u['ativo']?'badge-success':'badge-danger' ?>"><?= $u['ativo']?'Ativo':'Inativo' ?></span></td>
        <td><a href="form.php?id=<?= $u['id'] ?>" class="btn btn-outline btn-sm btn-icon"><i class="fa-solid fa-pen"></i></a></td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
