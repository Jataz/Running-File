<?php /** @var array $items */ /** @var array $deps */ ?>
<div style="display:grid;gap:12px">
  <div style="text-align:right">
    <button class="btn primary" id="btn-new-user">New User</button>
  </div>

  <table data-enhance="true" data-page-size="25">
    <thead><tr><th style="width:80px">ID</th><th>Username</th><th style="width:120px">Class</th><th style="width:180px">Department</th><th style="width:120px">Status</th><th style="width:320px">Actions</th></tr></thead>
    <tbody>
      <?php foreach ($items as $u): ?>
        <tr>
          <td><?= (int)$u['id'] ?></td>
          <td><?= htmlspecialchars($u['username']) ?></td>
          <td><?= htmlspecialchars($u['class']) ?></td>
          <td><?= htmlspecialchars($u['dept_name'] ?? '') ?></td>
          <td><?= ((int)($u['active'] ?? 1) === 1 ? 'Active' : 'Disabled') ?></td>
          <td>
            <button class="btn" data-action="edit" data-id="<?= (int)$u['id'] ?>" data-username="<?= htmlspecialchars($u['username']) ?>" data-class="<?= htmlspecialchars($u['class']) ?>" data-dept-id="<?= (int)($u['department_id'] ?? 0) ?>">Edit</button>
            <button class="btn" data-action="password" data-id="<?= (int)$u['id'] ?>" data-username="<?= htmlspecialchars($u['username']) ?>">Change Password</button>
            <button class="btn" data-action="toggle" data-id="<?= (int)$u['id'] ?>" data-next="<?= ((int)($u['active'] ?? 1) === 1 ? '0' : '1') ?>" data-username="<?= htmlspecialchars($u['username']) ?>"><?= ((int)($u['active'] ?? 1) === 1 ? 'Disable' : 'Enable') ?></button>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<!-- Create User Modal -->
<dialog id="dlg-new-user" style="min-width:640px">
  <div class="modal-head">Create User</div>
  <div class="modal-body" style="display:grid;grid-template-columns:repeat(2,1fr);gap:10px">
    <div class="input"><label>Username</label><input id="nu-username" required /></div>
    <div class="input"><label>Password</label><input id="nu-password" type="password" required /></div>
    <div class="input"><label>Class</label>
      <select id="nu-class" required>
        <option value="A">A — Clerk</option>
        <option value="B">B — Officer</option>
        <option value="C">C — Director</option>
        <option value="D">D — Permanent Secretary</option>
        <option value="E">E — Admin</option>
      </select>
    </div>
    <div class="input"><label>Department</label>
      <select id="nu-dept">
        <option value="">—</option>
        <?php foreach ($deps as $d): ?>
          <option value="<?= (int)$d['id'] ?>"><?= htmlspecialchars($d['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>
  <div class="modal-foot" style="display:flex;gap:8px;justify-content:flex-end">
    <button class="btn" data-close>Cancel</button>
    <button class="btn primary" id="nu-save">Create</button>
  </div>
</dialog>

<!-- Edit User Modal -->
<dialog id="dlg-edit-user" style="min-width:640px">
  <div class="modal-head">Edit User</div>
  <div class="modal-body" style="display:grid;grid-template-columns:repeat(2,1fr);gap:10px">
    <input type="hidden" id="eu-id" />
    <div class="input"><label>Username</label><input id="eu-username" required /></div>
    <div class="input"><label>Class</label>
      <select id="eu-class" required>
        <option value="A">A — Clerk</option>
        <option value="B">B — Officer</option>
        <option value="C">C — Director</option>
        <option value="D">D — Permanent Secretary</option>
        <option value="E">E — Admin</option>
      </select>
    </div>
    <div class="input"><label>Department</label>
      <select id="eu-dept">
        <option value="">—</option>
        <?php foreach ($deps as $d): ?>
          <option value="<?= (int)$d['id'] ?>"><?= htmlspecialchars($d['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>
  <div class="modal-foot" style="display:flex;gap:8px;justify-content:flex-end">
    <button class="btn" data-close>Cancel</button>
    <button class="btn primary" id="eu-save">Save Changes</button>
  </div>
</dialog>

<!-- Change Password Modal -->
<dialog id="dlg-pass" style="min-width:520px">
  <div class="modal-head">Change Password</div>
  <div class="modal-body" style="display:grid;gap:10px">
    <input type="hidden" id="pw-id" />
    <div class="input"><label>New Password</label><input id="pw-new" type="password" required /></div>
  </div>
  <div class="modal-foot" style="display:flex;gap:8px;justify-content:flex-end">
    <button class="btn" data-close>Cancel</button>
    <button class="btn primary" id="pw-save">Change</button>
  </div>
</dialog>

<!-- Enable/Disable Confirmation Modal -->
<dialog id="dlg-toggle" style="min-width:520px">
  <div class="modal-head">Confirm Action</div>
  <div class="modal-body" style="display:grid;gap:10px">
    <div id="tg-msg" class="badge"></div>
    <input type="hidden" id="tg-id" />
    <input type="hidden" id="tg-next" />
  </div>
  <div class="modal-foot" style="display:flex;gap:8px;justify-content:flex-end">
    <button class="btn" data-close>Cancel</button>
    <button class="btn primary" id="tg-confirm">Confirm</button>
  </div>
</dialog>

<script>
(() => {
  function api(url, opts={}){ return fetch(url, Object.assign({ credentials:'include' }, opts)).then(async r=>{ if(!r.ok) throw new Error(await r.text()); const ct=r.headers.get('content-type')||''; return ct.includes('application/json')? r.json(): r.text(); }); }
  function show(id){ const d=document.getElementById(id); try{ d.showModal(); }catch(e){} }
  function closeAll(){ document.querySelectorAll('dialog[open]').forEach(d=>{ try{ d.close(); }catch(e){} }); }
  document.querySelectorAll('dialog [data-close]').forEach(b=> b.addEventListener('click', closeAll));

  // New User
  const btnNew=document.getElementById('btn-new-user'); if(btnNew) btnNew.onclick=()=>show('dlg-new-user');
  const nuSave=document.getElementById('nu-save'); if(nuSave) nuSave.onclick=async()=>{
    const fd=new FormData();
    fd.append('username', document.getElementById('nu-username').value||'');
    fd.append('password', document.getElementById('nu-password').value||'');
    fd.append('class', document.getElementById('nu-class').value||'');
    const d=document.getElementById('nu-dept').value||''; if(d) fd.append('department_id', d);
    try{ await api('/api/users_create.php', { method:'POST', body: fd }); closeAll(); location.reload(); } catch(e){ alert('Create failed'); }
  };

  // Table actions
  const tbody=document.querySelector('table tbody');
  tbody.addEventListener('click', (ev)=>{
    const b=ev.target.closest('button.btn'); if(!b) return;
    const act=b.dataset.action; const id=b.dataset.id;
    if(act==='edit'){
      document.getElementById('eu-id').value=id;
      document.getElementById('eu-username').value=b.dataset.username||'';
      document.getElementById('eu-class').value=b.dataset.class||'A';
      const dep=b.dataset.deptId||''; const sel=document.getElementById('eu-dept'); if(sel){ sel.value=dep||''; }
      show('dlg-edit-user');
    } else if(act==='password'){
      document.getElementById('pw-id').value=id;
      document.getElementById('pw-new').value='';
      show('dlg-pass');
    } else if(act==='toggle'){
      document.getElementById('tg-id').value=id;
      document.getElementById('tg-next').value=b.dataset.next||'0';
      const next=b.dataset.next==='1';
      const msg = next? `Enable user "${b.dataset.username}"?` : `Disable user "${b.dataset.username}"?`;
      document.getElementById('tg-msg').textContent = msg;
      show('dlg-toggle');
    }
  });

  // Save Edit
  const euSave=document.getElementById('eu-save'); if(euSave) euSave.onclick=async()=>{
    const fd=new FormData();
    fd.append('id', document.getElementById('eu-id').value||'');
    fd.append('username', document.getElementById('eu-username').value||'');
    fd.append('class', document.getElementById('eu-class').value||'');
    const d=document.getElementById('eu-dept').value||''; if(d) fd.append('department_id', d);
    try{ await api('/api/users_edit.php', { method:'POST', body: fd }); closeAll(); location.reload(); } catch(e){ alert('Update failed'); }
  };

  // Save Password
  const pwSave=document.getElementById('pw-save'); if(pwSave) pwSave.onclick=async()=>{
    const fd=new FormData(); fd.append('id', document.getElementById('pw-id').value||''); fd.append('password', document.getElementById('pw-new').value||'');
    try{ await api('/api/users_password.php', { method:'POST', body: fd }); closeAll(); alert('Password changed'); } catch(e){ alert('Password change failed'); }
  };

  // Confirm Toggle
  const tgConfirm=document.getElementById('tg-confirm'); if(tgConfirm) tgConfirm.onclick=async()=>{
    const fd=new FormData(); fd.append('id', document.getElementById('tg-id').value||''); fd.append('active', document.getElementById('tg-next').value||'0');
    try{ await api('/api/users_update.php', { method:'POST', body: fd }); closeAll(); location.reload(); } catch(e){ alert('Update failed'); }
  };
})();
</script>