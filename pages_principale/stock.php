<?php
// ============================================================
// stock.php — Gestion des Stocks (admin + user)
// ============================================================
require_once '../include/auth_check.php';
require_once '../include/connexion.php';
$msg=''; $msgType=''; $pageTitle='Stocks';
$username=$_SESSION['username']??'Utilisateur';

// ── ENDPOINT AJAX : infos capture pour remplissage dynamique ──
if(isset($_GET['capture_info'])){
    header('Content-Type: application/json');
    $id=(int)$_GET['capture_info'];
    $r=$pdo->prepare("SELECT ca.*,p.id_famille,p.espece,p.reference_produit,v.id_navire,n.nom_navire,v.lieu_voyage FROM capture ca JOIN produit p ON ca.id_prod=p.id_prod JOIN voyage v ON ca.id_voyage=v.id_voyage JOIN navire n ON v.id_navire=n.id_navire WHERE ca.id_capture=?");
    $r->execute([$id]); echo json_encode($r->fetch()); exit;
}

// ── EXPORT PDF ──
if(isset($_GET['export'])&&$_GET['export']==='pdf'){
    $dateFrom=$_GET['date_from']??''; $dateTo=$_GET['date_to']??'';
    $sql="SELECT s.*,p.espece,p.reference_produit,d.nom_depot,n.nom_navire FROM stock s JOIN produit p ON s.id_prod=p.id_prod JOIN depot d ON s.id_depot=d.id_depot LEFT JOIN navire n ON s.id_navire=n.id_navire WHERE 1=1";
    $params=[];
    if($dateFrom){$sql.=" AND s.date_entree_stock>=?";$params[]=$dateFrom;}
    if($dateTo)  {$sql.=" AND s.date_entree_stock<=?";$params[]=$dateTo;}
    $sql.=" ORDER BY s.date_peremption ASC,s.id_stock DESC";
    $stmt=$pdo->prepare($sql);$stmt->execute($params);$rows=$stmt->fetchAll();
    $totalQte=array_sum(array_column($rows,'quantite_stock'));
    $today=date('Y-m-d'); $nPerimes=0;
    foreach($rows as $r){if($r['date_peremption']&&$r['date_peremption']<$today)$nPerimes++;}
    $lp=realpath(__DIR__.'/../images/logo_refrige.png');
    $lb='';if($lp&&file_exists($lp)){$ext=strtolower(pathinfo($lp,PATHINFO_EXTENSION));$lb='data:image/'.($ext==='png'?'png':'jpeg').';base64,'.base64_encode(file_get_contents($lp));}
    header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8">
<title>Rapport Stocks</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}body{font-family:Arial,sans-serif;font-size:10px;color:#111}
.hdr{display:flex;align-items:center;padding:12px 20px;border-bottom:3px solid #14b8a6;margin-bottom:14px}
.hdr img{width:56px;height:56px;object-fit:contain;flex-shrink:0}
.hdr-ico{width:56px;height:56px;background:#14b8a6;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;color:#fff;font-size:20px}
.hdr-t{flex:1;text-align:center}.hdr-t h1{font-size:15px;font-weight:800;color:#0d9488}.hdr-t p{font-size:9px;color:#6b7280}
.hdr-m{width:155px;text-align:right;font-size:9px;color:#6b7280;line-height:1.8}.hdr-m strong{color:#111}
.stats{display:grid;grid-template-columns:repeat(4,1fr);gap:8px;padding:0 20px;margin-bottom:12px}
.sb{border:1px solid #e5e7eb;border-radius:7px;padding:9px;text-align:center}
.sb .v{font-size:17px;font-weight:800;color:#0d9488}.sb .l{font-size:9px;color:#6b7280;margin-top:2px}
.sb.red .v{color:#dc2626}
.sec{padding:0 20px;margin-bottom:14px}
.sec-t{font-size:11px;font-weight:700;color:#0d9488;border-bottom:1px solid #e5e7eb;padding-bottom:4px;margin-bottom:8px}
.filt{font-size:9px;color:#6b7280;margin-bottom:8px;padding:6px 10px;background:#f8fafc;border-radius:6px;border:1px solid #e5e7eb}
table{width:100%;border-collapse:collapse}
thead th{background:#0d9488;color:#fff;font-size:8.5px;font-weight:700;text-transform:uppercase;padding:5px 7px;text-align:left}
tbody td{padding:4px 7px;border-bottom:1px solid #f3f4f6}
tbody tr:nth-child(even){background:#f9fafb}
.perime td{background:#fef2f2!important;color:#dc2626}
.billet{padding:2px 5px;border-radius:999px;font-size:8.5px;font-weight:600}
.b-ok{background:#f0fdf4;color:#16a34a;border:1px solid #bbf7d0}
.b-w{background:#fffbeb;color:#92400e;border:1px solid #fde68a}
.b-e{background:#fef2f2;color:#dc2626;border:1px solid #fecaca}
@media print{body{-webkit-print-color-adjust:exact;print-color-adjust:exact}}
</style></head><body>
<div class="hdr">
  <?php if($lb): ?><img src="<?=$lb?>" alt="Logo"><?php else: ?><div class="hdr-ico">🐟</div><?php endif; ?>
  <div class="hdr-t"><h1>Rapport des Stocks</h1><p>Système de Gestion des Stocks – FishStock Pro</p></div>
  <div class="hdr-m">Date : <strong><?=date('d/m/Y')?></strong><br>Heure : <strong><?=date('H:i')?></strong><br>Par : <strong><?=htmlspecialchars($username)?></strong><br>Lignes : <strong><?=count($rows)?></strong></div>
</div>
<div class="stats">
  <div class="sb"><div class="v"><?=count($rows)?></div><div class="l">Lignes</div></div>
  <div class="sb"><div class="v"><?=number_format($totalQte,0)?></div><div class="l">Qté totale</div></div>
  <div class="sb <?=$nPerimes>0?'red':''?>"><div class="v"><?=$nPerimes?></div><div class="l">Expirés</div></div>
  <div class="sb"><div class="v"><?=date('d/m/Y')?></div><div class="l">Date rapport</div></div>
</div>
<?php if($dateFrom||$dateTo): ?>
<div class="sec"><div class="filt">📅 Filtre appliqué : <?=$dateFrom?'Du <strong>'.date('d/m/Y',strtotime($dateFrom)).'</strong>':''?> <?=$dateTo?'Au <strong>'.date('d/m/Y',strtotime($dateTo)).'</strong>':''?></div></div>
<?php endif; ?>
<div class="sec"><div class="sec-t">📦 Détail des Stocks</div>
<table><thead><tr><th>#</th><th>Produit</th><th>Réf.</th><th>Dépôt</th><th>Navire</th><th>Qté</th><th>Unité</th><th>Entrée</th><th>Péremption</th><th>Statut</th><th>Lot</th></tr></thead>
<tbody>
<?php $td=date('Y-m-d');$ts=date('Y-m-d',strtotime('+30 days'));
foreach($rows as $s):
  $p=$s['date_peremption']&&$s['date_peremption']<$td;
  $es=$s['date_peremption']&&$s['date_peremption']>=$td&&$s['date_peremption']<=$ts;
  $rc=$p?'perime':'';
  $pb=$s['date_peremption']?($p?'<span class="billet b-e">Périmé</span>':($es?'<span class="billet b-w">'.date('d/m/Y',strtotime($s['date_peremption'])).'</span>':'<span class="billet b-ok">'.date('d/m/Y',strtotime($s['date_peremption'])).'</span>')):'—';
?>
<tr class="<?=$rc?>">
  <td>#<?=$s['id_stock']?></td><td style="font-weight:600"><?=htmlspecialchars($s['espece'])?></td>
  <td><?=htmlspecialchars($s['reference_produit'])?></td><td><?=htmlspecialchars($s['nom_depot'])?></td>
  <td><?=htmlspecialchars($s['nom_navire']??'—')?></td>
  <td style="font-weight:700;color:#0d9488"><?=$s['quantite_stock']?></td><td><?=$s['unite']?></td>
  <td><?=date('d/m/Y',strtotime($s['date_entree_stock']))?></td><td><?=$pb?></td>
  <td><?=htmlspecialchars($s['statut']??'—')?></td><td><?=htmlspecialchars($s['lot']??'—')?></td>
</tr>
<?php endforeach; ?>
</tbody></table>
<p style="margin-top:7px;color:#6b7280;font-size:8.5px">* Rouge = périmé | Jaune = expire dans 30 j</p>
</div>
<script>window.print();</script>
</body></html>
<?php exit; }

// ── AJAX recherche + filtre date ──
if(isset($_GET['ajax'])){
    $q='%'.($_GET['q']??'').'%';
    $df=$_GET['date_from']??''; $dt=$_GET['date_to']??'';
    $sql="SELECT s.*,p.espece,p.reference_produit,d.nom_depot FROM stock s JOIN produit p ON s.id_prod=p.id_prod JOIN depot d ON s.id_depot=d.id_depot WHERE (p.espece LIKE ? OR d.nom_depot LIKE ? OR s.lot LIKE ?)";
    $params=[$q,$q,$q];
    if($df){$sql.=" AND s.date_entree_stock>=?";$params[]=$df;}
    if($dt){$sql.=" AND s.date_entree_stock<=?";$params[]=$dt;}
    $sql.=" ORDER BY s.id_stock DESC";
    $stmt=$pdo->prepare($sql);$stmt->execute($params);
    $td=date('Y-m-d');$ts=date('Y-m-d',strtotime('+30 days'));
    foreach($stmt->fetchAll() as $r){
        $pe=$r['date_peremption']&&$r['date_peremption']<$td;
        $ex=$r['date_peremption']&&$r['date_peremption']>=$td&&$r['date_peremption']<=$ts;
        $rs=$pe?'style="background:#fef2f2"':($ex?'style="background:#fffbeb"':'');
        $pb=$pe?'<span class="badge badge-danger"><i class="fa-solid fa-skull-crossbones"></i> Périmé</span>':($ex?'<span class="badge badge-warning"><i class="fa-solid fa-clock"></i> '.date('d/m/Y',strtotime($r['date_peremption'])).'</span>':($r['date_peremption']?'<span class="badge badge-success">'.date('d/m/Y',strtotime($r['date_peremption'])).'</span>':'<span style="color:var(--text-muted)">—</span>'));
        echo '<tr '.$rs.'>';
        echo '<td style="color:var(--text-muted);font-size:.78rem">#'.$r['id_stock'].'</td>';
        echo '<td style="font-weight:500">'.htmlspecialchars($r['espece']).'</td>';
        echo '<td>'.htmlspecialchars($r['nom_depot']).'</td>';
        echo '<td><strong>'.$r['quantite_stock'].'</strong> <span style="color:var(--text-muted);font-size:.8rem">'.$r['unite'].'</span></td>';
        echo '<td>'.date('d/m/Y',strtotime($r['date_entree_stock'])).'</td>';
        echo '<td>'.$pb.'</td>';
        echo '<td>'.htmlspecialchars($r['lot']??'—').'</td>';
        echo '<td>'.htmlspecialchars($r['statut']??'—').'</td>';
        echo '<td><div style="display:flex;gap:5px">';
        echo '<button onclick=\'fillEdit('.json_encode($r).')\' class="btn btn-warning btn-sm"><i class="fa-solid fa-pen"></i></button>';
        if(isAdmin()) echo '<a href="?action=delete&id='.$r['id_stock'].'" class="btn btn-danger btn-sm confirm-action" data-confirm="Supprimer ce stock ?"><i class="fa-solid fa-trash"></i></a>';
        echo '</div></td></tr>';
    }
    exit;
}

// ── CRUD ──
if($_SERVER['REQUEST_METHOD']==='POST'){
    $act=$_POST['action']??'';
    try{
        if($act==='create'){
            $pdo->prepare("INSERT INTO stock(id_prod,id_capture,id_depot,id_navire,quantite_stock,unite,date_entree_stock,date_peremption,statut,lot) VALUES(?,?,?,?,?,?,?,?,?,?)")
                ->execute([$_POST['id_prod'],$_POST['id_capture']??null,$_POST['id_depot'],$_POST['id_navire']??null,$_POST['quantite_stock'],$_POST['unite'],$_POST['date_entree_stock'],$_POST['date_peremption']??null,trim($_POST['statut']),trim($_POST['lot'])]);
            $msg='Stock ajouté avec succès.';$msgType='success';
        }elseif($act==='update'){
            $pdo->prepare("UPDATE stock SET id_prod=?,id_capture=?,id_depot=?,id_navire=?,quantite_stock=?,unite=?,date_entree_stock=?,date_peremption=?,statut=?,lot=? WHERE id_stock=?")
                ->execute([$_POST['id_prod'],$_POST['id_capture']??null,$_POST['id_depot'],$_POST['id_navire']??null,$_POST['quantite_stock'],$_POST['unite'],$_POST['date_entree_stock'],$_POST['date_peremption']??null,trim($_POST['statut']),trim($_POST['lot']),$_POST['id_stock']]);
            $msg='Stock modifié.';$msgType='success';
        }
    }catch(PDOException $e){$msg='Erreur : '.$e->getMessage();$msgType='danger';}
}
if(($_GET['action']??'')==='delete'&&isAdmin()){
    try{$pdo->prepare("DELETE FROM stock WHERE id_stock=?")->execute([$_GET['id']]);$msg='Supprimé.';$msgType='success';}
    catch(PDOException $e){$msg='Erreur.';$msgType='danger';}
}

// ── Données ──
$rows=$pdo->query("SELECT s.*,p.espece,p.reference_produit,d.nom_depot FROM stock s JOIN produit p ON s.id_prod=p.id_prod JOIN depot d ON s.id_depot=d.id_depot ORDER BY s.id_stock DESC")->fetchAll();
$produits=$pdo->query("SELECT * FROM produit ORDER BY espece")->fetchAll();
$depots=$pdo->query("SELECT * FROM depot ORDER BY nom_depot")->fetchAll();
$navires=$pdo->query("SELECT * FROM navire ORDER BY nom_navire")->fetchAll();
// Capture avec toutes les infos pour auto-fill
$captures=$pdo->query("SELECT ca.*,p.id_prod as fk_prod,p.espece,p.reference_produit,v.id_navire as fk_navire,n.nom_navire FROM capture ca JOIN produit p ON ca.id_prod=p.id_prod JOIN voyage v ON ca.id_voyage=v.id_voyage JOIN navire n ON v.id_navire=n.id_navire ORDER BY ca.id_capture DESC")->fetchAll();
$totalPerimes=$pdo->query("SELECT COUNT(*) FROM stock WHERE date_peremption IS NOT NULL AND date_peremption<CURDATE()")->fetchColumn();
$totalQte=$pdo->query("SELECT COALESCE(SUM(quantite_stock),0) FROM stock")->fetchColumn();

// JSON des captures pour autofill JS
$capturesJson=[];
foreach($captures as $c){
    $capturesJson[$c['id_capture']]=['id_prod'=>$c['fk_prod'],'espece'=>$c['espece'],'id_navire'=>$c['fk_navire'],'nom_navire'=>$c['nom_navire'],'quantite_capture'=>$c['quantite_capture'],'unite_capture'=>$c['unite_capture'],'date_capture'=>$c['date_capture'],'lieu_capture'=>$c['lieu_capture']??''];
}

function oPro($p,$s=null){foreach($p as $v) echo '<option value="'.$v['id_prod'].'" '.($v['id_prod']==$s?'selected':'').'>'.htmlspecialchars($v['espece'].' ('.$v['reference_produit'].')').'</option>';}
function oDep($d,$s=null){foreach($d as $v) echo '<option value="'.$v['id_depot'].'" '.($v['id_depot']==$s?'selected':'').'>'.htmlspecialchars($v['nom_depot']).'</option>';}
function oNa($n,$s=null){foreach($n as $v) echo '<option value="'.$v['id_navire'].'" '.($v['id_navire']==$s?'selected':'').'>'.htmlspecialchars($v['nom_navire']).'</option>';}
function oCap($c,$s=null){echo '<option value="">-- Aucune --</option>';foreach($c as $v) echo '<option value="'.$v['id_capture'].'" '.($v['id_capture']==$s?'selected':'').'>#'.$v['id_capture'].' '.htmlspecialchars($v['espece'].' '.($v['reference_capture']??'')).'</option>';}
function sUnite($s='kg'){foreach(['tonne','kg','en carton'] as $o) echo '<option value="'.$o.'" '.($o==$s?'selected':'').'>'.$o.'</option>';}

include_once '../include/page_start.php';
$today=date('Y-m-d');$soon=date('Y-m-d',strtotime('+30 days'));
?>

<!-- ═══ EN-TÊTE ═══ -->
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:10px;">
    <div style="display:flex;align-items:center;gap:10px;">
        <div style="width:40px;height:40px;background:var(--primary-light);border-radius:10px;display:flex;align-items:center;justify-content:center;">
            <i class="fa-solid fa-warehouse" style="color:var(--primary)"></i>
        </div>
        <div>
            <h2 style="font-size:1.2rem;font-weight:700;color:var(--text-primary)">Gestion des Stocks</h2>
            <p style="font-size:.78rem;color:var(--text-muted)"><?=count($rows)?> ligne(s) &bull; Total : <?=number_format($totalQte,0)?> unités</p>
        </div>
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap;">
        <a href="#" onclick="exportPDF()" class="btn btn-danger btn-sm">
            <i class="fa-solid fa-file-pdf"></i> Exporter PDF
        </a>
        <button onclick="openModal('modalAdd')" class="btn btn-primary">
            <i class="fa-solid fa-plus"></i> Ajouter
        </button>
    </div>
</div>

<?php if($msg): ?><div class="alert alert-<?=$msgType?> alert-auto"><i class="fa-solid fa-circle-info"></i> <?=htmlspecialchars($msg)?></div><?php endif; ?>
<?php if($totalPerimes>0): ?>
<div style="margin-bottom:16px;">
    <div class="peremption-alert" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;border-radius:12px;padding:16px 20px;">
        <div>
            <div style="font-size:.95rem;font-weight:700;display:flex;align-items:center;gap:8px;"><i class="fa-solid fa-triangle-exclamation"></i> <?=$totalPerimes?> produit(s) expiré(s)</div>
            <div style="font-size:.8rem;opacity:.9;margin-top:3px">Lignes en rouge dans le tableau</div>
        </div>
        <i class="fa-solid fa-circle-exclamation" style="font-size:2rem;opacity:.8"></i>
    </div>
</div>
<?php endif; ?>

<!-- ═══ FILTRES ═══ -->
<div class="card" style="margin-bottom:14px;padding:14px 18px;">
    <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
        <div style="display:flex;align-items:center;gap:6px;font-size:.8rem;font-weight:600;color:var(--text-primary);">
            <i class="fa-solid fa-filter" style="color:var(--primary)"></i> Filtres
        </div>
        <div class="search-wrap" style="flex:1;min-width:180px;">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" id="searchInput" placeholder="Rechercher produit, dépôt, lot…">
        </div>
        <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;">
            <label style="font-size:.75rem;color:var(--text-muted);white-space:nowrap">Du</label>
            <input type="date" id="filterFrom" class="form-control" style="width:140px;padding:6px 10px;font-size:.8rem;">
            <label style="font-size:.75rem;color:var(--text-muted);white-space:nowrap">Au</label>
            <input type="date" id="filterTo" class="form-control" style="width:140px;padding:6px 10px;font-size:.8rem;">
            <button onclick="resetFilters()" class="btn btn-secondary btn-sm"><i class="fa-solid fa-rotate-left"></i> Reset</button>
        </div>
        <div style="font-size:.75rem;color:var(--text-muted);display:flex;gap:10px;flex-wrap:wrap;">
            <span><span style="width:9px;height:9px;background:#fef2f2;border:1px solid #fecaca;border-radius:2px;display:inline-block;"></span> Périmé</span>
            <span><span style="width:9px;height:9px;background:#fffbeb;border:1px solid #fde68a;border-radius:2px;display:inline-block;"></span> Expire bientôt</span>
        </div>
    </div>
</div>

<!-- ═══ TABLEAU ═══ -->
<div class="card">
    <div class="table-wrapper">
        <table>
            <thead>
                <tr><th>#</th><th>Produit</th><th>Dépôt</th><th>Quantité</th><th>Entrée</th><th>Péremption</th><th>Lot</th><th>Statut</th><th>Actions</th></tr>
            </thead>
            <tbody id="tableBody">
            <?php foreach($rows as $r):
                $pe=$r['date_peremption']&&$r['date_peremption']<$today;
                $ex=$r['date_peremption']&&$r['date_peremption']>=$today&&$r['date_peremption']<=$soon;
                $rs=$pe?'style="background:#fef2f2"':($ex?'style="background:#fffbeb"':'');
            ?>
            <tr <?=$rs?>>
                <td style="color:var(--text-muted);font-size:.78rem">#<?=$r['id_stock']?></td>
                <td style="font-weight:500"><?=htmlspecialchars($r['espece'])?></td>
                <td><?=htmlspecialchars($r['nom_depot'])?></td>
                <td><strong><?=$r['quantite_stock']?></strong> <span style="color:var(--text-muted);font-size:.8rem"><?=$r['unite']?></span></td>
                <td><?=date('d/m/Y',strtotime($r['date_entree_stock']))?></td>
                <td><?php if($pe): ?><span class="badge badge-danger"><i class="fa-solid fa-skull-crossbones"></i> Périmé</span><?php elseif($ex): ?><span class="badge badge-warning"><i class="fa-solid fa-clock"></i> <?=date('d/m/Y',strtotime($r['date_peremption']))?></span><?php elseif($r['date_peremption']): ?><span class="badge badge-success"><?=date('d/m/Y',strtotime($r['date_peremption']))?></span><?php else: ?><span style="color:var(--text-muted)">—</span><?php endif; ?></td>
                <td><?=htmlspecialchars($r['lot']??'—')?></td>
                <td><?=htmlspecialchars($r['statut']??'—')?></td>
                <td>
                    <div style="display:flex;gap:5px">
                        <button onclick='fillEdit(<?=json_encode($r)?>)' class="btn btn-warning btn-sm"><i class="fa-solid fa-pen"></i></button>
                        <?php if(isAdmin()): ?><a href="?action=delete&id=<?=$r['id_stock']?>" class="btn btn-danger btn-sm confirm-action" data-confirm="Supprimer ce stock ?"><i class="fa-solid fa-trash"></i></a><?php endif; ?>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ═══ MODAL AJOUTER (admin ET user) ═══ -->
<div id="modalAdd" class="modal-overlay">
    <div class="modal-box" style="max-width:600px">
        <div class="modal-title"><i class="fa-solid fa-warehouse" style="color:var(--primary)"></i> Nouveau stock</div>

        <!-- Carte auto-fill capture -->
        <div id="captureInfoCard" style="display:none;margin-bottom:14px;padding:10px 14px;background:var(--primary-light);border-radius:10px;border-left:3px solid var(--primary);">
            <div style="font-size:.8rem;font-weight:600;color:var(--primary);margin-bottom:6px;"><i class="fa-solid fa-magic-wand-sparkles"></i> Informations auto-remplies depuis la capture</div>
            <div id="captureInfoContent" style="font-size:.78rem;color:var(--text-secondary);display:flex;flex-wrap:wrap;gap:10px;"></div>
        </div>

        <form method="POST">
            <input type="hidden" name="action" value="create">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">

                <!-- Capture liée — TOUJOURS visible, auto-fill -->
                <div class="form-group" style="grid-column:1/-1">
                    <label class="form-label"><i class="fa-solid fa-fish" style="color:var(--primary);margin-right:4px"></i>Capture liée <span style="font-size:.72rem;color:var(--primary)">(sélectionner pour auto-remplir)</span></label>
                    <select name="id_capture" id="add_capture" class="form-control" onchange="autofillCapture(this,'add')">
                        <option value="">-- Aucune --</option>
                        <?php foreach($captures as $c): ?>
                        <option value="<?=$c['id_capture']?>"
                            data-prod="<?=$c['fk_prod']?>"
                            data-navire="<?=$c['fk_navire']?>"
                            data-qte="<?=$c['quantite_capture']?>"
                            data-unite="<?=$c['unite_capture']?>"
                            data-date="<?=$c['date_capture']?>"
                            data-espece="<?=htmlspecialchars($c['espece'])?>"
                            data-navnom="<?=htmlspecialchars($c['nom_navire'])?>">
                            #<?=$c['id_capture']?> — <?=htmlspecialchars($c['espece'])?> | <?=htmlspecialchars($c['nom_navire'])?> | <?=date('d/m/Y',strtotime($c['date_capture']))?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group" style="grid-column:1/-1">
                    <label class="form-label">Produit *</label>
                    <select name="id_prod" id="add_prod" class="form-control" required>
                        <option value="">-- Choisir --</option>
                        <?php oPro($produits); ?>
                    </select>
                </div>
                <div class="form-group" style="grid-column:1/-1">
                    <label class="form-label">Dépôt *</label>
                    <select name="id_depot" class="form-control" required>
                        <option value="">-- Choisir --</option>
                        <?php oDep($depots); ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Navire</label>
                    <select name="id_navire" id="add_navire" class="form-control">
                        <option value="">-- Aucun --</option>
                        <?php oNa($navires); ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Statut</label>
                    <input type="text" name="statut" class="form-control" value="disponible">
                </div>
                <div class="form-group">
                    <label class="form-label">Quantité *</label>
                    <input type="number" step="0.01" name="quantite_stock" id="add_qte" class="form-control" required min="0">
                </div>
                <div class="form-group">
                    <label class="form-label">Unité *</label>
                    <select name="unite" id="add_unite" class="form-control"><?php sUnite(); ?></select>
                </div>
                <div class="form-group">
                    <label class="form-label">Date entrée *</label>
                    <input type="date" name="date_entree_stock" id="add_date" class="form-control" required value="<?=date('Y-m-d')?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Date péremption</label>
                    <input type="date" name="date_peremption" class="form-control">
                </div>
                <div class="form-group" style="grid-column:1/-1">
                    <label class="form-label">Lot</label>
                    <input type="text" name="lot" class="form-control">
                </div>
            </div>
            <div style="display:flex;gap:10px;margin-top:16px;">
                <button type="submit" class="btn btn-primary" style="flex:1"><i class="fa-solid fa-save"></i> Enregistrer</button>
                <button type="button" onclick="closeModal('modalAdd')" class="btn btn-secondary" style="flex:1">Annuler</button>
            </div>
        </form>
    </div>
</div>

<!-- ═══ MODAL MODIFIER ═══ -->
<div id="modalEdit" class="modal-overlay">
    <div class="modal-box" style="max-width:600px">
        <div class="modal-title"><i class="fa-solid fa-pen" style="color:var(--warning)"></i> Modifier stock</div>

        <div id="captureInfoCardEdit" style="display:none;margin-bottom:14px;padding:10px 14px;background:var(--primary-light);border-radius:10px;border-left:3px solid var(--primary);">
            <div style="font-size:.8rem;font-weight:600;color:var(--primary);margin-bottom:6px;"><i class="fa-solid fa-magic-wand-sparkles"></i> Informations auto-remplies</div>
            <div id="captureInfoContentEdit" style="font-size:.78rem;color:var(--text-secondary);display:flex;flex-wrap:wrap;gap:10px;"></div>
        </div>

        <form method="POST">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id_stock" id="e_id">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div class="form-group" style="grid-column:1/-1">
                    <label class="form-label"><i class="fa-solid fa-fish" style="color:var(--primary);margin-right:4px"></i>Capture liée <span style="font-size:.72rem;color:var(--primary)">(sélectionner pour auto-remplir)</span></label>
                    <select name="id_capture" id="e_capture" class="form-control" onchange="autofillCapture(this,'edit')">
                        <option value="">-- Aucune --</option>
                        <?php foreach($captures as $c): ?>
                        <option value="<?=$c['id_capture']?>"
                            data-prod="<?=$c['fk_prod']?>"
                            data-navire="<?=$c['fk_navire']?>"
                            data-qte="<?=$c['quantite_capture']?>"
                            data-unite="<?=$c['unite_capture']?>"
                            data-date="<?=$c['date_capture']?>"
                            data-espece="<?=htmlspecialchars($c['espece'])?>"
                            data-navnom="<?=htmlspecialchars($c['nom_navire'])?>">
                            #<?=$c['id_capture']?> — <?=htmlspecialchars($c['espece'])?> | <?=htmlspecialchars($c['nom_navire'])?> | <?=date('d/m/Y',strtotime($c['date_capture']))?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group" style="grid-column:1/-1">
                    <label class="form-label">Produit *</label>
                    <select name="id_prod" id="e_prod" class="form-control" required>
                        <option value="">-- Choisir --</option><?php oPro($produits); ?>
                    </select>
                </div>
                <div class="form-group" style="grid-column:1/-1">
                    <label class="form-label">Dépôt *</label>
                    <select name="id_depot" id="e_dep" class="form-control" required>
                        <option value="">-- Choisir --</option><?php oDep($depots); ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Navire</label>
                    <select name="id_navire" id="e_nav" class="form-control">
                        <option value="">-- Aucun --</option><?php oNa($navires); ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Statut</label>
                    <input type="text" name="statut" id="e_stat" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">Quantité *</label>
                    <input type="number" step="0.01" name="quantite_stock" id="e_qte" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Unité *</label>
                    <select name="unite" id="e_unit" class="form-control"><?php sUnite(); ?></select>
                </div>
                <div class="form-group">
                    <label class="form-label">Date entrée *</label>
                    <input type="date" name="date_entree_stock" id="e_de" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Date péremption</label>
                    <input type="date" name="date_peremption" id="e_dp" class="form-control">
                </div>
                <div class="form-group" style="grid-column:1/-1">
                    <label class="form-label">Lot</label>
                    <input type="text" name="lot" id="e_lot" class="form-control">
                </div>
            </div>
            <div style="display:flex;gap:10px;margin-top:16px;">
                <button type="submit" class="btn btn-primary" style="flex:1"><i class="fa-solid fa-save"></i> Enregistrer</button>
                <button type="button" onclick="closeModal('modalEdit')" class="btn btn-secondary" style="flex:1">Annuler</button>
            </div>
        </form>
    </div>
</div>

<?php include '../include/page_end.php'; ?>
<script>
// ── Auto-fill depuis capture ──
function autofillCapture(sel, mode){
    const opt = sel.options[sel.selectedIndex];
    const prefix = mode==='add' ? 'add' : 'e';
    const infoCard    = document.getElementById('captureInfoCard'+(mode==='edit'?'Edit':''));
    const infoContent = document.getElementById('captureInfoContent'+(mode==='edit'?'Edit':''));

    if(!opt.value){ infoCard.style.display='none'; return; }

    const prod   = opt.dataset.prod;
    const navire = opt.dataset.navire;
    const qte    = opt.dataset.qte;
    const unite  = opt.dataset.unite;
    const date   = opt.dataset.date;
    const espece = opt.dataset.espece;
    const navnom = opt.dataset.navnom;

    // Remplir les champs
    const pSel = document.getElementById(prefix+'_prod');
    if(pSel){ pSel.value = prod; }

    const nSel = document.getElementById(prefix+'_nav')||(mode==='add'?document.getElementById('add_navire'):null);
    if(nSel){ nSel.value = navire||''; }

    const qEl = document.getElementById(prefix+'_qte')||(mode==='add'?document.getElementById('add_qte'):null);
    if(qEl){ qEl.value = qte; }

    const uEl = document.getElementById(prefix+'_unit')||(mode==='add'?document.getElementById('add_unite'):null);
    if(uEl){
        // Convertir unite_capture vers unite stock
        const uMap={'tonne':'tonne','kg':'kg','g':'kg'};
        uEl.value = uMap[unite]||'kg';
    }

    const dEl = document.getElementById(prefix+'_date')||(mode==='add'?document.getElementById('add_date'):null);
    if(dEl){ dEl.value = date; }

    // Carte info
    infoCard.style.display = 'block';
    infoContent.innerHTML = `
        <span><i class="fa-solid fa-fish" style="color:var(--primary)"></i> <strong>${espece}</strong></span>
        <span><i class="fa-solid fa-ship" style="color:var(--primary)"></i> ${navnom}</span>
        <span><i class="fa-solid fa-scale-balanced" style="color:var(--primary)"></i> ${qte} ${unite}</span>
        <span><i class="fa-solid fa-calendar" style="color:var(--primary)"></i> ${date}</span>
    `;
}

// ── Remplir modal edit ──
function fillEdit(d){
    document.getElementById('e_id').value   = d.id_stock;
    document.getElementById('e_prod').value = d.id_prod;
    document.getElementById('e_dep').value  = d.id_depot;
    document.getElementById('e_nav').value  = d.id_navire||'';
    document.getElementById('e_capture').value = d.id_capture||'';
    document.getElementById('e_qte').value  = d.quantite_stock;
    document.getElementById('e_unit').value = d.unite;
    document.getElementById('e_de').value   = d.date_entree_stock;
    document.getElementById('e_dp').value   = d.date_peremption||'';
    document.getElementById('e_stat').value = d.statut||'';
    document.getElementById('e_lot').value  = d.lot||'';
    document.getElementById('captureInfoCard'+'Edit').style.display='none';
    openModal('modalEdit');
}

// ── Filtre ──
function doFilter(){
    const q  = document.getElementById('searchInput').value;
    const df = document.getElementById('filterFrom').value;
    const dt = document.getElementById('filterTo').value;
    fetch('stock.php?ajax=1&q='+encodeURIComponent(q)+'&date_from='+df+'&date_to='+dt)
        .then(r=>r.text()).then(html=>{document.getElementById('tableBody').innerHTML=html||'<tr><td colspan="9" style="text-align:center;color:var(--text-muted);padding:20px">Aucun résultat</td></tr>';});
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

// ── Export PDF avec filtres ──
function exportPDF(){
    const df = document.getElementById('filterFrom').value;
    const dt = document.getElementById('filterTo').value;
    window.open('stock.php?export=pdf&date_from='+df+'&date_to='+dt, '_blank');
}
</script>
</body></html>
