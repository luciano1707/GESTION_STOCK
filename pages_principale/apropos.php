<?php
// pages_principale/apropos.php
require_once '../include/auth_check.php';
$pageTitle = 'À propos';
include_once '../include/page_start.php';
?>

<style>
/* ── Styles spécifiques À propos ── */
.about-wrap { max-width: 820px; display: flex; flex-direction: column; gap: 20px; }

/* ─ Hero card ─ */
.hero-card {
  background: linear-gradient(135deg, #0d9488 0%, #0891b2 60%, #6366f1 100%);
  border-radius: 20px;
  padding: 36px 32px;
  color: white;
  position: relative;
  overflow: hidden;
}
.hero-card::before {
  content: '';
  position: absolute; inset: 0;
  background:
    radial-gradient(ellipse 60% 60% at 90% 10%, rgba(255,255,255,.12), transparent),
    radial-gradient(ellipse 40% 40% at 10% 90%, rgba(255,255,255,.06), transparent);
}
.hero-card::after {
  content: '\f578'; /* fa-fish */
  font-family: 'Font Awesome 6 Free'; font-weight: 900;
  position: absolute; right: 32px; top: 50%; transform: translateY(-50%);
  font-size: 7rem; opacity: .08; pointer-events: none;
}
.hero-inner { position: relative; z-index: 1; display: flex; align-items: center; gap: 24px; flex-wrap: wrap; }
.hero-logo-wrap {
  width: 84px; height: 84px;
  background: rgba(255,255,255,.18);
  border: 2px solid rgba(255,255,255,.35);
  border-radius: 20px;
  backdrop-filter: blur(8px);
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0;
}
.hero-logo-wrap img  { width: 60px; height: 60px; object-fit: contain; }
.hero-logo-wrap .ico { font-size: 2.2rem; color: rgba(255,255,255,.9); display: none; }
.hero-text h1 { font-size: 1.8rem; font-weight: 800; letter-spacing: -.03em; line-height: 1.1; margin-bottom: 4px; }
.hero-text .tagline { font-size: .88rem; opacity: .85; margin-bottom: 10px; }
.hero-badges { display: flex; flex-wrap: wrap; gap: 8px; }
.hero-badge {
  display: inline-flex; align-items: center; gap: 5px;
  padding: 4px 11px; border-radius: 999px;
  background: rgba(255,255,255,.16); border: 1px solid rgba(255,255,255,.28);
  font-size: .72rem; font-weight: 600; backdrop-filter: blur(4px);
}
.hero-version {
  margin-left: auto; align-self: flex-start;
  background: rgba(255,255,255,.15); border: 1px solid rgba(255,255,255,.3);
  border-radius: 10px; padding: 8px 14px; text-align: center; flex-shrink: 0;
}
.hero-version .ver-num { font-size: 1.3rem; font-weight: 800; line-height: 1; }
.hero-version .ver-lbl { font-size: .68rem; opacity: .8; margin-top: 2px; }

/* ─ Grid 2 colonnes ─ */
.two-col { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
@media (max-width: 620px) { .two-col { grid-template-columns: 1fr; } }

/* ─ Section card générique ─ */
.sec-card { background: var(--card-bg); border: 1px solid var(--card-border); border-radius: 16px; padding: 22px 24px; }
.sec-head {
  display: flex; align-items: center; gap: 10px;
  margin-bottom: 18px; padding-bottom: 12px;
  border-bottom: 1px solid var(--card-border);
}
.sec-head-ico {
  width: 36px; height: 36px; border-radius: 10px;
  display: flex; align-items: center; justify-content: center;
  font-size: .9rem; flex-shrink: 0;
}
.sec-head-ico.teal   { background: #f0fdfa; color: #0d9488; }
.sec-head-ico.purple { background: #f5f3ff; color: #7c3aed; }
.sec-head-ico.blue   { background: #eff6ff; color: #2563eb; }
.sec-head-ico.amber  { background: #fffbeb; color: #d97706; }
html[data-theme="dark"] .sec-head-ico.teal   { background: rgba(20,184,166,.15); color: #2dd4bf; }
html[data-theme="dark"] .sec-head-ico.purple { background: rgba(124,58,237,.15); color: #a78bfa; }
html[data-theme="dark"] .sec-head-ico.blue   { background: rgba(37,99,235,.15);  color: #60a5fa; }
html[data-theme="dark"] .sec-head-ico.amber  { background: rgba(217,119,6,.15);  color: #fbbf24; }
.sec-head-title { font-size: 1rem; font-weight: 700; color: var(--text-primary); }
.sec-head-sub   { font-size: .75rem; color: var(--text-muted); margin-top: 1px; }

/* ─ Feature grid ─ */
.feat-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
.feat-item {
  display: flex; align-items: flex-start; gap: 9px;
  padding: 10px 12px; border-radius: 10px;
  background: var(--body-bg); border: 1px solid var(--card-border);
  transition: border-color .2s, transform .2s;
}
.feat-item:hover { border-color: var(--primary); transform: translateY(-1px); }
.feat-item .fi-ico {
  width: 26px; height: 26px; border-radius: 7px;
  background: var(--primary-light); color: var(--primary);
  display: flex; align-items: center; justify-content: center;
  font-size: .72rem; flex-shrink: 0; margin-top: 1px;
}
.feat-item .fi-text strong { display: block; font-size: .78rem; font-weight: 600; color: var(--text-primary); }
.feat-item .fi-text span   { font-size: .7rem; color: var(--text-muted); }

/* ─ Stack technos ─ */
.tech-group { margin-bottom: 14px; }
.tech-group:last-child { margin-bottom: 0; }
.tech-group-label { font-size: .68rem; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; color: var(--text-muted); margin-bottom: 8px; }
.tech-pills { display: flex; flex-wrap: wrap; gap: 6px; }
.tech-pill {
  display: inline-flex; align-items: center; gap: 5px;
  padding: 4px 10px; border-radius: 999px; font-size: .72rem; font-weight: 600;
  border: 1px solid transparent;
}
.tp-backend  { background: #fef2f2; color: #dc2626; border-color: #fecaca; }
.tp-frontend { background: #eff6ff; color: #2563eb; border-color: #bfdbfe; }
.tp-lib      { background: #fdf4ff; color: #9333ea; border-color: #e9d5ff; }
.tp-db       { background: #f0fdf4; color: #16a34a; border-color: #bbf7d0; }
html[data-theme="dark"] .tp-backend  { background: rgba(220,38,38,.12);  color: #f87171; border-color: rgba(220,38,38,.3); }
html[data-theme="dark"] .tp-frontend { background: rgba(37,99,235,.12);  color: #93c5fd; border-color: rgba(37,99,235,.3); }
html[data-theme="dark"] .tp-lib      { background: rgba(147,51,234,.12); color: #c4b5fd; border-color: rgba(147,51,234,.3); }
html[data-theme="dark"] .tp-db       { background: rgba(22,163,74,.12);  color: #86efac; border-color: rgba(22,163,74,.3); }

/* ─ Timeline projet ─ */
.timeline { display: flex; flex-direction: column; gap: 0; }
.tl-item {
  display: flex; gap: 14px; padding-bottom: 16px;
  position: relative;
}
.tl-item:last-child { padding-bottom: 0; }
.tl-left { display: flex; flex-direction: column; align-items: center; flex-shrink: 0; }
.tl-dot {
  width: 28px; height: 28px; border-radius: 50%;
  border: 2px solid var(--primary); background: var(--primary-light);
  color: var(--primary); font-size: .7rem;
  display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.tl-line {
  width: 2px; flex: 1; margin-top: 4px;
  background: linear-gradient(to bottom, var(--primary), transparent);
  min-height: 20px;
}
.tl-item:last-child .tl-line { display: none; }
.tl-content { padding-top: 3px; }
.tl-title { font-size: .82rem; font-weight: 700; color: var(--text-primary); }
.tl-desc  { font-size: .75rem; color: var(--text-muted); margin-top: 2px; line-height: 1.5; }

/* ─ Contact cards ─ */
.contact-grid { display: flex; flex-direction: column; gap: 8px; }
.contact-item {
  display: flex; align-items: center; gap: 14px;
  padding: 12px 14px; border-radius: 12px;
  background: var(--body-bg); border: 1px solid var(--card-border);
  text-decoration: none; transition: all .2s;
}
.contact-item:hover { border-color: var(--primary); transform: translateX(4px); }
.ci-ico {
  width: 40px; height: 40px; border-radius: 11px;
  display: flex; align-items: center; justify-content: center;
  color: white; font-size: .95rem; flex-shrink: 0;
}
.ci-ico.teal   { background: linear-gradient(135deg, #14b8a6, #0891b2); }
.ci-ico.red    { background: linear-gradient(135deg, #ef4444, #dc2626); }
.ci-ico.green  { background: linear-gradient(135deg, #22c55e, #16a34a); }
.ci-ico.blue   { background: linear-gradient(135deg, #3b82f6, #1d4ed8); }
.ci-ico.fb     { background: linear-gradient(135deg, #1877f2, #166fe5); }
.ci-label { font-size: .68rem; color: var(--text-muted); margin-bottom: 2px; }
.ci-value { font-size: .84rem; font-weight: 600; color: var(--text-primary); }

/* ─ Stat rapides ─ */
.quick-stats { display: grid; grid-template-columns: repeat(3,1fr); gap: 10px; }
.qs-item { text-align: center; padding: 12px 8px; background: var(--body-bg); border-radius: 12px; border: 1px solid var(--card-border); }
.qs-num  { font-size: 1.4rem; font-weight: 800; color: var(--primary); line-height: 1; }
.qs-lbl  { font-size: .67rem; color: var(--text-muted); margin-top: 3px; }
</style>

<div class="about-wrap">

  <!-- ═══ HERO ═══ -->
  <div class="hero-card">
    <div class="hero-inner">
      <div class="hero-logo-wrap">
        <img src="../images/logo_refrige.png" alt="Logo"
             onerror="this.style.display='none';this.nextElementSibling.style.display='block'">
        <div class="ico"><i class="fa-solid fa-fish"></i></div>
      </div>
      <div class="hero-text">
        <h1>FishStock Pro</h1>
        <div class="tagline">Système de Gestion des Stocks – Réfrigêpeche</div>
        <div class="hero-badges">
          <span class="hero-badge"><i class="fa-solid fa-fish"></i> Gestion de la pêche</span>
          <span class="hero-badge"><i class="fa-solid fa-warehouse"></i> Multi-dépôts</span>
          <span class="hero-badge"><i class="fa-solid fa-users-gear"></i> Multi-rôles</span>
        </div>
      </div>
      <div class="hero-version">
        <div class="ver-num">v1.0</div>
        <div class="ver-lbl">Version</div>
        <div style="margin-top:6px;font-size:.65rem;opacity:.7"><?= date('Y') ?></div>
      </div>
    </div>
  </div>

  <!-- ═══ STATS RAPIDES ═══ -->
  <div class="sec-card" style="padding: 18px 24px;">
    <div class="quick-stats">
      <div class="qs-item">
        <div class="qs-num">8</div>
        <div class="qs-lbl">Tables de données</div>
      </div>
      <div class="qs-item">
        <div class="qs-num">10</div>
        <div class="qs-lbl">Modules CRUD</div>
      </div>
      <div class="qs-item">
        <div class="qs-num">2</div>
        <div class="qs-lbl">Rôles utilisateurs</div>
      </div>
    </div>
  </div>

  <!-- ═══ GRILLE 2 COL : Fonctionnalités + Technologies ═══ -->
  <div class="two-col">

    <!-- Fonctionnalités -->
    <div class="sec-card">
      <div class="sec-head">
        <div class="sec-head-ico teal"><i class="fa-solid fa-list-check"></i></div>
        <div>
          <div class="sec-head-title">Fonctionnalités</div>
          <div class="sec-head-sub">Ce que l'application permet</div>
        </div>
      </div>
      <div class="feat-grid">
        <?php
        $features = [
          ['fa-warehouse',        'Stocks',         'Alertes péremption'],
          ['fa-fish',             'Captures',       'Suivi par voyage'],
          ['fa-ship',             'Voyages',        'Navigation & durée'],
          ['fa-flag',             'Campagnes',      'Planification'],
          ['fa-box',              'Produits',       'Catalogue par famille'],
          ['fa-building',         'Dépôts',         'Multi-sites'],
          ['fa-chart-pie',        'Dashboard',      'Graphiques Chart.js'],
          ['fa-file-pdf',         'Export PDF',     'Rapports imprimables'],
          ['fa-magnifying-glass', 'Recherche',      'AJAX temps réel'],
          ['fa-filter',           'Filtres date',   'Par période'],
        ];
        foreach ($features as $f): ?>
        <div class="feat-item">
          <div class="fi-ico"><i class="fa-solid <?= $f[0] ?>"></i></div>
          <div class="fi-text">
            <strong><?= $f[1] ?></strong>
            <span><?= $f[2] ?></span>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Technologies -->
    <div class="sec-card">
      <div class="sec-head">
        <div class="sec-head-ico purple"><i class="fa-solid fa-code"></i></div>
        <div>
          <div class="sec-head-title">Technologies</div>
          <div class="sec-head-sub">Stack technique utilisé</div>
        </div>
      </div>

      <div class="tech-group">
        <div class="tech-group-label"><i class="fa-solid fa-server" style="margin-right:4px"></i>Back-end</div>
        <div class="tech-pills">
          <span class="tech-pill tp-backend"><i class="fa-brands fa-php"></i> PHP 8</span>
          <span class="tech-pill tp-backend"><i class="fa-solid fa-shield"></i> PDO</span>
          <span class="tech-pill tp-backend"><i class="fa-solid fa-key"></i> Sessions</span>
        </div>
      </div>

      <div class="tech-group">
        <div class="tech-group-label"><i class="fa-solid fa-database" style="margin-right:4px"></i>Base de données</div>
        <div class="tech-pills">
          <span class="tech-pill tp-db"><i class="fa-solid fa-database"></i> MySQL</span>
          <span class="tech-pill tp-db"><i class="fa-solid fa-table"></i> 8 tables</span>
        </div>
      </div>

      <div class="tech-group">
        <div class="tech-group-label"><i class="fa-solid fa-palette" style="margin-right:4px"></i>Front-end</div>
        <div class="tech-pills">
          <span class="tech-pill tp-frontend"><i class="fa-brands fa-html5"></i> HTML5</span>
          <span class="tech-pill tp-frontend"><i class="fa-brands fa-css3-alt"></i> CSS3</span>
          <span class="tech-pill tp-frontend"><i class="fa-brands fa-js"></i> JavaScript</span>
          <span class="tech-pill tp-frontend"><i class="fa-solid fa-bolt"></i> AJAX</span>
        </div>
      </div>

      <div class="tech-group">
        <div class="tech-group-label"><i class="fa-solid fa-puzzle-piece" style="margin-right:4px"></i>Librairies</div>
        <div class="tech-pills">
          <span class="tech-pill tp-lib"><i class="fa-solid fa-chart-pie"></i> Chart.js</span>
          <span class="tech-pill tp-lib"><i class="fa-brands fa-font-awesome"></i> Font Awesome</span>
          <span class="tech-pill tp-lib"><i class="fa-solid fa-file-pdf"></i> Print PDF</span>
        </div>
      </div>
    </div>

  </div>

  <!-- ═══ GRILLE 2 COL : Timeline + Contact ═══ -->
  <div class="two-col">

    <!-- Étapes du projet -->
    <div class="sec-card">
      <div class="sec-head">
        <div class="sec-head-ico amber"><i class="fa-solid fa-timeline"></i></div>
        <div>
          <div class="sec-head-title">Conception du projet</div>
          <div class="sec-head-sub">Étapes de développement</div>
        </div>
      </div>
      <div class="timeline">
        <?php
        $tl = [
          ['fa-lightbulb',    'Analyse des besoins',      'Étude du domaine de la pêche et des flux de gestion de stocks'],
          ['fa-database',     'Modélisation BDD',          '8 tables relationnelles : famille, produit, navire, voyage…'],
          ['fa-code',         'Développement PHP',         'CRUD complet avec PDO, sessions, contrôle des rôles'],
          ['fa-paintbrush',   'Design UI',                 'Sidebar, thème clair/sombre, dashboard avec graphiques'],
          ['fa-file-pdf',     'Rapports & Exports',        'Export PDF avec logo, filtres par date, impression graphiques'],
          ['fa-rocket',       'Mise en production',        'Tests, corrections et livraison finale'],
        ];
        foreach ($tl as $item): ?>
        <div class="tl-item">
          <div class="tl-left">
            <div class="tl-dot"><i class="fa-solid <?= $item[0] ?>"></i></div>
            <div class="tl-line"></div>
          </div>
          <div class="tl-content">
            <div class="tl-title"><?= $item[1] ?></div>
            <div class="tl-desc"><?= $item[2] ?></div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Contact développeur -->
    <div class="sec-card">
      <div class="sec-head">
        <div class="sec-head-ico blue"><i class="fa-solid fa-address-card"></i></div>
        <div>
          <div class="sec-head-title">Développeur</div>
          <div class="sec-head-sub">Contact & informations</div>
        </div>
      </div>

      <!-- Profil -->
      <div style="display:flex;align-items:center;gap:14px;padding:14px;background:var(--body-bg);border-radius:12px;border:1px solid var(--card-border);margin-bottom:14px;">
        <div style="width:52px;height:52px;border-radius:14px;background:linear-gradient(135deg,#14b8a6,#0891b2);display:flex;align-items:center;justify-content:center;color:white;font-size:1.2rem;flex-shrink:0;">
          <i class="fa-solid fa-user-tie"></i>
        </div>
        <div>
          <div style="font-size:.68rem;color:var(--text-muted);margin-bottom:2px;">Projet académique</div>
          <div style="font-size:1rem;font-weight:800;color:var(--text-primary);line-height:1.2">RAKOTOMAMONJY<br>Jean Luciano</div>
          <div style="margin-top:5px;">
            <span style="display:inline-flex;align-items:center;gap:4px;font-size:.68rem;background:var(--primary-light);color:var(--primary);border-radius:999px;padding:2px 9px;font-weight:600;">
              <i class="fa-solid fa-graduation-cap"></i> Étudiant développeur
            </span>
          </div>
        </div>
      </div>

      <!-- Contacts -->
      <div class="contact-grid">
        <a href="mailto:rakotomamonjyjeanluciano@gmail.com" class="contact-item">
          <div class="ci-ico red"><i class="fa-solid fa-envelope"></i></div>
          <div>
            <div class="ci-label">Adresse e-mail</div>
            <div class="ci-value" style="font-size:.77rem;">rakotomamonjyjeanluciano@gmail.com</div>
          </div>
        </a>
        <a href="tel:0377586014" class="contact-item">
          <div class="ci-ico green"><i class="fa-solid fa-phone"></i></div>
          <div>
            <div class="ci-label">Téléphone</div>
            <div class="ci-value">037 75 860 14</div>
          </div>
        </a>
        <div class="contact-item" style="cursor:default;">
          <div class="ci-ico fb"><i class="fa-brands fa-facebook-f"></i></div>
          <div>
            <div class="ci-label">Facebook</div>
            <div class="ci-value">Fenosoa Luciano</div>
          </div>
        </div>
      </div>

      <!-- Note footer -->
      <div style="margin-top:14px;padding:10px 12px;background:rgba(20,184,166,.06);border:1px solid rgba(20,184,166,.2);border-radius:10px;font-size:.72rem;color:var(--text-muted);line-height:1.5;">
        <i class="fa-solid fa-circle-info" style="color:var(--primary);margin-right:5px;"></i>
        Ce projet a été réalisé dans le cadre d'un travail académique de fin d'études. Tous droits réservés © <?= date('Y') ?>.
      </div>
    </div>

  </div>

</div><!-- /about-wrap -->

<?php include '../include/page_end.php'; ?>
</body>
</html>
