<?php
// Server-backed SPA host: loads index.html, disables demo boot, and appends integration script.
$html = @file_get_contents(__DIR__ . '/index.html');
if ($html === false) { $html = '<!doctype html><html><body>Missing index.html</body></html>'; }
// Disable client-side boot; our integration handles login, lists, and actions
// Strip any inline demo scripts to avoid conflicting event bindings
$html = preg_replace('/<script[\s\S]*?<\/script>/', '', $html);
echo $html;

// Inject integration script (migrated from router)
$script = <<<'JS'
(function(){
  const $ = (s)=>document.querySelector(s);
  const rows = document.getElementById('rows');
  const app = document.getElementById('app');
  const login = document.getElementById('login');
  const who = document.getElementById('who');
  const btnNew = document.getElementById('btn-new');
  const dlgNew = document.getElementById('dlg-new');
  const nfSave = document.getElementById('nf-save');
  const nfDepts = document.getElementById('nf-depts');
  const nfSubject = document.getElementById('nf-subject');
  const nfOwner = document.getElementById('nf-owner');
  const nfDue = document.getElementById('nf-due');
  const nfTags = document.getElementById('nf-tags');
  const nfDesc = document.getElementById('nf-desc');

  async function api(url, opts={}){
    const res = await fetch(url, Object.assign({credentials:'include'}, opts));
    if(!res.ok){throw new Error(await res.text());}
    const ct = res.headers.get('content-type')||'';
    return ct.includes('application/json') ? res.json() : res.text();
  }

  async function loadDepartments(){
    try{
      const r = await api('/api/departments.php');
      const items = r.items||[];
      const singleSelects = document.querySelectorAll('.dept-select');
      singleSelects.forEach(sel=>{
        sel.innerHTML = items.map(it=>`<option value="${it.code}" data-id="${it.id}">${it.name}</option>`).join('');
      });
      if (nfDepts){
        nfDepts.innerHTML = items.map(it=>`<option value="${it.id}">${it.name}</option>`).join('');
      }
    }catch(e){ console.error('departments', e); }
  }

  async function refreshUser(){
    try{
      const r = await api('/auth.php?action=me');
      if (r.user){
        if (who) who.textContent = r.user.username + ' • Class ' + r.user.class + (r.user.dept_name? (' • '+r.user.dept_name):'');
        if (login) login.style.display = 'none';
        if (app) app.style.visibility = 'visible';
        await loadDepartments();
        await loadFiles();
      }
    }catch(e){}
  }

  async function loadFiles(){
    try{
      const r = await api('/api/files_list.php');
      if (rows){
        rows.innerHTML = '';
        (r.items||[]).forEach((it)=>{
          const tr = document.createElement('tr');
          tr.innerHTML = `
            <td>${it.ref||it.id}</td>
            <td>${it.subject||''}</td>
            <td>${it.departments||''}</td>
            <td>${it.owner||''}</td>
            <td class="status">${it.status||'new'}</td>
            <td>${it.due_date||''}</td>
            <td>
              <button class="btn" data-file-id="${it.id}">Open</button>
            </td>`;
          rows.appendChild(tr);
        });
        const stat = document.getElementById('stat-count');
        if (stat) stat.textContent = (r.items||[]).length + ' files';
      }
    }catch(e){ console.error(e); }
  }

  const liLogin = document.getElementById('li-login');
  if (liLogin){
    liLogin.addEventListener('click', async ()=>{
      const u = document.getElementById('li-user');
      const p = document.getElementById('li-pass');
      const fd = new FormData();
      fd.append('username', u? (u.value||'') : '');
      fd.append('password', p? (p.value||'') : '');
      try{ await api('/auth.php?action=login', {method:'POST', body: fd}); await refreshUser(); }
      catch(e){ alert('Login failed'); }
    });
  }

  const logoutBtn = document.getElementById('btn-logout');
  if (logoutBtn){
    logoutBtn.addEventListener('click', async ()=>{ try{ await api('/logout.php'); }catch(e){} location.reload(); });
  }

  if (btnNew && dlgNew && nfSave){
    btnNew.addEventListener('click', ()=>{ try { dlgNew.showModal(); } catch(e){} });
    nfSave.addEventListener('click', async ()=>{
      const fd = new FormData();
      fd.append('subject', nfSubject? (nfSubject.value||'') : '');
      fd.append('owner', nfOwner? (nfOwner.value||'') : '');
      fd.append('due', nfDue? (nfDue.value||'') : '');
      fd.append('tags', nfTags? (nfTags.value||'') : '');
      fd.append('description', nfDesc? (nfDesc.value||'') : '');
      if (nfDepts){
        Array.from(nfDepts.selectedOptions||[]).forEach(opt=>{
          fd.append('dept_ids[]', opt.value);
        });
      }
      try{
        const r = await api('/api/files_create.php', {method:'POST', body: fd});
        if (dlgNew && dlgNew.close) dlgNew.close();
        await loadFiles();
      }catch(e){ alert('Create failed'); }
    });
  }

  function ensureUploadFileId(id){
    const form = document.querySelector('form[action="/upload.php"]') || document.querySelector('form[data-upload]');
    if (!form) return;
    let hidden = form.querySelector('input[name="file_id"]');
    if (!hidden){
      hidden = document.createElement('input');
      hidden.type = 'hidden';
      hidden.name = 'file_id';
      form.appendChild(hidden);
    }
    hidden.value = String(id||'');
    const indicator = document.getElementById('upload-context');
    if (indicator){ indicator.textContent = id ? ('Attaching uploads to file #' + id) : ''; }
  }

  document.addEventListener('click', (ev)=>{
    const t = ev.target;
    if (t && t.matches('button.btn[data-file-id]')){
      const id = parseInt(t.getAttribute('data-file-id')||'0', 10);
      if (id>0){ ensureUploadFileId(id); }
    }
  });

  // Initial boot
  refreshUser();

  // Admin-only: add user management dialog and button
  async function ensureAdminUI(){
    try{
      const r = await api('/auth.php?action=me');
      const u = r.user;
      if (!u || u.class !== 'E') return;
      const header = document.querySelector('header');
      if (!header) return;
      // Button
      let btn = document.getElementById('btn-users');
      if (!btn){
        btn = document.createElement('button');
        btn.id = 'btn-users';
        btn.className = 'btn';
        btn.innerHTML = '<svg><use href="#ico-user"/></svg>Manage Users';
        header.insertBefore(btn, document.getElementById('btn-logout'));
      }
      // Dialog
      let dlg = document.getElementById('dlg-users');
      if (!dlg){
        dlg = document.createElement('dialog');
        dlg.id = 'dlg-users';
        dlg.innerHTML = `
          <div class="modal-head">Create User</div>
          <div class="modal-body">
            <div class="input"><label>Username</label><input id="us-username"/></div>
            <div class="input"><label>Password</label><input id="us-password" type="password"/></div>
            <div class="input"><label>Class</label>
              <select id="us-class">
                <option value="A">A — Clerk</option>
                <option value="B">B — Officer</option>
                <option value="C">C — Director</option>
                <option value="D">D — Permanent Secretary</option>
                <option value="E">E — Admin</option>
              </select>
            </div>
            <div class="input"><label>Department</label>
              <select id="us-dept"></select>
            </div>
          </div>
          <div class="modal-foot">
            <button class="btn" id="us-cancel">Close</button>
            <button class="btn primary" id="us-create"><svg><use href="#ico-check"/></svg>Create</button>
          </div>`;
        document.body.appendChild(dlg);
      }

      // Populate departments
      try{
        const deps = await api('/api/departments.php');
        const sel = document.getElementById('us-dept');
        if (sel){ sel.innerHTML = (deps.items||[]).map(it=>`<option value="${it.id}">${it.name}</option>`).join(''); }
      }catch(e){}

      btn.addEventListener('click', ()=>{ try{ dlg.showModal(); }catch(e){} });
      const cancel = document.getElementById('us-cancel');
      if (cancel) cancel.addEventListener('click', ()=>{ try{ dlg.close(); }catch(e){} });
      const create = document.getElementById('us-create');
      if (create) create.addEventListener('click', async ()=>{
        const fd = new FormData();
        fd.append('username', (document.getElementById('us-username')||{}).value||'');
        fd.append('password', (document.getElementById('us-password')||{}).value||'');
        fd.append('class', (document.getElementById('us-class')||{}).value||'');
        const sid = (document.getElementById('us-dept')||{}).value||'';
        if (sid) fd.append('department_id', sid);
        try{
          const r = await api('/api/users_create.php', {method:'POST', body: fd});
          alert('User created');
          try{ dlg.close(); }catch(e){}
        }catch(e){ alert('Create failed'); }
      });
    }catch(e){}
  }

  ensureAdminUI();
})();
JS;
echo "\n<script>\n$script\n</script>\n";