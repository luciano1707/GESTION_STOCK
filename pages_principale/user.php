<?php
require_once '../include/auth_check.php';
requireAdmin();
require_once '../include/connexion.php';
$msg=''; $msgType=''; $pageTitle='Utilisateurs';

if(isset($_GET['ajax'])){$q='%'.$_GET['q'].'%';$stmt=$pdo->prepare("SELECT id,username,role,created_at FROM users WHERE username LIKE ? OR role LIKE ? ORDER BY id DESC");$stmt->execute([$q,$q]);foreach($stmt->fetchAll() as $r){$bClass=$r['role']==='admin'?'badge-warning':'badge-info';$iClass=$r['role']==='admin'?'fa-user-shield':'fa-user';echo '<tr><td style="color:var(--text-muted);font-size:.78rem;">#'.$r['id'].'</td><td style="font-weight:500;">'.htmlspecialchars($r['username']).($r['id']==$_SESSION['user_id']?' <span class="badge badge-teal" style="font-size:.65rem">Vous</span>':'').'</td><td><span class="badge '.$bClass.'"><i class="fa-solid '.$iClass.'"></i> '.$r['role'].'</span></td><td>'.date('d/m/Y',strtotime($r['created_at'])).'</td><td><div style="display:flex;gap:6px;"><button onclick=\'fillEdit('.json_encode($r).')\' class="btn btn-warning btn-sm"><i class="fa-solid fa-pen"></i></button>'.($r['id']!=$_SESSION['user_id']?'<a href="?action=delete&id='.$r['id'].'" class="btn btn-danger btn-sm confirm-action" data-confirm="Supprimer cet utilisateur ?"><i class="fa-solid fa-trash"></i></a>':'').'</div></td></tr>';}exit;}

if($_SERVER['REQUEST_METHOD']==='POST'){$act=$_POST['action']??'';try{if($act==='create'){if(empty($_POST['password'])){$msg='Mot de passe requis.';$msgType='danger';}else{$pdo->prepare("INSERT INTO users(username,password,role) VALUES(?,?,?)")->execute([trim($_POST['username']),$_POST['password'],$_POST['role']]);$msg='Utilisateur créé.';$msgType='success';}}elseif($act==='update'){if(!empty($_POST['password'])){$pdo->prepare("UPDATE users SET username=?,password=?,role=? WHERE id=?")->execute([trim($_POST['username']),$_POST['password'],$_POST['role'],$_POST['id']]);}else{$pdo->prepare("UPDATE users SET username=?,role=? WHERE id=?")->execute([trim($_POST['username']),$_POST['role'],$_POST['id']]);}$msg='Modifié.';$msgType='success';}}catch(PDOException $e){$msg='Erreur : '.$e->getMessage();$msgType='danger';}}
if(($_GET['action']??'')==='delete'){if($_GET['id']==$_SESSION['user_id']){$msg='Impossible de supprimer votre propre compte.';$msgType='danger';}else{$pdo->prepare("DELETE FROM users WHERE id=?")->execute([$_GET['id']]);$msg='Supprimé.';$msgType='success';}}

$rows=$pdo->query("SELECT id,username,role,created_at FROM users ORDER BY id DESC")->fetchAll();
include_once '../include/page_start.php';
?>
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:10px;">
    <div style="display:flex;align-items:center;gap:10px;">
        <div style="width:40px;height:40px;background:#fefce8;border-radius:10px;display:flex;align-items:center;justify-content:center;"><i class="fa-solid fa-users-gear" style="color:#ca8a04"></i></div>
        <div><h2 style="font-size:1.2rem;font-weight:700;color:var(--text-primary)">Gestion des Utilisateurs</h2><p style="font-size:.78rem;color:var(--text-muted)"><?=count($rows)?> utilisateur(s)</p></div>
    </div>
    <button onclick="openModal('modalAdd')" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Ajouter</button>
</div>
<?php if($msg): ?><div class="alert alert-<?=$msgType?> alert-auto"><i class="fa-solid fa-circle-info"></i><?=htmlspecialchars($msg)?></div><?php endif; ?>
<div class="card">
    <div style="margin-bottom:14px;"><div class="search-wrap"><i class="fa-solid fa-magnifying-glass"></i><input type="text" id="searchInput" placeholder="Rechercher..."></div></div>
    <div class="table-wrapper"><table>
        <thead><tr><th>#</th><th>Nom d'utilisateur</th><th>Rôle</th><th>Créé le</th><th>Actions</th></tr></thead>
        <tbody id="tableBody">
        <?php foreach($rows as $r): $bClass=$r['role']==='admin'?'badge-warning':'badge-info'; $iClass=$r['role']==='admin'?'fa-user-shield':'fa-user'; ?>
        <tr>
            <td style="color:var(--text-muted);font-size:.78rem;">#<?=$r['id']?></td>
            <td style="font-weight:500;"><?=htmlspecialchars($r['username'])?> <?=$r['id']==$_SESSION['user_id']?'<span class="badge badge-teal" style="font-size:.65rem">Vous</span>':''?></td>
            <td><span class="badge <?=$bClass?>"><i class="fa-solid <?=$iClass?>"></i> <?=$r['role']?></span></td>
            <td><?=date('d/m/Y',strtotime($r['created_at']))?></td>
            <td><div style="display:flex;gap:6px;">
                <button onclick='fillEdit(<?=json_encode($r)?>)' class="btn btn-warning btn-sm"><i class="fa-solid fa-pen"></i></button>
                <?php if($r['id']!=$_SESSION['user_id']): ?><a href="?action=delete&id=<?=$r['id']?>" class="btn btn-danger btn-sm confirm-action" data-confirm="Supprimer ?"><i class="fa-solid fa-trash"></i></a><?php endif; ?>
            </div></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table></div>
</div>
<div id="modalAdd" class="modal-overlay"><div class="modal-box"><div class="modal-title"><i class="fa-solid fa-user-plus" style="color:var(--primary)"></i> Nouvel utilisateur</div>
<form method="POST"><input type="hidden" name="action" value="create">
<div class="form-group"><label class="form-label">Nom d'utilisateur *</label><input type="text" name="username" class="form-control" required></div>
<div class="form-group"><label class="form-label">Mot de passe *</label><input type="password" name="password" class="form-control" required autocomplete="new-password"></div>
<div class="form-group"><label class="form-label">Rôle *</label><select name="role" class="form-control"><option value="user">Utilisateur</option><option value="admin">Administrateur</option></select></div>
<div style="display:flex;gap:10px;margin-top:16px;"><button type="submit" class="btn btn-primary" style="flex:1">Créer</button><button type="button" onclick="closeModal('modalAdd')" class="btn btn-secondary" style="flex:1">Annuler</button></div>
</form></div></div>
<div id="modalEdit" class="modal-overlay"><div class="modal-box"><div class="modal-title"><i class="fa-solid fa-pen" style="color:var(--warning)"></i> Modifier utilisateur</div>
<form method="POST"><input type="hidden" name="action" value="update"><input type="hidden" name="id" id="e_id">
<div class="form-group"><label class="form-label">Nom d'utilisateur *</label><input type="text" name="username" id="e_user" class="form-control" required></div>
<div class="form-group"><label class="form-label">Nouveau mot de passe <span style="color:var(--text-muted);font-weight:400">(vide = inchangé)</span></label><input type="password" name="password" class="form-control" autocomplete="new-password"></div>
<div class="form-group"><label class="form-label">Rôle *</label><select name="role" id="e_role" class="form-control"><option value="user">Utilisateur</option><option value="admin">Administrateur</option></select></div>
<div style="display:flex;gap:10px;margin-top:16px;"><button type="submit" class="btn btn-primary" style="flex:1">Enregistrer</button><button type="button" onclick="closeModal('modalEdit')" class="btn btn-secondary" style="flex:1">Annuler</button></div>
</form></div></div>
<?php include '../include/page_end.php'; ?>
<script>
function fillEdit(d){document.getElementById('e_id').value=d.id;document.getElementById('e_user').value=d.username;document.getElementById('e_role').value=d.role;openModal('modalEdit');}
initSearch('searchInput','tableBody','user.php');
</script></body></html>
