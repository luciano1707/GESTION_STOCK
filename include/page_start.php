<?php
// include/page_start.php
// Inclusion en tête de chaque page CRUD
// Usage: include_once '../include/page_start.php'; puis passer $pageTitle, $pageIcon
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Gestion Stocks') ?> – FishStock Pro</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../include/style.css">
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

<!-- Topbar -->
<div class="topbar">
    <div style="display:flex;align-items:center;gap:12px;">
        <button onclick="openMobileSidebar()" style="display:none;background:none;border:none;cursor:pointer;color:var(--text-primary);font-size:1.2rem;" id="mobileBurger">
            <i class="fa-solid fa-bars"></i>
        </button>
        <div>
            <div class="topbar-title"><?= htmlspecialchars($pageTitle ?? '') ?></div>
            <div class="topbar-subtitle">
                <i class="fa-solid fa-user" style="margin-right:4px;color:var(--primary)"></i>
                <?= htmlspecialchars($_SESSION['username'] ?? '') ?>
                &bull; <span style="color:var(--primary)"><?= isAdmin() ? 'Admin' : 'Utilisateur' ?></span>
            </div>
        </div>
    </div>
    <div class="topbar-right">
        <span class="topbar-date"><i class="fa-regular fa-calendar"></i> <?= date('d/m/Y') ?></span>
        <button class="theme-toggle" id="themeToggle" title="Changer de thème">
            <i class="fa-solid fa-moon"></i>
        </button>
    </div>
</div>

<div class="page-content">
