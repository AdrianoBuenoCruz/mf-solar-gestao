<?php
require_once __DIR__ . '/../../includes/header.php';
if(!hasRole('admin','gerente')){header('Location: '.BASE_URL.'/index.php');exit;}
$db = getDB();
$id=(int)($_GET['id']??0);
$u=[];
if($id){$st=$db->prepare("SELECT * FROM usuarios WHERE id=?");$st->execute([$id]);$u=$st->fetch();if(!$u){header('Location: index.php');exit;}}
if($_SERVER['REQUEST_METHOD']==='POST'){
    $d=$_POST;
    $nome=$d['nome'];$email=$d['email'];$perfil=$d['perfil'];$ativo=(int)($d['ativo']??1);
    if($id){
        if(!empty($d['senha'])){
            $db->prepare("UPDATE usuarios SET nome=?,email=?,perfil=?,ativo=?,senha=? WHERE id=?")->execute([$nome,$email,$perfil,$ativo,password_hash($d['senha'],PASSWORD_BCRYPT),$id]);
        } else {
            $db->prepare("UPDATE usuarios SET nome=?,email=?,perfil=?,ativo=? WHERE id=?")->execute([$nome,$email,$perfil,$ativo,$id]);
        }
    } else {
        $db->prepare("INSERT INTO usuarios (nome,email,perfil,senha,ativo) VALUES (?,?,?,?,?)")->execute([$nome,$email,$perfil,password_hash($d['senha'],PASSWORD_BCRYPT),$ativo]);
    }
    $_SESSION['msg']=$id?'Usuário atualizado!':'Usuário criado!';
    header('Location: index.php');exit;
}
$v=$u?:[];
?>
<div class="page-header">
  <div class="page-title"><i class="fa-solid fa-user-gear"></i> <?= $id?'Editar Usuário':'Novo Usuário' ?></div>
  <a href="index.php" class="btn btn-outline"><i class="fa-solid fa-arrow-left"></i> Voltar</a>
</div>
<div class="card">
  <div class="card-body">
    <form method="POST">
      <div class="form-row cols-2">
        <div class="form-group"><label>Nome *</label><input type="text" name="nome" required value="<?= htmlspecialchars($v['nome']??'') ?>"></div>
        <div class="form-group"><label>E-mail *</label><input type="email" name="email" required value="<?= htmlspecialchars($v['email']??'') ?>"></div>
        <div class="form-group"><label>Senha <?= $id?'(deixe vazio para manter)':' *' ?></label><input type="password" name="senha" <?= $id?'':'required' ?>></div>
        <div class="form-group"><label>Perfil</label>
          <select name="perfil">
            <?php foreach(['admin'=>'Administrador','gerente'=>'Gerente','tecnico'=>'Técnico','vendedor'=>'Vendedor','financeiro'=>'Financeiro'] as $k=>$l): ?>
            <option value="<?= $k ?>" <?= ($v['perfil']??'vendedor')===$k?'selected':'' ?>><?= $l ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group"><label>Status</label>
          <select name="ativo"><option value="1" <?= ($v['ativo']??1)?'selected':'' ?>>Ativo</option><option value="0" <?= isset($v['ativo'])&&!$v['ativo']?'selected':'' ?>>Inativo</option></select>
        </div>
      </div>
      <div style="display:flex;gap:12px;justify-content:flex-end;margin-top:8px">
        <a href="index.php" class="btn btn-outline">Cancelar</a>
        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Salvar</button>
      </div>
    </form>
  </div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
