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
        <a class="btn" id="btn-new-file" href="/files/new">New File</a>
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
  
  <!-- Global Modal Styles and New File Modal -->
  <style>
    dialog::backdrop{ background:rgba(12,18,30,.35); backdrop-filter: blur(2px); }
    dialog{ border:1px solid var(--border); border-radius:14px; box-shadow:var(--shadow); padding:0; }
    .modal{ display:grid; grid-template-rows:auto 1fr auto; min-width:640px; }
    .modal-head{ padding:12px; border-bottom:1px solid var(--border); font-weight:800; color:var(--text); }
    .modal-body{ padding:12px; }
    .modal-foot{ padding:12px; border-top:1px solid var(--border); display:flex; gap:8px; justify-content:flex-end; }
    .input{ display:grid; gap:6px; }
    .input label{ color:#134e75; font-weight:700; }
    .input input, .input select, .input textarea{ border:1px solid var(--border); border-radius:10px; padding:10px; background:#fff; color:#0b1b2b; }
  </style>

  <?php if ($u): ?>
  <dialog id="dlg-new-file">
    <div class="modal">
      <div class="modal-head">Create New File</div>
      <div class="modal-body" style="display:grid;grid-template-columns:repeat(2,1fr);gap:10px">
        <div class="input"><label>Subject</label><input id="nf-subject" placeholder="e.g. Procurement of Supplies" required /></div>
        <div class="input"><label>Owner</label><input id="nf-owner" value="<?= htmlspecialchars($u['username'] ?? '') ?>" /></div>
        <div class="input"><label>Due Date</label><input id="nf-due" type="date" /></div>
        <div class="input"><label>Tags</label><input id="nf-tags" placeholder="comma,separated,tags" /></div>
        <div class="input" style="grid-column:1/-1"><label>Description</label><textarea id="nf-desc" rows="3" placeholder="Short description"></textarea></div>
        <div class="input" style="grid-column:1/-1"><label>Attach Document (optional)</label><input id="nf-file" type="file" /></div>
        <div class="input" style="grid-column:1/-1"><label>Document Description</label><input id="nf-docdesc" placeholder="Describe the attachment (optional)" /></div>
        <div class="input" style="grid-column:1/-1">
          <label>Departments</label>
          <select id="nf-depts" multiple size="6" style="height:auto"></select>
          <div class="badge" id="nf-depts-msg" style="margin-top:6px">Select departments to route this file. If none selected, your department is used.</div>
        </div>
      </div>
      <div class="modal-foot">
        <button class="btn" data-close>Cancel</button>
        <button class="btn primary" id="nf-create">Create</button>
      </div>
    </div>
  </dialog>
  <script>
    (function(){
      function api(url, opts={}){ return fetch(url, Object.assign({ credentials:'include' }, opts)).then(async r=>{ if(!r.ok) throw new Error(await r.text()); const ct=r.headers.get('content-type')||''; return ct.includes('application/json')? r.json(): r.text(); }); }
      const btn = document.getElementById('btn-new-file');
      const dlg = document.getElementById('dlg-new-file');
      const sel = document.getElementById('nf-depts');
      const closeBtns = dlg.querySelectorAll('[data-close]');
      closeBtns.forEach(b=> b.addEventListener('click', ()=>{ try{ dlg.close(); }catch(e){} }));
      async function loadDepartments(){
        if (!sel) return;
        sel.innerHTML = '';
        try {
          const data = await api('/api/departments.php');
          const items = (data && data.items) ? data.items : [];
          for (const d of items){
            const opt = document.createElement('option');
            opt.value = d.id; opt.textContent = d.name + (d.code? ' ('+d.code+')':'' );
            sel.appendChild(opt);
          }
        } catch(e) {
          const opt = document.createElement('option'); opt.textContent = 'Failed to load departments'; opt.disabled = true; sel.appendChild(opt);
        }
      }
      if (btn) {
        btn.addEventListener('click', async (ev)=>{
          // Prefer modal; keep href working as full-page fallback.
          ev.preventDefault();
          await loadDepartments();
          try{ dlg.showModal(); }catch(e){}
        });
      }
      const createBtn = document.getElementById('nf-create');
      if (createBtn) createBtn.addEventListener('click', async ()=>{
        const fd = new FormData();
        fd.append('subject', document.getElementById('nf-subject').value||'');
        fd.append('owner', document.getElementById('nf-owner').value||'');
        fd.append('due', document.getElementById('nf-due').value||'');
        fd.append('tags', document.getElementById('nf-tags').value||'');
        fd.append('description', document.getElementById('nf-desc').value||'');
        // departments
        const selected = Array.from(sel.selectedOptions).map(o=>o.value);
        for (const id of selected) fd.append('dept_ids[]', id);
        createBtn.disabled = true; createBtn.textContent = 'Creating…';
        try {
          const res = await api('/api/files_create.php', { method:'POST', body: fd });
          const fileId = (res && res.id) ? res.id : null;
          const input = document.getElementById('nf-file');
          const hasFile = input && input.files && input.files.length > 0;
          if (hasFile && fileId) {
            const fd2 = new FormData();
            fd2.append('file_id', fileId);
            fd2.append('respond', 'json');
            fd2.append('description', document.getElementById('nf-docdesc').value||'');
            fd2.append('file', input.files[0]);
            try { await api('/upload.php', { method:'POST', body: fd2 }); } catch(e) { alert('Upload failed'); }
          }
          try{ dlg.close(); }catch(e){}
          window.location.href = '/outbox';
        } catch(e) {
          alert('Create failed');
        } finally {
          createBtn.disabled = false; createBtn.textContent = 'Create';
        }
      });
    })();
  </script>
  <?php endif; ?>

  <!-- Lightweight Table Search & Pagination -->
  <style>
    .table-tools{ display:flex; gap:8px; align-items:center; margin:8px 0; }
    .table-tools .pill{ height:32px; box-shadow:none; }
    .pager{ display:flex; gap:6px; align-items:center; }
    .pager button{ height:28px; padding:0 8px; }
    .pager .badge{ background:#eef6ff; }
  </style>
  <script>
    (function(){
      function enhanceTable(tbl){
        const wrapper = document.createElement('div'); wrapper.className='table-tools';
        const searchWrap = document.createElement('div'); searchWrap.className='pill'; const searchInput=document.createElement('input'); searchInput.placeholder='Search table…'; searchWrap.appendChild(searchInput);
        const sizeSel=document.createElement('select'); [10,25,50].forEach(n=>{ const o=document.createElement('option'); o.value=String(n); o.textContent=n+' / page'; sizeSel.appendChild(o); });
        const pager=document.createElement('div'); pager.className='pager'; const prev=document.createElement('button'); prev.className='btn'; prev.textContent='Prev'; const next=document.createElement('button'); next.className='btn'; next.textContent='Next'; const info=document.createElement('span'); info.className='badge'; info.textContent='Page 1'; pager.append(prev, next, info);
        wrapper.append(searchWrap, sizeSel, pager);
        tbl.parentNode.insertBefore(wrapper, tbl);

        const tbody = tbl.querySelector('tbody'); if(!tbody) return;
        const rows = Array.from(tbody.querySelectorAll('tr'));
        let filtered = rows.map((_,i)=>i);
        let pageSize = parseInt(tbl.dataset.pageSize||sizeSel.value||'10',10) || 10;
        sizeSel.value = String(pageSize);
        let page = 1;

        function apply(){
          const total = filtered.length; const pages = Math.max(1, Math.ceil(total / pageSize));
          if(page>pages) page=pages; if(page<1) page=1;
          const start = (page-1)*pageSize; const end = start + pageSize;
          rows.forEach((r,i)=>{ const show = filtered.indexOf(i)>=0 && filtered.indexOf(i)>=start && filtered.indexOf(i)<end; r.style.display = show? '' : 'none'; });
          info.textContent = `Page ${page} / ${pages} — ${total} rows`;
          prev.disabled = page<=1; next.disabled = page>=pages;
        }
        function runSearch(){
          const q = (searchInput.value||'').toLowerCase();
          if(!q){ filtered = rows.map((_,i)=>i); apply(); return; }
          filtered = rows.map((r,i)=>({ r,i })).filter(obj=>{
            return Array.from(obj.r.cells).some(td=> (td.textContent||'').toLowerCase().includes(q));
          }).map(obj=>obj.i);
          page = 1; apply();
        }

        searchInput.addEventListener('input', runSearch);
        sizeSel.addEventListener('change', ()=>{ pageSize = parseInt(sizeSel.value,10)||10; page=1; apply(); });
        prev.addEventListener('click', ()=>{ page--; apply(); });
        next.addEventListener('click', ()=>{ page++; apply(); });
        apply();
      }
      document.addEventListener('DOMContentLoaded', ()=>{
        document.querySelectorAll('table[data-enhance="true"]').forEach(enhanceTable);
      });
    })();
  </script>
</body>
</html>