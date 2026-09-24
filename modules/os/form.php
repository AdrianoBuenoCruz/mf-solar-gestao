<?php
require_once __DIR__ . '/../../includes/header.php';
$db = getDB();
$id = (int)($_GET['id']??0);
$clienteId = (int)($_GET['cliente_id']??0);
$os=[];
if($id){ $st=$db->prepare("SELECT * FROM ordens_servico WHERE id=?"); $st->execute([$id]); $os=$st->fetch(); if(!$os){header('Location: index.php');exit;} }
$clientes=$db->query("SELECT id, tipo_pessoa, nome, razao_social, nome_fantasia FROM clientes WHERE ativo=1 ORDER BY nome,razao_social")->fetchAll();
$tecnicos=$db->query("SELECT id,nome FROM usuarios WHERE ativo=1 ORDER BY nome")->fetchAll();
$produtos=$db->query("SELECT id,codigo,nome,unidade,preco_venda FROM produtos WHERE ativo=1 ORDER BY nome")->fetchAll();

if($_SERVER['REQUEST_METHOD']==='POST'){
    $d=$_POST;
    $fields=['cliente_id','usuario_responsavel_id','tecnico_id','status','tipo','potencia_projeto','quantidade_paineis','area_instalacao','geracao_estimada_kwh','data_orcamento','data_aprovacao','data_instalacao_prevista','data_instalacao_real','data_vistoria','data_conclusao','valor_total','valor_materiais','valor_servicos','desconto','forma_pagamento','observacoes','observacoes_tecnicas'];
    $vals=[]; foreach($fields as $f) $vals[$f]=$d[$f]??null; foreach($vals as &$v) if($v==='')$v=null;
    if($id){
        $set=implode(', ',array_map(fn($f)=>"$f=:$f",$fields));
        $st=$db->prepare("UPDATE ordens_servico SET $set WHERE id=:id"); $vals['id']=$id;
        $st->execute($vals);
    } else {
        $num='OS'.date('Ymd').str_pad($db->query("SELECT COUNT(*)+1 FROM ordens_servico")->fetchColumn(),4,'0',STR_PAD_LEFT);
        $vals['numero']=$num;
        $cols=implode(', ',[...$fields,'numero']); $ph=implode(', ',[...array_map(fn($f)=>":$f",$fields),':numero']);
        $db->prepare("INSERT INTO ordens_servico ($cols) VALUES ($ph)")->execute($vals);
    }
    $_SESSION['msg']=$id?'OS atualizada!':'OS criada com sucesso!';
    header('Location: index.php'); exit;
}
$v=$os?:$_POST;
if(!$id && $clienteId) $v['cliente_id']=$clienteId;
$statusOpts=['orcamento'=>'Orçamento','aprovado'=>'Aprovado','em_andamento'=>'Em Andamento','instalado'=>'Instalado','vistoria'=>'Vistoria','concluido'=>'Concluído','cancelado'=>'Cancelado'];
$tipoOpts=['instalacao'=>'Instalação','manutencao'=>'Manutenção','ampliacao'=>'Ampliação','vistoria'=>'Vistoria','outro'=>'Outro'];
?>
<div class="page-header">
  <div class="page-title"><i class="fa-solid fa-file-invoice"></i> <?= $id?'Editar OS':'Nova Ordem de Serviço' ?></div>
  <a href="index.php" class="btn btn-outline"><i class="fa-solid fa-arrow-left"></i> Voltar</a>
</div>
<form method="POST">
<div class="card">
  <div class="card-header"><span class="card-title">Informações Gerais</span></div>
  <div class="card-body">
    <div class="form-row cols-3">
      <div class="form-group"><label>Cliente *</label>
        <select name="cliente_id" required>
          <option value="">Selecione</option>
          <?php foreach($clientes as $c): $nome=$c['tipo_pessoa']==='fisica'?$c['nome']:($c['nome_fantasia']?:$c['razao_social']); ?>
          <option value="<?= $c['id'] ?>" <?= ($v['cliente_id']??'')==$c['id']?'selected':'' ?>><?= htmlspecialchars($nome) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group"><label>Tipo</label>
        <select name="tipo"><?php foreach($tipoOpts as $k=>$l): ?><option value="<?= $k ?>" <?= ($v['tipo']??'instalacao')===$k?'selected':'' ?>><?= $l ?></option><?php endforeach; ?></select>
      </div>
      <div class="form-group"><label>Status</label>
        <select name="status"><?php foreach($statusOpts as $k=>$l): ?><option value="<?= $k ?>" <?= ($v['status']??'orcamento')===$k?'selected':'' ?>><?= $l ?></option><?php endforeach; ?></select>
      </div>
    </div>
    <div class="form-row cols-2">
      <div class="form-group"><label>Responsável Comercial</label>
        <select name="usuario_responsavel_id"><option value="">Selecione</option><?php foreach($tecnicos as $t): ?><option value="<?= $t['id'] ?>" <?= ($v['usuario_responsavel_id']??'')==$t['id']?'selected':'' ?>><?= htmlspecialchars($t['nome']) ?></option><?php endforeach; ?></select>
      </div>
      <div class="form-group"><label>Técnico Responsável</label>
        <select name="tecnico_id"><option value="">Selecione</option><?php foreach($tecnicos as $t): ?><option value="<?= $t['id'] ?>" <?= ($v['tecnico_id']??'')==$t['id']?'selected':'' ?>><?= htmlspecialchars($t['nome']) ?></option><?php endforeach; ?></select>
      </div>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-header"><span class="card-title">Dados Técnicos do Projeto</span></div>
  <div class="card-body">
    <div class="form-row cols-4">
      <div class="form-group"><label>Potência do Projeto (kWp)</label><input type="number" step="0.01" name="potencia_projeto" value="<?= htmlspecialchars($v['potencia_projeto']??'') ?>"></div>
      <div class="form-group"><label>Qtd. Painéis</label><input type="number" name="quantidade_paineis" value="<?= htmlspecialchars($v['quantidade_paineis']??'') ?>"></div>
      <div class="form-group"><label>Área de Instalação (m²)</label><input type="number" step="0.01" name="area_instalacao" value="<?= htmlspecialchars($v['area_instalacao']??'') ?>"></div>
      <div class="form-group"><label>Geração Estimada (kWh/mês)</label><input type="number" step="0.01" name="geracao_estimada_kwh" value="<?= htmlspecialchars($v['geracao_estimada_kwh']??'') ?>"></div>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-header"><span class="card-title">Datas</span></div>
  <div class="card-body">
    <div class="form-row cols-3">
      <div class="form-group"><label>Data do Orçamento</label><input type="date" name="data_orcamento" value="<?= htmlspecialchars($v['data_orcamento']??date('Y-m-d')) ?>"></div>
      <div class="form-group"><label>Data de Aprovação</label><input type="date" name="data_aprovacao" value="<?= htmlspecialchars($v['data_aprovacao']??'') ?>"></div>
      <div class="form-group"><label>Instalação Prevista</label><input type="date" name="data_instalacao_prevista" value="<?= htmlspecialchars($v['data_instalacao_prevista']??'') ?>"></div>
      <div class="form-group"><label>Instalação Real</label><input type="date" name="data_instalacao_real" value="<?= htmlspecialchars($v['data_instalacao_real']??'') ?>"></div>
      <div class="form-group"><label>Data da Vistoria</label><input type="date" name="data_vistoria" value="<?= htmlspecialchars($v['data_vistoria']??'') ?>"></div>
      <div class="form-group"><label>Data de Conclusão</label><input type="date" name="data_conclusao" value="<?= htmlspecialchars($v['data_conclusao']??'') ?>"></div>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-header"><span class="card-title">Financeiro</span></div>
  <div class="card-body">
    <div class="form-row cols-4">
      <div class="form-group"><label>Valor Materiais (R$)</label><input type="number" step="0.01" name="valor_materiais" value="<?= htmlspecialchars($v['valor_materiais']??'') ?>"></div>
      <div class="form-group"><label>Valor Serviços (R$)</label><input type="number" step="0.01" name="valor_servicos" value="<?= htmlspecialchars($v['valor_servicos']??'') ?>"></div>
      <div class="form-group"><label>Desconto (R$)</label><input type="number" step="0.01" name="desconto" value="<?= htmlspecialchars($v['desconto']??0) ?>"></div>
      <div class="form-group"><label>Valor Total (R$)</label><input type="number" step="0.01" name="valor_total" value="<?= htmlspecialchars($v['valor_total']??'') ?>"></div>
    </div>
    <div class="form-row cols-2">
      <div class="form-group"><label>Forma de Pagamento</label><input type="text" name="forma_pagamento" placeholder="Ex: 50% entrada + 50% na instalação" value="<?= htmlspecialchars($v['forma_pagamento']??'') ?>"></div>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-header"><span class="card-title">Observações</span></div>
  <div class="card-body">
    <div class="form-row cols-2">
      <div class="form-group"><label>Observações Gerais</label><textarea name="observacoes" rows="4"><?= htmlspecialchars($v['observacoes']??'') ?></textarea></div>
      <div class="form-group"><label>Observações Técnicas</label><textarea name="observacoes_tecnicas" rows="4"><?= htmlspecialchars($v['observacoes_tecnicas']??'') ?></textarea></div>
    </div>
  </div>
</div>

<div style="display:flex;gap:12px;justify-content:flex-end;margin-bottom:32px">
  <a href="index.php" class="btn btn-outline">Cancelar</a>
  <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Salvar OS</button>
</div>
</form>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
