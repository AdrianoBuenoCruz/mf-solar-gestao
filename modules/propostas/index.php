<?php
require_once __DIR__ . '/../../includes/header.php';
$db=getDB();
$busca=trim($_GET['busca']??'');$status=$_GET['status']??'';
$where=['1=1'];$params=[];
if($busca!==''){$where[]="(p.numero LIKE ? OR COALESCE(c.nome,c.razao_social,c.nome_fantasia) LIKE ?)";$params[]="%$busca%";$params[]="%$busca%";}
if($status!==''){$where[]='p.status=?';$params[]=$status;}
$sql="SELECT p.*,COALESCE(c.nome,c.nome_fantasia,c.razao_social) cliente FROM propostas p JOIN clientes c ON c.id=p.cliente_id WHERE ".implode(' AND ',$where)." ORDER BY p.id DESC";
$st=$db->prepare($sql);$st->execute($params);$propostas=$st->fetchAll();
$labels=['rascunho'=>'Rascunho','enviada'=>'Enviada','aprovada'=>'Aprovada','recusada'=>'Recusada','vencida'=>'Vencida','convertida'=>'Convertida em OS'];
$badges=['rascunho'=>'gray','enviada'=>'info','aprovada'=>'success','recusada'=>'danger','vencida'=>'warning','convertida'=>'primary'];
?>
<div class="page-header"><div class="page-title"><i class="fa-solid fa-file-signature"></i> Propostas Comerciais</div><a href="form.php" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Nova proposta</a></div>
<?php if(!empty($_SESSION['msg'])):?><div class="alert alert-success"><i class="fa-solid fa-circle-check"></i><?= htmlspecialchars($_SESSION['msg']);unset($_SESSION['msg']); ?></div><?php endif;?>
<form class="search-bar" method="GET"><input name="busca" placeholder="Número ou cliente" value="<?= htmlspecialchars($busca) ?>"><select name="status"><option value="">Todos os status</option><?php foreach($labels as $k=>$v):?><option value="<?= $k ?>" <?= $status===$k?'selected':'' ?>><?= $v ?></option><?php endforeach;?></select><button class="btn btn-outline"><i class="fa-solid fa-magnifying-glass"></i> Buscar</button></form>
<div class="card"><div class="table-wrap"><table><thead><tr><th>Número</th><th>Cliente</th><th>Data</th><th>Validade</th><th>Potência</th><th>Valor</th><th>Status</th><th></th></tr></thead><tbody>
<?php foreach($propostas as $p):?><tr><td><strong><?= htmlspecialchars($p['numero']) ?></strong></td><td><?= htmlspecialchars($p['cliente']) ?></td><td><?= date('d/m/Y',strtotime($p['data_proposta'])) ?></td><td><?= $p['validade']?date('d/m/Y',strtotime($p['validade'])):'–' ?></td><td><?= number_format((float)$p['potencia_kwp'],2,',','.') ?> kWp</td><td>R$ <?= number_format((float)$p['valor_total'],2,',','.') ?></td><td><span class="badge badge-<?= $badges[$p['status']]??'gray' ?>"><?= $labels[$p['status']]??$p['status'] ?></span></td><td style="white-space:nowrap"><a class="btn btn-outline btn-sm btn-icon" href="view.php?id=<?= $p['id'] ?>" title="Visualizar"><i class="fa-solid fa-eye"></i></a> <a class="btn btn-outline btn-sm btn-icon" href="form.php?id=<?= $p['id'] ?>" title="Editar"><i class="fa-solid fa-pen"></i></a> <a class="btn btn-primary btn-sm btn-icon" href="imprimir.php?id=<?= $p['id'] ?>" target="_blank" title="Imprimir/PDF"><i class="fa-solid fa-file-pdf"></i></a></td></tr><?php endforeach;?>
<?php if(!$propostas):?><tr><td colspan="8" style="text-align:center;padding:28px;color:#9CA3AF">Nenhuma proposta encontrada</td></tr><?php endif;?></tbody></table></div></div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
