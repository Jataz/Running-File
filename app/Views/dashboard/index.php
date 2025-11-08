<?php /** @var int $total */ /** @var array $byStatus */ /** @var array $recent */ /** @var array $reports */ ?>
<div style="display:grid;gap:12px">
  <div style="display:grid;grid-template-columns:repeat(6,1fr);gap:10px">
    <div class="panel">
      <div class="badge">My Outgoing</div>
      <div style="font:bold 20px/1.2 system-ui;color:#2d3748;"><?= (int)($reports['my_outgoing'] ?? 0) ?></div>
    </div>
    <div class="panel">
      <div class="badge">My Inbox</div>
      <div style="font:bold 20px/1.2 system-ui;color:#2d3748;"><?= (int)($reports['my_inbox'] ?? 0) ?></div>
    </div>
    <div class="panel">
      <div class="badge">Overdue</div>
      <div style="font:bold 20px/1.2 system-ui;color:#c53030;"><?= (int)($reports['overdue'] ?? 0) ?></div>
    </div>
    <div class="panel">
      <div class="badge">Pending Review</div>
      <div style="font:bold 20px/1.2 system-ui;color:#2d3748;"><?= (int)($reports['pending_review'] ?? 0) ?></div>
    </div>
    <div class="panel">
      <div class="badge">Approved Today</div>
      <div style="font:bold 20px/1.2 system-ui;color:#2d3748;"><?= (int)($reports['approved_today'] ?? 0) ?></div>
    </div>
    <div class="panel">
      <div class="badge">Documents</div>
      <div style="font:bold 20px/1.2 system-ui;color:#2d3748;"><?= (int)($reports['documents_total'] ?? 0) ?></div>
    </div>
  </div>
  <div style="display:flex;gap:10px">
    <span class="badge">Total files: <?= (int)$total ?></span>
    <?php foreach ($byStatus as $row): ?>
      <span class="badge"><?= htmlspecialchars($row['status']) ?>: <?= (int)$row['c'] ?></span>
    <?php endforeach; ?>
  </div>
  <table data-enhance="true" data-page-size="10">
    <thead><tr><th style="width:110px">Ref</th><th>Subject</th><th style="width:120px">Owner</th><th style="width:120px">Status</th><th style="width:160px">Due</th><th style="width:180px">Created</th><th style="width:120px">Actions</th></tr></thead>
    <tbody>
      <?php foreach ($recent as $f): ?>
      <tr>
        <td><?= htmlspecialchars($f['ref'] ?? $f['id']) ?></td>
        <td><?= htmlspecialchars($f['subject'] ?? '') ?></td>
        <td><?= htmlspecialchars($f['owner'] ?? '') ?></td>
        <td><?= htmlspecialchars($f['status'] ?? 'new') ?></td>
        <td><?= htmlspecialchars($f['due_date'] ?? '') ?></td>
        <td><?= htmlspecialchars($f['created_at'] ?? '') ?></td>
        <td><a class="btn" href="/files/<?= (int)$f['id'] ?>">View</a></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>