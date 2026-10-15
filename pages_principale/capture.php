<?php
require_once '../include/auth_check.php';
require_once '../include/connexion.php';
$msg=''; $msgType=''; $pageTitle='Captures';

if(isset($_GET['export'])&&$_GET['export']==='pdf'){
    $rows=$pdo->query("SELECT ca.*,p.espece,p.reference_produit,n.nom_navire FROM capture ca JOIN produit p ON ca.id_prod=p.id_prod JOIN voyage v ON ca.id_voyage=v.id_voyage JOIN navire n ON v.id_navire=n.id_navire ORDER BY ca.date_capture DESC")->fetchAll();
    echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Rapport Captures</title><style>body{font-family:Arial;font-size:10px;margin:20px}h1{text-align:center;color:#0d9488}table{width:100%;border-collapse:collapse;margin-top:15px}th{background:#0d9488;color:white;padding:6px;text-align:left}td{padding:5px;border-bottom:1px solid #e5e7eb}tr:nth-child(even){background:#f0fdfa}.footer{text-align:center;margin-top:20px;color:#9ca3af;font-size:10px}</style></head><body>';
    echo '<div style="text-align:center"><img src="../images/logo_refrige.png" style="height:55px" onerror="this.style.display=\'none\'"></div>';
    echo '<h1>Rapport des Captures</h1><p style="text-align:center;color:#6b7280">Généré le '.date('d/m/Y H:i').'</p>';
    echo '<table><thead><tr><th>#</th><th>Produit</th><th>Réf.</th><th>Navire</th><th>Lieu</th><th>Quantité</th><th>Unité</th><th>Période</th><th>Date</th></tr></thead><tbody>';
    foreach($rows as $r) echo '<tr><td>#'.$r['id_capture'].'</td><td>'.htmlspecialchars($r['espece']).'</td><td>'.htmlspecialchars($r['reference_produit']).'</td><td>'.htmlspecialchars($r['nom_navire']).'</td><td>'.htmlspecialchars($r['lieu_capture']??'—').'</td><td>'.$r['quantite_capture'].'</td><td>'.$r['unite_capture'].'</td><td>'.$r['periode'].'</td><td>'.date('d/m/Y',strtotime($r['date_capture'])).'</td></tr>';
    echo '</tbody></table><div class="footer">Développé par RAKOTOMAMONJY Jean Luciano</div><script>window.print();<\/script></body></html>';
    exit;
}

if(isset($_GET['ajax'])){
    $q='%'.($_GET['q']??'').'%';
    $df=$_GET['date_from']??''; $dt=$_GET['date_to']??'';
    $sql="SELECT ca.*,p.espece,v.lieu_voyage FROM capture ca JOIN produit p ON ca.id_prod=p.id_prod JOIN voyage v ON ca.id_voyage=v.id_voyage WHERE (p.espece LIKE ? OR ca.lieu_capture LIKE ?)";
    $params=[$q,$q];
    if($df){$sql.=" AND ca.date_capture>=?";$params[]=$df;}
    if($dt){$sql.=" AND ca.date_capture<=?";$params[]=$dt;}
    $sql.=" ORDER BY ca.id_capture DESC";
    $stmt=$pdo->prepare($sql);$stmt->execute($params);
    foreach($stmt->fetchAll() as $r){
        $periodeClass=$r['periode']==='jour'?'badge-warning':'badge-info';
        echo '<tr><td style="color:var(--text-muted);font-size:.78rem;">#'.$r['id_capture'].'</td><td style="font-weight:500;">'.htmlspecialchars($r['espece']).'</td><td>#'.$r['id_voyage'].'</td><td>'.htmlspecialchars($r['lieu_capture']??'—').'</td><td><strong>'.$r['quantite_capture'].'</strong> '.$r['unite_capture'].'</td><td><span class="badge '.$periodeClass.'">'.$r['periode'].'</span></td><td>'.date('d/m/Y',strtotime($r['date_capture'])).'</td><td><div style="display:flex;gap:6px;"><button onclick=\'fillEdit('.json_encode($r).')\' class="btn btn-warning btn-sm"><i class="fa-solid fa-pen"></i></button>'.(isAdmin()?'<a href="?action=delete&id='.$r['id_capture'].'" class="btn btn-danger btn-sm confirm-action" data-confirm="Supprimer ?"><i class="fa-solid fa-trash"></i></a>':'').'</div></td></tr>';
    }
    exit;
}

if($_SERVER['REQUEST_METHOD']==='POST'){$act=$_POST['action']??'';try{if($act==='create'){$pdo->prepare("INSERT INTO capture(id_prod,id_voyage,lieu_capture,reference_capture,quantite_capture,unite_capture,date_capture,periode) VALUES(?,?,?,?,?,?,?,?)")->execute([$_POST['id_prod'],$_POST['id_voyage'],trim($_POST['lieu_capture']),trim($_POST['reference_capture']),$_POST['quantite_capture'],$_POST['unite_capture'],$_POST['date_capture'],$_POST['periode']]);$msg='Capture ajoutée.';$msgType='success';}elseif($act==='update'){$pdo->prepare("UPDATE capture SET id_prod=?,id_voyage=?,lieu_capture=?,reference_capture=?,quantite_capture=?,unite_capture=?,date_capture=?,periode=? WHERE id_capture=?")->execute([$_POST['id_prod'],$_POST['id_voyage'],trim($_POST['lieu_capture']),trim($_POST['reference_capture']),$_POST['quantite_capture'],$_POST['unite_capture'],$_POST['date_capture'],$_POST['periode'],$_POST['id_capture']]);$msg='Capture modifiée.';$msgType='success';}}catch(PDOException $e){$msg='Erreur : '.$e->getMessage();$msgType='danger';}}
if(($_GET['action']??'')==='delete'&&isAdmin()){try{$pdo->prepare("DELETE FROM capture WHERE id_capture=?")->execute([$_GET['id']]);$msg='Supprimée.';$msgType='success';}catch(PDOException $e){$msg='Impossible.';$msgType='danger';}}

$rows=$pdo->query("SELECT ca.*,p.espece,v.lieu_voyage FROM capture ca JOIN produit p ON ca.id_prod=p.id_prod JOIN voyage v ON ca.id_voyage=v.id_voyage ORDER BY ca.id_capture DESC")->fetchAll();
$produits=$pdo->query("SELECT * FROM produit ORDER BY espece")->fetchAll();
$voyages=$pdo->query("SELECT v.*,n.nom_navire FROM voyage v JOIN navire n ON v.id_navire=n.id_navire ORDER BY v.id_voyage DESC")->fetchAll();

function oP($p,$s=null){foreach($p as $v){echo '<option value="'.$v['id_prod'].'" '.($v['id_prod']==$s?'selected':'').'>'.htmlspecialchars($v['espece'].' ('.$v['reference_produit'].')').'</option>';}}
function oV($voys,$s=null){foreach($voys as $v){echo '<option value="'.$v['id_voyage'].'" '.($v['id_voyage']==$s?'selected':'').'>#'.$v['id_voyage'].' '.htmlspecialchars($v['nom_navire'].' ('.($v['lieu_voyage']??'—').')').'</option>';}}
function sUC($s='kg'){foreach(['tonne','kg','g'] as $o) echo '<option value="'.$o.'" '.($o==$s?'selected':'').'>'.$o.'</option>';}
function sPer($s='jour'){foreach(['jour','nuit'] as $o) echo '<option value="'.$o.'" '.($o==$s?'selected':'').'>'.$o.'</option>';}

include_once '../include/page_start.php';
?>
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:10px;">
    <div style="display:flex;align-items:center;gap:10px;">
        <div style="width:40px;height:40px;background:var(--primary-light);border-radius:10px;display:flex;align-items:center;justify-content:center;"><i class="fa-solid fa-fish" style="color:var(--primary)"></i></div>
        <div><h2 style="font-size:1.2rem;font-weight:700;color:var(--text-primary)">Gestion des Captures</h2><p style="font-size:.78rem;color:var(--text-muted)"><?=count($rows)?> capture(s)</p></div>
    </div>
    <div style="display:flex;gap:8px;">
        <a href="?export=pdf" target="_blank" class="btn btn-danger btn-sm"><i class="fa-solid fa-file-pdf"></i> Exporter PDF</a>
        <button onclick="openModal('modalAdd')" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Ajouter</button>
    </div>
</div>
<?php if($msg): ?><div class="alert alert-<?=$msgType?> alert-auto"><i class="fa-solid fa-circle-info"></i><?=htmlspecialchars($msg)?></div><?php endif; ?>
<div class="card">
    <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:14px;">
        <div class="search-wrap" style="flex:1;min-width:160px;">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" id="searchInput" placeholder="Rechercher produit, lieu…">
        </div>
        <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;">
            <label style="font-size:.75rem;color:var(--text-muted)">Du</label>
            <input type="date" id="filterFrom" class="form-control" style="width:135px;padding:6px 10px;font-size:.8rem;">
            <label style="font-size:.75rem;color:var(--text-muted)">Au</label>
            <input type="date" id="filterTo" class="form-control" style="width:135px;padding:6px 10px;font-size:.8rem;">
            <button onclick="resetFilters()" class="btn btn-secondary btn-sm"><i class="fa-solid fa-rotate-left"></i></button>
        </div>
    </div>
    <div class="table-wrapper"><table>
        <thead><tr><th>#</th><th>Produit</th><th>Voyage</th><th>Lieu</th><th>Quantité</th><th>Période</th><th>Date</th><th>Actions</th></tr></thead>
        <tbody id="tableBody">
        <?php foreach($rows as $r): $pClass=$r['periode']==='jour'?'badge-warning':'badge-info'; ?>
        <tr>
            <td style="color:var(--text-muted);font-size:.78rem;">#<?=$r['id_capture']?></td>
            <td style="font-weight:500;"><?=htmlspecialchars($r['espece'])?></td>
            <td>#<?=$r['id_voyage']?></td>
            <td><?=htmlspecialchars($r['lieu_capture']??'—')?></td>
            <td><strong><?=$r['quantite_capture']?></strong> <?=$r['unite_capture']?></td>
            <td><span class="badge <?=$pClass?>"><?=$r['periode']?></span></td>
            <td><?=date('d/m/Y',strtotime($r['date_capture']))?></td>
            <td><div style="display:flex;gap:6px;">
                <button onclick='fillEdit(<?=json_encode($r)?>)' class="btn btn-warning btn-sm"><i class="fa-solid fa-pen"></i></button>
                <?php if(isAdmin()): ?><a href="?action=delete&id=<?=$r['id_capture']?>" class="btn btn-danger btn-sm confirm-action" data-confirm="Supprimer ?"><i class="fa-solid fa-trash"></i></a><?php endif; ?>
            </div></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table></div>
</div>
<div id="modalAdd" class="modal-overlay"><div class="modal-box" style="max-width:550px"><div class="modal-title"><i class="fa-solid fa-fish" style="color:var(--primary)"></i> Nouvelle capture</div>
<form method="POST"><input type="hidden" name="action" value="create">
<div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
<div class="form-group" style="grid-column:1/-1"><label class="form-label">Produit *</label><select name="id_prod" class="form-control" required><option value="">-- Choisir --</option><?php oP($produits); ?></select></div>
<div class="form-group" style="grid-column:1/-1"><label class="form-label">Voyage *</label><select name="id_voyage" class="form-control" required><option value="">-- Choisir --</option><?php oV($voyages); ?></select></div>
<div class="form-group"><label class="form-label">Réf. capture</label><input type="text" name="reference_capture" class="form-control"></div>
<div class="form-group"><label class="form-label">Lieu capture</label><input type="text" name="lieu_capture" class="form-control"></div>
<div class="form-group"><label class="form-label">Quantité *</label><input type="number" step="0.01" name="quantite_capture" class="form-control" required></div>
<div class="form-group"><label class="form-label">Unité *</label><select name="unite_capture" class="form-control"><?php sUC(); ?></select></div>
<div class="form-group"><label class="form-label">Date *</label><input type="date" name="date_capture" class="form-control" required></div>
<div class="form-group"><label class="form-label">Période *</label><select name="periode" class="form-control"><?php sPer(); ?></select></div>
</div>
<div style="display:flex;gap:10px;margin-top:16px;"><button type="submit" class="btn btn-primary" style="flex:1">Enregistrer</button><button type="button" onclick="closeModal('modalAdd')" class="btn btn-secondary" style="flex:1">Annuler</button></div>
</form></div></div>
<div id="modalEdit" class="modal-overlay"><div class="modal-box" style="max-width:550px"><div class="modal-title"><i class="fa-solid fa-pen" style="color:var(--warning)"></i> Modifier capture</div>
<form method="POST"><input type="hidden" name="action" value="update"><input type="hidden" name="id_capture" id="e_id">
<div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
<div class="form-group" style="grid-column:1/-1"><label class="form-label">Produit *</label><select name="id_prod" id="e_prod" class="form-control" required><option value="">-- Choisir --</option><?php oP($produits); ?></select></div>
<div class="form-group" style="grid-column:1/-1"><label class="form-label">Voyage *</label><select name="id_voyage" id="e_voy" class="form-control" required><option value="">-- Choisir --</option><?php oV($voyages); ?></select></div>
<div class="form-group"><label class="form-label">Réf. capture</label><input type="text" name="reference_capture" id="e_ref" class="form-control"></div>
<div class="form-group"><label class="form-label">Lieu capture</label><input type="text" name="lieu_capture" id="e_lieu" class="form-control"></div>
<div class="form-group"><label class="form-label">Quantité *</label><input type="number" step="0.01" name="quantite_capture" id="e_qte" class="form-control" required></div>
<div class="form-group"><label class="form-label">Unité *</label><select name="unite_capture" id="e_unit" class="form-control"><?php sUC(); ?></select></div>
<div class="form-group"><label class="form-label">Date *</label><input type="date" name="date_capture" id="e_date" class="form-control" required></div>
<div class="form-group"><label class="form-label">Période *</label><select name="periode" id="e_per" class="form-control"><?php sPer(); ?></select></div>
</div>
<div style="display:flex;gap:10px;margin-top:16px;"><button type="submit" class="btn btn-primary" style="flex:1">Enregistrer</button><button type="button" onclick="closeModal('modalEdit')" class="btn btn-secondary" style="flex:1">Annuler</button></div>
</form></div></div>
<?php include '../include/page_end.php'; ?>
<script>
function fillEdit(d){document.getElementById('e_id').value=d.id_capture;document.getElementById('e_prod').value=d.id_prod;document.getElementById('e_voy').value=d.id_voyage;document.getElementById('e_ref').value=d.reference_capture||'';document.getElementById('e_lieu').value=d.lieu_capture||'';document.getElementById('e_qte').value=d.quantite_capture;document.getElementById('e_unit').value=d.unite_capture;document.getElementById('e_date').value=d.date_capture;document.getElementById('e_per').value=d.periode;openModal('modalEdit');}
function doFilter(){
    const q  = document.getElementById('searchInput').value;
    const df = document.getElementById('filterFrom').value;
    const dt = document.getElementById('filterTo').value;
    fetch('capture.php?ajax=1&q='+encodeURIComponent(q)+'&date_from='+df+'&date_to='+dt)
        .then(r=>r.text()).then(html=>{document.getElementById('tableBody').innerHTML=html||'<tr><td colspan="8" style="text-align:center;color:var(--text-muted);padding:20px">Aucun résultat</td></tr>';});
}
function resetFilters(){
    document.getElementById('searchInput').value='';
    document.getElementById('filterFrom').value='';
    document.getElementById('filterTo').value='';
    doFilter();
}
document.getElementById('searchInput').addEventListener('input', doFilter);
document.getElementById('filterFrom').addEventListener('change', doFilter);
document.getElementById('filterTo').addEventListener('change', doFilter);
</script></body></html>
