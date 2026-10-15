<?php
// auth/login.php
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: ../pages_principale/index.php');
    exit;
}
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once '../include/connexion.php';
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($username && $password) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND password = ?");
        $stmt->execute([$username, $password]);
        $user = $stmt->fetch();
        if ($user) {
            $_SESSION['user_id']  = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role']     = $user['role'];
            $_SESSION['welcome']  = true;
            header('Location: loading.php');
            exit;
        } else {
            $error = "Nom d'utilisateur ou mot de passe incorrect.";
        }
    } else {
        $error = "Veuillez remplir tous les champs.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion – Gestion Stock Produit</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        * { box-sizing:border-box; margin:0; padding:0; }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-image: url('../images/vue.jpg');
            background-size: cover;
            background-position: center;
            background-color: #1a3a4a;
            font-family: 'Segoe UI', system-ui, sans-serif;
        }

        /* Overlay sombre sur le fond */
        body::before {
            content:'';
            position:fixed;
            inset:0;
            background:rgba(10,30,50,0.55);
        }

        .login-card {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 420px;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0,0,0,0.4);
            animation: fadeUp 0.5s ease;
        }

        @keyframes fadeUp {
            from { opacity:0; transform:translateY(24px); }
            to   { opacity:1; transform:translateY(0); }
        }

        /* ===== En-tête teal ===== */
        .card-header {
            background: #14b8a6;
            padding: 32px 24px 28px;
            text-align: center;
            position: relative;
        }

        /* Forme ovale blanche derrière le logo */
        .logo-oval {
            width: 120px;
            height: 80px;
            background: white;
            border-radius: 50%;
            margin: 0 auto 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 16px rgba(0,0,0,0.15);
            overflow: hidden;
        }
        .logo-oval img {
            width: 90px;
            height: 60px;
            object-fit: contain;
        }
        .logo-oval .logo-fallback {
            font-size: 2rem;
            color: #14b8a6;
        }

        .card-header h1 {
            color: white;
            font-size: 1.35rem;
            font-weight: 800;
            margin-bottom: 4px;
        }
        .card-header p {
            color: rgba(255,255,255,0.85);
            font-size: 0.82rem;
        }

        /* ===== Corps blanc ===== */
        .card-body {
            background: white;
            padding: 28px 28px 32px;
        }

        .card-body h2 {
            text-align: center;
            font-size: 1.1rem;
            font-weight: 700;
            color: #111827;
            margin-bottom: 22px;
        }

        /* Champs input */
        .input-group {
            display: flex;
            align-items: center;
            gap: 0;
            border: 1.5px solid #e5e7eb;
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 14px;
            background: white;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .input-group:focus-within {
            border-color: #14b8a6;
            box-shadow: 0 0 0 3px rgba(20,184,166,0.12);
        }
        .input-icon {
            width: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #14b8a6;
            font-size: 0.95rem;
            flex-shrink: 0;
        }
        .input-group input {
            flex: 1;
            border: none;
            outline: none;
            padding: 13px 12px 13px 0;
            font-size: 0.9rem;
            color: #111827;
            background: transparent;
        }
        .input-group input::placeholder { color: #9ca3af; }
        .eye-btn {
            width: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #9ca3af;
            cursor: pointer;
            font-size: 0.9rem;
            flex-shrink: 0;
        }
        .eye-btn:hover { color: #14b8a6; }

        /* Erreur */
        .error-msg {
            display: flex;
            align-items: center;
            gap: 8px;
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #dc2626;
            border-radius: 8px;
            padding: 10px 14px;
            font-size: 0.83rem;
            margin-bottom: 16px;
        }

        /* Bouton connexion */
        .btn-connect {
            width: 100%;
            background: #14b8a6;
            color: white;
            border: none;
            border-radius: 10px;
            padding: 14px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: background 0.2s, transform 0.15s;
            margin-top: 6px;
        }
        .btn-connect:hover {
            background: #0d9488;
            transform: translateY(-1px);
        }

        /* Footer carte */
        .card-footer {
            background: white;
            border-top: 1px solid #f3f4f6;
            padding: 12px;
            text-align: center;
            font-size: 0.75rem;
            color: #9ca3af;
        }
        .card-footer a { color: #14b8a6; text-decoration: none; }
    </style>
</head>
<body>

<div class="login-card">

    <!-- En-tête teal -->
    <div class="card-header">
        <div class="logo-oval">
            <img src="../images/logo_refrige.png" alt="Logo"
                 onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
            <div class="logo-fallback" style="display:none;align-items:center;justify-content:center;width:100%;height:100%">
                <i class="fa-solid fa-fish"></i>
            </div>
        </div>
        <h1>Gestion Stock Produit</h1>
        <p>Système de gestion des stocks</p>
    </div>

    <!-- Corps blanc -->
    <div class="card-body">
        <h2>Connexion au système</h2>

        <?php if ($error): ?>
        <div class="error-msg">
            <i class="fa-solid fa-circle-exclamation"></i>
            <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <form method="POST" autocomplete="off">

            <!-- Nom d'utilisateur -->
            <div class="input-group">
                <div class="input-icon"><i class="fa-solid fa-user"></i></div>
                <input type="text" name="username" placeholder="Nom d'utilisateur"
                       value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required autocomplete="username">
            </div>

            <!-- Mot de passe -->
            <div class="input-group">
                <div class="input-icon"><i class="fa-solid fa-lock"></i></div>
                <input type="password" id="pwd" name="password" placeholder="Mot de passe" required autocomplete="current-password">
                <div class="eye-btn" onclick="togglePwd()">
                    <i id="eyeIcon" class="fa-regular fa-eye"></i>
                </div>
            </div>

            <button type="submit" class="btn-connect">
                <i class="fa-solid fa-right-to-bracket"></i>
                Se connecter
            </button>

        </form>
    </div>

    <div class="card-footer">
        &copy; <?= date('Y') ?> FishStock Pro &mdash; Tous droits réservés
    </div>

</div>

<script>
    // Appliquer le thème sauvegardé
    const t = localStorage.getItem('theme') || 'light';
    document.documentElement.setAttribute('data-theme', t);

    function togglePwd() {
        const p = document.getElementById('pwd');
        const i = document.getElementById('eyeIcon');
        if (p.type === 'password') {
            p.type = 'text';
            i.classList.replace('fa-eye','fa-eye-slash');
        } else {
            p.type = 'password';
            i.classList.replace('fa-eye-slash','fa-eye');
        }
    }
</script>
</body>
</html>
