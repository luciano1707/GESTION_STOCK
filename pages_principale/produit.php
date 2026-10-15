<?php
// pages_principale/produit.php
require_once '../include/auth_check.php';
require_once '../include/connexion.php';
$msg=''; $msgType='';
$pageTitle='Produits';

if(isset($_GET['ajax'])){
    $q='%'.$_GET['q'].'%';
    $stmt=$pdo->prepare("SELECT p.*,f.nom_famille FROM produit p JOIN famille f ON p.id_famille=f.id_famille WHERE p.espece LIKE ? OR p.reference_produit LIKE ? OR f.nom_famille LIKE ? ORDER BY p.id_prod DESC");
    $stmt->execute([$q,$q,$q]);
    foreach($stmt->fetchAll() as $r){echo '<tr><td style="color:var(--text-muted);font-size:.78rem;">#'.$r['id_prod'].'</td><td><span class="badge badge-teal">'.htmlspecialchars($r['reference_produit']).'</span></td><td style="font-weight:500;">'.htmlspecialchars($r['espece']).'</td><td>'.htmlspecialchars($r['calibre']??'—').'</td><td><span class="badge badge-info">'.htmlspecialchars($r['nom_famille']).'</span></td><td><div style="display:flex;gap:6px;"><button onclick=\'fillEdit('.json_encode($r).')\' class="btn btn-warning btn-sm"><i class="fa-solid fa-pen"></i></button>'.(isAdmin()?'<a href="?action=delete&id='.$r['id_prod'].'" class="btn btn-danger btn-sm confirm-action" data-confirm="Supprimer ?"><i class="fa-solid fa-trash"></i></a>':'').'</div></td></tr>';}
    exit;
}
if($_SERVER['REQUEST_METHOD']==='POST'){$act=$_POST['action']??'';try{if($act==='create'){$pdo->prepare("INSERT INTO produit(id_famille,espece,calibre,reference_produit) VALUES(?,?,?,?)")->execute([$_POST['id_famille'],trim($_POST['espece']),trim($_POST['calibre']),trim($_POST['reference_produit'])]);$msg='Produit ajouté.';$msgType='success';}elseif($act==='update'){$pdo->prepare("UPDATE produit SET id_famille=?,espece=?,calibre=?,reference_produit=? WHERE id_prod=?")->execute([$_POST['id_famille'],trim($_POST['espece']),trim($_POST['calibre']),trim($_POST['reference_produit']),$_POST['id_prod']]);$msg='Produit modifié.';$msgType='success';}}catch(PDOException $e){$msg='Erreur : '.$e->getMessage();$msgType='danger';}}
if(($_GET['action']??'')==='delete'&&isAdmin()){try{$pdo->prepare("DELETE FROM produit WHERE id_prod=?")->execute([$_GET['id']]);$msg='Produit supprimé.';$msgType='success';}catch(PDOException $e){$msg='Impossible (stocks liés).';$msgType='danger';}}

$rows=$pdo->query("SELECT p.*,f.nom_famille FROM produit p JOIN famille f ON p.id_famille=f.id_famille ORDER BY p.id_prod DESC")->fetchAll();
$familles=$pdo->query("SELECT * FROM famille ORDER BY nom_famille")->fetchAll();
include_once '../include/page_start.php';
?>
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:10px;">
    <div style="display:flex;align-items:center;gap:10px;">
        <div style="width:40px;height:40px;background:var(--primary-light);border-radius:10px;display:flex;align-items:center;justify-content:center;"><i class="fa-solid fa-box" style="color:var(--primary)"></i></div>
        <div><h2 style="font-size:1.2rem;font-weight:700;color:var(--text-primary)">Gestion des Produits</h2><p style="font-size:.78rem;color:var(--text-muted)"><?=count($rows)?> produit(s)</p></div>
    </div>
    <?php if(isAdmin()): ?><button onclick="openModal('modalAdd')" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Ajouter</button><?php endif; ?>
</div>
<?php if($msg): ?><div class="alert alert-<?=$msgType?> alert-auto"><i class="fa-solid fa-circle-info"></i><?=htmlspecialchars($msg)?></div><?php endif; ?>
<div class="card">
    <div style="margin-bottom:14px;"><div class="search-wrap"><i class="fa-solid fa-magnifying-glass"></i><input type="text" id="searchInput" placeholder="Rechercher..."></div></div>
    <div class="table-wrapper"><table>
        <thead><tr><th>#</th><th>Référence</th><th>Espèce</th><th>Calibre</th><th>Famille</th><th>Actions</th></tr></thead>
        <tbody id="tableBody">
        <?php foreach($rows as $r): ?>
        <tr>
            <td style="color:var(--text-muted);font-size:.78rem;">#<?=$r['id_prod']?></td>
            <td><span class="badge badge-teal"><?=htmlspecialchars($r['reference_produit'])?></span></td>
            <td style="font-weight:500;"><?=htmlspecialchars($r['espece'])?></td>
            <td><?=htmlspecialchars($r['calibre']??'—')?></td>
            <td><span class="badge badge-info"><?=htmlspecialchars($r['nom_famille'])?></span></td>
            <td><div style="display:flex;gap:6px;">
                <button onclick='fillEdit(<?=json_encode($r)?>)' class="btn btn-warning btn-sm"><i class="fa-solid fa-pen"></i></button>
                <?php if(isAdmin()): ?><a href="?action=delete&id=<?=$r['id_prod']?>" class="btn btn-danger btn-sm confirm-action" data-confirm="Supprimer ce produit ?"><i class="fa-solid fa-trash"></i></a><?php endif; ?>
            </div></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table></div>
</div>
<?php function optFam($fams,$sel=null){foreach($fams as $f){echo '<option value="'.$f['id_famille'].'" '.($f['id_famille']==$sel?'selected':'').'>'.htmlspecialchars($f['nom_famille']).'</option>';}} ?>
<div id="modalAdd" class="modal-overlay"><div class="modal-box">
    <div class="modal-title"><i class="fa-solid fa-box" style="color:var(--primary)"></i> Nouveau produit</div>
    <form method="POST"><input type="hidden" name="action" value="create">
        <div class="form-group"><label class="form-label">Référence *</label><input type="text" name="reference_produit" class="form-control" required></div>
        <div class="form-group"><label class="form-label">Espèce *</label><input type="text" name="espece" class="form-control" required></div>
        <div class="form-group"><label class="form-label">Calibre</label><input type="text" name="calibre" class="form-control"></div>
        <div class="form-group"><label class="form-label">Famille *</label><select name="id_famille" class="form-control" required><option value="">-- Choisir --</option><?php optFam($familles); ?></select></div>
        <div style="display:flex;gap:10px;margin-top:16px;"><button type="submit" class="btn btn-primary" style="flex:1">Enregistrer</button><button type="button" onclick="closeModal('modalAdd')" class="btn btn-secondary" style="flex:1">Annuler</button></div>
    </form>
</div></div>
<div id="modalEdit" class="modal-overlay"><div class="modal-box">
    <div class="modal-title"><i class="fa-solid fa-pen" style="color:var(--warning)"></i> Modifier produit</div>
    <form method="POST"><input type="hidden" name="action" value="update"><input type="hidden" name="id_prod" id="e_id">
        <div class="form-group"><label class="form-label">Référence *</label><input type="text" name="reference_produit" id="e_ref" class="form-control" required></div>
        <div class="form-group"><label class="form-label">Espèce *</label><input type="text" name="espece" id="e_esp" class="form-control" required></div>
        <div class="form-group"><label class="form-label">Calibre</label><input type="text" name="calibre" id="e_cal" class="form-control"></div>
        <div class="form-group"><label class="form-label">Famille *</label><select name="id_famille" id="e_fam" class="form-control" required><option value="">-- Choisir --</option><?php optFam($familles); ?></select></div>
        <div style="display:flex;gap:10px;margin-top:16px;"><button type="submit" class="btn btn-primary" style="flex:1">Enregistrer</button><button type="button" onclick="closeModal('modalEdit')" class="btn btn-secondary" style="flex:1">Annuler</button></div>
    </form>
</div></div>
<?php include '../include/page_end.php'; ?>
<script>
function fillEdit(d){document.getElementById('e_id').value=d.id_prod;document.getElementById('e_ref').value=d.reference_produit;document.getElementById('e_esp').value=d.espece;document.getElementById('e_cal').value=d.calibre||'';document.getElementById('e_fam').value=d.id_famille;openModal('modalEdit');}
initSearch('searchInput','tableBody','produit.php');
</script></body></html>
