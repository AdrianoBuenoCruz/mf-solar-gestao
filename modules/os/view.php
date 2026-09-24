<?php
require_once __DIR__ . '/../../includes/header.php';
$db = getDB();
$id = (int)($_GET['id']??0);
$st = $db->prepare("SELECT os.*, c.nome as cnome, c.razao_social, c.nome_fantasia, c.celular, c.email, c.cidade, c.estado, u.nome as responsavel, t.nome as tecnico FROM ordens_servico os LEFT JOIN clientes c ON c.id=os.cliente_id LEFT JOIN usuarios u ON u.id=os.usuario_responsavel_id LEFT JOIN usuarios t ON t.id=os.tecnico_id WHERE os.id=?");
$st->execute([$id]); $os=$st->fetch();
if(!$os){header('Location: index.php');exit;}
$statusLabel=['orcamento'=>'Orçamento','aprovado'=>'Aprovado','em_andamento'=>'Em Andamento','instalado'=>'Instalado','vistoria'=>'Vistoria','concluido'=>'Concluído','cancelado'=>'Cancelado'];
$nomeCliente = $os['cnome'] ?: ($os['nome_fantasia']?:$os['razao_social']);
?>
<div class="page-header">
  <div class="page-title"><i class="fa-solid fa-file-invoice"></i> OS <?= htmlspecialchars($os['numero']?:'#'.$os['id']) ?></div>
  <div style="display:flex;gap:8px">
    <a href="form.php?id=<?= $id ?>" class="btn btn-primary"><i class="fa-solid fa-pen"></i> Editar</a>
    <a href="index.php" class="btn btn-outline"><i class="fa-solid fa-arrow-left"></i> Voltar</a>
  </div>
</div>
<div class="grid-2">
  <div class="card">
    <div class="card-header"><span class="card-title">Detalhes da OS</span><span class="badge badge-<?= match($os['status']){'concluido'=>'success','cancelado'=>'danger','em_andamento'=>'warning','aprovado'=>'primary',default=>'info'} ?>"><?= $statusLabel[$os['status']] ?></span></div>
    <div class="card-body">
      <p><strong>Número:</strong> <?= htmlspecialchars($os['numero']?:'#'.$os['id']) ?></p>
      <p><strong>Tipo:</strong> <?= ucfirst(str_replace('_',' ',$os['tipo'])) ?></p>
      <p><strong>Responsável Comercial:</strong> <?= htmlspecialchars($os['responsavel']??'–') ?></p>
      <p><strong>Técnico:</strong> <?= htmlspecialchars($os['tecnico']??'–') ?></p>
      <p><strong>Potência do Projeto:</strong> <?= $os['potencia_projeto']?number_format($os['potencia_projeto'],2).' kWp':'–' ?></p>
      <p><strong>Qtd. Painéis:</strong> <?= $os['quantidade_paineis']??'–' ?></p>
      <p><strong>Geração Estimada:</strong> <?= $os['geracao_estimada_kwh']?number_format($os['geracao_estimada_kwh'],0).' kWh/mês':'–' ?></p>
    </div>
  </div>
  <div class="card">
    <div class="card-header"><span class="card-title">Cliente</span></div>
    <div class="card-body">
      <p><strong>Nome:</strong> <a href="<?= BASE_URL ?>/modules/clientes/view.php?id=<?= $os['cliente_id'] ?>" style="color:var(--primary)"><?= htmlspecialchars($nomeCliente) ?></a></p>
      <p><strong>Celular:</strong> <?= htmlspecialchars($os['celular']??'–') ?></p>
      <p><strong>E-mail:</strong> <?= htmlspecialchars($os['email']??'–') ?></p>
      <p><strong>Cidade/UF:</strong> <?= htmlspecialchars($os['cidade']??'–') ?><?= $os['estado']?' / '.$os['estado']:'' ?></p>
    </div>
  </div>
  <div class="card">
    <div class="card-header"><span class="card-title">Datas</span></div>
    <div class="card-body">
      <p><strong>Orçamento:</strong> <?= $os['data_orcamento']?date('d/m/Y',strtotime($os['data_orcamento'])):'–' ?></p>
      <p><strong>Aprovação:</strong> <?= $os['data_aprovacao']?date('d/m/Y',strtotime($os['data_aprovacao'])):'–' ?></p>
      <p><strong>Instalação Prevista:</strong> <?= $os['data_instalacao_prevista']?date('d/m/Y',strtotime($os['data_instalacao_prevista'])):'–' ?></p>
      <p><strong>Instalação Real:</strong> <?= $os['data_instalacao_real']?date('d/m/Y',strtotime($os['data_instalacao_real'])):'–' ?></p>
      <p><strong>Conclusão:</strong> <?= $os['data_conclusao']?date('d/m/Y',strtotime($os['data_conclusao'])):'–' ?></p>
    </div>
  </div>
  <div class="card">
    <div class="card-header"><span class="card-title">Financeiro</span></div>
    <div class="card-body">
      <p><strong>Materiais:</strong> R$ <?= number_format($os['valor_materiais'],2,',','.') ?></p>
      <p><strong>Serviços:</strong> R$ <?= number_format($os['valor_servicos'],2,',','.') ?></p>
      <p><strong>Desconto:</strong> R$ <?= number_format($os['desconto'],2,',','.') ?></p>
      <p style="font-size:18px"><strong>Total:</strong> <strong style="color:var(--success)">R$ <?= number_format($os['valor_total'],2,',','.') ?></strong></p>
      <p><strong>Pagamento:</strong> <?= htmlspecialchars($os['forma_pagamento']??'–') ?></p>
    </div>
  </div>
</div>
<?php if($os['observacoes']||$os['observacoes_tecnicas']): ?>
<div class="grid-2">
  <?php if($os['observacoes']): ?><div class="card"><div class="card-header"><span class="card-title">Observações</span></div><div class="card-body"><?= nl2br(htmlspecialchars($os['observacoes'])) ?></div></div><?php endif; ?>
  <?php if($os['observacoes_tecnicas']): ?><div class="card"><div class="card-header"><span class="card-title">Obs. Técnicas</span></div><div class="card-body"><?= nl2br(htmlspecialchars($os['observacoes_tecnicas'])) ?></div></div><?php endif; ?>
</div>
<?php endif; ?>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
