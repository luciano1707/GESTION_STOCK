<?php
require_once '../include/auth_check.php';
require_once '../include/connexion.php';
$msg=''; $msgType='';
$pageTitle = 'Familles'; $pageIcon = 'fa-layer-group';

if (isset($_GET['ajax'])) {
    $q='%'.$_GET['q'].'%';
    $stmt=$pdo->prepare("SELECT * FROM famille WHERE nom_famille LIKE ? OR code_famille LIKE ? ORDER BY id_famille DESC");
    $stmt->execute([$q,$q]);
    foreach($stmt->fetchAll() as $r): ?>
    <tr>
        <td><?=$r['id_famille']?></td>
        <td><span class="badge badge-teal"><?=htmlspecialchars($r['code_famille'])?></span></td>
        <td><?=htmlspecialchars($r['nom_famille'])?></td>
        <td><div style="display:flex;gap:6px;">
            <button onclick='fillEdit(<?=json_encode($r)?>)' class="btn btn-warning btn-sm"><i class="fa-solid fa-pen"></i></button>
            <?php if(isAdmin()): ?><a href="?action=delete&id=<?=$r['id_famille']?>" class="btn btn-danger btn-sm confirm-action" data-confirm="Supprimer cette famille ?"><i class="fa-solid fa-trash"></i></a><?php endif; ?>
        </div></td>
    </tr>
    <?php endforeach; exit;
}
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $act=$_POST['action']??'';
    try {
        if ($act==='create') { $pdo->prepare("INSERT INTO famille(nom_famille,code_famille) VALUES(?,?)")->execute([trim($_POST['nom_famille']),trim($_POST['code_famille'])]); $msg='Famille ajoutée.'; $msgType='success'; }
        elseif ($act==='update') { $pdo->prepare("UPDATE famille SET nom_famille=?,code_famille=? WHERE id_famille=?")->execute([trim($_POST['nom_famille']),trim($_POST['code_famille']),$_POST['id_famille']]); $msg='Famille modifiée.'; $msgType='success'; }
    } catch(PDOException $e){ $msg='Erreur : '.$e->getMessage(); $msgType='danger'; }
}
if (($_GET['action']??'')==='delete' && isAdmin()) {
    try { $pdo->prepare("DELETE FROM famille WHERE id_famille=?")->execute([$_GET['id']]); $msg='Famille supprimée.'; $msgType='success'; }
    catch(PDOException $e){ $msg='Impossible (produits liés).'; $msgType='danger'; }
}
$rows = $pdo->query("SELECT * FROM famille ORDER BY id_famille DESC")->fetchAll();

include_once '../include/page_start.php';
?>

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:10px;">
    <div style="display:flex;align-items:center;gap:10px;">
        <div style="width:40px;height:40px;background:var(--primary-light);border-radius:10px;display:flex;align-items:center;justify-content:center;">
            <i class="fa-solid fa-layer-group" style="color:var(--primary)"></i>
        </div>
        <div>
            <h2 style="font-size:1.2rem;font-weight:700;color:var(--text-primary)">Gestion des Familles</h2>
            <p style="font-size:0.78rem;color:var(--text-muted)"><?=count($rows)?> famille(s) enregistrée(s)</p>
        </div>
    </div>
    <button onclick="openModal('modalAdd')" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Ajouter</button>
</div>

<?php if($msg): ?><div class="alert alert-<?=$msgType?> alert-auto"><i class="fa-solid fa-circle-info"></i><?=htmlspecialchars($msg)?></div><?php endif; ?>

<div class="card">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;flex-wrap:wrap;gap:10px;">
        <div class="search-wrap">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" id="searchInput" placeholder="Rechercher une famille...">
        </div>
    </div>
    <div class="table-wrapper">
        <table>
            <thead><tr><th>#</th><th>Code</th><th>Nom famille</th><th>Actions</th></tr></thead>
            <tbody id="tableBody">
            <?php foreach($rows as $r): ?>
            <tr>
                <td style="color:var(--text-muted);font-size:0.78rem;">#<?=$r['id_famille']?></td>
                <td><span class="badge badge-teal"><?=htmlspecialchars($r['code_famille'])?></span></td>
                <td style="font-weight:500;"><?=htmlspecialchars($r['nom_famille'])?></td>
                <td><div style="display:flex;gap:6px;">
                    <button onclick='fillEdit(<?=json_encode($r)?>)' class="btn btn-warning btn-sm"><i class="fa-solid fa-pen"></i></button>
                    <?php if(isAdmin()): ?><a href="?action=delete&id=<?=$r['id_famille']?>" class="btn btn-danger btn-sm confirm-action" data-confirm="Supprimer cette famille ?"><i class="fa-solid fa-trash"></i></a><?php endif; ?>
                </div></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Ajouter -->
<div id="modalAdd" class="modal-overlay"><div class="modal-box">
    <div class="modal-title"><i class="fa-solid fa-plus" style="color:var(--primary)"></i> Nouvelle famille</div>
    <form method="POST"><input type="hidden" name="action" value="create">
        <div class="form-group"><label class="form-label">Code famille *</label><input type="text" name="code_famille" class="form-control" required placeholder="ex: FAM001"></div>
        <div class="form-group"><label class="form-label">Nom famille *</label><input type="text" name="nom_famille" class="form-control" required placeholder="ex: Poissons"></div>
        <div style="display:flex;gap:10px;margin-top:16px;">
            <button type="submit" class="btn btn-primary" style="flex:1">Enregistrer</button>
            <button type="button" onclick="closeModal('modalAdd')" class="btn btn-secondary" style="flex:1">Annuler</button>
        </div>
    </form>
</div></div>

<!-- Modal Éditer -->
<div id="modalEdit" class="modal-overlay"><div class="modal-box">
    <div class="modal-title"><i class="fa-solid fa-pen" style="color:var(--warning)"></i> Modifier famille</div>
    <form method="POST"><input type="hidden" name="action" value="update"><input type="hidden" name="id_famille" id="e_id">
        <div class="form-group"><label class="form-label">Code famille *</label><input type="text" name="code_famille" id="e_code" class="form-control" required></div>
        <div class="form-group"><label class="form-label">Nom famille *</label><input type="text" name="nom_famille" id="e_nom" class="form-control" required></div>
        <div style="display:flex;gap:10px;margin-top:16px;">
            <button type="submit" class="btn btn-primary" style="flex:1">Enregistrer</button>
            <button type="button" onclick="closeModal('modalEdit')" class="btn btn-secondary" style="flex:1">Annuler</button>
        </div>
    </form>
</div></div>

<?php include '../include/page_end.php'; ?>
<script>
function fillEdit(d){ document.getElementById('e_id').value=d.id_famille; document.getElementById('e_code').value=d.code_famille; document.getElementById('e_nom').value=d.nom_famille; openModal('modalEdit'); }
initSearch('searchInput','tableBody','famille.php');
</script>
</body></html>
