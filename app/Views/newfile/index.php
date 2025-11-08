<?php /** @var array $user */ ?>
<div style="display:grid;gap:12px">
  <div class="badge">Create a new file routed to departments</div>
  <form id="nf-form" class="panel" style="display:grid;grid-template-columns:repeat(2,1fr);gap:10px" enctype="multipart/form-data">
    <div class="input"><label>Subject</label><input name="subject" id="nf2-subject" required /></div>
    <div class="input"><label>Owner</label><input name="owner" id="nf2-owner" value="<?= htmlspecialchars($user['username'] ?? '') ?>" /></div>
    <div class="input"><label>Due Date</label><input name="due" id="nf2-due" type="date" /></div>
    <div class="input"><label>Tags</label><input name="tags" id="nf2-tags" placeholder="comma,separated,tags" /></div>
    <div class="input" style="grid-column:1/-1"><label>Description</label><textarea name="description" id="nf2-desc" rows="3"></textarea></div>
    <div class="input" style="grid-column:1/-1"><label>Attach Document (optional)</label><input id="nf2-file" type="file" /></div>
    <div class="input" style="grid-column:1/-1"><label>Document Description</label><input id="nf2-docdesc" placeholder="Describe the attachment (optional)" /></div>
    <div class="input" style="grid-column:1/-1">
      <label>Departments</label>
      <select id="nf2-depts" multiple size="8" style="height:auto"></select>
      <div class="badge" style="margin-top:6px">Select departments to route this file. If none selected, your department is used.</div>
    </div>
    <div style="grid-column:1/-1;display:flex;gap:8px;justify-content:flex-end">
      <a class="btn" href="/files">Cancel</a>
      <button class="btn primary" id="nf2-create" type="submit">Create</button>
    </div>
  </form>
</div>
<script>
  (function(){
    function api(url, opts={}){ return fetch(url, Object.assign({ credentials:'include' }, opts)).then(async r=>{ if(!r.ok) throw new Error(await r.text()); const ct=r.headers.get('content-type')||''; return ct.includes('application/json')? r.json(): r.text(); }); }
    async function loadDepartments(){
      const sel = document.getElementById('nf2-depts'); sel.innerHTML = '';
      try {
        const data = await api('/api/departments.php');
        const items = (data && data.items) ? data.items : [];
        for (const d of items){ const opt=document.createElement('option'); opt.value=d.id; opt.textContent=d.name + (d.code? ' ('+d.code+')':'' ); sel.appendChild(opt); }
      } catch(e) { const opt=document.createElement('option'); opt.textContent='Failed to load departments'; opt.disabled=true; document.getElementById('nf2-depts').appendChild(opt); }
    }
    loadDepartments();
    const form = document.getElementById('nf-form'); const btn = document.getElementById('nf2-create');
    form.addEventListener('submit', async (e)=>{
      e.preventDefault(); btn.disabled=true; btn.textContent='Creating…';
      const fd = new FormData();
      fd.append('subject', document.getElementById('nf2-subject').value||'');
      fd.append('owner', document.getElementById('nf2-owner').value||'');
      fd.append('due', document.getElementById('nf2-due').value||'');
      fd.append('tags', document.getElementById('nf2-tags').value||'');
      fd.append('description', document.getElementById('nf2-desc').value||'');
      Array.from(document.getElementById('nf2-depts').selectedOptions).forEach(o=> fd.append('dept_ids[]', o.value));
      try {
        const res = await api('/api/files_create.php', { method:'POST', body: fd });
        const fileId = (res && res.id) ? res.id : null;
        const input = document.getElementById('nf2-file');
        const hasFile = input && input.files && input.files.length > 0;
        if (hasFile && fileId) {
          const fd2 = new FormData();
          fd2.append('file_id', fileId);
          fd2.append('respond', 'json');
          fd2.append('description', document.getElementById('nf2-docdesc').value||'');
          fd2.append('file', input.files[0]);
          try { await api('/upload.php', { method:'POST', body: fd2 }); } catch(e) { alert('Upload failed'); }
        }
        window.location.href = '/outbox';
      } catch(e){ alert('Create failed'); btn.disabled=false; btn.textContent='Create'; }
    });
  })();
</script>