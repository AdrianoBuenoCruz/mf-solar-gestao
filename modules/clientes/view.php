<?php
require_once __DIR__ . '/../../includes/header.php';
$db = getDB();
$id = (int)($_GET['id'] ?? 0);
$stmt = $db->prepare("SELECT * FROM clientes WHERE id = ?");
$stmt->execute([$id]);
$c = $stmt->fetch();
if (!$c) { header('Location: index.php'); exit; }

$os = $db->prepare("SELECT * FROM ordens_servico WHERE cliente_id = ? ORDER BY created_at DESC");
$os->execute([$id]);
$ordensServico = $os->fetchAll();
$statusLabel = ['orcamento'=>'Orçamento','aprovado'=>'Aprovado','em_andamento'=>'Em Andamento','instalado'=>'Instalado','vistoria'=>'Vistoria','concluido'=>'Concluído','cancelado'=>'Cancelado'];
?>

<div class="page-header">
  <div class="page-title"><i class="fa-solid fa-user"></i> <?= htmlspecialchars($c['tipo_pessoa']==='fisica' ? $c['nome'] : ($c['nome_fantasia']?:$c['razao_social'])) ?></div>
  <div style="display:flex;gap:8px">
    <a href="<?= BASE_URL ?>/modules/propostas/form.php?cliente_id=<?= $id ?>" class="btn btn-success"><i class="fa-solid fa-file-signature"></i> Nova proposta</a>
    <a href="form.php?id=<?= $id ?>" class="btn btn-primary"><i class="fa-solid fa-pen"></i> Editar</a>
    <a href="index.php" class="btn btn-outline"><i class="fa-solid fa-arrow-left"></i> Voltar</a>
  </div>
</div>

<div class="grid-2">
  <div>
    <div class="card">
      <div class="card-header"><span class="card-title">Dados <?= $c['tipo_pessoa']==='fisica'?'Pessoais':'da Empresa' ?></span></div>
      <div class="card-body">
        <?php if($c['tipo_pessoa']==='fisica'): ?>
        <p><strong>Nome:</strong> <?= htmlspecialchars($c['nome']) ?></p>
        <p><strong>CPF:</strong> <?= htmlspecialchars($c['cpf'] ?: '–') ?></p>
        <p><strong>RG:</strong> <?= htmlspecialchars($c['rg'] ?: '–') ?></p>
        <p><strong>Nascimento:</strong> <?= $c['data_nascimento'] ? date('d/m/Y',strtotime($c['data_nascimento'])) : '–' ?></p>
        <?php else: ?>
        <p><strong>Razão Social:</strong> <?= htmlspecialchars($c['razao_social']) ?></p>
        <p><strong>Nome Fantasia:</strong> <?= htmlspecialchars($c['nome_fantasia'] ?: '–') ?></p>
        <p><strong>CNPJ:</strong> <?= htmlspecialchars($c['cnpj'] ?: '–') ?></p>
        <p><strong>IE:</strong> <?= htmlspecialchars($c['inscricao_estadual'] ?: '–') ?></p>
        <p><strong>Responsável:</strong> <?= htmlspecialchars($c['responsavel'] ?: '–') ?></p>
        <?php endif; ?>
        <p><strong>Origem:</strong> <?= $c['origem'] ? ucfirst($c['origem']) : '–' ?></p>
        <p><strong>Cadastro:</strong> <?= date('d/m/Y H:i',strtotime($c['created_at'])) ?></p>
      </div>
    </div>

    <div class="card">
      <div class="card-header"><span class="card-title">Contato</span></div>
      <div class="card-body">
        <p><strong>E-mail:</strong> <?= htmlspecialchars($c['email'] ?: '–') ?></p>
        <p><strong>Telefone:</strong> <?= htmlspecialchars($c['telefone'] ?: '–') ?></p>
        <p><strong>Celular:</strong> <?= htmlspecialchars($c['celular'] ?: '–') ?></p>
        <p><strong>WhatsApp:</strong> <?= $c['whatsapp'] ? '<a href="https://wa.me/55'.preg_replace('/\D/','',$c['whatsapp']).'" target="_blank">'.htmlspecialchars($c['whatsapp']).' <i class="fa-brands fa-whatsapp" style="color:#25D366"></i></a>' : '–' ?></p>
      </div>
    </div>

    <div class="card">
      <div class="card-header"><span class="card-title">Endereço</span></div>
      <div class="card-body">
        <p><?= htmlspecialchars($c['logradouro'] ?: '') ?><?= $c['numero']?', '.$c['numero']:'' ?><?= $c['complemento']?' '.$c['complemento']:'' ?></p>
        <p><?= htmlspecialchars($c['bairro'] ?: '') ?></p>
        <p><?= htmlspecialchars($c['cidade'] ?: '') ?><?= $c['estado']?' – '.$c['estado']:'' ?> <?= $c['cep'] ? ' – CEP '.$c['cep'] : '' ?></p>
      </div>
    </div>
  </div>

  <div>
    <div class="card">
      <div class="card-header"><span class="card-title"><i class="fa-solid fa-solar-panel"></i> Dados da Instalação</span></div>
      <div class="card-body">
        <p><strong>Distribuidora:</strong> <?= htmlspecialchars($c['distribuidora'] ?: '–') ?></p>
        <p><strong>Nº UC:</strong> <?= htmlspecialchars($c['numero_uc'] ?: '–') ?></p>
        <p><strong>Classe Tarifária:</strong> <?= $c['classe_tarifaria'] ? ucfirst(str_replace('_',' ',$c['classe_tarifaria'])) : '–' ?></p>
        <p><strong>Tensão da Rede:</strong> <?= $c['tensao_rede'] ? ucfirst($c['tensao_rede']) : '–' ?></p>
        <p><strong>Consumo Médio:</strong> <?= $c['consumo_medio_kwh'] ? number_format($c['consumo_medio_kwh'],0,'.',',').' kWh/mês' : '–' ?></p>
        <p><strong>Demanda Contratada:</strong> <?= $c['demanda_contratada'] ? number_format($c['demanda_contratada'],2).' kW' : '–' ?></p>
        <p><strong>Área Disponível:</strong> <?= $c['area_disponivel'] ? number_format($c['area_disponivel'],2).' m²' : '–' ?></p>
        <p><strong>Tipo de Telhado:</strong> <?= $c['tipo_telhado'] ? ucfirst($c['tipo_telhado']) : '–' ?></p>
        <p><strong>Orientação:</strong> <?= htmlspecialchars($c['orientacao_telhado'] ?: '–') ?></p>
        <p><strong>Inclinação:</strong> <?= $c['inclinacao_telhado'] ? $c['inclinacao_telhado'].'°' : '–' ?></p>
        <?php if($c['coordenadas_lat'] && $c['coordenadas_lng']): ?>
        <p><a href="https://maps.google.com/?q=<?= $c['coordenadas_lat'] ?>,<?= $c['coordenadas_lng'] ?>" target="_blank" class="btn btn-outline btn-sm"><i class="fa-solid fa-map-pin"></i> Ver no Google Maps</a></p>
        <?php endif; ?>
      </div>
    </div>

    <?php if($c['observacoes']): ?>
    <div class="card">
      <div class="card-header"><span class="card-title">Observações</span></div>
      <div class="card-body"><p><?= nl2br(htmlspecialchars($c['observacoes'])) ?></p></div>
    </div>
    <?php endif; ?>
  </div>
</div>

<!-- HISTÓRICO DE OS -->
<div class="card">
  <div class="card-header">
    <span class="card-title"><i class="fa-solid fa-file-invoice"></i> Ordens de Serviço (<?= count($ordensServico) ?>)</span>
    <a href="<?= BASE_URL ?>/modules/os/form.php?cliente_id=<?= $id ?>" class="btn btn-primary btn-sm"><i class="fa-solid fa-plus"></i> Nova OS</a>
  </div>
  <div class="table-wrap">
    <table>
      <thead><tr><th>Nº OS</th><th>Tipo</th><th>Status</th><th>Potência</th><th>Valor</th><th>Data</th><th></th></tr></thead>
      <tbody>
      <?php foreach($ordensServico as $os): ?>
      <tr>
        <td><a href="<?= BASE_URL ?>/modules/os/view.php?id=<?= $os['id'] ?>" style="color:var(--primary);font-weight:600"><?= htmlspecialchars($os['numero'] ?: '#'.$os['id']) ?></a></td>
        <td><?= ucfirst(str_replace('_',' ',$os['tipo'])) ?></td>
        <td><span class="badge badge-<?= match($os['status']){ 'concluido'=>'success','cancelado'=>'danger','em_andamento'=>'warning',default=>'info' } ?>"><?= $statusLabel[$os['status']] ?? $os['status'] ?></span></td>
        <td><?= $os['potencia_projeto'] ? number_format($os['potencia_projeto'],2).' kWp' : '–' ?></td>
        <td>R$ <?= number_format($os['valor_total'],2,',','.') ?></td>
        <td><?= date('d/m/Y',strtotime($os['created_at'])) ?></td>
        <td><a href="<?= BASE_URL ?>/modules/os/view.php?id=<?= $os['id'] ?>" class="btn btn-outline btn-sm btn-icon"><i class="fa-solid fa-eye"></i></a></td>
      </tr>
      <?php endforeach; ?>
      <?php if(empty($ordensServico)): ?><tr><td colspan="7" style="text-align:center;padding:20px;color:#9CA3AF">Nenhuma OS para este cliente</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
