<?php
require_once __DIR__ . '/includes/header.php';
$db = getDB();

// KPIs
$totalClientes   = $db->query("SELECT COUNT(*) FROM clientes WHERE ativo=1")->fetchColumn();
$osAbertas       = $db->query("SELECT COUNT(*) FROM ordens_servico WHERE status NOT IN ('concluido','cancelado')")->fetchColumn();
$osConcluidas    = $db->query("SELECT COUNT(*) FROM ordens_servico WHERE status='concluido'")->fetchColumn();
$faturamentoMes  = $db->query("SELECT IFNULL(SUM(valor_total),0) FROM ordens_servico WHERE status='concluido' AND MONTH(data_conclusao)=MONTH(CURDATE()) AND YEAR(data_conclusao)=YEAR(CURDATE())")->fetchColumn();
$estoqueCritico  = $db->query("SELECT COUNT(*) FROM produtos WHERE ativo=1 AND estoque_atual <= estoque_minimo")->fetchColumn();
$receitaMes      = $db->query("SELECT IFNULL(SUM(valor),0) FROM contas WHERE tipo='receber' AND status='pago' AND MONTH(data_pagamento)=MONTH(CURDATE()) AND YEAR(data_pagamento)=YEAR(CURDATE())")->fetchColumn();

// OS por status
$osStatus = $db->query("SELECT status, COUNT(*) as total FROM ordens_servico WHERE status NOT IN ('cancelado') GROUP BY status")->fetchAll();

// Últimas OS
$ultimasOS = $db->query("
    SELECT os.*, c.nome, c.razao_social FROM ordens_servico os
    LEFT JOIN clientes c ON c.id = os.cliente_id
    ORDER BY os.created_at DESC LIMIT 8
")->fetchAll();

// Estoque crítico
$produtosCriticos = $db->query("
    SELECT p.*, cat.nome as categoria FROM produtos p
    LEFT JOIN categorias cat ON cat.id = p.categoria_id
    WHERE p.ativo=1 AND p.estoque_atual <= p.estoque_minimo
    ORDER BY (p.estoque_atual/NULLIF(p.estoque_minimo,0)) ASC
    LIMIT 6
")->fetchAll();

// Gráfico: OS por mês (últimos 6 meses)
$graficoOS = $db->query("
    SELECT DATE_FORMAT(created_at,'%b/%y') as mes, COUNT(*) as total
    FROM ordens_servico
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(created_at,'%Y-%m')
    ORDER BY MIN(created_at) ASC
")->fetchAll();

$labelsMes = array_column($graficoOS, 'mes');
$dadosMes  = array_column($graficoOS, 'total');

$statusLabel = ['orcamento'=>'Orçamento','aprovado'=>'Aprovado','em_andamento'=>'Em Andamento','instalado'=>'Instalado','vistoria'=>'Vistoria','concluido'=>'Concluído'];
$statusColor = ['orcamento'=>'#9CA3AF','aprovado'=>'#3B82F6','em_andamento'=>'#F59E0B','instalado'=>'#06B6D4','vistoria'=>'#8B5CF6','concluido'=>'#10B981'];
?>

<div class="page-header">
  <div class="page-title">Dashboard <small>Visão geral do negócio</small></div>
  <span style="color:#9CA3AF;font-size:13px"><i class="fa-solid fa-calendar-days"></i> <?= date('d/m/Y') ?></span>
</div>

<!-- KPIs -->
<div class="kpi-grid">
  <div class="kpi-card yellow">
    <div class="kpi-icon"><i class="fa-solid fa-users"></i></div>
    <div><div class="kpi-value"><?= number_format($totalClientes) ?></div><div class="kpi-label">Clientes cadastrados</div></div>
  </div>
  <div class="kpi-card">
    <div class="kpi-icon"><i class="fa-solid fa-file-invoice"></i></div>
    <div><div class="kpi-value"><?= number_format($osAbertas) ?></div><div class="kpi-label">OS em aberto</div></div>
  </div>
  <div class="kpi-card green">
    <div class="kpi-icon"><i class="fa-solid fa-circle-check"></i></div>
    <div><div class="kpi-value"><?= number_format($osConcluidas) ?></div><div class="kpi-label">OS concluídas</div></div>
  </div>
  <div class="kpi-card cyan">
    <div class="kpi-icon"><i class="fa-solid fa-dollar-sign"></i></div>
    <div><div class="kpi-value">R$ <?= number_format($faturamentoMes,0,'.','.') ?></div><div class="kpi-label">Faturamento do mês</div></div>
  </div>
  <div class="kpi-card red">
    <div class="kpi-icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
    <div><div class="kpi-value"><?= $estoqueCritico ?></div><div class="kpi-label">Produtos em estoque crítico</div></div>
  </div>
</div>

<!-- CHARTS ROW -->
<div class="grid-2">
  <div class="card">
    <div class="card-header"><span class="card-title"><i class="fa-solid fa-chart-line"></i> OS por Mês</span></div>
    <div class="card-body"><canvas id="chartOS" height="180"></canvas></div>
  </div>
  <div class="card">
    <div class="card-header"><span class="card-title"><i class="fa-solid fa-chart-pie"></i> Status das OS</span></div>
    <div class="card-body"><canvas id="chartStatus" height="180"></canvas></div>
  </div>
</div>

<!-- ÚLTIMAS OS + ESTOQUE CRÍTICO -->
<div class="grid-2">
  <div class="card">
    <div class="card-header">
      <span class="card-title"><i class="fa-solid fa-clock-rotate-left"></i> Últimas Ordens de Serviço</span>
      <a href="<?= BASE_URL ?>/modules/os/index.php" class="btn btn-outline btn-sm">Ver todas</a>
    </div>
    <div class="card-body" style="padding:0">
      <div class="table-wrap">
        <table>
          <thead><tr><th>Nº OS</th><th>Cliente</th><th>Status</th><th>Valor</th></tr></thead>
          <tbody>
          <?php foreach($ultimasOS as $os): ?>
          <tr>
            <td><a href="<?= BASE_URL ?>/modules/os/view.php?id=<?= $os['id'] ?>" style="color:var(--primary);font-weight:600"><?= htmlspecialchars($os['numero'] ?: '#'.$os['id']) ?></a></td>
            <td><?= htmlspecialchars($os['nome'] ?: $os['razao_social']) ?></td>
            <td><span class="badge badge-<?= match($os['status']){ 'concluido'=>'success','cancelado'=>'danger','em_andamento'=>'warning',default=>'info' } ?>"><?= $statusLabel[$os['status']] ?? $os['status'] ?></span></td>
            <td>R$ <?= number_format($os['valor_total'],2,',','.') ?></td>
          </tr>
          <?php endforeach; ?>
          <?php if(empty($ultimasOS)): ?><tr><td colspan="4" style="text-align:center;color:#9CA3AF;padding:20px">Nenhuma OS cadastrada</td></tr><?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-header">
      <span class="card-title"><i class="fa-solid fa-triangle-exclamation" style="color:var(--danger)"></i> Estoque Crítico</span>
      <a href="<?= BASE_URL ?>/modules/estoque/index.php" class="btn btn-outline btn-sm">Ver estoque</a>
    </div>
    <div class="card-body" style="padding:0">
      <div class="table-wrap">
        <table>
          <thead><tr><th>Produto</th><th>Atual</th><th>Mínimo</th><th>Status</th></tr></thead>
          <tbody>
          <?php foreach($produtosCriticos as $p): ?>
          <tr>
            <td><?= htmlspecialchars($p['nome']) ?></td>
            <td class="stock-critical"><?= number_format($p['estoque_atual'],0) ?></td>
            <td><?= number_format($p['estoque_minimo'],0) ?></td>
            <td><span class="badge badge-danger">Crítico</span></td>
          </tr>
          <?php endforeach; ?>
          <?php if(empty($produtosCriticos)): ?><tr><td colspan="4" style="text-align:center;color:var(--success);padding:20px"><i class="fa-solid fa-circle-check"></i> Estoque OK</td></tr><?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script>
const ctx1 = document.getElementById('chartOS').getContext('2d');
new Chart(ctx1, {
    type: 'bar',
    data: {
        labels: <?= json_encode($labelsMes) ?>,
        datasets: [{ label: 'OS Abertas', data: <?= json_encode(array_map('intval',$dadosMes)) ?>, backgroundColor: '#3B82F6', borderRadius: 6 }]
    },
    options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
});

<?php
$statusData = []; $statusColors = []; $statusNames = [];
foreach($osStatus as $row){
    $statusNames[]  = $statusLabel[$row['status']] ?? $row['status'];
    $statusData[]   = (int)$row['total'];
    $statusColors[] = $statusColor[$row['status']] ?? '#9CA3AF';
}
?>
const ctx2 = document.getElementById('chartStatus').getContext('2d');
new Chart(ctx2, {
    type: 'doughnut',
    data: {
        labels: <?= json_encode($statusNames) ?>,
        datasets: [{ data: <?= json_encode($statusData) ?>, backgroundColor: <?= json_encode($statusColors) ?>, borderWidth: 2 }]
    },
    options: { responsive: true, plugins: { legend: { position: 'bottom', labels: { font: { size: 12 } } } } }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
