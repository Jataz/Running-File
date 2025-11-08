<?php /** @var string $pageTitle */ /** @var string $pageContent */ ?>
<?php require_once __DIR__ . '/../../middleware.php'; $u = current_user(); ?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title><?= htmlspecialchars($pageTitle) ?> — Running File System</title>
  <style>
  :root{ --bg:#eaf4ff; --panel:#ffffff; --muted:#3b5b85; --text:#0b1b2b; --border:#c6dbff; --accent:#3b82f6; --accent2:#60a5fa; --shadow:0 8px 24px rgba(40,90,160,.18); }
  *{box-sizing:border-box} html,body{height:100%} body{margin:0;background:linear-gradient(180deg,#eaf4ff,#dbeafe 60%,#eaf4ff);color:var(--text);font-family:"Trebuchet MS", Tahoma, Arial, sans-serif;font-size:14px}
  .app{display:grid;grid-template-columns:260px 1fr;grid-template-rows:60px 1fr;grid-template-areas:"top top" "side main";height:100%}
  .app.app-login{grid-template-columns:1fr;grid-template-areas:"top" "main"}
  header{grid-area:top;display:flex;align-items:center;gap:12px;padding:10px;border-bottom:1px solid var(--border);background:#f0f7ff;position:sticky;top:0;z-index:5}
  .badge{padding:2px 6px;border-radius:8px;font-size:12px;background:#e6f0ff;border:1px solid var(--border);color:#134e75}
  .pill{background:#ffffff;border:1px solid var(--border);border-radius:12px;height:38px;padding:0 10px;display:flex;align-items:center;gap:8px;color:var(--muted);box-shadow:var(--shadow)}
  .pill input{all:unset;color:var(--text);width:260px}
  .btn{height:36px;border-radius:10px;border:1px solid var(--border);background:#f8fbff;color:#0b1b2b;padding:0 12px;display:inline-flex;align-items:center;gap:8px;cursor:pointer;box-shadow:var(--shadow);text-decoration:none}
  .btn.primary{background:linear-gradient(135deg,var(--accent),var(--accent2));border:0;color:#0b1b2b;font-weight:800}
  .side{grid-area:side;border-right:1px solid var(--border);background:linear-gradient(180deg,#f6faff,#eaf2ff);padding:12px;overflow:auto}
  .nav{display:flex;flex-direction:column;gap:6px}
  .nav a{all:unset;display:flex;align-items:center;gap:10px;padding:10px;border-radius:10px;cursor:pointer;color:#0b1b2b}
  .nav a:hover{background:rgba(59,130,246,.12);outline:1px solid rgba(59,130,246,.18)}
  .main{grid-area:main;display:grid;grid-template-rows:auto 1fr;gap:10px;padding:10px;overflow:auto}
  .panel{background:#ffffff;border:1px solid var(--border);border-radius:14px;padding:10px;min-height:0;box-shadow:var(--shadow)}
  table{width:100%;border-collapse:collapse}
  th,td{padding:10px;border-bottom:1px solid var(--border);font-size:14px}
  thead th{position:sticky;top:0;background:#f0f7ff;z-index:1}
  </style>
</head>
<body>
  <div class="app<?= $u ? '' : ' app-login' ?>">
    <header>
      <div style="font-weight:800">📁 Running File System — Ministry DMS <span class="badge">Official</span></div>
      <?php if ($u): ?>
        <div class="pill" style="margin-left:12px;flex:1"><input placeholder="Search"/></div>
        <a class="btn" href="/upload.php">New File</a>
        <a class="btn" href="/admin/users">Manage Users</a>
        <a class="btn" href="/logout.php?go=login">Logout</a>
      <?php else: ?>
        <div style="margin-left:auto"><a class="btn primary" href="/login">Login</a></div>
      <?php endif; ?>
    </header>
    <?php if ($u): ?>
    <aside class="side">
      <div class="nav">
        <a href="/">🏠 Dashboard</a>
        <a href="/inbox">📥 Inbox</a>
        <a href="/outbox">📤 Outgoing</a>
        <a href="/files">🗂️ All Files</a>
        <a href="/board">🧭 Workflow Board</a>
        <a href="/reports">📈 Reports</a>
        <a href="/audit">🧾 Audit Log</a>
        <a href="/settings">⚙️ Settings</a>
      </div>
    </aside>
    <?php endif; ?>
    <main class="main">
      <div class="panel">
        <h2 style="margin:0;font-weight:800;"><?= htmlspecialchars($pageTitle) ?></h2>
      </div>
      <section class="panel">
        <?= $pageContent ?>
      </section>
    </main>
  </div>
</body>
</html>