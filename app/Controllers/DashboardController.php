<?php
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../middleware.php';

class DashboardController {
    public function index(): void {
        require_auth();
        $u = current_user();
        try {
            $pdo = get_pdo();
            $total = (int)$pdo->query("SELECT COUNT(*) AS c FROM files")->fetch()['c'];
            $byStatus = $pdo->query("SELECT status, COUNT(*) AS c FROM files GROUP BY status")->fetchAll();
            $recent = $pdo->query("SELECT id, ref, subject, owner, status, due_date, created_at FROM files ORDER BY created_at DESC LIMIT 10")->fetchAll();

            // Role-based reports
            $uid = (int)($u['id'] ?? 0);
            $deptId = (int)($u['department_id'] ?? 0);
            $allAccess = class_has_all_access($u['class'] ?? '');

            // My outgoing files
            $myOutgoing = (int)($pdo->prepare("SELECT COUNT(*) AS c FROM files WHERE created_by = ?")->execute([$uid]) ? $pdo->query("SELECT COUNT(*) AS c FROM files WHERE created_by = $uid")->fetch()['c'] : 0);
            // My department inbox files
            $myDeptInbox = 0;
            if ($deptId > 0) {
                $stmt = $pdo->prepare("SELECT COUNT(DISTINCT f.id) AS c FROM files f JOIN file_departments fd ON fd.file_id = f.id WHERE fd.department_id = ?");
                $stmt->execute([$deptId]);
                $myDeptInbox = (int)$stmt->fetch()['c'];
            }

            // Overdue (mine or department depending on role)
            if ($allAccess) {
                $overdue = (int)$pdo->query("SELECT COUNT(*) AS c FROM files WHERE COALESCE(due_date, '9999-12-31') < CURRENT_DATE AND status NOT IN ('closed','approved')")->fetch()['c'];
            } else {
                // Count overdue for my outgoing + my department
                $stmt = $pdo->prepare("SELECT COUNT(DISTINCT f.id) AS c FROM files f LEFT JOIN file_departments fd ON fd.file_id = f.id WHERE (f.created_by = ? OR fd.department_id = ?) AND COALESCE(f.due_date, '9999-12-31') < CURRENT_DATE AND f.status NOT IN ('closed','approved')");
                $stmt->execute([$uid, $deptId]);
                $overdue = (int)$stmt->fetch()['c'];
            }

            // Pending review
            if ($allAccess) {
                $pendingReview = (int)$pdo->query("SELECT COUNT(*) AS c FROM files WHERE status = 'review'")->fetch()['c'];
            } else {
                $stmt = $pdo->prepare("SELECT COUNT(DISTINCT f.id) AS c FROM files f LEFT JOIN file_departments fd ON fd.file_id = f.id WHERE f.status = 'review' AND (f.created_by = ? OR fd.department_id = ?)");
                $stmt->execute([$uid, $deptId]);
                $pendingReview = (int)$stmt->fetch()['c'];
            }

            // Approved today
            if ($allAccess) {
                $approvedToday = (int)$pdo->query("SELECT COUNT(*) AS c FROM files WHERE status = 'approved' AND DATE(created_at) = CURRENT_DATE")->fetch()['c'];
            } else {
                $stmt = $pdo->prepare("SELECT COUNT(DISTINCT f.id) AS c FROM files f LEFT JOIN file_departments fd ON fd.file_id = f.id WHERE f.status = 'approved' AND DATE(f.created_at) = CURRENT_DATE AND (f.created_by = ? OR fd.department_id = ?)");
                $stmt->execute([$uid, $deptId]);
                $approvedToday = (int)$stmt->fetch()['c'];
            }

            // Documents count
            if ($allAccess) {
                $documentsTotal = (int)$pdo->query("SELECT COUNT(*) AS c FROM documents")->fetch()['c'];
            } else {
                $stmt = $pdo->prepare("SELECT COUNT(*) AS c FROM documents WHERE uploaded_by = ? OR department_id = ?");
                $stmt->execute([$uid, $deptId]);
                $documentsTotal = (int)$stmt->fetch()['c'];
            }

            $reports = [
                'my_outgoing' => $myOutgoing,
                'my_inbox' => $myDeptInbox,
                'overdue' => $overdue,
                'pending_review' => $pendingReview,
                'approved_today' => $approvedToday,
                'documents_total' => $documentsTotal,
            ];
        } catch (Throwable $e) {
            http_response_code(500);
            echo 'Server error: ' . htmlspecialchars($e->getMessage());
            return;
        }
        $title = 'Dashboard';
        $content = $this->renderView(__DIR__ . '/../Views/dashboard/index.php', [ 'total' => $total, 'byStatus' => $byStatus, 'recent' => $recent, 'reports' => $reports ]);
        $this->renderLayout($title, $content, 'light');
    }

    private function renderLayout(string $title, string $content, string $theme = ''): void {
        $layoutPath = __DIR__ . '/../Views/layout.php';
        $pageTitle = $title;
        $pageContent = $content;
        $theme = $theme; // pass theme to layout for optional light styling
        include $layoutPath;
    }
    private function renderView(string $path, array $vars): string {
        extract($vars);
        ob_start(); include $path; return ob_get_clean();
    }
}
?>