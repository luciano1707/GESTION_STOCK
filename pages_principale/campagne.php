<?php
require_once '../include/auth_check.php';
require_once '../include/connexion.php';
$msg=''; $msgType=''; $pageTitle='Campagnes';

if(isset($_GET['ajax'])){$q='%'.$_GET['q'].'%';$stmt=$pdo->prepare("SELECT c.*,n.nom_navire FROM campagne c JOIN navire n ON c.id_navire=n.id_navire WHERE n.nom_navire LIKE ? OR c.annee_camp LIKE ? ORDER BY c.id_camp DESC");$stmt->execute([$q,$q]);foreach($stmt->fetchAll() as $r){echo '<tr><td style="color:var(--text-muted);font-size:.78rem;">#'.$r['id_camp'].'</td><td><strong>'.$r['annee_camp'].'</strong></td><td>'.date('d/m/Y',strtotime($r['date_debut_camp'])).'</td><td>'.date('d/m/Y',strtotime($r['date_fin_camp'])).'</td><td>'.htmlspecialchars($r['nom_navire']).'</td><td><div style="display:flex;gap:6px;"><button onclick=\'fillEdit('.json_encode($r).')\' class="btn btn-warning btn-sm"><i class="fa-solid fa-pen"></i></button>'.(isAdmin()?'<a href="?action=delete&id='.$r['id_camp'].'" class="btn btn-danger btn-sm confirm-action" data-confirm="Supprimer ?"><i class="fa-solid fa-trash"></i></a>':'').'</div></td></tr>';}exit;}

if($_SERVER['REQUEST_METHOD']==='POST'){$act=$_POST['action']??'';try{if($act==='create'){$pdo->prepare("INSERT INTO campagne(annee_camp,date_debut_camp,date_fin_camp,id_navire) VALUES(?,?,?,?)")->execute([$_POST['annee_camp'],$_POST['date_debut_camp'],$_POST['date_fin_camp'],$_POST['id_navire']]);$msg='Campagne ajoutée.';$msgType='success';}elseif($act==='update'){$pdo->prepare("UPDATE campagne SET annee_camp=?,date_debut_camp=?,date_fin_camp=?,id_navire=? WHERE id_camp=?")->execute([$_POST['annee_camp'],$_POST['date_debut_camp'],$_POST['date_fin_camp'],$_POST['id_navire'],$_POST['id_camp']]);$msg='Campagne modifiée.';$msgType='success';}}catch(PDOException $e){$msg='Erreur : '.$e->getMessage();$msgType='danger';}}
if(($_GET['action']??'')==='delete'&&isAdmin()){try{$pdo->prepare("DELETE FROM campagne WHERE id_camp=?")->execute([$_GET['id']]);$msg='Supprimée.';$msgType='success';}catch(PDOException $e){$msg='Impossible (voyages liés).';$msgType='danger';}}

$rows=$pdo->query("SELECT c.*,n.nom_navire FROM campagne c JOIN navire n ON c.id_navire=n.id_navire ORDER BY c.id_camp DESC")->fetchAll();
$navires=$pdo->query("SELECT * FROM navire ORDER BY nom_navire")->fetchAll();
include_once '../include/page_start.php';
?>
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:10px;">
    <div style="display:flex;align-items:center;gap:10px;">
        <div style="width:40px;height:40px;background:#fefce8;border-radius:10px;display:flex;align-items:center;justify-content:center;"><i class="fa-solid fa-flag" style="color:#ca8a04"></i></div>
        <div><h2 style="font-size:1.2rem;font-weight:700;color:var(--text-primary)">Gestion des Campagnes</h2><p style="font-size:.78rem;color:var(--text-muted)"><?=count($rows)?> campagne(s)</p></div>
    </div>
    <button onclick="openModal('modalAdd')" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Ajouter</button>
</div>
<?php if($msg): ?><div class="alert alert-<?=$msgType?> alert-auto"><i class="fa-solid fa-circle-info"></i><?=htmlspecialchars($msg)?></div><?php endif; ?>
<div class="card">
    <div style="margin-bottom:14px;"><div class="search-wrap"><i class="fa-solid fa-magnifying-glass"></i><input type="text" id="searchInput" placeholder="Rechercher..."></div></div>
    <div class="table-wrapper"><table>
        <thead><tr><th>#</th><th>Année</th><th>Début</th><th>Fin</th><th>Navire</th><th>Actions</th></tr></thead>
        <tbody id="tableBody">
        <?php foreach($rows as $r): ?>
        <tr>
            <td style="color:var(--text-muted);font-size:.78rem;">#<?=$r['id_camp']?></td>
            <td><strong><?=$r['annee_camp']?></strong></td>
            <td><?=date('d/m/Y',strtotime($r['date_debut_camp']))?></td>
            <td><?=date('d/m/Y',strtotime($r['date_fin_camp']))?></td>
            <td><?=htmlspecialchars($r['nom_navire'])?></td>
            <td><div style="display:flex;gap:6px;">
                <button onclick='fillEdit(<?=json_encode($r)?>)' class="btn btn-warning btn-sm"><i class="fa-solid fa-pen"></i></button>
                <?php if(isAdmin()): ?><a href="?action=delete&id=<?=$r['id_camp']?>" class="btn btn-danger btn-sm confirm-action" data-confirm="Supprimer ?"><i class="fa-solid fa-trash"></i></a><?php endif; ?>
            </div></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table></div>
</div>
<?php function optNav($navs,$sel=null){foreach($navs as $n){echo '<option value="'.$n['id_navire'].'" '.($n['id_navire']==$sel?'selected':'').'>'.htmlspecialchars($n['nom_navire']).'</option>';}} ?>
<div id="modalAdd" class="modal-overlay"><div class="modal-box"><div class="modal-title"><i class="fa-solid fa-flag" style="color:var(--primary)"></i> Nouvelle campagne</div>
<form method="POST"><input type="hidden" name="action" value="create">
<div class="form-group"><label class="form-label">Année *</label><input type="number" name="annee_camp" class="form-control" required min="2000" max="2099" value="<?=date('Y')?>"></div>
<div class="form-group"><label class="form-label">Date début *</label><input type="date" name="date_debut_camp" class="form-control" required></div>
<div class="form-group"><label class="form-label">Date fin *</label><input type="date" name="date_fin_camp" class="form-control" required></div>
<div class="form-group"><label class="form-label">Navire *</label><select name="id_navire" class="form-control" required><option value="">-- Choisir --</option><?php optNav($navires); ?></select></div>
<div style="display:flex;gap:10px;margin-top:16px;"><button type="submit" class="btn btn-primary" style="flex:1">Enregistrer</button><button type="button" onclick="closeModal('modalAdd')" class="btn btn-secondary" style="flex:1">Annuler</button></div>
</form></div></div>
<div id="modalEdit" class="modal-overlay"><div class="modal-box"><div class="modal-title"><i class="fa-solid fa-pen" style="color:var(--warning)"></i> Modifier campagne</div>
<form method="POST"><input type="hidden" name="action" value="update"><input type="hidden" name="id_camp" id="e_id">
<div class="form-group"><label class="form-label">Année *</label><input type="number" name="annee_camp" id="e_an" class="form-control" required></div>
<div class="form-group"><label class="form-label">Date début *</label><input type="date" name="date_debut_camp" id="e_dd" class="form-control" required></div>
<div class="form-group"><label class="form-label">Date fin *</label><input type="date" name="date_fin_camp" id="e_df" class="form-control" required></div>
<div class="form-group"><label class="form-label">Navire *</label><select name="id_navire" id="e_nav" class="form-control" required><option value="">-- Choisir --</option><?php optNav($navires); ?></select></div>
<div style="display:flex;gap:10px;margin-top:16px;"><button type="submit" class="btn btn-primary" style="flex:1">Enregistrer</button><button type="button" onclick="closeModal('modalEdit')" class="btn btn-secondary" style="flex:1">Annuler</button></div>
</form></div></div>
<?php include '../include/page_end.php'; ?>
<script>
function fillEdit(d){document.getElementById('e_id').value=d.id_camp;document.getElementById('e_an').value=d.annee_camp;document.getElementById('e_dd').value=d.date_debut_camp;document.getElementById('e_df').value=d.date_fin_camp;document.getElementById('e_nav').value=d.id_navire;openModal('modalEdit');}
initSearch('searchInput','tableBody','campagne.php');
</script></body></html>
