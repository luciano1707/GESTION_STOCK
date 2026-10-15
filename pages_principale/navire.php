<?php
// ============================================================
// pages_principale/navire.php — Gestion des Navires
// ============================================================
require_once '../include/auth_check.php';
require_once '../include/connexion.php';
$msg = ''; $msgType = ''; $pageTitle = 'Navires';
$username = $_SESSION['username'] ?? 'Utilisateur';

// ============================================================
// EXPORT PDF — logo gauche, titre centre, utilisateur, sans footer
// ============================================================
if (isset($_GET['export']) && $_GET['export'] === 'pdf') {
    $rows = $pdo->query("
        SELECT n.*,
               COUNT(DISTINCT v.id_voyage) as nb_voyages,
               COUNT(DISTINCT c.id_camp)   as nb_campagnes,
               MAX(v.date_debut_voyage)     as dernier_voyage
        FROM navire n
        LEFT JOIN voyage   v ON n.id_navire = v.id_navire
        LEFT JOIN campagne c ON n.id_navire = c.id_navire
        GROUP BY n.id_navire
        ORDER BY nb_voyages DESC
    ")->fetchAll();

    $logoPath = realpath(__DIR__ . '/../images/logo_refrige.png');
    $logoB64  = '';
    if ($logoPath && file_exists($logoPath)) {
        $ext  = strtolower(pathinfo($logoPath, PATHINFO_EXTENSION));
        $mime = ($ext === 'png') ? 'image/png' : 'image/jpeg';
        $logoB64 = 'data:'.$mime.';base64,'.base64_encode(file_get_contents($logoPath));
    }
    header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html><html lang="fr"><head>
<meta charset="UTF-8">
<title>Rapport Navires – <?= date('d/m/Y') ?></title>
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
.section{padding:0 20px;margin-bottom:16px;}
.sec-title{font-size:12px;font-weight:700;color:#0d9488;border-bottom:1px solid #e5e7eb;padding-bottom:5px;margin-bottom:9px;}
table{width:100%;border-collapse:collapse;}
thead th{background:#0d9488;color:white;font-size:9px;font-weight:700;text-transform:uppercase;padding:7px 10px;text-align:left;}
tbody td{padding:6px 10px;border-bottom:1px solid #f3f4f6;font-size:10px;}
tbody tr:nth-child(even){background:#f9fafb;}
.badge-actif{background:#f0fdf4;color:#16a34a;border:1px solid #bbf7d0;padding:2px 7px;border-radius:999px;font-size:9px;font-weight:600;}
/* Cards navires */
.nav-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-top:10px;}
.nav-card{border:1px solid #e5e7eb;border-radius:8px;padding:12px;}
.nav-card .nc-name{font-size:12px;font-weight:800;color:#0d9488;margin-bottom:3px;}
.nav-card .nc-code{font-size:9px;color:#6b7280;margin-bottom:6px;}
.nav-card .nc-cap{font-size:10px;color:#374151;margin-bottom:8px;}
.nav-card .nc-stat{display:flex;justify-content:space-between;padding-top:6px;border-top:1px solid #e5e7eb;}
.nc-stat-item{text-align:center;}
.nc-stat-item .nsi-val{font-size:14px;font-weight:800;color:#0d9488;}
.nc-stat-item .nsi-lbl{font-size:8px;color:#6b7280;}
@media print{body{-webkit-print-color-adjust:exact;print-color-adjust:exact;}}
</style></head><body>

<div class="rpt-header">
    <?php if ($logoB64): ?>
    <img src="<?= $logoB64 ?>" class="rpt-logo" alt="Logo">
    <?php else: ?>
    <div class="rpt-logo-box">🐟</div>
    <?php endif; ?>
    <div class="rpt-title">
        <h1>Rapport des Navires</h1>
        <p>Système de Gestion des Stocks – FishStock Pro</p>
    </div>
    <div class="rpt-meta">
        Date : <strong><?= date('d/m/Y') ?></strong><br>
        Heure : <strong><?= date('H:i') ?></strong><br>
        Généré par : <strong><?= htmlspecialchars($username) ?></strong><br>
        Flotte : <strong><?= count($rows) ?> navire(s)</strong>
    </div>
</div>

<?php
$totalVoyages   = array_sum(array_column($rows,'nb_voyages'));
$totalCampagnes = array_sum(array_column($rows,'nb_campagnes'));
?>
<div class="stats-row">
    <div class="stat-box"><div class="sv"><?= count($rows) ?></div><div class="sl">Navires enregistrés</div></div>
    <div class="stat-box"><div class="sv"><?= $totalVoyages ?></div><div class="sl">Total voyages</div></div>
    <div class="stat-box"><div class="sv"><?= $totalCampagnes ?></div><div class="sl">Total campagnes</div></div>
    <div class="stat-box"><div class="sv"><?= date('d/m/Y') ?></div><div class="sl">Date du rapport</div></div>
</div>

<!-- Cartes navires -->
<div class="section">
    <div class="sec-title">⚓ Vue d'ensemble de la Flotte</div>
    <div class="nav-grid">
    <?php foreach ($rows as $n): ?>
    <div class="nav-card">
        <div class="nc-name"><?= htmlspecialchars($n['nom_navire']) ?></div>
        <div class="nc-code">Code : <?= htmlspecialchars($n['code_navire']) ?></div>
        <div class="nc-cap">👤 <?= htmlspecialchars($n['capitaine_navire'] ?? 'Non renseigné') ?></div>
        <div class="nc-stat">
            <div class="nc-stat-item"><div class="nsi-val"><?= $n['nb_voyages'] ?></div><div class="nsi-lbl">Voyages</div></div>
            <div class="nc-stat-item"><div class="nsi-val"><?= $n['nb_campagnes'] ?></div><div class="nsi-lbl">Campagnes</div></div>
            <div class="nc-stat-item"><div class="nsi-val"><?= $n['dernier_voyage'] ? date('m/Y',strtotime($n['dernier_voyage'])) : '—' ?></div><div class="nsi-lbl">Dernier voyage</div></div>
        </div>
    </div>
    <?php endforeach; ?>
    </div>
</div>

<!-- Tableau récapitulatif -->
<div class="section">
    <div class="sec-title">📋 Tableau Récapitulatif</div>
    <table>
        <thead>
            <tr><th>#</th><th>Code</th><th>Nom navire</th><th>Capitaine</th><th>Voyages</th><th>Campagnes</th><th>Dernier voyage</th></tr>
        </thead>
        <tbody>
        <?php foreach ($rows as $n): ?>
        <tr>
            <td>#<?= $n['id_navire'] ?></td>
            <td><strong><?= htmlspecialchars($n['code_navire']) ?></strong></td>
            <td style="font-weight:600"><?= htmlspecialchars($n['nom_navire']) ?></td>
            <td><?= htmlspecialchars($n['capitaine_navire'] ?? '—') ?></td>
            <td style="text-align:center;font-weight:700;color:#0d9488"><?= $n['nb_voyages'] ?></td>
            <td style="text-align:center;font-weight:700;color:#0d9488"><?= $n['nb_campagnes'] ?></td>
            <td><?= $n['dernier_voyage'] ? date('d/m/Y',strtotime($n['dernier_voyage'])) : '—' ?></td>
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
    $stmt = $pdo->prepare("SELECT * FROM navire WHERE nom_navire LIKE ? OR code_navire LIKE ? OR capitaine_navire LIKE ? ORDER BY id_navire DESC");
    $stmt->execute([$q,$q,$q]);
    foreach ($stmt->fetchAll() as $r) {
        echo '<tr>';
        echo '<td style="color:var(--text-muted);font-size:.78rem;">#'.$r['id_navire'].'</td>';
        echo '<td><span class="badge badge-info">'.htmlspecialchars($r['code_navire']).'</span></td>';
        echo '<td style="font-weight:500;">'.htmlspecialchars($r['nom_navire']).'</td>';
        echo '<td>'.htmlspecialchars($r['capitaine_navire']??'—').'</td>';
        echo '<td><div style="display:flex;gap:6px;">';
        echo '<button onclick=\'fillEdit('.json_encode($r).')\' class="btn btn-warning btn-sm"><i class="fa-solid fa-pen"></i></button>';
        if (isAdmin()) echo '<a href="?action=delete&id='.$r['id_navire'].'" class="btn btn-danger btn-sm confirm-action" data-confirm="Supprimer ce navire ?"><i class="fa-solid fa-trash"></i></a>';
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
            $pdo->prepare("INSERT INTO navire(nom_navire,code_navire,capitaine_navire) VALUES(?,?,?)")
                ->execute([trim($_POST['nom_navire']), trim($_POST['code_navire']), trim($_POST['capitaine_navire'])]);
            $msg = 'Navire ajouté avec succès.'; $msgType = 'success';
        } elseif ($act === 'update') {
            $pdo->prepare("UPDATE navire SET nom_navire=?,code_navire=?,capitaine_navire=? WHERE id_navire=?")
                ->execute([trim($_POST['nom_navire']), trim($_POST['code_navire']), trim($_POST['capitaine_navire']), $_POST['id_navire']]);
            $msg = 'Navire modifié avec succès.'; $msgType = 'success';
        }
    } catch (PDOException $e) { $msg = 'Erreur : '.$e->getMessage(); $msgType = 'danger'; }
}
if (($_GET['action'] ?? '') === 'delete' && isAdmin()) {
    try { $pdo->prepare("DELETE FROM navire WHERE id_navire=?")->execute([$_GET['id']]); $msg = 'Navire supprimé.'; $msgType = 'success'; }
    catch (PDOException $e) { $msg = 'Impossible de supprimer (voyages liés).'; $msgType = 'danger'; }
}

$rows    = $pdo->query("SELECT n.*, COUNT(v.id_voyage) as nb_voyages FROM navire n LEFT JOIN voyage v ON n.id_navire=v.id_navire GROUP BY n.id_navire ORDER BY n.id_navire DESC")->fetchAll();
$allRows = $pdo->query("SELECT * FROM navire ORDER BY id_navire DESC")->fetchAll();

include_once '../include/page_start.php';
?>

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:10px;">
    <div style="display:flex;align-items:center;gap:10px;">
        <div style="width:40px;height:40px;background:#eff6ff;border-radius:10px;display:flex;align-items:center;justify-content:center;">
            <i class="fa-solid fa-anchor" style="color:#2563eb"></i>
        </div>
        <div>
            <h2 style="font-size:1.2rem;font-weight:700;color:var(--text-primary)">Gestion des Navires</h2>
            <p style="font-size:.78rem;color:var(--text-muted)"><?= count($rows) ?> navire(s) enregistré(s)</p>
        </div>
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap;">
        <a href="?export=pdf" target="_blank" class="btn btn-danger btn-sm">
            <i class="fa-solid fa-file-pdf"></i> Exporter PDF
        </a>
        <?php if (isAdmin()): ?>
        <button onclick="openModal('modalAdd')" class="btn btn-primary">
            <i class="fa-solid fa-plus"></i> Ajouter
        </button>
        <?php endif; ?>
    </div>
</div>

<?php if ($msg): ?>
<div class="alert alert-<?= $msgType ?> alert-auto"><i class="fa-solid fa-circle-info"></i> <?= htmlspecialchars($msg) ?></div>
<?php endif; ?>

<!-- Cartes navires (vue synthèse) -->
<?php if ($rows): ?>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:12px;margin-bottom:20px;">
    <?php foreach ($rows as $n): ?>
    <div class="navire-card">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:10px;">
            <div>
                <div style="font-size:.95rem;font-weight:700;color:var(--text-primary)"><?= htmlspecialchars($n['nom_navire']) ?></div>
                <span class="badge badge-info" style="font-size:.65rem"><?= htmlspecialchars($n['code_navire']) ?></span>
            </div>
            <div class="navire-icon"><i class="fa-solid fa-anchor"></i></div>
        </div>
        <div style="font-size:.8rem;color:var(--text-muted);margin-bottom:10px;">
            <i class="fa-solid fa-user" style="margin-right:4px;color:var(--primary)"></i>
            <?= htmlspecialchars($n['capitaine_navire'] ?? 'Non renseigné') ?>
        </div>
        <div style="display:flex;align-items:center;justify-content:space-between;padding-top:8px;border-top:1px solid var(--card-border);">
            <span style="font-size:.8rem;color:var(--text-muted);display:flex;align-items:center;gap:5px;">
                <i class="fa-solid fa-route"></i> Voyages
            </span>
            <strong style="color:var(--primary)"><?= $n['nb_voyages'] ?></strong>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Tableau -->
<div class="card">
    <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:14px;">
        <div class="search-wrap" style="flex:1;min-width:160px;">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" id="searchInput" placeholder="Rechercher navire, capitaine, code…">
        </div>
    </div>
    <div class="table-wrapper">
        <table>
            <thead><tr><th>#</th><th>Code</th><th>Nom navire</th><th>Capitaine</th><th>Actions</th></tr></thead>
            <tbody id="tableBody">
            <?php foreach ($allRows as $r): ?>
            <tr>
                <td style="color:var(--text-muted);font-size:.78rem;">#<?= $r['id_navire'] ?></td>
                <td><span class="badge badge-info"><?= htmlspecialchars($r['code_navire']) ?></span></td>
                <td style="font-weight:500;"><?= htmlspecialchars($r['nom_navire']) ?></td>
                <td><?= htmlspecialchars($r['capitaine_navire'] ?? '—') ?></td>
                <td>
                    <div style="display:flex;gap:6px;">
                        <button onclick='fillEdit(<?= json_encode($r) ?>)' class="btn btn-warning btn-sm"><i class="fa-solid fa-pen"></i></button>
                        <?php if (isAdmin()): ?>
                        <a href="?action=delete&id=<?= $r['id_navire'] ?>" class="btn btn-danger btn-sm confirm-action" data-confirm="Supprimer ce navire ?"><i class="fa-solid fa-trash"></i></a>
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
        <div class="modal-title"><i class="fa-solid fa-anchor" style="color:var(--primary)"></i> Nouveau navire</div>
        <form method="POST">
            <input type="hidden" name="action" value="create">
            <div class="form-group"><label class="form-label">Code navire *</label><input type="text" name="code_navire" class="form-control" required placeholder="ex: NAV001"></div>
            <div class="form-group"><label class="form-label">Nom navire *</label><input type="text" name="nom_navire" class="form-control" required placeholder="ex: Mana Aquamas"></div>
            <div class="form-group"><label class="form-label">Capitaine</label><input type="text" name="capitaine_navire" class="form-control" placeholder="Nom du capitaine"></div>
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
        <div class="modal-title"><i class="fa-solid fa-pen" style="color:var(--warning)"></i> Modifier navire</div>
        <form method="POST">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id_navire" id="e_id">
            <div class="form-group"><label class="form-label">Code navire *</label><input type="text" name="code_navire" id="e_code" class="form-control" required></div>
            <div class="form-group"><label class="form-label">Nom navire *</label><input type="text" name="nom_navire" id="e_nom" class="form-control" required></div>
            <div class="form-group"><label class="form-label">Capitaine</label><input type="text" name="capitaine_navire" id="e_cap" class="form-control"></div>
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
    document.getElementById('e_id').value   = d.id_navire;
    document.getElementById('e_code').value = d.code_navire;
    document.getElementById('e_nom').value  = d.nom_navire;
    document.getElementById('e_cap').value  = d.capitaine_navire || '';
    openModal('modalEdit');
}
document.getElementById('searchInput').addEventListener('input', function(){
    const q = this.value;
    fetch('navire.php?ajax=1&q='+encodeURIComponent(q))
        .then(r=>r.text()).then(html=>{document.getElementById('tableBody').innerHTML=html||'<tr><td colspan="5" style="text-align:center;color:var(--text-muted);padding:20px">Aucun résultat</td></tr>';});
});
</script>
</body>
</html>
