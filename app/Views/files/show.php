<?php /** @var array $file */ /** @var array $docs */ ?>
<div style="display:grid;gap:16px">
  <div class="badge">File Details</div>
  <div style="background:#fff;border:1px solid #e6ecf5;border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,0.06);padding:16px">
    <div style="display:flex;justify-content:flex-end;gap:8px;margin-bottom:8px">
      <?php $cu = current_user(); $canEdit = $cu && ((int)($file['created_by'] ?? 0) === (int)$cu['id'] || class_has_all_access($cu['class'] ?? '')); ?>
      <?php if ($canEdit): ?>
        <button class="btn" id="btn-edit-file">Edit File</button>
        <label class="btn" for="up-new-doc">Upload Document</label>
        <input id="up-new-doc" type="file" style="display:none" />
      <?php endif; ?>
    </div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
      <div>
        <div style="font-weight:600;color:#2a4365">Reference</div>
        <div><?= htmlspecialchars(($file['ref'] ?? '') ?: $file['id']) ?></div>
      </div>
      <div>
        <div style="font-weight:600;color:#2a4365">Subject</div>
        <div><?= htmlspecialchars($file['subject'] ?? '') ?></div>
      </div>
      <div>
        <div style="font-weight:600;color:#2a4365">Owner</div>
        <div><?= htmlspecialchars($file['owner'] ?? '') ?></div>
      </div>
      <div>
        <div style="font-weight:600;color:#2a4365">Status</div>
        <div class="badge" style="display:inline-block"><?= htmlspecialchars($file['status'] ?? 'new') ?></div>
      </div>
      <div>
        <div style="font-weight:600;color:#2a4365">Due Date</div>
        <div><?= htmlspecialchars($file['due_date'] ?? '') ?></div>
      </div>
      <div>
        <div style="font-weight:600;color:#2a4365">Departments</div>
        <div><?= htmlspecialchars($file['departments'] ?? '') ?></div>
      </div>
      <div style="grid-column:1/-1">
        <div style="font-weight:600;color:#2a4365">Description</div>
        <div><?= nl2br(htmlspecialchars($file['description'] ?? '')) ?></div>
      </div>
      <div>
        <div style="font-weight:600;color:#2a4365">Created</div>
        <div><?= htmlspecialchars($file['created_at'] ?? '') ?></div>
      </div>
      <div>
        <div style="font-weight:600;color:#2a4365">Created By</div>
        <div><?= htmlspecialchars($file['created_by_username'] ?? (string)($file['created_by'] ?? '')) ?></div>
      </div>
    </div>
  </div>

  <div>
    <div class="badge">Documents</div>
    <?php if (!$docs): ?>
      <p style="color:#718096">No documents attached.</p>
    <?php else: ?>
      <table>
        <thead>
          <tr>
            <th>Filename</th>
            <th style="width:160px">Type</th>
            <th style="width:120px">Size</th>
            <th style="width:180px">Uploaded</th>
            <th style="width:220px">Description</th>
            <th style="width:220px">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($docs as $d): ?>
            <tr>
              <td><?= htmlspecialchars($d['original_name'] ?? '') ?></td>
              <td><?= htmlspecialchars($d['mime_type'] ?? '') ?></td>
              <td><?= htmlspecialchars(number_format((int)($d['size'] ?? 0))) ?> B</td>
              <td><?= htmlspecialchars($d['uploaded_at'] ?? '') ?></td>
              <td><?= htmlspecialchars($d['description'] ?? '') ?></td>
              <td>
                <a class="btn" href="/download.php?id=<?= (int)$d['id'] ?>">Download</a>
                <?php $cu = current_user(); $isDocOwner = $cu && (((int)($d['uploaded_by'] ?? 0) === (int)$cu['id']) || class_has_all_access($cu['class'] ?? '')); ?>
                <?php if ($isDocOwner): ?>
                  <a class="btn" href="/delete.php?id=<?= (int)$d['id'] ?>&file_id=<?= (int)$file['id'] ?>" onclick="return confirm('Delete this document?')">Delete</a>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>

  <div style="display:flex;gap:8px">
    <a class="btn" href="/files">Back to All Files</a>
    <a class="btn" href="/inbox">Inbox</a>
    <a class="btn" href="/outbox">Outgoing</a>
    <a class="btn" href="/dashboard">Dashboard</a>
  </div>
  
  <?php if ($canEdit): ?>
  <!-- Edit File Modal -->
  <dialog id="dlg-edit-file" style="min-width:720px">
    <div class="modal">
      <div class="modal-head">Edit File</div>
      <div class="modal-body" style="display:grid;grid-template-columns:repeat(2,1fr);gap:10px">
        <input type="hidden" id="ef-id" value="<?= (int)$file['id'] ?>" />
        <div class="input"><label>Subject</label><input id="ef-subject" value="<?= htmlspecialchars($file['subject'] ?? '') ?>" required /></div>
        <div class="input"><label>Owner</label><input id="ef-owner" value="<?= htmlspecialchars($file['owner'] ?? '') ?>" /></div>
        <div class="input"><label>Due Date</label><input id="ef-due" type="date" value="<?= htmlspecialchars(substr((string)($file['due_date'] ?? ''),0,10)) ?>" /></div>
        <div class="input"><label>Tags</label><input id="ef-tags" value="<?= htmlspecialchars($file['tags'] ?? '') ?>" /></div>
        <div class="input" style="grid-column:1/-1"><label>Description</label><textarea id="ef-desc" rows="3"><?= htmlspecialchars($file['description'] ?? '') ?></textarea></div>
        <div class="input" style="grid-column:1/-1"><label>Status</label>
          <select id="ef-status">
            <?php $st = $file['status'] ?? 'new'; foreach (['new','pending','review','approved','rejected','closed'] as $opt): ?>
              <option value="<?= $opt ?>" <?= $st===$opt?'selected':'' ?>><?= ucfirst($opt) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="input" style="grid-column:1/-1">
          <label>Departments</label>
          <select id="ef-depts" multiple size="6" style="height:auto"></select>
        </div>
      </div>
      <div class="modal-foot">
        <button class="btn" data-close>Cancel</button>
        <button class="btn primary" id="ef-save">Save</button>
      </div>
    </div>
  </dialog>
  <script>
    (function(){
      function api(url, opts={}){ return fetch(url, Object.assign({ credentials:'include' }, opts)).then(async r=>{ if(!r.ok) throw new Error(await r.text()); const ct=r.headers.get('content-type')||''; return ct.includes('application/json')? r.json(): r.text(); }); }
      const btnEdit=document.getElementById('btn-edit-file');
      const dlg=document.getElementById('dlg-edit-file');
      const efDepts=document.getElementById('ef-depts');
      const closeBtns = dlg? dlg.querySelectorAll('[data-close]') : [];
      closeBtns.forEach(b=> b.addEventListener('click', ()=>{ try{ dlg.close(); }catch(e){} }));
      async function loadDepartments(){
        if (!efDepts) return;
        efDepts.innerHTML='';
        try{ const data = await api('/api/departments.php'); const items=(data&&data.items)? data.items:[]; for(const d of items){ const opt=document.createElement('option'); opt.value=d.id; opt.textContent=d.name + (d.code? ' ('+d.code+')':'' ); efDepts.appendChild(opt); }
        }catch(e){ const opt=document.createElement('option'); opt.textContent='Failed to load departments'; opt.disabled=true; efDepts.appendChild(opt); }
      }
      if (btnEdit){ btnEdit.addEventListener('click', async (ev)=>{ ev.preventDefault(); await loadDepartments(); try{ dlg.showModal(); }catch(e){} }); }

      const upInput=document.getElementById('up-new-doc');
      if (upInput){ upInput.addEventListener('change', async ()=>{
        if (!upInput.files || upInput.files.length===0) return;
        const fd=new FormData(); fd.append('file_id', document.getElementById('ef-id').value||''); fd.append('respond','json'); fd.append('file', upInput.files[0]);
        try{ await api('/upload.php', { method:'POST', body: fd }); location.reload(); } catch(e){ alert('Upload failed'); }
      }); }

      const saveBtn=document.getElementById('ef-save');
      if (saveBtn){ saveBtn.addEventListener('click', async ()=>{
        const fd = new FormData();
        fd.append('id', document.getElementById('ef-id').value||'');
        fd.append('subject', document.getElementById('ef-subject').value||'');
        fd.append('owner', document.getElementById('ef-owner').value||'');
        fd.append('due', document.getElementById('ef-due').value||'');
        fd.append('tags', document.getElementById('ef-tags').value||'');
        fd.append('description', document.getElementById('ef-desc').value||'');
        fd.append('status', document.getElementById('ef-status').value||'');
        Array.from(efDepts.selectedOptions).forEach(o=> fd.append('dept_ids[]', o.value));
        saveBtn.disabled=true; saveBtn.textContent='Saving…';
        try{ await api('/api/files_update.php', { method:'POST', body: fd }); location.reload(); }
        catch(e){ alert('Update failed'); saveBtn.disabled=false; saveBtn.textContent='Save'; }
      }); }
      // Auto-open edit modal if URL hash is #edit
      if (location.hash === '#edit' && btnEdit){ btnEdit.click(); }
    })();
  </script>
  <?php endif; ?>
</div>