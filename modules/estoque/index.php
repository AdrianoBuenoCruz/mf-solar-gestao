<?php
require_once __DIR__ . '/../../includes/header.php';
$db = getDB();

$busca    = $_GET['busca'] ?? '';
$cat      = $_GET['categoria'] ?? '';
$critico  = $_GET['critico'] ?? '';
$page     = max(1,(int)($_GET['page']??1));
$perPage  = 25; $offset = ($page-1)*$perPage;

$where = ["p.ativo=1"]; $params = [];
if ($busca)   { $where[] = "(p.nome LIKE ? OR p.codigo LIKE ? OR p.marca LIKE ?)"; $params = array_merge($params, ["%$busca%","%$busca%","%$busca%"]); }
if ($cat)     { $where[] = "p.categoria_id=?"; $params[] = $cat; }
if ($critico) { $where[] = "p.estoque_atual <= p.estoque_minimo"; }

$sql  = "SELECT p.*, c.nome as categoria FROM produtos p LEFT JOIN categorias c ON c.id=p.categoria_id WHERE ".implode(' AND ',$where);
$tot  = $db->prepare("SELECT COUNT(*) FROM ($sql) t"); $tot->execute($params); $tot = $tot->fetchColumn();
$pages = ceil($tot/$perPage);
$st   = $db->prepare("$sql ORDER BY p.nome ASC LIMIT $perPage OFFSET $offset"); $st->execute($params);
$produtos = $st->fetchAll();
$categorias = $db->query("SELECT * FROM categorias WHERE ativo=1 ORDER BY nome")->fetchAll();
$criticos = $db->query("SELECT COUNT(*) FROM produtos WHERE ativo=1 AND estoque_atual<=estoque_minimo")->fetchColumn();
$msg = $_SESSION['msg']??''; unset($_SESSION['msg']);
?>

<div class="page-header">
  <div class="page-title"><i class="fa-solid fa-boxes-stacked"></i> Estoque <small><?= number_format($tot) ?> produtos</small></div>
  <div style="display:flex;gap:8px">
    <a href="movimentacao.php" class="btn btn-warning"><i class="fa-solid fa-right-left"></i> Movimentação</a>
    <a href="form.php" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Novo Produto</a>
  </div>
</div>

<?php if($criticos>0): ?>
<div class="alert alert-danger"><i class="fa-solid fa-triangle-exclamation"></i> <strong><?= $criticos ?></strong> produto(s) com estoque crítico! <a href="?critico=1" style="font-weight:700;text-decoration:underline">Ver</a></div>
<?php endif; ?>
<?php if($msg): ?><div class="alert alert-success" data-dismiss><i class="fa-solid fa-check"></i> <?= htmlspecialchars($msg) ?></div><?php endif; ?>

<div class="card">
  <div class="card-body" style="padding-bottom:8px">
    <form method="GET" class="search-bar">
      <input type="text" name="busca" placeholder="Buscar produto, código, marca…" value="<?= htmlspecialchars($busca) ?>">
      <select name="categoria"><option value="">Todas as categorias</option><?php foreach($categorias as $c): ?><option value="<?= $c['id'] ?>" <?= $cat==(string)$c['id']?'selected':'' ?>><?= htmlspecialchars($c['nome']) ?></option><?php endforeach; ?></select>
      <label style="display:flex;align-items:center;gap:6px;font-size:13px"><input type="checkbox" name="critico" value="1" <?= $critico?'checked':'' ?>> Somente críticos</label>
      <button type="submit" class="btn btn-primary"><i class="fa-solid fa-magnifying-glass"></i></button>
      <a href="index.php" class="btn btn-outline">Limpar</a>
    </form>
  </div>
  <div class="table-wrap">
    <table>
      <thead><tr><th>Código</th><th>Produto</th><th>Categoria</th><th>Unid.</th><th>Estoque</th><th>Mínimo</th><th>Status</th><th>Custo</th><th>Venda</th><th></th></tr></thead>
      <tbody>
      <?php foreach($produtos as $p):
        $pct = $p['estoque_minimo']>0 ? $p['estoque_atual']/$p['estoque_minimo'] : 999;
        $cls = $pct<=0 ? 'stock-critical' : ($pct<=1 ? 'stock-critical' : ($pct<=1.5 ? 'stock-low' : 'stock-ok'));
        $badgeCls = $pct<=1 ? 'badge-danger' : ($pct<=1.5?'badge-warning':'badge-success');
        $badgeTxt = $pct<=1 ? 'Crítico' : ($pct<=1.5?'Baixo':'OK');
      ?>
      <tr>
        <td><code><?= htmlspecialchars($p['codigo']) ?></code></td>
        <td><strong><?= htmlspecialchars($p['nome']) ?></strong><?php if($p['marca']): ?><br><small style="color:#9CA3AF"><?= htmlspecialchars($p['marca']) ?></small><?php endif; ?></td>
        <td><?= htmlspecialchars($p['categoria']) ?></td>
        <td><?= $p['unidade'] ?></td>
        <td class="<?= $cls ?>"><?= number_format($p['estoque_atual'],0) ?></td>
        <td><?= number_format($p['estoque_minimo'],0) ?></td>
        <td><span class="badge <?= $badgeCls ?>"><?= $badgeTxt ?></span></td>
        <td>R$ <?= number_format($p['preco_custo'],2,',','.') ?></td>
        <td>R$ <?= number_format($p['preco_venda'],2,',','.') ?></td>
        <td style="white-space:nowrap">
          <a href="form.php?id=<?= $p['id'] ?>" class="btn btn-outline btn-sm btn-icon" title="Editar"><i class="fa-solid fa-pen"></i></a>
          <a href="movimentacao.php?produto_id=<?= $p['id'] ?>" class="btn btn-warning btn-sm btn-icon" title="Movimentar"><i class="fa-solid fa-right-left"></i></a>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if(empty($produtos)): ?><tr><td colspan="10" style="text-align:center;padding:30px;color:#9CA3AF">Nenhum produto encontrado</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
  <?php if($pages>1): ?><div class="card-body"><div class="pagination"><?php for($i=1;$i<=$pages;$i++): ?><a href="?<?= http_build_query(array_merge($_GET,['page'=>$i])) ?>" class="<?= $i===$page?'active':'' ?>"><?= $i ?></a><?php endfor; ?></div></div><?php endif; ?>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
