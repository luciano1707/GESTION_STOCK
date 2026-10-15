<?php
// pages_principale/index.php
require_once '../include/auth_check.php';
require_once '../include/connexion.php';

$isAdmin  = isAdmin();
$username = $_SESSION['username'] ?? 'Utilisateur';

// ---- Stats ----
$totalProduits = $pdo->query("SELECT COUNT(*) FROM produit")->fetchColumn();
$totalFamilles = $pdo->query("SELECT COUNT(*) FROM famille")->fetchColumn();
$totalNavires  = $pdo->query("SELECT COUNT(*) FROM navire")->fetchColumn();
$totalQteStock = $pdo->query("SELECT COALESCE(SUM(quantite_stock),0) FROM stock")->fetchColumn();
$uniteStock    = $pdo->query("SELECT unite FROM stock ORDER BY id_stock DESC LIMIT 1")->fetchColumn() ?: 'kg';
$totalPerimes  = $pdo->query("SELECT COUNT(*) FROM stock WHERE date_peremption IS NOT NULL AND date_peremption < CURDATE()")->fetchColumn();

$top5 = $pdo->query("
    SELECT p.espece, f.nom_famille, SUM(s.quantite_stock) as qte, s.unite
    FROM stock s JOIN produit p ON s.id_prod=p.id_prod JOIN famille f ON p.id_famille=f.id_famille
    GROUP BY s.id_prod, s.unite ORDER BY qte DESC LIMIT 5
")->fetchAll();

$parDepot = $pdo->query("
    SELECT d.nom_depot, COUNT(DISTINCT s.id_prod) as nb_prods, SUM(s.quantite_stock) as total, s.unite
    FROM stock s JOIN depot d ON s.id_depot=d.id_depot
    GROUP BY d.id_depot ORDER BY total DESC LIMIT 5
")->fetchAll();

$navires = $pdo->query("
    SELECT n.nom_navire, n.capitaine_navire, COUNT(v.id_voyage) as nb_voyages
    FROM navire n LEFT JOIN voyage v ON n.id_navire=v.id_navire
    GROUP BY n.id_navire ORDER BY nb_voyages DESC LIMIT 6
")->fetchAll();

$parFamille = $pdo->query("
    SELECT f.nom_famille, SUM(s.quantite_stock) as total
    FROM stock s JOIN produit p ON s.id_prod=p.id_prod JOIN famille f ON p.id_famille=f.id_famille
    GROUP BY f.id_famille
")->fetchAll();

$evolution = $pdo->query("
    SELECT DATE_FORMAT(date_entree_stock,'%b %Y') as mois,
           DATE_FORMAT(date_entree_stock,'%Y-%m') as mois_key,
           SUM(quantite_stock) as total
    FROM stock WHERE date_entree_stock >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    GROUP BY mois_key ORDER BY mois_key ASC
")->fetchAll();

// ============================================================
// EXPORT PDF — admin seulement
// ============================================================
if (isset($_GET['export']) && $_GET['export'] === 'pdf' && $isAdmin) {
    $logoPath   = realpath(__DIR__ . '/../images/logo_refrige.png');
    $logoB64    = '';
    if ($logoPath && file_exists($logoPath)) {
        $ext = strtolower(pathinfo($logoPath, PATHINFO_EXTENSION));
        $mime = ($ext === 'png') ? 'image/png' : 'image/jpeg';
        $logoB64 = 'data:'.$mime.';base64,'.base64_encode(file_get_contents($logoPath));
    }
    header('Content-Type: text/html; charset=utf-8');
    ?>
<!DOCTYPE html><html lang="fr"><head>
<meta charset="UTF-8">
<title>Rapport Tableau de Bord – <?= date('d/m/Y') ?></title>
<style>
* { box-sizing:border-box; margin:0; padding:0; }
body { font-family:Arial,Helvetica,sans-serif; font-size:11px; color:#111; background:#fff; }

.rpt-header {
    display:flex; align-items:center;
    padding:14px 24px; border-bottom:3px solid #14b8a6; margin-bottom:18px;
}
.rpt-logo { width:60px; height:60px; object-fit:contain; flex-shrink:0; }
.rpt-logo-box { width:60px; height:60px; background:#14b8a6; border-radius:8px;
    display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.rpt-title { flex:1; text-align:center; }
.rpt-title h1 { font-size:17px; font-weight:800; color:#0d9488; margin-bottom:3px; }
.rpt-title p  { font-size:10px; color:#6b7280; }
.rpt-meta { width:150px; text-align:right; font-size:10px; color:#6b7280; line-height:1.7; flex-shrink:0; }
.rpt-meta strong { color:#111; }

.stats-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:10px; padding:0 24px; margin-bottom:18px; }
.stat-box { border:1px solid #e5e7eb; border-radius:8px; padding:12px; text-align:center; }
.stat-box .sv { font-size:20px; font-weight:800; color:#0d9488; }
.stat-box .sl { font-size:10px; color:#6b7280; margin-top:2px; }

.alert-exp { background:#fef2f2; border:1px solid #fecaca; border-radius:8px;
    padding:9px 14px; margin:0 24px 18px; color:#dc2626; font-size:11px; }

.section { padding:0 24px; margin-bottom:18px; }
.sec-title { font-size:12px; font-weight:700; color:#0d9488; border-bottom:1px solid #e5e7eb;
    padding-bottom:5px; margin-bottom:9px; }

table { width:100%; border-collapse:collapse; }
thead th { background:#f0fdfa; color:#0d9488; font-size:10px; font-weight:700;
    text-transform:uppercase; padding:6px 10px; text-align:left; border-bottom:2px solid #99f6e4; }
tbody td { padding:6px 10px; border-bottom:1px solid #f3f4f6; }
tbody tr:nth-child(even) { background:#f9fafb; }

.depot-row { display:flex; align-items:center; justify-content:space-between;
    padding:7px 10px; border:1px solid #e5e7eb; border-radius:7px; margin-bottom:5px; }
.depot-qty { font-weight:800; color:#0d9488; }

.nav-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:8px; }
.nav-box { border:1px solid #e5e7eb; border-radius:7px; padding:9px 11px; }
.nav-box .nn { font-weight:700; font-size:11px; }
.nav-box .nc { font-size:10px; color:#6b7280; }
.nav-box .nv { font-size:10px; color:#0d9488; font-weight:600; margin-top:5px; }

@media print { body { -webkit-print-color-adjust:exact; print-color-adjust:exact; } }
</style>
</head><body>

<div class="rpt-header">
    <?php if ($logoB64): ?>
    <img src="<?= $logoB64 ?>" class="rpt-logo" alt="Logo">
    <?php else: ?>
    <div class="rpt-logo-box" style="color:white;font-size:22px;">🐟</div>
    <?php endif; ?>

    <div class="rpt-title">
        <h1>Tableau de Bord – Rapport</h1>
        <p>Système de Gestion des Stocks – FishStock Pro</p>
    </div>

    <div class="rpt-meta">
        Date : <strong><?= date('d/m/Y') ?></strong><br>
        Heure : <strong><?= date('H:i') ?></strong><br>
        Généré par : <strong><?= htmlspecialchars($username) ?></strong>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-box"><div class="sv"><?= $totalProduits ?></div><div class="sl">Produits enregistrés</div></div>
    <div class="stat-box"><div class="sv"><?= $totalFamilles ?></div><div class="sl">Familles de produits</div></div>
    <div class="stat-box"><div class="sv"><?= $totalNavires ?></div><div class="sl">Navires actifs</div></div>
    <div class="stat-box"><div class="sv"><?= number_format($totalQteStock,0) ?> <span style="font-size:13px"><?= $uniteStock ?></span></div><div class="sl">Stock total</div></div>
</div>

<?php if ($totalPerimes > 0): ?>
<div class="alert-exp">⚠️ <strong><?= $totalPerimes ?> produit(s) expiré(s)</strong> — Action immédiate requise</div>
<?php endif; ?>

<div class="section">
    <div class="sec-title">🏆 Top 5 Produits en Stock</div>
    <?php if ($top5): ?>
    <table><thead><tr><th>#</th><th>Produit</th><th>Famille</th><th>Quantité</th><th>Unité</th></tr></thead><tbody>
    <?php foreach ($top5 as $i => $p): ?>
    <tr><td style="color:#0d9488;font-weight:700"><?=$i+1?></td>
    <td style="font-weight:600"><?=htmlspecialchars($p['espece'])?></td>
    <td><?=htmlspecialchars($p['nom_famille'])?></td>
    <td style="font-weight:800;color:#0d9488"><?=number_format($p['qte'],0)?></td>
    <td><?=$p['unite']?></td></tr>
    <?php endforeach; ?>
    </tbody></table>
    <?php else: ?><p style="color:#9ca3af">Aucun stock enregistré.</p><?php endif; ?>
</div>

<div class="section">
    <div class="sec-title">🏢 Stocks par Dépôt</div>
    <?php foreach ($parDepot as $d): ?>
    <div class="depot-row">
        <div><div style="font-weight:600"><?=htmlspecialchars($d['nom_depot'])?></div>
        <div style="font-size:10px;color:#6b7280"><?=$d['nb_prods']?> produit(s)</div></div>
        <div class="depot-qty"><?=number_format($d['total'],0)?> <?=$d['unite']?></div>
    </div>
    <?php endforeach; ?>
</div>

<div class="section">
    <div class="sec-title">⚓ Navires Actifs</div>
    <div class="nav-grid">
    <?php foreach ($navires as $n): ?>
    <div class="nav-box">
        <div class="nn"><?=htmlspecialchars($n['nom_navire'])?></div>
        <div class="nc">Capitaine : <?=htmlspecialchars($n['capitaine_navire']??'—')?></div>
        <div class="nv">⛵ <?=$n['nb_voyages']?> voyage(s)</div>
    </div>
    <?php endforeach; ?>
    </div>
</div>

<script>window.print();</script>
</body></html>
    <?php exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord – FishStock Pro</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../include/style.css">
    <style>
        /* ===== Règles d'impression (bouton Imprimer graphiques) ===== */
        @media print {
            /* Cacher tout le shell */
            .sidebar, .topbar, .main-wrapper > footer,
            .banner-card, .stats-section, .top5-section,
            .peremption-alert-wrap, .navires-section,
            .print-hide { display:none !important; }

            .main-wrapper { margin-left:0 !important; }
            .page-content { padding:0 !important; }
            body { background:#fff !important; }

            /* Afficher le header d'impression */
            .print-header { display:flex !important; }

            /* Afficher uniquement les graphiques */
            .charts-section { display:grid !important; }
            .charts-section .card { break-inside:avoid; box-shadow:none !important; border:1px solid #e5e7eb !important; }
        }

        /* Header affiché seulement à l'impression */
        .print-header {
            display:none;
            align-items:center;
            gap:16px;
            padding:12px 0 16px;
            border-bottom:3px solid #14b8a6;
            margin-bottom:20px;
        }
        .print-header img { width:56px; height:56px; object-fit:contain; }
        .print-header-icon {
            width:56px; height:56px; background:#14b8a6; border-radius:10px;
            display:flex; align-items:center; justify-content:center; color:white; font-size:1.4rem;
        }
        .print-header-text { flex:1; text-align:center; }
        .print-header-text h2 { font-size:15px; font-weight:800; color:#0d9488; }
        .print-header-text p  { font-size:10px; color:#6b7280; }
        .print-header-meta { text-align:right; font-size:10px; color:#6b7280; line-height:1.7; }
        .print-header-meta strong { color:#111; }
    </style>
    <script>
        (function(){
            const t = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-theme', t);
        })();
    </script>
</head>
<body>
<div class="app-layout">

    <?php include '../include/sidebar.php'; ?>

    <div class="main-wrapper" id="mainWrapper">

        <!-- ===== TOPBAR ===== -->
        <div class="topbar">
            <div style="display:flex;align-items:center;gap:12px;">
                <button onclick="openMobileSidebar()"
                        id="mobileBurger"
                        style="display:none;background:none;border:none;cursor:pointer;color:var(--text-primary);font-size:1.2rem;">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <div>
                    <div class="topbar-title">Tableau de Bord</div>
                    <div class="topbar-subtitle">
                        <i class="fa-solid fa-user" style="margin-right:4px;color:var(--primary)"></i>
                        Connecté : <strong><?= htmlspecialchars($username) ?></strong>
                        &bull; <span style="color:var(--primary)"><?= $isAdmin ? 'Admin' : 'Utilisateur' ?></span>
                    </div>
                </div>
            </div>
            <div class="topbar-right">
                <span class="topbar-date"><i class="fa-regular fa-calendar"></i> <?= date('d/m/Y') ?></span>
                <button class="theme-toggle" id="themeToggle" title="Changer de thème">
                    <i class="fa-solid fa-moon"></i>
                </button>
                <?php if ($isAdmin): ?>
                <!-- Menu Actions rapides — ADMIN seulement -->
                <div style="position:relative;" id="actionsWrap">
                    <button class="btn btn-primary btn-sm" onclick="toggleActionsMenu()" id="actionsBtn">
                        <i class="fa-solid fa-bolt"></i> Actions rapides
                        <i class="fa-solid fa-chevron-down" style="font-size:.6rem;margin-left:2px"></i>
                    </button>
                    <div id="actionsMenu"
                         style="display:none;position:absolute;right:0;top:calc(100% + 8px);
                                background:var(--card-bg);border:1px solid var(--card-border);
                                border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,.15);
                                min-width:190px;z-index:300;padding:6px;">
                        <?php
                        $actions = [
                            ['stock.php',   'fa-warehouse', 'Gérer les stocks',  'var(--primary)'],
                            ['capture.php', 'fa-fish',      'Nouvelle capture',  'var(--primary)'],
                            ['voyage.php',  'fa-ship',      'Nouveau voyage',    'var(--primary)'],
                            ['produit.php', 'fa-box',       'Ajouter produit',   'var(--primary)'],
                        ];
                        foreach ($actions as $a): ?>
                        <a href="<?= $a[0] ?>"
                           style="display:flex;align-items:center;gap:9px;padding:9px 12px;
                                  border-radius:7px;text-decoration:none;color:var(--text-primary);
                                  font-size:.85rem;font-weight:500;transition:background .15s;"
                           onmouseover="this.style.background='var(--body-bg)'"
                           onmouseout="this.style.background='transparent'">
                            <i class="fa-solid <?= $a[1] ?>" style="color:<?= $a[3] ?>;width:16px;text-align:center"></i>
                            <?= $a[2] ?>
                        </a>
                        <?php endforeach; ?>
                        <div style="height:1px;background:var(--card-border);margin:4px 0;"></div>
                        <a href="user.php"
                           style="display:flex;align-items:center;gap:9px;padding:9px 12px;
                                  border-radius:7px;text-decoration:none;color:var(--text-primary);
                                  font-size:.85rem;font-weight:500;transition:background .15s;"
                           onmouseover="this.style.background='var(--body-bg)'"
                           onmouseout="this.style.background='transparent'">
                            <i class="fa-solid fa-users-gear" style="color:#f59e0b;width:16px;text-align:center"></i>
                            Utilisateurs
                        </a>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- ===== PAGE CONTENT ===== -->
        <div class="page-content">

            <!-- Header visible uniquement à l'impression (graphiques) -->
            <div class="print-header">
                <img src="../images/logo_refrige.png" alt="Logo"
                     onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                <div class="print-header-icon" style="display:none"><i class="fa-solid fa-fish"></i></div>
                <div class="print-header-text">
                    <h2>FishStock Pro – Graphiques des Stocks</h2>
                    <p>Système de Gestion des Stocks</p>
                </div>
                <div class="print-header-meta">
                    Date : <strong><?= date('d/m/Y') ?></strong><br>
                    Heure : <strong><?= date('H:i') ?></strong><br>
                    Par : <strong><?= htmlspecialchars($username) ?></strong>
                </div>
            </div>

            <!-- Bannière titre + boutons -->
            <div class="card banner-card" style="margin-bottom:20px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
                <div style="display:flex;align-items:center;gap:14px;">
                    <div style="width:48px;height:48px;background:var(--primary-light);border-radius:12px;display:flex;align-items:center;justify-content:center;">
                        <i class="fa-solid fa-gauge" style="font-size:1.4rem;color:var(--primary)"></i>
                    </div>
                    <div>
                        <h1 style="font-size:1.5rem;font-weight:800;color:var(--text-primary)">Tableau de Bord</h1>
                        <p style="color:var(--text-muted);font-size:.82rem;">
                            Bienvenue, <strong style="color:var(--text-primary)"><?= htmlspecialchars($username) ?></strong>
                            &bull; <i class="fa-regular fa-calendar" style="margin:0 3px"></i><?= date('d F Y') ?>
                        </p>
                    </div>
                </div>
                <div style="display:flex;gap:10px;flex-wrap:wrap;">
                    <button onclick="imprimerGraphiques()" class="btn btn-outline">
                        <i class="fa-solid fa-print"></i> Imprimer graphiques
                    </button>
                    <?php if ($isAdmin): ?>
                    <a href="?export=pdf" target="_blank" class="btn btn-primary">
                        <i class="fa-solid fa-file-pdf"></i> Exporter PDF
                    </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- ===== STATS ===== -->
            <div class="grid-4 stats-section" style="margin-bottom:20px;">

                <div class="stat-card">
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:12px;">
                        <div class="stat-card-icon icon-teal"><i class="fa-solid fa-fish"></i></div>
                        <span class="stat-badge stat-badge-up"><i class="fa-solid fa-arrow-up"></i> 12%</span>
                    </div>
                    <div class="stat-value"><?= $totalProduits ?></div>
                    <div class="stat-label">Produits enregistrés</div>
                    <?php if ($isAdmin): ?>
                        <a href="produit.php" class="stat-link">Voir tous →</a>
                    <?php else: ?>
                        <span style="font-size:.78rem;color:var(--text-muted);">Produits en base</span>
                    <?php endif; ?>
                </div>

                <div class="stat-card">
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:12px;">
                        <div class="stat-card-icon icon-green"><i class="fa-solid fa-layer-group"></i></div>
                        <span class="stat-badge stat-badge-up"><i class="fa-solid fa-arrow-up"></i> 5%</span>
                    </div>
                    <div class="stat-value"><?= $totalFamilles ?></div>
                    <div class="stat-label">Familles de produits</div>
                    <?php if ($isAdmin): ?>
                        <a href="famille.php" class="stat-link">Voir toutes →</a>
                    <?php else: ?>
                        <span style="font-size:.78rem;color:var(--text-muted);">Familles en base</span>
                    <?php endif; ?>
                </div>

                <div class="stat-card">
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:12px;">
                        <div class="stat-card-icon icon-purple"><i class="fa-solid fa-ship"></i></div>
                        <span class="stat-badge stat-badge-stable">Stable</span>
                    </div>
                    <div class="stat-value"><?= $totalNavires ?></div>
                    <div class="stat-label">Navires actifs</div>
                    <?php if ($isAdmin): ?>
                        <a href="navire.php" class="stat-link">Voir les navires →</a>
                    <?php else: ?>
                        <span style="font-size:.78rem;color:var(--text-muted);">Flotte totale</span>
                    <?php endif; ?>
                </div>

                <div class="stat-card">
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:12px;">
                        <div class="stat-card-icon icon-orange"><i class="fa-solid fa-warehouse"></i></div>
                        <span class="stat-badge stat-badge-up"><i class="fa-solid fa-arrow-up"></i> 8%</span>
                    </div>
                    <div class="stat-value"><?= number_format($totalQteStock,0) ?> <span style="font-size:1rem;font-weight:500;color:var(--text-secondary)"><?= $uniteStock ?></span></div>
                    <div class="stat-label">Stock total disponible</div>
                    <a href="stock.php" class="stat-link"><?= $isAdmin ? 'Gérer le stock' : 'Voir le stock' ?> →</a>
                </div>

            </div>

            <!-- ===== ALERTE PÉREMPTION ===== -->
            <?php if ($totalPerimes > 0): ?>
            <div class="peremption-alert-wrap" style="margin-bottom:20px;">
                <?php if ($isAdmin): ?><a href="stock.php" style="text-decoration:none;display:block;"><?php endif; ?>
                <div class="peremption-alert" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;<?= !$isAdmin ? 'cursor:default;' : '' ?>">
                    <div>
                        <div style="font-size:1rem;font-weight:700;display:flex;align-items:center;gap:8px;">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                            <?= $totalPerimes ?> produit(s) expiré(s) —
                            <?= $isAdmin ? 'Action immédiate requise' : 'Contactez l\'administrateur' ?>
                        </div>
                        <div style="font-size:.82rem;opacity:.9;margin-top:4px;">
                            <?= $isAdmin ? 'Cliquez pour voir les détails' : 'Information réservée à l\'administrateur' ?>
                        </div>
                    </div>
                    <i class="fa-solid fa-circle-exclamation" style="font-size:2rem;opacity:.8"></i>
                </div>
                <?php if ($isAdmin): ?></a><?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- ===== TOP5 + DEPOTS ===== -->
            <div class="grid-2 top5-section" style="margin-bottom:20px;">
                <div class="card">
                    <div class="card-title"><i class="fa-solid fa-trophy" style="color:#f59e0b"></i> Top 5 Produits en Stock</div>
                    <?php if ($top5): ?>
                    <table>
                        <thead><tr><th>Produit</th><th>Famille</th><th>Quantité</th><th>Unité</th></tr></thead>
                        <tbody>
                        <?php foreach ($top5 as $p): ?>
                        <tr>
                            <td style="display:flex;align-items:center;gap:8px;">
                                <span style="width:8px;height:8px;background:var(--primary);border-radius:50%;display:inline-block;flex-shrink:0;"></span>
                                <?= htmlspecialchars($p['espece']) ?>
                            </td>
                            <td style="color:var(--text-muted)"><?= htmlspecialchars($p['nom_famille']) ?></td>
                            <td><strong><?= number_format($p['qte'],0) ?></strong></td>
                            <td style="color:var(--text-muted)"><?= $p['unite'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?><p style="color:var(--text-muted);font-size:.875rem;">Aucun stock.</p><?php endif; ?>
                    <?php if ($isAdmin): ?>
                    <div style="margin-top:14px;padding-top:12px;border-top:1px solid var(--card-border);">
                        <a href="stock.php" style="font-size:.82rem;color:var(--text-muted);text-decoration:none;display:flex;align-items:center;gap:6px;">
                            <i class="fa-solid fa-list"></i> Voir tous les stocks
                        </a>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="card">
                    <div class="card-title"><i class="fa-solid fa-building" style="color:#8b5cf6"></i> Stocks par Dépôt</div>
                    <?php if ($parDepot): ?>
                    <?php foreach ($parDepot as $d): ?>
                    <div class="depot-stat">
                        <div class="depot-icon"><i class="fa-solid fa-building"></i></div>
                        <div style="flex:1;min-width:0;">
                            <div style="font-size:.85rem;font-weight:600;color:var(--text-primary);"><?= htmlspecialchars($d['nom_depot']) ?></div>
                            <div style="font-size:.75rem;color:var(--text-muted);"><?= $d['nb_prods'] ?> produit(s)</div>
                        </div>
                        <div style="text-align:right;flex-shrink:0;">
                            <div style="font-size:.95rem;font-weight:700;color:var(--text-primary);"><?= number_format($d['total'],0) ?> <?= $d['unite'] ?></div>
                            <div style="font-size:.72rem;color:var(--text-muted);">stock</div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php else: ?><p style="color:var(--text-muted);font-size:.875rem;">Aucun dépôt.</p><?php endif; ?>
                </div>
            </div>

            <!-- ===== NAVIRES ===== -->
            <div class="card navires-section" style="margin-bottom:20px;">
                <div class="card-title" style="justify-content:space-between;flex-wrap:wrap;gap:8px;">
                    <span><i class="fa-solid fa-ship" style="color:var(--primary)"></i> Navires Actifs</span>
                    <?php if ($isAdmin): ?>
                    <a href="voyage.php" class="btn btn-primary btn-sm">
                        <i class="fa-solid fa-plus"></i> Nouveau voyage
                    </a>
                    <?php endif; ?>
                </div>
                <?php if ($navires): ?>
                <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:12px;">
                    <?php foreach ($navires as $n): ?>
                    <div class="navire-card">
                        <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:12px;">
                            <div>
                                <div style="font-size:.9rem;font-weight:700;color:var(--text-primary);"><?= htmlspecialchars($n['nom_navire']) ?></div>
                                <div style="font-size:.78rem;color:var(--text-muted);">Capitaine: <?= htmlspecialchars($n['capitaine_navire']??'—') ?></div>
                            </div>
                            <div class="navire-icon"><i class="fa-solid fa-anchor"></i></div>
                        </div>
                        <div style="display:flex;align-items:center;justify-content:space-between;">
                            <span style="font-size:.82rem;color:var(--text-muted);display:flex;align-items:center;gap:6px;">
                                <i class="fa-solid fa-route"></i> Voyages
                            </span>
                            <strong style="color:var(--text-primary)"><?= $n['nb_voyages'] ?></strong>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?><p style="color:var(--text-muted);font-size:.875rem;">Aucun navire.</p><?php endif; ?>
            </div>

            <!-- ===== GRAPHIQUES ===== -->
            <div class="grid-2 charts-section">

                <div class="card">
                    <div class="card-title" style="justify-content:space-between;flex-wrap:wrap;">
                        <span><i class="fa-solid fa-chart-pie" style="color:var(--primary)"></i> Répartition par Famille</span>
                        <span style="font-size:.78rem;color:var(--text-muted);">En kilogrammes</span>
                    </div>
                    <?php if ($parFamille): ?>
                    <canvas id="chartFamille" style="max-height:240px;max-width:240px;margin:0 auto;display:block;"></canvas>
                    <div style="margin-top:12px;padding-top:10px;border-top:1px solid var(--card-border);display:flex;justify-content:space-between;font-size:.8rem;color:var(--text-muted);">
                        <span>Total : <strong style="color:var(--text-primary)"><?= number_format(array_sum(array_column($parFamille,'total')),0) ?> kg</strong></span>
                        <?php if ($isAdmin): ?>
                        <a href="famille.php" style="color:var(--primary);text-decoration:none;">Détails <i class="fa-solid fa-arrow-up-right-from-square" style="font-size:.7rem"></i></a>
                        <?php endif; ?>
                    </div>
                    <?php else: ?><p style="color:var(--text-muted);font-size:.875rem;text-align:center;padding:30px 0">Pas de données</p><?php endif; ?>
                </div>

                <div class="card">
                    <div class="card-title" style="justify-content:space-between;flex-wrap:wrap;">
                        <span><i class="fa-solid fa-chart-line" style="color:var(--primary)"></i> Évolution des Stocks</span>
                        <span style="font-size:.78rem;color:var(--text-muted);">6 derniers mois</span>
                    </div>
                    <?php if ($evolution): ?>
                    <canvas id="chartEvolution" style="max-height:240px;"></canvas>
                    <p style="font-size:.75rem;color:var(--text-muted);margin-top:10px;">
                        <i class="fa-solid fa-circle-info" style="margin-right:4px;"></i>
                        Données basées sur les entrées en stock
                    </p>
                    <?php else: ?><p style="color:var(--text-muted);font-size:.875rem;text-align:center;padding:30px 0">Pas de données</p><?php endif; ?>
                </div>

            </div>

        </div><!-- /page-content -->
        <?php include '../include/footer.php'; ?>
    </div><!-- /main-wrapper -->
</div><!-- /app-layout -->

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="../js/script.js"></script>
<script>
// ===== Graphiques =====
<?php if ($parFamille): ?>
const famLabels = <?= json_encode(array_column($parFamille,'nom_famille')) ?>;
const famData   = <?= json_encode(array_column($parFamille,'total')) ?>;
const tealPalette = ['#14b8a6','#0d9488','#0f766e','#134e4a','#22c55e','#16a34a','#0891b2'];

new Chart(document.getElementById('chartFamille'), {
    type: 'doughnut',
    data: { labels: famLabels, datasets: [{ data: famData, backgroundColor: tealPalette, borderWidth: 3, borderColor: '#fff' }] },
    options: { cutout: '65%', animation:{ duration:600 }, plugins:{ legend:{ position:'bottom', labels:{ color:'#6b7280', font:{size:11}, padding:12 } } } }
});
<?php endif; ?>

<?php if ($evolution): ?>
const evoLabels = <?= json_encode(array_column($evolution,'mois')) ?>;
const evoData   = <?= json_encode(array_column($evolution,'total')) ?>;

new Chart(document.getElementById('chartEvolution'), {
    type: 'line',
    data: { labels: evoLabels, datasets: [{ label:'Quantité entrée', data:evoData, borderColor:'#14b8a6', backgroundColor:'rgba(20,184,166,0.12)', fill:true, tension:0.3, pointBackgroundColor:'#14b8a6', pointRadius:5 }] },
    options: { animation:{duration:600}, scales:{ x:{ticks:{color:'#6b7280'},grid:{color:'rgba(0,0,0,0.05)'}}, y:{ticks:{color:'#6b7280',callback:v=>v+' kg'},grid:{color:'rgba(0,0,0,0.05)'}} }, plugins:{legend:{display:false}} }
});
<?php endif; ?>

// ===== Actions rapides dropdown =====
function toggleActionsMenu() {
    const m = document.getElementById('actionsMenu');
    if (m) m.style.display = (m.style.display === 'none' || !m.style.display) ? 'block' : 'none';
}
document.addEventListener('click', function(e) {
    const wrap = document.getElementById('actionsWrap');
    if (wrap && !wrap.contains(e.target)) {
        const m = document.getElementById('actionsMenu');
        if (m) m.style.display = 'none';
    }
});

// ===== Imprimer uniquement les graphiques =====
function imprimerGraphiques() {
    // Cacher tout sauf .charts-section
    const toHide = document.querySelectorAll(
        '.banner-card, .stats-section, .peremption-alert-wrap, .top5-section, .navires-section, footer'
    );
    toHide.forEach(el => el.style.display = 'none');
    window.print();
    setTimeout(() => toHide.forEach(el => el.style.display = ''), 800);
}

// ===== Mobile burger =====
const mq = window.matchMedia('(max-width:768px)');
function checkMobile() {
    const b = document.getElementById('mobileBurger');
    if (b) b.style.display = mq.matches ? 'block' : 'none';
}
mq.addEventListener('change', checkMobile);
checkMobile();
</script>
</body>
</html>
