<?php
// ============================================================
// pages_principale/voyage.php — Gestion des Voyages
// ============================================================
require_once '../include/auth_check.php';
require_once '../include/connexion.php';
$msg = ''; $msgType = ''; $pageTitle = 'Voyages';
$username = $_SESSION['username'] ?? 'Utilisateur';

// ============================================================
// EXPORT PDF — logo gauche, titre centre, utilisateur, sans footer
// ============================================================
if (isset($_GET['export']) && $_GET['export'] === 'pdf') {
    $rows = $pdo->query("
        SELECT v.*, n.nom_navire, n.capitaine_navire, n.code_navire,
               c.annee_camp, c.date_debut_camp, c.date_fin_camp,
               COUNT(ca.id_capture) as nb_captures,
               COALESCE(SUM(ca.quantite_capture),0) as total_capture
        FROM voyage v
        JOIN navire   n ON v.id_navire = n.id_navire
        JOIN campagne c ON v.id_camp   = c.id_camp
        LEFT JOIN capture ca ON v.id_voyage = ca.id_voyage
        GROUP BY v.id_voyage
        ORDER BY v.date_debut_voyage DESC
    ")->fetchAll();

    $logoPath = realpath(__DIR__ . '/../images/logo_refrige.png');
    $logoB64  = '';
    if ($logoPath && file_exists($logoPath)) {
        $ext  = strtolower(pathinfo($logoPath, PATHINFO_EXTENSION));
        $mime = ($ext === 'png') ? 'image/png' : 'image/jpeg';
        $logoB64 = 'data:'.$mime.';base64,'.base64_encode(file_get_contents($logoPath));
    }

    $enCours   = 0; $termines = 0;
    foreach ($rows as $v) { if (!$v['date_fin_voyage']) $enCours++; else $termines++; }

    header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html><html lang="fr"><head>
<meta charset="UTF-8">
<title>Rapport Voyages – <?= date('d/m/Y') ?></title>
<style>
*{box-sizing:border-box;margin:0;padding:0;}
body{font-family:Arial,Helvetica,sans-serif;font-size:10px;color:#111;background:#fff;}
.rpt-header{display:flex;align-items:center;padding:14px 20px;border-bottom:3px solid #14b8a6;margin-bottom:16px;}
.rpt-logo{width:58px;height:58px;object-fit:contain;flex-shrink:0;}
.rpt-logo-box{width:58px;height:58px;background:#14b8a6;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;color:white;font-size:22px;}
.rpt-title{flex:1;text-align:center;}
.rpt-title h1{font-size:16px;font-weight:800;color:#0d9488;margin-bottom:3px;}
.rpt-title p{font-size:10px;color:#6b7280;}
.rpt-meta{width:160px;text-align:right;font-size:10px;color:#6b7280;line-height:1.8;flex-shrink:0;}
.rpt-meta strong{color:#111;}
.stats-row{display:flex;gap:10px;padding:0 20px;margin-bottom:14px;}
.stat-box{flex:1;border:1px solid #e5e7eb;border-radius:8px;padding:10px;text-align:center;}
.stat-box .sv{font-size:18px;font-weight:800;color:#0d9488;}
.stat-box .sl{font-size:9px;color:#6b7280;margin-top:2px;}
.stat-box.orange .sv{color:#d97706;}
.stat-box.green  .sv{color:#16a34a;}
.section{padding:0 20px;margin-bottom:16px;}
.sec-title{font-size:12px;font-weight:700;color:#0d9488;border-bottom:1px solid #e5e7eb;padding-bottom:5px;margin-bottom:9px;}
table{width:100%;border-collapse:collapse;}
thead th{background:#0d9488;color:white;font-size:9px;font-weight:700;text-transform:uppercase;padding:6px 8px;text-align:left;}
tbody td{padding:5px 8px;border-bottom:1px solid #f3f4f6;font-size:10px;vertical-align:middle;}
tbody tr:nth-child(even){background:#f9fafb;}
.badge-ok{background:#f0fdf4;color:#16a34a;border:1px solid #bbf7d0;padding:2px 6px;border-radius:999px;font-size:9px;font-weight:600;}
.badge-warn{background:#fffbeb;color:#92400e;border:1px solid #fde68a;padding:2px 6px;border-radius:999px;font-size:9px;font-weight:600;}
@media print{body{-webkit-print-color-adjust:exact;print-color-adjust:exact;}}
</style></head><body>

<div class="rpt-header">
    <?php if ($logoB64): ?>
    <img src="<?= $logoB64 ?>" class="rpt-logo" alt="Logo">
    <?php else: ?>
    <div class="rpt-logo-box">🐟</div>
    <?php endif; ?>
    <div class="rpt-title">
        <h1>Rapport des Voyages</h1>
        <p>Système de Gestion des Stocks – FishStock Pro</p>
    </div>
    <div class="rpt-meta">
        Date : <strong><?= date('d/m/Y') ?></strong><br>
        Heure : <strong><?= date('H:i') ?></strong><br>
        Généré par : <strong><?= htmlspecialchars($username) ?></strong><br>
        Total : <strong><?= count($rows) ?> voyage(s)</strong>
    </div>
</div>

<div class="stats-row">
    <div class="stat-box"><div class="sv"><?= count($rows) ?></div><div class="sl">Total voyages</div></div>
    <div class="stat-box green"><div class="sv"><?= $termines ?></div><div class="sl">Terminés</div></div>
    <div class="stat-box orange"><div class="sv"><?= $enCours ?></div><div class="sl">En cours</div></div>
    <div class="stat-box"><div class="sv"><?= array_sum(array_column($rows,'nb_captures')) ?></div><div class="sl">Total captures</div></div>
</div>

<div class="section">
    <div class="sec-title">⛵ Détail des Voyages</div>
    <table>
        <thead>
            <tr>
                <th>#</th><th>Navire</th><th>Capitaine</th><th>Campagne</th>
                <th>Lieu</th><th>Départ</th><th>Retour</th><th>Durée</th>
                <th>Captures</th><th>Statut</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($rows as $v):
            $duree = '—';
            if ($v['date_debut_voyage'] && $v['date_fin_voyage']) {
                $diff = (new DateTime($v['date_debut_voyage']))->diff(new DateTime($v['date_fin_voyage']));
                $duree = $diff->days . ' j';
            }
            $statut = $v['date_fin_voyage'] ? 'Terminé' : 'En cours';
            $sClass = $v['date_fin_voyage'] ? 'badge-ok' : 'badge-warn';
        ?>
        <tr>
            <td>#<?= $v['id_voyage'] ?></td>
            <td style="font-weight:600"><?= htmlspecialchars($v['nom_navire']) ?></td>
            <td><?= htmlspecialchars($v['capitaine_navire'] ?? '—') ?></td>
            <td><?= $v['annee_camp'] ?></td>
            <td><?= htmlspecialchars($v['lieu_voyage'] ?? '—') ?></td>
            <td><?= $v['date_debut_voyage'] ? date('d/m/Y',strtotime($v['date_debut_voyage'])) : '—' ?></td>
            <td><?= $v['date_fin_voyage']   ? date('d/m/Y',strtotime($v['date_fin_voyage']))   : '—' ?></td>
            <td style="text-align:center"><?= $duree ?></td>
            <td style="text-align:center;font-weight:700;color:#0d9488"><?= $v['nb_captures'] ?></td>
            <td><span class="<?= $sClass ?>"><?= $statut ?></span></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>window.print();</script>
</body></html>
<?php
    exit;
}

// ============================================================
// AJAX recherche
// ============================================================
if (isset($_GET['ajax'])) {
    $q = '%'.($_GET['q']??'').'%';
    $df = $_GET['date_from']??''; $dt = $_GET['date_to']??'';
    $sql = "SELECT v.*,n.nom_navire,c.annee_camp FROM voyage v JOIN navire n ON v.id_navire=n.id_navire JOIN campagne c ON v.id_camp=c.id_camp WHERE (n.nom_navire LIKE ? OR v.lieu_voyage LIKE ?)";
    $params = [$q,$q];
    if($df){$sql.=" AND v.date_debut_voyage>=?";$params[]=$df;}
    if($dt){$sql.=" AND v.date_debut_voyage<=?";$params[]=$dt;}
    $sql.=" ORDER BY v.id_voyage DESC";
    $stmt = $pdo->prepare($sql);$stmt->execute($params);
    foreach ($stmt->fetchAll() as $r) {
        $statBadge = $r['date_fin_voyage']
            ? '<span class="badge badge-success"><i class="fa-solid fa-check"></i> Terminé</span>'
            : '<span class="badge badge-warning"><i class="fa-solid fa-spinner fa-spin"></i> En cours</span>';
        echo '<tr>';
        echo '<td style="color:var(--text-muted);font-size:.78rem;">#'.$r['id_voyage'].'</td>';
        echo '<td style="font-weight:500;">'.htmlspecialchars($r['nom_navire']).'</td>';
        echo '<td>'.$r['annee_camp'].'</td>';
        echo '<td>'.htmlspecialchars($r['lieu_voyage']??'—').'</td>';
        echo '<td>'.date('d/m/Y',strtotime($r['date_debut_voyage'])).'</td>';
        echo '<td>'.($r['date_fin_voyage'] ? date('d/m/Y',strtotime($r['date_fin_voyage'])) : $statBadge).'</td>';
        echo '<td><div style="display:flex;gap:6px;">';
        echo '<button onclick=\'fillEdit('.json_encode($r).')\' class="btn btn-warning btn-sm"><i class="fa-solid fa-pen"></i></button>';
        if (isAdmin()) echo '<a href="?action=delete&id='.$r['id_voyage'].'" class="btn btn-danger btn-sm confirm-action" data-confirm="Supprimer ce voyage ?"><i class="fa-solid fa-trash"></i></a>';
        echo '</div></td></tr>';
    }
    exit;
}

// ============================================================
// CRUD
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $act = $_POST['action'] ?? '';
    try {
        if ($act === 'create') {
            $pdo->prepare("INSERT INTO voyage(date_debut_voyage,date_fin_voyage,lieu_voyage,id_navire,id_camp) VALUES(?,?,?,?,?)")
                ->execute([$_POST['date_debut_voyage'], $_POST['date_fin_voyage']??null, trim($_POST['lieu_voyage']), $_POST['id_navire'], $_POST['id_camp']]);
            $msg = 'Voyage ajouté avec succès.'; $msgType = 'success';
        } elseif ($act === 'update') {
            $pdo->prepare("UPDATE voyage SET date_debut_voyage=?,date_fin_voyage=?,lieu_voyage=?,id_navire=?,id_camp=? WHERE id_voyage=?")
                ->execute([$_POST['date_debut_voyage'], $_POST['date_fin_voyage']??null, trim($_POST['lieu_voyage']), $_POST['id_navire'], $_POST['id_camp'], $_POST['id_voyage']]);
            $msg = 'Voyage modifié avec succès.'; $msgType = 'success';
        }
    } catch (PDOException $e) { $msg = 'Erreur : '.$e->getMessage(); $msgType = 'danger'; }
}
if (($_GET['action'] ?? '') === 'delete' && isAdmin()) {
    try { $pdo->prepare("DELETE FROM voyage WHERE id_voyage=?")->execute([$_GET['id']]); $msg = 'Voyage supprimé.'; $msgType = 'success'; }
    catch (PDOException $e) { $msg = 'Impossible (captures liées).'; $msgType = 'danger'; }
}

$rows     = $pdo->query("SELECT v.*,n.nom_navire,c.annee_camp FROM voyage v JOIN navire n ON v.id_navire=n.id_navire JOIN campagne c ON v.id_camp=c.id_camp ORDER BY v.id_voyage DESC")->fetchAll();
$navires  = $pdo->query("SELECT * FROM navire ORDER BY nom_navire")->fetchAll();
$campagnes= $pdo->query("SELECT * FROM campagne ORDER BY annee_camp DESC")->fetchAll();

function oN($n,$s=null){ foreach($n as $v) echo '<option value="'.$v['id_navire'].'" '.($v['id_navire']==$s?'selected':'').'>'.htmlspecialchars($v['nom_navire']).'</option>'; }
function oC($c,$s=null){ foreach($c as $v) echo '<option value="'.$v['id_camp'].'" '.($v['id_camp']==$s?'selected':'').'>'.$v['annee_camp'].' (camp #'.$v['id_camp'].')</option>'; }

include_once '../include/page_start.php';
?>

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:10px;">
    <div style="display:flex;align-items:center;gap:10px;">
        <div style="width:40px;height:40px;background:#eff6ff;border-radius:10px;display:flex;align-items:center;justify-content:center;">
            <i class="fa-solid fa-ship" style="color:#2563eb"></i>
        </div>
        <div>
            <h2 style="font-size:1.2rem;font-weight:700;color:var(--text-primary)">Gestion des Voyages</h2>
            <p style="font-size:.78rem;color:var(--text-muted)"><?= count($rows) ?> voyage(s) enregistré(s)</p>
        </div>
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap;">
        <a href="?export=pdf" target="_blank" class="btn btn-danger btn-sm">
            <i class="fa-solid fa-file-pdf"></i> Exporter PDF
        </a>
        <button onclick="openModal('modalAdd')" class="btn btn-primary">
            <i class="fa-solid fa-plus"></i> Ajouter
        </button>
    </div>
</div>

<?php if ($msg): ?>
<div class="alert alert-<?= $msgType ?> alert-auto"><i class="fa-solid fa-circle-info"></i> <?= htmlspecialchars($msg) ?></div>
<?php endif; ?>

<div class="card">
    <!-- Filtres -->
    <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:14px;">
        <div class="search-wrap" style="flex:1;min-width:160px;">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" id="searchInput" placeholder="Rechercher navire, lieu…">
        </div>
        <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;">
            <label style="font-size:.75rem;color:var(--text-muted)">Du</label>
            <input type="date" id="filterFrom" class="form-control" style="width:135px;padding:6px 10px;font-size:.8rem;">
            <label style="font-size:.75rem;color:var(--text-muted)">Au</label>
            <input type="date" id="filterTo" class="form-control" style="width:135px;padding:6px 10px;font-size:.8rem;">
            <button onclick="resetFilters()" class="btn btn-secondary btn-sm"><i class="fa-solid fa-rotate-left"></i></button>
        </div>
    </div>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr><th>#</th><th>Navire</th><th>Campagne</th><th>Lieu</th><th>Départ</th><th>Statut / Retour</th><th>Actions</th></tr>
            </thead>
            <tbody id="tableBody">
            <?php foreach ($rows as $r):
                $statBadge = $r['date_fin_voyage']
                    ? '<span class="badge badge-success"><i class="fa-solid fa-check"></i> Terminé</span>'
                    : '<span class="badge badge-warning"><i class="fa-solid fa-spinner fa-spin"></i> En cours</span>';
            ?>
            <tr>
                <td style="color:var(--text-muted);font-size:.78rem;">#<?= $r['id_voyage'] ?></td>
                <td style="font-weight:500;"><?= htmlspecialchars($r['nom_navire']) ?></td>
                <td><?= $r['annee_camp'] ?></td>
                <td><?= htmlspecialchars($r['lieu_voyage'] ?? '—') ?></td>
                <td><?= date('d/m/Y', strtotime($r['date_debut_voyage'])) ?></td>
                <td><?= $r['date_fin_voyage'] ? date('d/m/Y',strtotime($r['date_fin_voyage'])) : $statBadge ?></td>
                <td>
                    <div style="display:flex;gap:6px;">
                        <button onclick='fillEdit(<?= json_encode($r) ?>)' class="btn btn-warning btn-sm"><i class="fa-solid fa-pen"></i></button>
                        <?php if (isAdmin()): ?>
                        <a href="?action=delete&id=<?= $r['id_voyage'] ?>" class="btn btn-danger btn-sm confirm-action" data-confirm="Supprimer ce voyage ?"><i class="fa-solid fa-trash"></i></a>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- MODAL AJOUTER -->
<div id="modalAdd" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-title"><i class="fa-solid fa-ship" style="color:var(--primary)"></i> Nouveau voyage</div>
        <form method="POST">
            <input type="hidden" name="action" value="create">
            <div class="form-group"><label class="form-label">Navire *</label>
                <select name="id_navire" class="form-control" required><option value="">-- Choisir --</option><?php oN($navires); ?></select></div>
            <div class="form-group"><label class="form-label">Campagne *</label>
                <select name="id_camp" class="form-control" required><option value="">-- Choisir --</option><?php oC($campagnes); ?></select></div>
            <div class="form-group"><label class="form-label">Lieu</label><input type="text" name="lieu_voyage" class="form-control" placeholder="ex: Canal de Mozambique"></div>
            <div class="form-group"><label class="form-label">Date départ *</label><input type="date" name="date_debut_voyage" class="form-control" required></div>
            <div class="form-group"><label class="form-label">Date retour <span style="color:var(--text-muted);font-weight:400">(vide si en cours)</span></label>
                <input type="date" name="date_fin_voyage" class="form-control"></div>
            <div style="display:flex;gap:10px;margin-top:16px;">
                <button type="submit" class="btn btn-primary" style="flex:1"><i class="fa-solid fa-save"></i> Enregistrer</button>
                <button type="button" onclick="closeModal('modalAdd')" class="btn btn-secondary" style="flex:1">Annuler</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL MODIFIER -->
<div id="modalEdit" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-title"><i class="fa-solid fa-pen" style="color:var(--warning)"></i> Modifier voyage</div>
        <form method="POST">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id_voyage" id="e_id">
            <div class="form-group"><label class="form-label">Navire *</label>
                <select name="id_navire" id="e_nav" class="form-control" required><option value="">-- Choisir --</option><?php oN($navires); ?></select></div>
            <div class="form-group"><label class="form-label">Campagne *</label>
                <select name="id_camp" id="e_camp" class="form-control" required><option value="">-- Choisir --</option><?php oC($campagnes); ?></select></div>
            <div class="form-group"><label class="form-label">Lieu</label><input type="text" name="lieu_voyage" id="e_lieu" class="form-control"></div>
            <div class="form-group"><label class="form-label">Date départ *</label><input type="date" name="date_debut_voyage" id="e_dd" class="form-control" required></div>
            <div class="form-group"><label class="form-label">Date retour</label><input type="date" name="date_fin_voyage" id="e_df" class="form-control"></div>
            <div style="display:flex;gap:10px;margin-top:16px;">
                <button type="submit" class="btn btn-primary" style="flex:1"><i class="fa-solid fa-save"></i> Enregistrer</button>
                <button type="button" onclick="closeModal('modalEdit')" class="btn btn-secondary" style="flex:1">Annuler</button>
            </div>
        </form>
    </div>
</div>

<?php include '../include/page_end.php'; ?>
<script>
function fillEdit(d) {
    document.getElementById('e_id').value   = d.id_voyage;
    document.getElementById('e_nav').value  = d.id_navire;
    document.getElementById('e_camp').value = d.id_camp;
    document.getElementById('e_lieu').value = d.lieu_voyage || '';
    document.getElementById('e_dd').value   = d.date_debut_voyage;
    document.getElementById('e_df').value   = d.date_fin_voyage || '';
    openModal('modalEdit');
}
function doFilter(){
    const q  = document.getElementById('searchInput').value;
    const df = document.getElementById('filterFrom').value;
    const dt = document.getElementById('filterTo').value;
    fetch('voyage.php?ajax=1&q='+encodeURIComponent(q)+'&date_from='+df+'&date_to='+dt)
        .then(r=>r.text()).then(html=>{document.getElementById('tableBody').innerHTML=html||'<tr><td colspan="7" style="text-align:center;color:var(--text-muted);padding:20px">Aucun résultat</td></tr>';});
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
</script>
</body>
</html>
