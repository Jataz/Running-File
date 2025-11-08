<?php /** @var int $total */ /** @var array $byStatus */ /** @var array $recent */ ?>
<div style="display:grid;gap:12px">
  <div style="display:flex;gap:10px">
    <span class="badge">Total files: <?= (int)$total ?></span>
    <?php foreach ($byStatus as $row): ?>
      <span class="badge"><?= htmlspecialchars($row['status']) ?>: <?= (int)$row['c'] ?></span>
    <?php endforeach; ?>
  </div>
  <table>
    <thead><tr><th style="width:110px">Ref</th><th>Subject</th><th style="width:120px">Owner</th><th style="width:120px">Status</th><th style="width:160px">Due</th><th style="width:180px">Created</th></tr></thead>
    <tbody>
      <?php foreach ($recent as $f): ?>
      <tr>
        <td><?= htmlspecialchars($f['ref'] ?? $f['id']) ?></td>
        <td><?= htmlspecialchars($f['subject'] ?? '') ?></td>
        <td><?= htmlspecialchars($f['owner'] ?? '') ?></td>
        <td><?= htmlspecialchars($f['status'] ?? 'new') ?></td>
        <td><?= htmlspecialchars($f['due_date'] ?? '') ?></td>
        <td><?= htmlspecialchars($f['created_at'] ?? '') ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>