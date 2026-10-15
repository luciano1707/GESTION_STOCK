<?php
require_once '../include/auth_check.php';
require_once '../include/connexion.php';
$msg=''; $msgType='';
$pageTitle='Dépôts';

if(isset($_GET['ajax'])){$q='%'.$_GET['q'].'%';$stmt=$pdo->prepare("SELECT * FROM depot WHERE nom_depot LIKE ? OR code_depot LIKE ? ORDER BY id_depot DESC");$stmt->execute([$q,$q]);foreach($stmt->fetchAll() as $r){echo '<tr><td style="color:var(--text-muted);font-size:.78rem;">#'.$r['id_depot'].'</td><td><span class="badge badge-purple">'.htmlspecialchars($r['code_depot']).'</span></td><td style="font-weight:500;">'.htmlspecialchars($r['nom_depot']).'</td><td><div style="display:flex;gap:6px;"><button onclick=\'fillEdit('.json_encode($r).')\' class="btn btn-warning btn-sm"><i class="fa-solid fa-pen"></i></button>'.(isAdmin()?'<a href="?action=delete&id='.$r['id_depot'].'" class="btn btn-danger btn-sm confirm-action" data-confirm="Supprimer ce dépôt ?"><i class="fa-solid fa-trash"></i></a>':'').'</div></td></tr>';}exit;}

if($_SERVER['REQUEST_METHOD']==='POST'){$act=$_POST['action']??'';try{if($act==='create'){$pdo->prepare("INSERT INTO depot(code_depot,nom_depot) VALUES(?,?)")->execute([trim($_POST['code_depot']),trim($_POST['nom_depot'])]);$msg='Dépôt ajouté.';$msgType='success';}elseif($act==='update'){$pdo->prepare("UPDATE depot SET code_depot=?,nom_depot=? WHERE id_depot=?")->execute([trim($_POST['code_depot']),trim($_POST['nom_depot']),$_POST['id_depot']]);$msg='Dépôt modifié.';$msgType='success';}}catch(PDOException $e){$msg='Erreur : '.$e->getMessage();$msgType='danger';}}
if(($_GET['action']??'')==='delete'&&isAdmin()){try{$pdo->prepare("DELETE FROM depot WHERE id_depot=?")->execute([$_GET['id']]);$msg='Dépôt supprimé.';$msgType='success';}catch(PDOException $e){$msg='Impossible (stocks liés).';$msgType='danger';}}
$rows=$pdo->query("SELECT * FROM depot ORDER BY id_depot DESC")->fetchAll();
include_once '../include/page_start.php';
?>
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:10px;">
    <div style="display:flex;align-items:center;gap:10px;">
        <div style="width:40px;height:40px;background:#f5f3ff;border-radius:10px;display:flex;align-items:center;justify-content:center;"><i class="fa-solid fa-building" style="color:#7c3aed"></i></div>
        <div><h2 style="font-size:1.2rem;font-weight:700;color:var(--text-primary)">Gestion des Dépôts</h2><p style="font-size:.78rem;color:var(--text-muted)"><?=count($rows)?> dépôt(s)</p></div>
    </div>
    <button onclick="openModal('modalAdd')" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Ajouter</button>
</div>
<?php if($msg): ?><div class="alert alert-<?=$msgType?> alert-auto"><i class="fa-solid fa-circle-info"></i><?=htmlspecialchars($msg)?></div><?php endif; ?>
<div class="card">
    <div style="margin-bottom:14px;"><div class="search-wrap"><i class="fa-solid fa-magnifying-glass"></i><input type="text" id="searchInput" placeholder="Rechercher un dépôt..."></div></div>
    <div class="table-wrapper"><table>
        <thead><tr><th>#</th><th>Code</th><th>Nom dépôt</th><th>Actions</th></tr></thead>
        <tbody id="tableBody">
        <?php foreach($rows as $r): ?>
        <tr><td style="color:var(--text-muted);font-size:.78rem;">#<?=$r['id_depot']?></td><td><span class="badge badge-purple"><?=htmlspecialchars($r['code_depot'])?></span></td><td style="font-weight:500;"><?=htmlspecialchars($r['nom_depot'])?></td>
        <td><div style="display:flex;gap:6px;"><button onclick='fillEdit(<?=json_encode($r)?>)' class="btn btn-warning btn-sm"><i class="fa-solid fa-pen"></i></button><?php if(isAdmin()): ?><a href="?action=delete&id=<?=$r['id_depot']?>" class="btn btn-danger btn-sm confirm-action" data-confirm="Supprimer ce dépôt ?"><i class="fa-solid fa-trash"></i></a><?php endif; ?></div></td></tr>
        <?php endforeach; ?>
        </tbody>
    </table></div>
</div>
<div id="modalAdd" class="modal-overlay"><div class="modal-box"><div class="modal-title"><i class="fa-solid fa-plus" style="color:var(--primary)"></i> Nouveau dépôt</div>
<form method="POST"><input type="hidden" name="action" value="create">
<div class="form-group"><label class="form-label">Code dépôt *</label><input type="text" name="code_depot" class="form-control" required></div>
<div class="form-group"><label class="form-label">Nom dépôt *</label><input type="text" name="nom_depot" class="form-control" required></div>
<div style="display:flex;gap:10px;margin-top:16px;"><button type="submit" class="btn btn-primary" style="flex:1">Enregistrer</button><button type="button" onclick="closeModal('modalAdd')" class="btn btn-secondary" style="flex:1">Annuler</button></div>
</form></div></div>
<div id="modalEdit" class="modal-overlay"><div class="modal-box"><div class="modal-title"><i class="fa-solid fa-pen" style="color:var(--warning)"></i> Modifier dépôt</div>
<form method="POST"><input type="hidden" name="action" value="update"><input type="hidden" name="id_depot" id="e_id">
<div class="form-group"><label class="form-label">Code dépôt *</label><input type="text" name="code_depot" id="e_code" class="form-control" required></div>
<div class="form-group"><label class="form-label">Nom dépôt *</label><input type="text" name="nom_depot" id="e_nom" class="form-control" required></div>
<div style="display:flex;gap:10px;margin-top:16px;"><button type="submit" class="btn btn-primary" style="flex:1">Enregistrer</button><button type="button" onclick="closeModal('modalEdit')" class="btn btn-secondary" style="flex:1">Annuler</button></div>
</form></div></div>
<?php include '../include/page_end.php'; ?>
<script>
function fillEdit(d){document.getElementById('e_id').value=d.id_depot;document.getElementById('e_code').value=d.code_depot;document.getElementById('e_nom').value=d.nom_depot;openModal('modalEdit');}
initSearch('searchInput','tableBody','depot.php');
</script></body></html>
