<?php
require_once __DIR__ . '/../../includes/header.php';
$db = getDB();
$id = (int)($_GET['id'] ?? 0);
$cliente = [];
if ($id) {
    $stmt = $db->prepare("SELECT * FROM clientes WHERE id = ?");
    $stmt->execute([$id]);
    $cliente = $stmt->fetch();
    if (!$cliente) { header('Location: index.php'); exit; }
}
$erros = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $d = $_POST;
    // Validações básicas
    if ($d['tipo_pessoa'] === 'fisica' && empty($d['nome'])) $erros[] = 'Nome é obrigatório.';
    if ($d['tipo_pessoa'] === 'juridica' && empty($d['razao_social'])) $erros[] = 'Razão Social é obrigatória.';

    if (empty($erros)) {
        $fields = [
            'tipo_pessoa','nome','cpf','rg','data_nascimento','razao_social','nome_fantasia','cnpj',
            'inscricao_estadual','responsavel','email','telefone','celular','whatsapp',
            'cep','logradouro','numero','complemento','bairro','cidade','estado',
            'distribuidora','numero_uc','classe_tarifaria','tensao_rede','consumo_medio_kwh',
            'demanda_contratada','coordenadas_lat','coordenadas_lng','area_disponivel',
            'tipo_telhado','orientacao_telhado','inclinacao_telhado','observacoes','origem'
        ];
        $vals = [];
        foreach($fields as $f) $vals[$f] = $d[$f] ?? null;
        // Limpar campos vazios para NULL
        foreach($vals as &$v) if ($v === '') $v = null;

        if ($id) {
            $set = implode(', ', array_map(fn($f)=>"$f=:$f", $fields));
            $stmt = $db->prepare("UPDATE clientes SET $set WHERE id=:id");
            $vals['id'] = $id;
        } else {
            $cols = implode(', ', $fields);
            $placeholders = implode(', ', array_map(fn($f)=>":$f", $fields));
            $stmt = $db->prepare("INSERT INTO clientes ($cols) VALUES ($placeholders)");
        }
        $stmt->execute($vals);
        $_SESSION['msg'] = $id ? 'Cliente atualizado com sucesso!' : 'Cliente cadastrado com sucesso!';
        header('Location: index.php');
        exit;
    }
}
$v = $cliente ?: $_POST;
$novo = !$id;
?>

<div class="page-header">
  <div class="page-title"><i class="fa-solid fa-user-plus"></i> <?= $novo?'Novo Cliente':'Editar Cliente' ?></div>
  <a href="index.php" class="btn btn-outline"><i class="fa-solid fa-arrow-left"></i> Voltar</a>
</div>

<?php foreach($erros as $e): ?><div class="alert alert-danger"><i class="fa-solid fa-circle-exclamation"></i> <?= $e ?></div><?php endforeach; ?>

<form method="POST">

<!-- TIPO DE PESSOA -->
<div class="card">
  <div class="card-header"><span class="card-title">Identificação</span></div>
  <div class="card-body">
    <div class="form-row cols-3">
      <div class="form-group">
        <label>Tipo de Pessoa</label>
        <select name="tipo_pessoa" id="tipo_pessoa">
          <option value="fisica" <?= ($v['tipo_pessoa']??'fisica')==='fisica'?'selected':'' ?>>Pessoa Física</option>
          <option value="juridica" <?= ($v['tipo_pessoa']??'')==='juridica'?'selected':'' ?>>Pessoa Jurídica</option>
        </select>
      </div>
      <div class="form-group">
        <label>Origem do Lead</label>
        <select name="origem">
          <?php foreach([''=>'Selecione','indicacao'=>'Indicação','site'=>'Site','instagram'=>'Instagram','facebook'=>'Facebook','google'=>'Google','ligacao'=>'Ligação','outro'=>'Outro'] as $val=>$lbl): ?>
          <option value="<?= $val ?>" <?= ($v['origem']??'')===$val?'selected':'' ?>><?= $lbl ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <!-- PESSOA FÍSICA -->
    <div id="campos_pf">
      <div class="form-section-title">Dados Pessoais</div>
      <div class="form-row cols-3">
        <div class="form-group"><label>Nome Completo *</label><input type="text" name="nome" value="<?= htmlspecialchars($v['nome']??'') ?>"></div>
        <div class="form-group"><label>CPF</label><input type="text" name="cpf" data-mask="cpf" value="<?= htmlspecialchars($v['cpf']??'') ?>"></div>
        <div class="form-group"><label>RG</label><input type="text" name="rg" value="<?= htmlspecialchars($v['rg']??'') ?>"></div>
      </div>
      <div class="form-row cols-3">
        <div class="form-group"><label>Data de Nascimento</label><input type="date" name="data_nascimento" value="<?= htmlspecialchars($v['data_nascimento']??'') ?>"></div>
      </div>
    </div>

    <!-- PESSOA JURÍDICA -->
    <div id="campos_pj" style="display:none">
      <div class="form-section-title">Dados da Empresa</div>
      <div class="form-row cols-2">
        <div class="form-group"><label>Razão Social *</label><input type="text" name="razao_social" value="<?= htmlspecialchars($v['razao_social']??'') ?>"></div>
        <div class="form-group"><label>Nome Fantasia</label><input type="text" name="nome_fantasia" value="<?= htmlspecialchars($v['nome_fantasia']??'') ?>"></div>
      </div>
      <div class="form-row cols-3">
        <div class="form-group"><label>CNPJ</label><input type="text" name="cnpj" data-mask="cnpj" value="<?= htmlspecialchars($v['cnpj']??'') ?>"></div>
        <div class="form-group"><label>Inscrição Estadual</label><input type="text" name="inscricao_estadual" value="<?= htmlspecialchars($v['inscricao_estadual']??'') ?>"></div>
        <div class="form-group"><label>Responsável</label><input type="text" name="responsavel" value="<?= htmlspecialchars($v['responsavel']??'') ?>"></div>
      </div>
    </div>
  </div>
</div>

<!-- CONTATO -->
<div class="card">
  <div class="card-header"><span class="card-title">Contato</span></div>
  <div class="card-body">
    <div class="form-row cols-4">
      <div class="form-group"><label>E-mail</label><input type="email" name="email" value="<?= htmlspecialchars($v['email']??'') ?>"></div>
      <div class="form-group"><label>Telefone</label><input type="text" name="telefone" data-mask="phone" value="<?= htmlspecialchars($v['telefone']??'') ?>"></div>
      <div class="form-group"><label>Celular</label><input type="text" name="celular" data-mask="phone" value="<?= htmlspecialchars($v['celular']??'') ?>"></div>
      <div class="form-group"><label>WhatsApp</label><input type="text" name="whatsapp" data-mask="phone" value="<?= htmlspecialchars($v['whatsapp']??'') ?>"></div>
    </div>
  </div>
</div>

<!-- ENDEREÇO -->
<div class="card">
  <div class="card-header"><span class="card-title">Endereço</span></div>
  <div class="card-body">
    <div class="form-row cols-4">
      <div class="form-group"><label>CEP</label><input type="text" name="cep" data-mask="cep" value="<?= htmlspecialchars($v['cep']??'') ?>"></div>
      <div class="form-group" style="grid-column:span 2"><label>Logradouro</label><input type="text" name="logradouro" value="<?= htmlspecialchars($v['logradouro']??'') ?>"></div>
      <div class="form-group"><label>Número</label><input type="text" name="numero" value="<?= htmlspecialchars($v['numero']??'') ?>"></div>
    </div>
    <div class="form-row cols-4">
      <div class="form-group"><label>Complemento</label><input type="text" name="complemento" value="<?= htmlspecialchars($v['complemento']??'') ?>"></div>
      <div class="form-group"><label>Bairro</label><input type="text" name="bairro" value="<?= htmlspecialchars($v['bairro']??'') ?>"></div>
      <div class="form-group"><label>Cidade</label><input type="text" name="cidade" value="<?= htmlspecialchars($v['cidade']??'') ?>"></div>
      <div class="form-group"><label>Estado (UF)</label>
        <select name="estado">
          <option value="">UF</option>
          <?php foreach(['AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SP','SE','TO'] as $uf): ?>
          <option value="<?= $uf ?>" <?= ($v['estado']??'')===$uf?'selected':'' ?>><?= $uf ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
  </div>
</div>

<!-- DADOS DA INSTALAÇÃO -->
<div class="card">
  <div class="card-header"><span class="card-title"><i class="fa-solid fa-solar-panel"></i> Dados da Instalação</span></div>
  <div class="card-body">
    <div class="form-section-title">Informações da UC (Unidade Consumidora)</div>
    <div class="form-row cols-4">
      <div class="form-group"><label>Distribuidora</label><input type="text" name="distribuidora" value="<?= htmlspecialchars($v['distribuidora']??'') ?>"></div>
      <div class="form-group"><label>Número UC</label><input type="text" name="numero_uc" value="<?= htmlspecialchars($v['numero_uc']??'') ?>"></div>
      <div class="form-group"><label>Classe Tarifária</label>
        <select name="classe_tarifaria">
          <?php foreach([''=>'Selecione','residencial'=>'Residencial','comercial'=>'Comercial','industrial'=>'Industrial','rural'=>'Rural','poder_publico'=>'Poder Público','iluminacao_publica'=>'Ilum. Pública'] as $val=>$lbl): ?>
          <option value="<?= $val ?>" <?= ($v['classe_tarifaria']??'')===$val?'selected':'' ?>><?= $lbl ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group"><label>Tensão da Rede</label>
        <select name="tensao_rede">
          <?php foreach([''=>'Selecione','monofasico'=>'Monofásico','bifasico'=>'Bifásico','trifasico'=>'Trifásico'] as $val=>$lbl): ?>
          <option value="<?= $val ?>" <?= ($v['tensao_rede']??'')===$val?'selected':'' ?>><?= $lbl ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
    <div class="form-row cols-4">
      <div class="form-group"><label>Consumo Médio (kWh/mês)</label><input type="number" step="0.01" name="consumo_medio_kwh" value="<?= htmlspecialchars($v['consumo_medio_kwh']??'') ?>"></div>
      <div class="form-group"><label>Demanda Contratada (kW)</label><input type="number" step="0.01" name="demanda_contratada" value="<?= htmlspecialchars($v['demanda_contratada']??'') ?>"></div>
      <div class="form-group"><label>Área Disponível (m²)</label><input type="number" step="0.01" name="area_disponivel" value="<?= htmlspecialchars($v['area_disponivel']??'') ?>"></div>
    </div>

    <div class="form-section-title">Local de Instalação (Telhado)</div>
    <div class="form-row cols-4">
      <div class="form-group"><label>Tipo de Telhado</label>
        <select name="tipo_telhado">
          <?php foreach([''=>'Selecione','fibrocimento'=>'Fibrocimento','ceramica'=>'Cerâmica','metalico'=>'Metálico','laje'=>'Laje','outro'=>'Outro'] as $val=>$lbl): ?>
          <option value="<?= $val ?>" <?= ($v['tipo_telhado']??'')===$val?'selected':'' ?>><?= $lbl ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group"><label>Orientação</label><input type="text" name="orientacao_telhado" placeholder="Ex: Norte" value="<?= htmlspecialchars($v['orientacao_telhado']??'') ?>"></div>
      <div class="form-group"><label>Inclinação (°)</label><input type="number" step="0.1" name="inclinacao_telhado" value="<?= htmlspecialchars($v['inclinacao_telhado']??'') ?>"></div>
    </div>
    <div class="form-row cols-2">
      <div class="form-group"><label>Latitude</label><input type="number" step="any" name="coordenadas_lat" value="<?= htmlspecialchars($v['coordenadas_lat']??'') ?>"></div>
      <div class="form-group"><label>Longitude</label><input type="number" step="any" name="coordenadas_lng" value="<?= htmlspecialchars($v['coordenadas_lng']??'') ?>"></div>
    </div>
  </div>
</div>

<!-- OBSERVAÇÕES -->
<div class="card">
  <div class="card-header"><span class="card-title">Observações</span></div>
  <div class="card-body">
    <div class="form-group">
      <label>Observações Gerais</label>
      <textarea name="observacoes" rows="4"><?= htmlspecialchars($v['observacoes']??'') ?></textarea>
    </div>
  </div>
</div>

<div style="display:flex;gap:12px;justify-content:flex-end;margin-bottom:32px">
  <a href="index.php" class="btn btn-outline">Cancelar</a>
  <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Salvar Cliente</button>
</div>

</form>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
