<?php
// auth/logout.php
session_start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Déconnexion</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../include/style.css">
    <script>
        const t = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', t);
    </script>
</head>
<body style="display:flex;align-items:center;justify-content:center;min-height:100vh;">
    <div class="card" style="max-width:380px;width:100%;text-align:center;padding:32px;">
        <div style="width:64px;height:64px;background:#fef2f2;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:1.8rem;color:#ef4444;">
            <i class="fa-solid fa-right-from-bracket"></i>
        </div>
        <h2 style="font-size:1.15rem;font-weight:700;color:var(--text-primary);margin-bottom:8px;">Déconnexion</h2>
        <p style="color:var(--text-secondary);font-size:0.875rem;margin-bottom:24px;">Êtes-vous sûr de vouloir vous déconnecter ?</p>
        <div style="display:flex;gap:10px;">
            <a href="../pages_principale/index.php" class="btn btn-secondary" style="flex:1;justify-content:center;">
                <i class="fa-solid fa-xmark"></i> Annuler
            </a>
            <a href="do_logout.php" class="btn btn-danger" style="flex:1;justify-content:center;">
                <i class="fa-solid fa-check"></i> Confirmer
            </a>
        </div>
    </div>
</body>
</html>
