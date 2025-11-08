<?php /** @var array $items */ ?>
<div class="badge">Files you created</div>
<table>
  <thead><tr><th style="width:110px">Ref</th><th>Subject</th><th style="width:180px">Departments</th><th style="width:120px">Owner</th><th style="width:120px">Status</th><th style="width:160px">Due</th></tr></thead>
  <tbody>
    <?php foreach ($items as $it): ?>
      <tr>
        <td><?= htmlspecialchars(($it['ref'] ?? '') ?: $it['id']) ?></td>
        <td><?= htmlspecialchars($it['subject'] ?? '') ?></td>
        <td><?= htmlspecialchars($it['departments'] ?? '') ?></td>
        <td><?= htmlspecialchars($it['owner'] ?? '') ?></td>
        <td><?= htmlspecialchars($it['status'] ?? 'new') ?></td>
        <td><?= htmlspecialchars($it['due_date'] ?? '') ?></td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>