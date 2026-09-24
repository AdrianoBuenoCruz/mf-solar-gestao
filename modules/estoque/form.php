<?php
require_once __DIR__ . '/../../includes/header.php';
$db = getDB();
$id = (int)($_GET['id']??0);
$p = [];
if ($id) { $st=$db->prepare("SELECT * FROM produtos WHERE id=?"); $st->execute([$id]); $p=$st->fetch(); if(!$p){header('Location: index.php');exit;} }
$categorias  = $db->query("SELECT * FROM categorias WHERE ativo=1 ORDER BY nome")->fetchAll();
$fornecedores= $db->query("SELECT * FROM fornecedores WHERE ativo=1 ORDER BY razao_social")->fetchAll();

if ($_SERVER['REQUEST_METHOD']==='POST') {
    $d = $_POST;
    $fields=['categoria_id','fornecedor_id','codigo','nome','descricao','marca','modelo','unidade','potencia_wp','tensao_voc','corrente_isc','eficiencia','garantia_produto','garantia_desempenho','estoque_atual','estoque_minimo','estoque_maximo','localizacao_estoque','preco_custo','preco_venda','margem_padrao'];
    $vals=[]; foreach($fields as $f) $vals[$f]=$d[$f]??null; foreach($vals as &$v) if($v==='')$v=null;
    if ($id) {
        $set=implode(', ',array_map(fn($f)=>"$f=:$f",$fields));
        $st=$db->prepare("UPDATE produtos SET $set WHERE id=:id"); $vals['id']=$id;
    } else {
        $cols=implode(', ',$fields); $ph=implode(', ',array_map(fn($f)=>":$f",$fields));
        $st=$db->prepare("INSERT INTO produtos ($cols) VALUES ($ph)");
    }
    $st->execute($vals);
    $_SESSION['msg']=$id?'Produto atualizado!':'Produto cadastrado!';
    header('Location: index.php'); exit;
}
$v=$p?:$_POST;
?>

<div class="page-header">
  <div class="page-title"><i class="fa-solid fa-box"></i> <?= $id?'Editar Produto':'Novo Produto' ?></div>
  <a href="index.php" class="btn btn-outline"><i class="fa-solid fa-arrow-left"></i> Voltar</a>
</div>

<form method="POST">
<div class="card">
  <div class="card-header"><span class="card-title">Informações Gerais</span></div>
  <div class="card-body">
    <div class="form-row cols-3">
      <div class="form-group"><label>Código</label><input type="text" name="codigo" value="<?= htmlspecialchars($v['codigo']??'') ?>"></div>
      <div class="form-group" style="grid-column:span 2"><label>Nome do Produto *</label><input type="text" name="nome" required value="<?= htmlspecialchars($v['nome']??'') ?>"></div>
    </div>
    <div class="form-row cols-4">
      <div class="form-group"><label>Categoria</label>
        <select name="categoria_id"><option value="">Selecione</option><?php foreach($categorias as $c): ?><option value="<?= $c['id'] ?>" <?= ($v['categoria_id']??'')==$c['id']?'selected':'' ?>><?= htmlspecialchars($c['nome']) ?></option><?php endforeach; ?></select>
      </div>
      <div class="form-group"><label>Fornecedor</label>
        <select name="fornecedor_id"><option value="">Selecione</option><?php foreach($fornecedores as $f): ?><option value="<?= $f['id'] ?>" <?= ($v['fornecedor_id']??'')==$f['id']?'selected':'' ?>><?= htmlspecialchars($f['razao_social']) ?></option><?php endforeach; ?></select>
      </div>
      <div class="form-group"><label>Marca</label><input type="text" name="marca" value="<?= htmlspecialchars($v['marca']??'') ?>"></div>
      <div class="form-group"><label>Modelo</label><input type="text" name="modelo" value="<?= htmlspecialchars($v['modelo']??'') ?>"></div>
    </div>
    <div class="form-row cols-4">
      <div class="form-group"><label>Unidade</label>
        <select name="unidade"><?php foreach(['un'=>'Unidade','m'=>'Metro','m2'=>'m²','kg'=>'Kg','rolo'=>'Rolo','kit'=>'Kit','pc'=>'Peça','par'=>'Par'] as $u=>$l): ?><option value="<?= $u ?>" <?= ($v['unidade']??'un')===$u?'selected':'' ?>><?= $l ?></option><?php endforeach; ?></select>
      </div>
      <div class="form-group" style="grid-column:span 3"><label>Descrição</label><input type="text" name="descricao" value="<?= htmlspecialchars($v['descricao']??'') ?>"></div>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-header"><span class="card-title">Especificações Técnicas</span></div>
  <div class="card-body">
    <div class="form-row cols-4">
      <div class="form-group"><label>Potência (Wp)</label><input type="number" step="0.01" name="potencia_wp" value="<?= htmlspecialchars($v['potencia_wp']??'') ?>"></div>
      <div class="form-group"><label>Tensão Voc (V)</label><input type="number" step="0.01" name="tensao_voc" value="<?= htmlspecialchars($v['tensao_voc']??'') ?>"></div>
      <div class="form-group"><label>Corrente Isc (A)</label><input type="number" step="0.01" name="corrente_isc" value="<?= htmlspecialchars($v['corrente_isc']??'') ?>"></div>
      <div class="form-group"><label>Eficiência (%)</label><input type="number" step="0.01" name="eficiencia" value="<?= htmlspecialchars($v['eficiencia']??'') ?>"></div>
    </div>
    <div class="form-row cols-4">
      <div class="form-group"><label>Garantia Produto (anos)</label><input type="number" name="garantia_produto" value="<?= htmlspecialchars($v['garantia_produto']??'') ?>"></div>
      <div class="form-group"><label>Garantia Desempenho (anos)</label><input type="number" name="garantia_desempenho" value="<?= htmlspecialchars($v['garantia_desempenho']??'') ?>"></div>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-header"><span class="card-title">Estoque</span></div>
  <div class="card-body">
    <div class="form-row cols-4">
      <div class="form-group"><label>Estoque Atual</label><input type="number" step="0.01" name="estoque_atual" value="<?= htmlspecialchars($v['estoque_atual']??0) ?>"></div>
      <div class="form-group"><label>Estoque Mínimo</label><input type="number" step="0.01" name="estoque_minimo" value="<?= htmlspecialchars($v['estoque_minimo']??0) ?>"></div>
      <div class="form-group"><label>Estoque Máximo</label><input type="number" step="0.01" name="estoque_maximo" value="<?= htmlspecialchars($v['estoque_maximo']??0) ?>"></div>
      <div class="form-group"><label>Localização</label><input type="text" name="localizacao_estoque" placeholder="Ex: Prateleira A3" value="<?= htmlspecialchars($v['localizacao_estoque']??'') ?>"></div>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-header"><span class="card-title">Preços</span></div>
  <div class="card-body">
    <div class="form-row cols-3">
      <div class="form-group"><label>Preço de Custo (R$)</label><input type="number" step="0.01" name="preco_custo" value="<?= htmlspecialchars($v['preco_custo']??'') ?>"></div>
      <div class="form-group"><label>Margem Padrão (%)</label><input type="number" step="0.01" name="margem_padrao" value="<?= htmlspecialchars($v['margem_padrao']??'') ?>"></div>
      <div class="form-group"><label>Preço de Venda (R$)</label><input type="number" step="0.01" name="preco_venda" value="<?= htmlspecialchars($v['preco_venda']??'') ?>"></div>
    </div>
  </div>
</div>

<div style="display:flex;gap:12px;justify-content:flex-end;margin-bottom:32px">
  <a href="index.php" class="btn btn-outline">Cancelar</a>
  <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Salvar</button>
</div>
</form>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
