<?php /** @var array $file */ /** @var array $docs */ ?>
<div style="display:grid;gap:16px">
  <div class="badge">File Details</div>
  <div style="background:#fff;border:1px solid #e6ecf5;border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,0.06);padding:16px">
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
        <div><?= htmlspecialchars((string)($file['created_by'] ?? '')) ?></div>
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
            <th style="width:120px">Actions</th>
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
</div>