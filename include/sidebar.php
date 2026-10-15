<?php
// include/sidebar.php
if (session_status() === PHP_SESSION_NONE) session_start();
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
$isAdmin = ($_SESSION['role'] ?? '') === 'admin';
$username = $_SESSION['username'] ?? 'Utilisateur';
$initial = strtoupper(substr($username, 0, 1));
?>
<!-- SIDEBAR -->
<aside class="sidebar" id="sidebar">
    <button class="sidebar-toggle" id="sidebarToggle" title="Réduire">
        <i class="fa-solid fa-chevron-left"></i>
    </button>

    <!-- Logo -->
    <a href="index.php" class="sidebar-logo">
        <div class="sidebar-logo-icon">
            <img src="../images/logo_refrige.png" alt="Logo"
                 onerror="this.parentElement.innerHTML='<i class=\'fa-solid fa-fish\' style=\'color:white;font-size:1.1rem\'></i>'">
        </div>
        <div class="sidebar-logo-text">
            <strong>FishStock Pro</strong>
            <span>Gestion Stock Produit</span>
        </div>
    </a>

    <!-- Utilisateur -->
    <div class="sidebar-user">
        <div class="sidebar-avatar"><?= $initial ?></div>
        <div class="sidebar-user-info">
            <strong><?= htmlspecialchars($username) ?></strong>
            <?php if ($isAdmin): ?>
            <span class="sidebar-badge-admin">Admin</span>
            <?php else: ?>
            <span class="sidebar-badge-user">Utilisateur</span>
            <?php endif; ?>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="sidebar-nav">

        <!-- Tableau de bord -->
        <a href="index.php" class="sidebar-link <?= $currentPage === 'index' ? 'active' : '' ?>">
            <span class="link-icon"><i class="fa-solid fa-gauge"></i></span>
            <span class="link-label">Tableau de bord</span>
        </a>

        <?php if ($isAdmin): ?>
        <!-- Section admin uniquement -->
        <div class="sidebar-section-label">GESTION DES STOCKS</div>
        <a href="stock.php" class="sidebar-link <?= $currentPage === 'stock' ? 'active' : '' ?>">
            <span class="link-icon"><i class="fa-solid fa-warehouse"></i></span>
            <span class="link-label">Stocks</span>
        </a>

        <div class="sidebar-section-label">NAVIGATION</div>
        <a href="voyage.php" class="sidebar-link <?= $currentPage === 'voyage' ? 'active' : '' ?>">
            <span class="link-icon"><i class="fa-solid fa-ship"></i></span>
            <span class="link-label">Voyages</span>
        </a>
        <a href="capture.php" class="sidebar-link <?= $currentPage === 'capture' ? 'active' : '' ?>">
            <span class="link-icon"><i class="fa-solid fa-fish"></i></span>
            <span class="link-label">Captures</span>
        </a>

        <div class="sidebar-section-label">CATALOGUE</div>
        <a href="produit.php" class="sidebar-link <?= $currentPage === 'produit' ? 'active' : '' ?>">
            <span class="link-icon"><i class="fa-solid fa-box"></i></span>
            <span class="link-label">Produits</span>
        </a>
        <a href="famille.php" class="sidebar-link <?= $currentPage === 'famille' ? 'active' : '' ?>">
            <span class="link-icon"><i class="fa-solid fa-layer-group"></i></span>
            <span class="link-label">Familles</span>
        </a>

        <div class="sidebar-section-label">CONFIGURATION</div>
        <a href="depot.php" class="sidebar-link <?= $currentPage === 'depot' ? 'active' : '' ?>">
            <span class="link-icon"><i class="fa-solid fa-building"></i></span>
            <span class="link-label">Dépôts</span>
        </a>
        <a href="navire.php" class="sidebar-link <?= $currentPage === 'navire' ? 'active' : '' ?>">
            <span class="link-icon"><i class="fa-solid fa-anchor"></i></span>
            <span class="link-label">Navires</span>
        </a>
        <a href="campagne.php" class="sidebar-link <?= $currentPage === 'campagne' ? 'active' : '' ?>">
            <span class="link-icon"><i class="fa-solid fa-flag"></i></span>
            <span class="link-label">Campagnes</span>
        </a>
        <a href="user.php" class="sidebar-link <?= $currentPage === 'user' ? 'active' : '' ?>">
            <span class="link-icon"><i class="fa-solid fa-users-gear"></i></span>
            <span class="link-label">Utilisateurs</span>
        </a>

        <?php else: ?>
        <!-- Menu utilisateur simple -->
        <div class="sidebar-section-label">GESTION DES STOCKS</div>
        <a href="stock.php" class="sidebar-link <?= $currentPage === 'stock' ? 'active' : '' ?>">
            <span class="link-icon"><i class="fa-solid fa-warehouse"></i></span>
            <span class="link-label">Stocks</span>
        </a>

        <div class="sidebar-section-label">NAVIGATION</div>
        <a href="voyage.php" class="sidebar-link <?= $currentPage === 'voyage' ? 'active' : '' ?>">
            <span class="link-icon"><i class="fa-solid fa-ship"></i></span>
            <span class="link-label">Voyages</span>
        </a>
        <a href="capture.php" class="sidebar-link <?= $currentPage === 'capture' ? 'active' : '' ?>">
            <span class="link-icon"><i class="fa-solid fa-fish"></i></span>
            <span class="link-label">Captures</span>
        </a>
        <?php endif; ?>

        <!-- À propos (tous) -->
        <a href="apropos.php" class="sidebar-link <?= $currentPage === 'apropos' ? 'active' : '' ?>" style="margin-top:8px">
            <span class="link-icon"><i class="fa-solid fa-circle-info"></i></span>
            <span class="link-label">À propos</span>
        </a>

        <!-- Déconnexion -->
        <a href="../auth/logout.php" class="sidebar-link sidebar-link-logout" style="margin-top:4px">
            <span class="link-icon"><i class="fa-solid fa-right-from-bracket"></i></span>
            <span class="link-label">Déconnexion</span>
        </a>

    </nav>
</aside>

<!-- Overlay mobile -->
<div class="mobile-overlay" id="mobileOverlay" onclick="closeMobileSidebar()"></div>
