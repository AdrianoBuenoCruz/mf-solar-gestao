<?php
// MÓDULO PONTO - INDEPENDENTE, SEM SIDEBAR
// Pode ser acessado de qualquer máquina sem login no sistema principal
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';

$db = getDB();
$msg = ''; $msgTipo = '';
$usuario = null; $batidas = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $matricula = trim($_POST['matricula'] ?? '');
    $senha     = trim($_POST['senha'] ?? '');

    if ($matricula && $senha) {
        $st = $db->prepare("SELECT * FROM usuarios WHERE (id=? OR email=?) AND ativo=1");
        $st->execute([$matricula, $matricula]);
        $u = $st->fetch();
        if ($u && password_verify($senha, $u['senha'])) {
            // Registrar batida
            $tipo = 'entrada';
            $ultimaBatida = $db->prepare("SELECT tipo FROM batidas_ponto WHERE usuario_id=? AND DATE(data_hora)=CURDATE() ORDER BY id DESC LIMIT 1");
            $ultimaBatida->execute([$u['id']]);
            $ultima = $ultimaBatida->fetchColumn();
            $tipo = (!$ultima || $ultima==='saida') ? 'entrada' : 'saida';

            $db->prepare("INSERT INTO batidas_ponto (usuario_id, tipo, data_hora, ip, dispositivo) VALUES (?,?,NOW(),?,?)")
               ->execute([$u['id'], $tipo, $_SERVER['REMOTE_ADDR']??'', $_SERVER['HTTP_USER_AGENT']??'']);

            // Atualizar registro do dia
            $reg = $db->prepare("SELECT * FROM registros_ponto WHERE usuario_id=? AND data=CURDATE()");
            $reg->execute([$u['id']]);
            $regAtual = $reg->fetch();
            $agora = date('H:i:s');

            if (!$regAtual) {
                $db->prepare("INSERT INTO registros_ponto (usuario_id,data,entrada1,ip_registro) VALUES (?,CURDATE(),?,?)")
                   ->execute([$u['id'],$agora,$_SERVER['REMOTE_ADDR']??'']);
            } else {
                // Preencher slots sequencialmente
                if ($tipo==='entrada') {
                    if (!$regAtual['entrada2']) $db->prepare("UPDATE registros_ponto SET entrada2=? WHERE id=?")->execute([$agora,$regAtual['id']]);
                    elseif (!$regAtual['entrada3']) $db->prepare("UPDATE registros_ponto SET entrada3=? WHERE id=?")->execute([$agora,$regAtual['id']]);
                } else {
                    if (!$regAtual['saida1']) $db->prepare("UPDATE registros_ponto SET saida1=? WHERE id=?")->execute([$agora,$regAtual['id']]);
                    elseif (!$regAtual['saida2']) $db->prepare("UPDATE registros_ponto SET saida2=? WHERE id=?")->execute([$agora,$regAtual['id']]);
                    elseif (!$regAtual['saida3']) $db->prepare("UPDATE registros_ponto SET saida3=? WHERE id=?")->execute([$agora,$regAtual['id']]);
                }
                // Somar apenas períodos completos, descontando os intervalos.
                $db->prepare("UPDATE registros_ponto SET horas_trabalhadas=(IF(entrada1 IS NOT NULL AND saida1 IS NOT NULL,TIME_TO_SEC(TIMEDIFF(saida1,entrada1)),0)+IF(entrada2 IS NOT NULL AND saida2 IS NOT NULL,TIME_TO_SEC(TIMEDIFF(saida2,entrada2)),0)+IF(entrada3 IS NOT NULL AND saida3 IS NOT NULL,TIME_TO_SEC(TIMEDIFF(saida3,entrada3)),0))/3600 WHERE id=?")->execute([$regAtual['id']]);
            }

            $msg = 'Olá, ' . $u['nome'] . '! ' . ($tipo==='entrada'?'✅ Entrada':'🚪 Saída') . ' registrada às ' . date('H:i');
            $msgTipo = 'ok';
            $usuario = $u;

            // Batidas de hoje
            $bs = $db->prepare("SELECT * FROM batidas_ponto WHERE usuario_id=? AND DATE(data_hora)=CURDATE() ORDER BY data_hora ASC");
            $bs->execute([$u['id']]);
            $batidas = $bs->fetchAll();
        } else {
            $msg = 'Matrícula ou senha incorretos.';
            $msgTipo = 'err';
        }
    } else {
        $msg = 'Preencha matrícula e senha.';
        $msgTipo = 'err';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Ponto Eletrônico – Solar Gestão</title>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<div class="ponto-terminal">
  <div class="ponto-card">
    <div class="ponto-logo"><i class="fa-solid fa-solar-panel"></i></div>
    <div class="ponto-empresa">Solar Gestão</div>
    <div class="ponto-relogio" id="relogio">--:--:--</div>
    <div class="ponto-data" id="dataAtual"></div>

    <?php if ($msg): ?>
    <div class="ponto-msg <?= $msgTipo ?>"><?= htmlspecialchars($msg) ?></div>
    <?php if (!empty($batidas)): ?>
    <div class="ponto-historico">
      <h4>Batidas de hoje</h4>
      <?php foreach($batidas as $b): ?>
      <div class="ponto-hit">
        <span class="tipo"><?= ucfirst($b['tipo']) ?></span>
        <span><?= date('H:i',strtotime($b['data_hora'])) ?></span>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
    <br>
    <a href="index.php" style="color:rgba(255,255,255,.5);font-size:13px">← Nova batida</a>

    <?php else: ?>
    <form method="POST">
      <div class="ponto-campo">
        <input type="text" name="matricula" placeholder="MATRÍCULA / E-MAIL" autofocus autocomplete="off">
      </div>
      <div class="ponto-campo">
        <input type="password" name="senha" placeholder="SENHA">
      </div>
      <button type="submit" class="btn-registrar">
        <i class="fa-solid fa-fingerprint"></i> REGISTRAR PONTO
      </button>
    </form>
    <?php endif; ?>
  </div>
</div>

<script>
function atualizaRelogio(){
    const agora = new Date();
    const h = String(agora.getHours()).padStart(2,'0');
    const m = String(agora.getMinutes()).padStart(2,'0');
    const s = String(agora.getSeconds()).padStart(2,'0');
    document.getElementById('relogio').textContent = h+':'+m+':'+s;
    const dias = ['Domingo','Segunda','Terça','Quarta','Quinta','Sexta','Sábado'];
    const meses = ['jan','fev','mar','abr','mai','jun','jul','ago','set','out','nov','dez'];
    document.getElementById('dataAtual').textContent =
        dias[agora.getDay()] + ', ' + agora.getDate() + ' de ' + meses[agora.getMonth()] + ' de ' + agora.getFullYear();
}
atualizaRelogio();
setInterval(atualizaRelogio, 1000);
<?php if($msg && $msgTipo==='ok'): ?>
// Auto redireciona após 5s
setTimeout(()=>location.href='index.php', 5000);
<?php endif; ?>
</script>
</body>
</html>
