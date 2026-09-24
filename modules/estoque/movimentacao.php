<?php
require_once __DIR__ . '/../../includes/header.php';
$db = getDB();
$produtoId = (int)($_GET['produto_id']??0);
$produtos = $db->query("SELECT id, nome, codigo, estoque_atual, unidade FROM produtos WHERE ativo=1 ORDER BY nome")->fetchAll();

if ($_SERVER['REQUEST_METHOD']==='POST') {
    $pid  = (int)$_POST['produto_id'];
    $tipo = $_POST['tipo'];
    $qty  = (float)$_POST['quantidade'];
    $obs  = $_POST['observacao']??'';
    $nf   = $_POST['nota_fiscal']??'';
    $preco= (float)$_POST['preco_unitario'];

    $stk = $db->prepare("SELECT estoque_atual FROM produtos WHERE id=?"); $stk->execute([$pid]); $atual=(float)$stk->fetchColumn();
    $novo = match($tipo){ 'entrada'=>$atual+$qty,'saida'=>$atual-$qty,'ajuste'=>$qty,'devolucao'=>$atual+$qty,default=>$atual+$qty };
    if ($novo<0) { $erro='Saldo insuficiente!'; }
    else {
        $db->prepare("UPDATE produtos SET estoque_atual=? WHERE id=?")->execute([$novo,$pid]);
        $db->prepare("INSERT INTO movimentacoes_estoque (produto_id,usuario_id,tipo,quantidade,estoque_anterior,estoque_posterior,preco_unitario,nota_fiscal,observacao) VALUES (?,?,?,?,?,?,?,?,?)")->execute([$pid,$_SESSION['usuario_id'],$tipo,$qty,$atual,$novo,$preco?:null,$nf?:null,$obs?:null]);
        $_SESSION['msg']='Movimentação registrada!'; header('Location: index.php'); exit;
    }
}

// Histórico
$page=max(1,(int)($_GET['page']??1)); $perPage=20; $offset=($page-1)*$perPage;
$hist=$db->prepare("SELECT m.*, p.nome as produto, u.nome as usuario FROM movimentacoes_estoque m LEFT JOIN produtos p ON p.id=m.produto_id LEFT JOIN usuarios u ON u.id=m.usuario_id ORDER BY m.created_at DESC LIMIT $perPage OFFSET $offset");
$hist->execute(); $historico=$hist->fetchAll();
$total=$db->query("SELECT COUNT(*) FROM movimentacoes_estoque")->fetchColumn();
$pages=ceil($total/$perPage);
$erro=$erro??'';
?>

<div class="page-header">
  <div class="page-title"><i class="fa-solid fa-right-left"></i> Movimentação de Estoque</div>
  <a href="index.php" class="btn btn-outline"><i class="fa-solid fa-arrow-left"></i> Voltar</a>
</div>
<?php if($erro): ?><div class="alert alert-danger"><i class="fa-solid fa-circle-exclamation"></i> <?= $erro ?></div><?php endif; ?>

<div class="grid-2">
  <div class="card">
    <div class="card-header"><span class="card-title">Registrar Movimentação</span></div>
    <div class="card-body">
      <form method="POST">
        <div class="form-group" style="margin-bottom:14px">
          <label>Produto *</label>
          <select name="produto_id" required>
            <option value="">Selecione o produto</option>
            <?php foreach($produtos as $p): ?>
            <option value="<?= $p['id'] ?>" <?= $produtoId===$p['id']?'selected':'' ?>><?= htmlspecialchars($p['nome']) ?> (<?= number_format($p['estoque_atual'],0) ?> <?= $p['unidade'] ?>)</option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-row cols-2">
          <div class="form-group">
            <label>Tipo *</label>
            <select name="tipo" required>
              <option value="entrada">Entrada</option>
              <option value="saida">Saída</option>
              <option value="ajuste">Ajuste de Saldo</option>
              <option value="devolucao">Devolução</option>
            </select>
          </div>
          <div class="form-group"><label>Quantidade *</label><input type="number" step="0.01" min="0.01" name="quantidade" required></div>
        </div>
        <div class="form-row cols-2">
          <div class="form-group"><label>Preço Unitário</label><input type="number" step="0.01" name="preco_unitario"></div>
          <div class="form-group"><label>Nota Fiscal</label><input type="text" name="nota_fiscal"></div>
        </div>
        <div class="form-group" style="margin-bottom:16px">
          <label>Observação</label>
          <textarea name="observacao" rows="3"></textarea>
        </div>
        <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center"><i class="fa-solid fa-check"></i> Registrar</button>
      </form>
    </div>
  </div>

  <div class="card">
    <div class="card-header"><span class="card-title">Histórico de Movimentações</span></div>
    <div class="table-wrap">
      <table>
        <thead><tr><th>Data</th><th>Produto</th><th>Tipo</th><th>Qtd</th><th>Anterior</th><th>Posterior</th><th>Usuário</th></tr></thead>
        <tbody>
        <?php foreach($historico as $h): ?>
        <tr>
          <td><?= date('d/m/Y H:i',strtotime($h['created_at'])) ?></td>
          <td><?= htmlspecialchars($h['produto']) ?></td>
          <td><span class="badge <?= match($h['tipo']){'entrada','devolucao'=>'badge-success','saida'=>'badge-danger',default=>'badge-warning'} ?>"><?= ucfirst($h['tipo']) ?></span></td>
          <td><?= number_format($h['quantidade'],0) ?></td>
          <td><?= number_format($h['estoque_anterior'],0) ?></td>
          <td><?= number_format($h['estoque_posterior'],0) ?></td>
          <td><?= htmlspecialchars($h['usuario']??'–') ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if(empty($historico)): ?><tr><td colspan="7" style="text-align:center;padding:20px;color:#9CA3AF">Sem movimentações</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
    <?php if($pages>1): ?><div class="card-body"><div class="pagination"><?php for($i=1;$i<=$pages;$i++): ?><a href="?page=<?= $i ?>" class="<?= $i===$page?'active':'' ?>"><?= $i ?></a><?php endfor; ?></div></div><?php endif; ?>
  </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
