<?php
require_once __DIR__ . '/../../includes/header.php';
$db = getDB();

$busca   = $_GET['busca'] ?? '';
$cidade  = $_GET['cidade'] ?? '';
$tipo    = $_GET['tipo'] ?? '';
$page    = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;
$offset  = ($page - 1) * $perPage;

$where = ["c.ativo = 1"];
$params = [];
if ($busca) { $where[] = "(c.nome LIKE ? OR c.razao_social LIKE ? OR c.cpf LIKE ? OR c.cnpj LIKE ? OR c.celular LIKE ? OR c.email LIKE ?)"; $params = array_merge($params, array_fill(0,6,"%$busca%")); }
if ($cidade) { $where[] = "c.cidade = ?"; $params[] = $cidade; }
if ($tipo)   { $where[] = "c.tipo_pessoa = ?"; $params[] = $tipo; }
$sql = "SELECT c.* FROM clientes c WHERE " . implode(' AND ', $where);

$total  = $db->prepare("SELECT COUNT(*) FROM ($sql) t"); $total->execute($params); $total = $total->fetchColumn();
$pages  = ceil($total / $perPage);
$stmt   = $db->prepare("$sql ORDER BY c.created_at DESC LIMIT $perPage OFFSET $offset");
$stmt->execute($params);
$clientes = $stmt->fetchAll();

$cidades = $db->query("SELECT DISTINCT cidade FROM clientes WHERE ativo=1 AND cidade IS NOT NULL AND cidade != '' ORDER BY cidade")->fetchAll(PDO::FETCH_COLUMN);

$msg = $_SESSION['msg'] ?? ''; unset($_SESSION['msg']);
?>

<div class="page-header">
  <div class="page-title"><i class="fa-solid fa-users"></i> Clientes <small><?= number_format($total) ?> registros</small></div>
  <a href="form.php" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Novo Cliente</a>
</div>

<?php if($msg): ?><div class="alert alert-success" data-dismiss><i class="fa-solid fa-check"></i> <?= htmlspecialchars($msg) ?></div><?php endif; ?>

<div class="card">
  <div class="card-body" style="padding-bottom:8px">
    <form method="GET" class="search-bar">
      <input type="text" name="busca" placeholder="Buscar por nome, CPF/CNPJ, telefone ou e-mail…" value="<?= htmlspecialchars($busca) ?>">
      <select name="cidade"><option value="">Todas as cidades</option><?php foreach($cidades as $c): ?><option value="<?= htmlspecialchars($c) ?>" <?= $cidade===$c?'selected':'' ?>><?= htmlspecialchars($c) ?></option><?php endforeach; ?></select>
      <select name="tipo"><option value="">Tipo</option><option value="fisica" <?= $tipo==='fisica'?'selected':'' ?>>Pessoa Física</option><option value="juridica" <?= $tipo==='juridica'?'selected':'' ?>>Pessoa Jurídica</option></select>
      <button type="submit" class="btn btn-primary"><i class="fa-solid fa-magnifying-glass"></i></button>
      <a href="index.php" class="btn btn-outline">Limpar</a>
    </form>
  </div>
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>#</th><th>Nome / Razão Social</th><th>Tipo</th><th>CPF / CNPJ</th><th>Celular</th><th>Cidade/UF</th><th>Classe Tarifária</th><th>Cadastro</th><th></th>
        </tr>
      </thead>
      <tbody>
      <?php foreach($clientes as $c): ?>
      <tr>
        <td><?= $c['id'] ?></td>
        <td><strong><?= htmlspecialchars($c['tipo_pessoa']==='fisica' ? $c['nome'] : ($c['nome_fantasia'] ?: $c['razao_social'])) ?></strong><?php if($c['tipo_pessoa']==='juridica' && $c['nome_fantasia']): ?><br><small style="color:#9CA3AF"><?= htmlspecialchars($c['razao_social']) ?></small><?php endif; ?></td>
        <td><span class="badge <?= $c['tipo_pessoa']==='fisica'?'badge-info':'badge-primary' ?>"><?= $c['tipo_pessoa']==='fisica'?'PF':'PJ' ?></span></td>
        <td><?= htmlspecialchars($c['tipo_pessoa']==='fisica' ? $c['cpf'] : $c['cnpj']) ?></td>
        <td><?= htmlspecialchars($c['celular'] ?: $c['telefone']) ?></td>
        <td><?= htmlspecialchars($c['cidade']) ?><?= $c['estado']?' / '.$c['estado']:'' ?></td>
        <td><?= $c['classe_tarifaria'] ? ucfirst(str_replace('_',' ',$c['classe_tarifaria'])) : '–' ?></td>
        <td><?= date('d/m/Y', strtotime($c['created_at'])) ?></td>
        <td style="white-space:nowrap">
          <a href="view.php?id=<?= $c['id'] ?>" class="btn btn-outline btn-sm btn-icon" title="Ver"><i class="fa-solid fa-eye"></i></a>
          <a href="form.php?id=<?= $c['id'] ?>" class="btn btn-outline btn-sm btn-icon" title="Editar"><i class="fa-solid fa-pen"></i></a>
          <a href="delete.php?id=<?= $c['id'] ?>" class="btn btn-danger btn-sm btn-icon" title="Excluir" data-confirm="Desativar este cliente?"><i class="fa-solid fa-trash"></i></a>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if(empty($clientes)): ?><tr><td colspan="9" style="text-align:center;padding:30px;color:#9CA3AF">Nenhum cliente encontrado</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
  <?php if($pages > 1): ?>
  <div class="card-body">
    <div class="pagination">
      <?php for($i=1;$i<=$pages;$i++): ?>
        <a href="?<?= http_build_query(array_merge($_GET,['page'=>$i])) ?>" class="<?= $i===$page?'active':'' ?>"><?= $i ?></a>
      <?php endfor; ?>
    </div>
  </div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
