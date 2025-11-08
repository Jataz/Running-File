<?php /** @var array $items */ /** @var array $deps */ ?>
<div style="display:grid;gap:12px">
  <form method="post" action="/api/users_create.php" style="display:grid;grid-template-columns:repeat(4,1fr);gap:10px">
    <div class="input"><label>Username</label><input name="username" required /></div>
    <div class="input"><label>Password</label><input name="password" type="password" required /></div>
    <div class="input"><label>Class</label>
      <select name="class" required>
        <option value="A">A — Clerk</option>
        <option value="B">B — Officer</option>
        <option value="C">C — Director</option>
        <option value="D">D — Permanent Secretary</option>
        <option value="E">E — Admin</option>
      </select>
    </div>
    <div class="input"><label>Department</label>
      <select name="department_id">
        <option value="">—</option>
        <?php foreach ($deps as $d): ?>
          <option value="<?= (int)$d['id'] ?>"><?= htmlspecialchars($d['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div style="grid-column:1/-1;text-align:right"><button class="btn primary" type="submit">Create User</button></div>
  </form>

  <table>
    <thead><tr><th style="width:80px">ID</th><th>Username</th><th style="width:120px">Class</th><th style="width:180px">Department</th><th style="width:120px">Status</th><th style="width:220px">Actions</th></tr></thead>
    <tbody>
      <?php foreach ($items as $u): ?>
        <tr>
          <td><?= (int)$u['id'] ?></td>
          <td><?= htmlspecialchars($u['username']) ?></td>
          <td><?= htmlspecialchars($u['class']) ?></td>
          <td><?= htmlspecialchars($u['dept_name'] ?? '') ?></td>
          <td><?= ((int)($u['active'] ?? 1) === 1 ? 'Active' : 'Disabled') ?></td>
          <td>
            <form method="post" action="/api/users_update.php" style="display:inline">
              <input type="hidden" name="id" value="<?= (int)$u['id'] ?>" />
              <input type="hidden" name="active" value="<?= ((int)($u['active'] ?? 1) === 1 ? '0' : '1') ?>" />
              <button class="btn" type="submit"><?= ((int)($u['active'] ?? 1) === 1 ? 'Disable' : 'Enable') ?></button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>