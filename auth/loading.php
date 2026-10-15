<?php
// auth/loading.php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }
$role     = $_SESSION['role']     ?? 'user';
$username = htmlspecialchars($_SESSION['username'] ?? 'Utilisateur');
$isAdmin  = ($role === 'admin');
$roleLabel = $isAdmin ? 'Administrateur' : 'Utilisateur';
?><!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Chargement – FishStock Pro</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@600;700;800&family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
/* ═══════════════════════════════════════════
   BASE
═══════════════════════════════════════════ */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

:root {
  --teal:   #14b8a6;
  --teal-d: #0d9488;
  --blue:   #0891b2;
  --indigo: #6366f1;
  --green:  #16a34a;

  --bg:      #edfaf8;
  --card:    #ffffff;
  --text:    #0f172a;
  --text-2:  #334155;
  --muted:   #64748b;
  --border:  #e2e8f0;
  --surface: #f8fafc;

  --ff-head: 'Syne', sans-serif;
  --ff-body: 'DM Sans', sans-serif;
}
html.dark {
  --bg:      #060d16;
  --card:    #0c1929;
  --text:    #f1f5f9;
  --text-2:  #cbd5e1;
  --muted:   #64748b;
  --border:  #1a3352;
  --surface: #0f2235;
}

body {
  font-family: var(--ff-body);
  min-height: 100dvh;
  background: var(--bg);
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  transition: background .5s;
}

/* ─ Fond gradient animé ─ */
body::before {
  content: '';
  position: fixed; inset: 0;
  background:
    radial-gradient(ellipse 75% 55% at 15% 15%, rgba(20,184,166,.14) 0%, transparent 55%),
    radial-gradient(ellipse 55% 45% at 85% 85%, rgba(99,102,241,.10) 0%, transparent 55%),
    radial-gradient(ellipse 45% 40% at 50% 50%, rgba(8,145,178,.07) 0%, transparent 55%);
  pointer-events: none; z-index: 0;
  animation: bgPulse 6s ease-in-out infinite alternate;
}
@keyframes bgPulse { from { opacity: .7; } to { opacity: 1; } }
html.dark body::before {
  background:
    radial-gradient(ellipse 75% 55% at 15% 15%, rgba(20,184,166,.2)  0%, transparent 55%),
    radial-gradient(ellipse 55% 45% at 85% 85%, rgba(99,102,241,.15) 0%, transparent 55%),
    radial-gradient(ellipse 45% 40% at 50% 50%, rgba(8,145,178,.10)  0%, transparent 55%);
}

/* ─ Particules ─ */
.ptcls { position: fixed; inset: 0; pointer-events: none; z-index: 1; overflow: hidden; }
.p {
  position: absolute; border-radius: 50%; opacity: 0;
  animation: rise linear infinite; filter: blur(.4px);
}
@keyframes rise {
  0%   { transform: translateY(110vh) rotate(0deg)   scale(0);   opacity: 0; }
  8%   { opacity: .18; }
  86%  { opacity: .1; }
  100% { transform: translateY(-60px)  rotate(520deg) scale(1.1); opacity: 0; }
}

/* ═══════════════════════════════════════════
   CARD
═══════════════════════════════════════════ */
.card {
  position: relative; z-index: 10;
  width: min(460px, 94vw);
  background: var(--card);
  border-radius: 32px;
  padding: 40px 36px 28px;
  box-shadow:
    0 0 0 1px rgba(20,184,166,.16),
    0 4px 8px  rgba(0,0,0,.04),
    0 20px 60px rgba(20,184,166,.10),
    0 48px 96px rgba(0,0,0,.07);
  animation: cardIn .65s cubic-bezier(.34,1.56,.64,1) both;
}
@keyframes cardIn {
  from { opacity: 0; transform: translateY(42px) scale(.92); }
  to   { opacity: 1; transform: none; }
}
/* Ligne déco haut */
.card::before {
  content: '';
  position: absolute; top: 0; left: 36px; right: 36px; height: 3px;
  border-radius: 0 0 4px 4px;
  background: linear-gradient(90deg, transparent, var(--teal), var(--blue), var(--indigo), transparent);
}

/* ═══════════════════════════════════════════
   ZONE LOGO
═══════════════════════════════════════════ */
.logo-zone {
  display: flex; flex-direction: column; align-items: center;
  margin-bottom: 26px;
  animation: fadeUp .5s .1s both;
}
@keyframes fadeUp {
  from { opacity: 0; transform: translateY(14px); }
  to   { opacity: 1; transform: none; }
}

/* Triple anneau */
.rings { position: relative; width: 124px; height: 124px; margin-bottom: 20px; }

.ring {
  position: absolute; inset: 0;
  border-radius: 50%;
  border: 3px solid transparent;
}
.ring-1 {
  border-top-color: var(--teal);
  border-right-color: rgba(20,184,166,.18);
  animation: spin 1.6s linear infinite;
}
.ring-2 {
  inset: 10px;
  border-right-color: var(--blue);
  border-bottom-color: rgba(8,145,178,.18);
  animation: spin 2.2s linear infinite reverse;
}
.ring-3 {
  inset: 20px;
  border-bottom-color: var(--indigo);
  border-left-color: rgba(99,102,241,.18);
  animation: spin 2.9s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* Lueur */
.rings::after {
  content: '';
  position: absolute; inset: 28px; border-radius: 50%;
  background: radial-gradient(circle, rgba(20,184,166,.14), transparent 70%);
  animation: glow 2s ease-in-out infinite alternate;
}
@keyframes glow {
  from { opacity: .5; transform: scale(.9); }
  to   { opacity: 1;  transform: scale(1.1); }
}

.logo-inner {
  position: absolute; inset: 28px; border-radius: 50%; z-index: 1;
  background: var(--card);
  box-shadow: 0 0 0 1.5px rgba(20,184,166,.25), 0 6px 20px rgba(20,184,166,.18);
  display: flex; align-items: center; justify-content: center; overflow: hidden;
}
.logo-inner img   { width: 50px; height: 50px; object-fit: contain; }
.logo-fallback    { font-size: 1.6rem; color: var(--teal); display: none; }

.app-name {
  font-family: var(--ff-head); font-size: 1.7rem; font-weight: 800;
  color: var(--text); letter-spacing: -.04em; line-height: 1;
  animation: fadeUp .45s .2s both;
}
.app-sub {
  font-size: .72rem; color: var(--muted); margin: 5px 0 10px;
  letter-spacing: .07em; text-transform: uppercase;
  animation: fadeUp .45s .25s both;
}
.welcome {
  font-size: .92rem; font-weight: 600; color: var(--teal);
  margin-bottom: 9px; animation: fadeUp .45s .3s both;
}

/* Badge rôle */
.role-badge {
  display: inline-flex; align-items: center; gap: 5px;
  padding: 5px 14px; border-radius: 999px;
  font-size: .7rem; font-weight: 700; letter-spacing: .02em;
  animation: badgePop .55s .38s cubic-bezier(.34,1.56,.64,1) both;
}
@keyframes badgePop { from { opacity: 0; transform: scale(.6); } to { opacity: 1; transform: scale(1); } }
.badge-admin { background: #fff7ed; color: #c2410c; border: 1px solid #fed7aa; }
.badge-user  { background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; }
html.dark .badge-admin { background: rgba(194,65,12,.16); color: #fb923c; border-color: rgba(251,146,60,.3); }
html.dark .badge-user  { background: rgba(21,128,61,.16); color: #4ade80; border-color: rgba(74,222,128,.3); }

/* ═══════════════════════════════════════════
   STEPS
═══════════════════════════════════════════ */
.steps { display: flex; flex-direction: column; gap: 4px; margin: 22px 0 20px; animation: fadeUp .5s .4s both; }

.step {
  display: flex; align-items: center; gap: 11px;
  padding: 8px 12px; border-radius: 12px; border: 1px solid transparent;
  font-size: .79rem; color: var(--muted); transition: all .3s ease;
}
.step-ico {
  width: 28px; height: 28px; border-radius: 8px; flex-shrink: 0;
  background: var(--surface); border: 1px solid var(--border);
  display: flex; align-items: center; justify-content: center;
  font-size: .75rem; color: var(--muted); transition: all .3s ease;
}
.step-label { flex: 1; font-weight: 500; }
.step-dot   { width: 5px; height: 5px; border-radius: 50%; background: var(--border); transition: all .3s; flex-shrink: 0; }

.step.active {
  background: rgba(20,184,166,.07); border-color: rgba(20,184,166,.2);
  color: var(--teal-d);
}
.step.active .step-ico { background: rgba(20,184,166,.14); border-color: rgba(20,184,166,.3); color: var(--teal); }
.step.active .step-dot { background: var(--teal); animation: dotBlink .8s ease-in-out infinite alternate; }
@keyframes dotBlink { from { opacity: 1; transform: scale(1); } to { opacity: .4; transform: scale(.7); } }

.step.done { color: var(--green); }
.step.done .step-ico { background: #f0fdf4; border-color: #bbf7d0; color: var(--green); }
.step.done .step-dot { background: var(--green); animation: none; }

html.dark .step.done .step-ico { background: rgba(21,128,61,.2); border-color: rgba(74,222,128,.3); }
html.dark .step.active { background: rgba(20,184,166,.12); border-color: rgba(20,184,166,.25); }

/* ═══════════════════════════════════════════
   PROGRESSBAR
═══════════════════════════════════════════ */
.prog-section { animation: fadeUp .5s .45s both; }

.prog-header {
  display: flex; justify-content: space-between; align-items: center;
  margin-bottom: 8px;
}
.prog-lbl {
  display: flex; align-items: center; gap: 6px;
  font-size: .75rem; color: var(--muted);
}
.prog-pct {
  font-family: var(--ff-head); font-size: 1.05rem; font-weight: 700;
  color: var(--teal); min-width: 46px; text-align: right;
  font-variant-numeric: tabular-nums; transition: color .4s;
}

/* Piste */
.prog-track {
  position: relative; height: 10px;
  background: var(--surface); border: 1px solid var(--border);
  border-radius: 999px; overflow: visible;
}

/* Remplissage shimmer */
.prog-fill {
  position: absolute; top: -1px; left: -1px;
  height: calc(100% + 2px); width: 0%;
  border-radius: 999px;
  background: linear-gradient(90deg, #14b8a6 0%, #0891b2 35%, #6366f1 65%, #14b8a6 100%);
  background-size: 260% 100%;
  animation: shimmer 2.2s linear infinite;
  transition: width .5s cubic-bezier(.4,0,.2,1);
  box-shadow: 0 0 12px rgba(20,184,166,.4), 0 0 5px rgba(20,184,166,.6);
}
@keyframes shimmer { 0% { background-position: 260% center; } 100% { background-position: -260% center; } }

/* Curseur lumineux */
.prog-fill::after {
  content: '';
  position: absolute; right: -5px; top: 50%;
  transform: translateY(-50%);
  width: 14px; height: 14px; border-radius: 50%;
  background: #fff;
  box-shadow:
    0 0 0 3px rgba(20,184,166,.3),
    0 0 14px rgba(20,184,166,.8),
    0 0 28px rgba(20,184,166,.4);
  animation: cursorPulse 1.3s ease-in-out infinite;
}
@keyframes cursorPulse {
  0%,100% { transform: translateY(-50%) scale(1);   box-shadow: 0 0 0 3px rgba(20,184,166,.3), 0 0 14px rgba(20,184,166,.7); }
  50%     { transform: translateY(-50%) scale(1.25); box-shadow: 0 0 0 5px rgba(20,184,166,.15), 0 0 24px rgba(20,184,166,.9); }
}

/* Segments */
.prog-segs {
  display: flex; gap: 3px; margin-top: 7px;
  padding: 0 2px;
}
.seg {
  flex: 1; height: 4px; border-radius: 2px;
  background: var(--border); transition: background .3s, transform .25s;
}
.seg.active { background: var(--teal);   transform: scaleY(1.5); }
.seg.done   { background: var(--green);  transform: scaleY(1); }

/* Pied */
.prog-footer {
  display: flex; justify-content: space-between; align-items: center;
  margin-top: 9px; font-size: .7rem; color: var(--muted); opacity: .8;
}
.prog-sub { font-style: italic; }

/* ─ Bouton skip ─ */
.skip-btn {
  display: flex; align-items: center; justify-content: center; gap: 6px;
  width: 100%; margin-top: 18px; padding: 8px;
  background: none; border: none; border-radius: 10px;
  font-family: var(--ff-body); font-size: .72rem; color: var(--muted);
  cursor: pointer; transition: all .2s;
  animation: fadeUp .5s .5s both;
}
.skip-btn:hover { color: var(--teal); background: rgba(20,184,166,.07); }

/* ─ Responsive ─ */
@media (max-width: 480px) {
  .card { padding: 30px 22px 22px; border-radius: 24px; }
  .rings { width: 104px; height: 104px; }
  .ring-2 { inset: 8px; } .ring-3 { inset: 16px; }
  .logo-inner { inset: 24px; }
  .logo-inner img { width: 42px; height: 42px; }
  .app-name { font-size: 1.45rem; }
}
</style>
</head>
<body>

<!-- Particules -->
<div class="ptcls" id="ptcls"></div>

<!-- ═══ CARD ═══ -->
<div class="card">

  <!-- Logo + Identité -->
  <div class="logo-zone">
    <div class="rings">
      <div class="ring ring-1"></div>
      <div class="ring ring-2"></div>
      <div class="ring ring-3"></div>
      <div class="logo-inner">
        <img src="../images/logo_refrige.png" alt="Logo"
             onerror="this.style.display='none';document.querySelector('.logo-fallback').style.display='flex'">
        <div class="logo-fallback"><i class="fa-solid fa-fish"></i></div>
      </div>
    </div>
    <div class="app-name">FishStock Pro</div>
    <div class="app-sub">Système de Gestion des Stocks</div>
    <div class="welcome">Bienvenue, <?= $username ?> 👋</div>
    <?php if ($isAdmin): ?>
    <span class="role-badge badge-admin">
      <i class="fa-solid fa-shield-halved"></i> Administrateur
    </span>
    <?php else: ?>
    <span class="role-badge badge-user">
      <i class="fa-solid fa-user-check"></i> Utilisateur
    </span>
    <?php endif; ?>
  </div>

  <!-- Étapes -->
  <div class="steps" id="stepsList">
    <div class="step" id="s0">
      <div class="step-ico"><i class="fa-solid fa-database"></i></div>
      <span class="step-label">Connexion à la base de données</span>
      <div class="step-dot"></div>
    </div>
    <div class="step" id="s1">
      <div class="step-ico"><i class="fa-solid fa-cloud-arrow-down"></i></div>
      <span class="step-label">Chargement des données</span>
      <div class="step-dot"></div>
    </div>
    <div class="step" id="s2">
      <div class="step-ico"><i class="fa-solid fa-gauge-high"></i></div>
      <span class="step-label">Préparation du tableau de bord</span>
      <div class="step-dot"></div>
    </div>
    <div class="step" id="s3">
      <div class="step-ico"><i class="fa-solid fa-lock-open"></i></div>
      <span class="step-label">Vérification des permissions</span>
      <div class="step-dot"></div>
    </div>
    <div class="step" id="s4">
      <div class="step-ico"><i class="fa-solid fa-rocket"></i></div>
      <span class="step-label">Prêt !</span>
      <div class="step-dot"></div>
    </div>
  </div>

  <!-- Progressbar -->
  <div class="prog-section">
    <div class="prog-header">
      <div class="prog-lbl">
        <i class="fa-solid fa-circle-notch fa-spin" id="spinIco" style="color:var(--teal)"></i>
        <span id="progMsg">Initialisation…</span>
      </div>
      <span class="prog-pct" id="progPct">0%</span>
    </div>

    <div class="prog-track">
      <div class="prog-fill" id="progBar"></div>
    </div>

    <div class="prog-segs">
      <div class="seg" id="sg0"></div>
      <div class="seg" id="sg1"></div>
      <div class="seg" id="sg2"></div>
      <div class="seg" id="sg3"></div>
      <div class="seg" id="sg4"></div>
    </div>

    <div class="prog-footer">
      <span><i class="fa-regular fa-clock" style="margin-right:4px"></i><span id="elapsed">0.0s</span></span>
      <span class="prog-sub" id="subMsg">Veuillez patienter…</span>
    </div>
  </div>

  <button class="skip-btn" onclick="redirect()">
    <i class="fa-solid fa-forward-fast"></i> Accéder directement
  </button>

</div>

<script>
/* Thème */
if ((localStorage.getItem('theme') || 'light') === 'dark')
  document.documentElement.classList.add('dark');

/* Particules */
(function() {
  const c = document.getElementById('ptcls');
  const pal = ['#14b8a6','#0d9488','#0891b2','#6366f1','#22c55e','#a78bfa','#34d399','#38bdf8'];
  for (let i = 0; i < 22; i++) {
    const el = document.createElement('div'); el.className = 'p';
    const sz = Math.random() * 20 + 7;
    el.style.cssText = `width:${sz}px;height:${sz}px;left:${Math.random()*100}%;`
      + `background:${pal[i % pal.length]};`
      + `animation-duration:${(Math.random()*10+7).toFixed(1)}s;`
      + `animation-delay:${(Math.random()*6).toFixed(1)}s`;
    c.appendChild(el);
  }
})();

/* Config */
const STEPS = [
  { idx:0, pct:20,  msg:'Connexion à la base de données…',   sub:'PDO MySQL connecté' },
  { idx:1, pct:43,  msg:'Chargement des données…',            sub:'Récupération des stocks' },
  { idx:2, pct:67,  msg:'Préparation du tableau de bord…',    sub:'Graphiques initialisés' },
  { idx:3, pct:87,  msg:'Vérification des permissions…',      sub:'Rôle : <?= $roleLabel ?>' },
  { idx:4, pct:100, msg:'Prêt !',                              sub:'Redirection en cours…' },
];
const T = [300, 680, 1080, 1430, 1780];

const bar    = document.getElementById('progBar');
const pctEl  = document.getElementById('progPct');
const msgEl  = document.getElementById('progMsg');
const subEl  = document.getElementById('subMsg');
const spinEl = document.getElementById('spinIco');

/* Chrono */
const t0 = Date.now();
const chrono = setInterval(() => {
  document.getElementById('elapsed').textContent = ((Date.now()-t0)/1000).toFixed(1)+'s';
}, 100);

/* Animation */
let prev = -1;
function goStep(i) {
  const s = STEPS[i];
  bar.style.width = s.pct + '%';
  pctEl.textContent = s.pct + '%';
  msgEl.textContent = s.msg;
  subEl.textContent = s.sub;

  // Segments
  for (let k = 0; k <= 4; k++) {
    const sg = document.getElementById('sg'+k);
    sg.className = 'seg' + (k < i ? ' done' : k === i ? ' active' : '');
  }

  // Step précédent → done
  if (prev >= 0) {
    const p = document.getElementById('s'+prev);
    p.classList.replace('active','done');
    p.querySelector('.step-ico').innerHTML = '<i class="fa-solid fa-check"></i>';
  }
  // Step courant → active
  document.getElementById('s'+s.idx).classList.add('active');
  prev = s.idx;

  // Fin
  if (s.pct >= 100) {
    pctEl.style.color = 'var(--green)';
    spinEl.className  = 'fa-solid fa-check';
    spinEl.style.color = 'var(--green)';
    setTimeout(redirect, 380);
  }
}

STEPS.forEach((s,i) => setTimeout(() => goStep(i), T[i]));

function redirect() {
  clearInterval(chrono);
  window.location.href = '../pages_principale/index.php';
}
</script>
</body>
</html>
