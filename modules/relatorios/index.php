<?php
require_once __DIR__ . '/../../includes/header.php';
$db = getDB();

$tipo   = $_GET['tipo'] ?? 'vendas';
$tiposRelatorio=['vendas'=>'Vendas / OS','estoque'=>'Estoque','clientes'=>'Clientes Cadastrados'];if(hasRole('admin'))$tiposRelatorio['ponto']='Ponto Eletrônico';
if($tipo==='ponto'&&!hasRole('admin')){http_response_code(403);die('Relatório de ponto restrito ao administrador.');}
$de     = $_GET['de'] ?? date('Y-m-01');
$ate    = $_GET['ate'] ?? date('Y-m-d');

$dados = [];
$titulo = '';

switch ($tipo) {
    case 'vendas':
        $titulo = 'Relatório de Vendas / OS Concluídas';
        $stmt = $db->prepare("
            SELECT os.numero, os.data_conclusao, os.potencia_projeto,
                   os.valor_total, os.valor_materiais, os.valor_servicos,
                   c.nome, c.razao_social, c.cidade
            FROM ordens_servico os
            LEFT JOIN clientes c ON c.id=os.cliente_id
            WHERE os.status='concluido'
              AND os.data_conclusao BETWEEN ? AND ?
            ORDER BY os.data_conclusao DESC
        ");
        $stmt->execute([$de, $ate]);
        $dados = $stmt->fetchAll();
        break;

    case 'estoque':
        $titulo = 'Relatório de Estoque';
        $dados = $db->query("
            SELECT p.codigo, p.nome, p.marca, cat.nome as categoria,
                   p.unidade, p.estoque_atual, p.estoque_minimo,
                   p.preco_custo, p.preco_venda,
                   (p.estoque_atual * p.preco_custo) as valor_total
            FROM produtos p
            LEFT JOIN categorias cat ON cat.id=p.categoria_id
            WHERE p.ativo=1
            ORDER BY cat.nome, p.nome
        ")->fetchAll();
        break;

    case 'clientes':
        $titulo = 'Relatório de Clientes';
        $stmt = $db->prepare("
            SELECT c.*, COUNT(os.id) as total_os,
                   IFNULL(SUM(os.valor_total),0) as total_gasto
            FROM clientes c
            LEFT JOIN ordens_servico os ON os.cliente_id=c.id
            WHERE c.ativo=1
              AND c.created_at BETWEEN ? AND ?
            GROUP BY c.id
            ORDER BY c.created_at DESC
        ");
        $stmt->execute([$de.' 00:00:00', $ate.' 23:59:59']);
        $dados = $stmt->fetchAll();
        break;

    case 'ponto':
        $titulo = 'Relatório de Ponto';
        $stmt = $db->prepare("
            SELECT u.nome, r.data, r.entrada1, r.saida1, r.entrada2, r.saida2,
                   r.horas_trabalhadas, r.horas_extras, r.tipo_dia, r.justificativa
            FROM registros_ponto r
            JOIN usuarios u ON u.id=r.usuario_id
            WHERE r.data BETWEEN ? AND ?
            ORDER BY u.nome, r.data
        ");
        $stmt->execute([$de, $ate]);
        $dados = $stmt->fetchAll();
        break;
}

// Totais para vendas
$totalVendas = $tipo==='vendas' ? array_sum(array_column($dados,'valor_total')) : 0;
$totalKwp    = $tipo==='vendas' ? array_sum(array_column($dados,'potencia_projeto')) : 0;
?>

<div class="page-header">
  <div class="page-title"><i class="fa-solid fa-chart-bar"></i> Relatórios</div>
  <?php if(!empty($dados)): ?>
  <button onclick="window.print()" class="btn btn-outline"><i class="fa-solid fa-print"></i> Imprimir</button>
  <?php endif; ?>
</div>

<!-- FILTROS -->
<div class="card">
  <div class="card-body">
    <form method="GET" style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end">
      <div class="form-group" style="min-width:180px">
        <label>Tipo de Relatório</label>
        <select name="tipo">
          <?php foreach($tiposRelatorio as $k=>$l): ?>
          <option value="<?= $k ?>" <?= $tipo===$k?'selected':'' ?>><?= $l ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <?php if($tipo!=='estoque'): ?>
      <div class="form-group"><label>De</label><input type="date" name="de" value="<?= $de ?>"></div>
      <div class="form-group"><label>Até</label><input type="date" name="ate" value="<?= $ate ?>"></div>
      <?php endif; ?>
      <button type="submit" class="btn btn-primary"><i class="fa-solid fa-filter"></i> Filtrar</button>
    </form>
  </div>
</div>

<!-- RESULTADO -->
<div class="card">
  <div class="card-header">
    <span class="card-title"><?= $titulo ?></span>
    <span style="font-size:13px;color:#6B7280"><?= count($dados) ?> registros</span>
  </div>

  <?php if($tipo==='vendas'): ?>
  <div style="display:flex;gap:16px;padding:16px 20px;border-bottom:1px solid var(--gray-200)">
    <div class="kpi-card green" style="flex:1;margin:0"><div class="kpi-icon"><i class="fa-solid fa-dollar-sign"></i></div><div><div class="kpi-value">R$ <?= number_format($totalVendas,0,'.','.') ?></div><div class="kpi-label">Total faturado</div></div></div>
    <div class="kpi-card cyan" style="flex:1;margin:0"><div class="kpi-icon"><i class="fa-solid fa-solar-panel"></i></div><div><div class="kpi-value"><?= number_format($totalKwp,2) ?> kWp</div><div class="kpi-label">Potência instalada</div></div></div>
    <div class="kpi-card" style="flex:1;margin:0"><div class="kpi-icon"><i class="fa-solid fa-file-check"></i></div><div><div class="kpi-value"><?= count($dados) ?></div><div class="kpi-label">OS concluídas</div></div></div>
  </div>
  <div class="table-wrap">
    <table>
      <thead><tr><th>Nº OS</th><th>Cliente</th><th>Cidade</th><th>Potência</th><th>Materiais</th><th>Serviços</th><th>Total</th><th>Conclusão</th></tr></thead>
      <tbody>
      <?php foreach($dados as $r): ?>
      <tr>
        <td><?= htmlspecialchars($r['numero']??'–') ?></td>
        <td><?= htmlspecialchars($r['nome']?:$r['razao_social']) ?></td>
        <td><?= htmlspecialchars($r['cidade']??'–') ?></td>
        <td><?= $r['potencia_projeto'] ? number_format($r['potencia_projeto'],2).' kWp' : '–' ?></td>
        <td>R$ <?= number_format($r['valor_materiais'],2,',','.') ?></td>
        <td>R$ <?= number_format($r['valor_servicos'],2,',','.') ?></td>
        <td><strong>R$ <?= number_format($r['valor_total'],2,',','.') ?></strong></td>
        <td><?= $r['data_conclusao'] ? date('d/m/Y',strtotime($r['data_conclusao'])) : '–' ?></td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <?php elseif($tipo==='estoque'): ?>
  <div class="table-wrap">
    <table>
      <thead><tr><th>Código</th><th>Produto</th><th>Categoria</th><th>Estoque</th><th>Mínimo</th><th>Status</th><th>Custo Unit.</th><th>Valor em Estoque</th></tr></thead>
      <tbody>
      <?php foreach($dados as $r):
        $pct=$r['estoque_minimo']>0?$r['estoque_atual']/$r['estoque_minimo']:999;
        $cls=$pct<=1?'badge-danger':($pct<=1.5?'badge-warning':'badge-success');
        $txt=$pct<=1?'Crítico':($pct<=1.5?'Baixo':'OK');
      ?>
      <tr>
        <td><code><?= htmlspecialchars($r['codigo']) ?></code></td>
        <td><?= htmlspecialchars($r['nome']) ?><?= $r['marca']?' <small style="color:#9CA3AF">('.$r['marca'].')</small>':'' ?></td>
        <td><?= htmlspecialchars($r['categoria']??'–') ?></td>
        <td><?= number_format($r['estoque_atual'],0) ?> <?= $r['unidade'] ?></td>
        <td><?= number_format($r['estoque_minimo'],0) ?></td>
        <td><span class="badge <?= $cls ?>"><?= $txt ?></span></td>
        <td>R$ <?= number_format($r['preco_custo'],2,',','.') ?></td>
        <td><strong>R$ <?= number_format($r['valor_total'],2,',','.') ?></strong></td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <?php elseif($tipo==='clientes'): ?>
  <div class="table-wrap">
    <table>
      <thead><tr><th>Nome/Razão Social</th><th>Tipo</th><th>Cidade/UF</th><th>Contato</th><th>Distribuidora</th><th>OS</th><th>Total Investido</th><th>Cadastro</th></tr></thead>
      <tbody>
      <?php foreach($dados as $r): ?>
      <tr>
        <td><?= htmlspecialchars($r['tipo_pessoa']==='fisica'?$r['nome']:($r['nome_fantasia']?:$r['razao_social'])) ?></td>
        <td><span class="badge <?= $r['tipo_pessoa']==='fisica'?'badge-info':'badge-primary' ?>"><?= $r['tipo_pessoa']==='fisica'?'PF':'PJ' ?></span></td>
        <td><?= htmlspecialchars($r['cidade']??'–') ?><?= $r['estado']?' / '.$r['estado']:'' ?></td>
        <td><?= htmlspecialchars($r['celular']?:$r['telefone']??'–') ?></td>
        <td><?= htmlspecialchars($r['distribuidora']??'–') ?></td>
        <td><?= $r['total_os'] ?></td>
        <td>R$ <?= number_format($r['total_gasto'],2,',','.') ?></td>
        <td><?= date('d/m/Y',strtotime($r['created_at'])) ?></td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <?php elseif($tipo==='ponto'): ?>
  <div class="table-wrap">
    <table>
      <thead><tr><th>Colaborador</th><th>Data</th><th>Entrada 1</th><th>Saída 1</th><th>Entrada 2</th><th>Saída 2</th><th>Horas</th><th>H. Extra</th><th>Situação</th></tr></thead>
      <tbody>
      <?php foreach($dados as $r): ?>
      <tr>
        <td><?= htmlspecialchars($r['nome']) ?></td>
        <td><?= date('d/m/Y',strtotime($r['data'])) ?></td>
        <td><?= $r['entrada1']?substr($r['entrada1'],0,5):'–' ?></td>
        <td><?= $r['saida1']?substr($r['saida1'],0,5):'–' ?></td>
        <td><?= $r['entrada2']?substr($r['entrada2'],0,5):'–' ?></td>
        <td><?= $r['saida2']?substr($r['saida2'],0,5):'–' ?></td>
        <td><?= $r['horas_trabalhadas'] ? number_format($r['horas_trabalhadas'],2).'h' : '–' ?></td>
        <td><?= $r['horas_extras'] ? '<span style="color:var(--warning)">'.number_format($r['horas_extras'],2).'h</span>' : '0h' ?></td>
        <td><?= ucfirst(str_replace('_',' ',$r['tipo_dia'])) ?></td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>

  <?php if(empty($dados)): ?>
  <div class="card-body" style="text-align:center;padding:40px;color:#9CA3AF">
    <i class="fa-solid fa-chart-bar" style="font-size:32px;margin-bottom:8px;display:block"></i>
    Nenhum dado encontrado para o período selecionado.
  </div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
