<?php
require_once __DIR__ . '/../../includes/header.php';
$db = getDB();
$busca  = $_GET['busca']??'';
$status = $_GET['status']??'';
$page   = max(1,(int)($_GET['page']??1)); $perPage=20; $offset=($page-1)*$perPage;

$where=["1=1"]; $params=[];
if($busca){ $where[]="(os.numero LIKE ? OR c.nome LIKE ? OR c.razao_social LIKE ?)"; $params=array_merge($params,["%$busca%","%$busca%","%$busca%"]); }
if($status){ $where[]="os.status=?"; $params[]=$status; }
$sql="SELECT os.*, c.nome, c.razao_social FROM ordens_servico os LEFT JOIN clientes c ON c.id=os.cliente_id WHERE ".implode(' AND ',$where);
$tot=$db->prepare("SELECT COUNT(*) FROM ($sql) t"); $tot->execute($params); $tot=$tot->fetchColumn();
$pages=ceil($tot/$perPage);
$st=$db->prepare("$sql ORDER BY os.created_at DESC LIMIT $perPage OFFSET $offset"); $st->execute($params);
$os=$st->fetchAll();
$statusLabel=['orcamento'=>'Orçamento','aprovado'=>'Aprovado','em_andamento'=>'Em Andamento','instalado'=>'Instalado','vistoria'=>'Vistoria','concluido'=>'Concluído','cancelado'=>'Cancelado'];
$msg=$_SESSION['msg']??''; unset($_SESSION['msg']);
?>
<div class="page-header">
  <div class="page-title"><i class="fa-solid fa-file-invoice"></i> Ordens de Serviço <small><?= number_format($tot) ?> registros</small></div>
  <a href="form.php" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Nova OS</a>
</div>
<?php if($msg): ?><div class="alert alert-success" data-dismiss><i class="fa-solid fa-check"></i> <?= htmlspecialchars($msg) ?></div><?php endif; ?>
<div class="card">
  <div class="card-body" style="padding-bottom:8px">
    <form method="GET" class="search-bar">
      <input type="text" name="busca" placeholder="Buscar por nº OS ou cliente…" value="<?= htmlspecialchars($busca) ?>">
      <select name="status"><option value="">Todos os status</option><?php foreach($statusLabel as $k=>$l): ?><option value="<?= $k ?>" <?= $status===$k?'selected':'' ?>><?= $l ?></option><?php endforeach; ?></select>
      <button type="submit" class="btn btn-primary"><i class="fa-solid fa-magnifying-glass"></i></button>
      <a href="index.php" class="btn btn-outline">Limpar</a>
    </form>
  </div>
  <div class="table-wrap">
    <table>
      <thead><tr><th>Nº OS</th><th>Cliente</th><th>Tipo</th><th>Status</th><th>Potência</th><th>Valor Total</th><th>Dt. Criação</th><th>Dt. Instalação</th><th></th></tr></thead>
      <tbody>
      <?php foreach($os as $o): ?>
      <tr>
        <td><a href="view.php?id=<?= $o['id'] ?>" style="color:var(--primary);font-weight:700"><?= htmlspecialchars($o['numero']?:'#'.$o['id']) ?></a></td>
        <td><?= htmlspecialchars($o['nome']?:$o['razao_social']) ?></td>
        <td><?= ucfirst(str_replace('_',' ',$o['tipo'])) ?></td>
        <td><span class="badge badge-<?= match($o['status']){'concluido'=>'success','cancelado'=>'danger','em_andamento'=>'warning','aprovado'=>'primary',default=>'info'} ?>"><?= $statusLabel[$o['status']]??$o['status'] ?></span></td>
        <td><?= $o['potencia_projeto']?number_format($o['potencia_projeto'],2).' kWp':'–' ?></td>
        <td><strong>R$ <?= number_format($o['valor_total'],2,',','.') ?></strong></td>
        <td><?= date('d/m/Y',strtotime($o['created_at'])) ?></td>
        <td><?= $o['data_instalacao_prevista']?date('d/m/Y',strtotime($o['data_instalacao_prevista'])):'–' ?></td>
        <td style="white-space:nowrap">
          <a href="view.php?id=<?= $o['id'] ?>" class="btn btn-outline btn-sm btn-icon"><i class="fa-solid fa-eye"></i></a>
          <a href="form.php?id=<?= $o['id'] ?>" class="btn btn-outline btn-sm btn-icon"><i class="fa-solid fa-pen"></i></a>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if(empty($os)): ?><tr><td colspan="9" style="text-align:center;padding:30px;color:#9CA3AF">Nenhuma OS encontrada</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
  <?php if($pages>1): ?><div class="card-body"><div class="pagination"><?php for($i=1;$i<=$pages;$i++): ?><a href="?<?= http_build_query(array_merge($_GET,['page'=>$i])) ?>" class="<?= $i===$page?'active':'' ?>"><?= $i ?></a><?php endfor; ?></div></div><?php endif; ?>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
