<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/config.php';

try {
    $pdo = get_pdo();
    // Create core tables
    $pdo->exec(<<<SQL
CREATE TABLE IF NOT EXISTS departments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  code VARCHAR(20) UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
SQL);

    $pdo->exec(<<<SQL
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(120) UNIQUE NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  class ENUM('A','B','C','D','E') NOT NULL,
  department_id INT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
SQL);

    $pdo->exec(<<<SQL
CREATE TABLE IF NOT EXISTS documents (
  id INT AUTO_INCREMENT PRIMARY KEY,
  stored_name VARCHAR(255) NOT NULL,
  original_name VARCHAR(255) NOT NULL,
  mime_type VARCHAR(255),
  size BIGINT,
  description TEXT,
  sha256 CHAR(64),
  department_id INT NULL,
  uploaded_by INT NULL,
  uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL,
  FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
SQL);

    // Ensure documents has optional link to files (column first; FK added after files exist)
    try { $pdo->exec('ALTER TABLE documents ADD COLUMN file_id INT NULL'); } catch (Throwable $e) { /* ignore */ }

    // Business files table
    $pdo->exec(<<<SQL
CREATE TABLE IF NOT EXISTS files (
  id INT AUTO_INCREMENT PRIMARY KEY,
  ref VARCHAR(64),
  subject VARCHAR(255) NOT NULL,
  owner VARCHAR(255),
  due_date DATE,
  tags TEXT,
  description TEXT,
  status ENUM('new','pending','review','approved','rejected','closed') DEFAULT 'new',
  created_by INT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
SQL);

    // After files exist, ensure FK from documents.file_id -> files.id
    try { $pdo->exec('ALTER TABLE documents ADD CONSTRAINT fk_documents_file_id FOREIGN KEY (file_id) REFERENCES files(id) ON DELETE SET NULL'); } catch (Throwable $e) { /* ignore */ }

    // Files to Departments (many-to-many)
    $pdo->exec(<<<SQL
CREATE TABLE IF NOT EXISTS file_departments (
  file_id INT NOT NULL,
  department_id INT NOT NULL,
  PRIMARY KEY (file_id, department_id),
  FOREIGN KEY (file_id) REFERENCES files(id) ON DELETE CASCADE,
  FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
SQL);

    if (!is_dir(UPLOAD_DIR)) {
        @mkdir(UPLOAD_DIR, 0775, true);
    }

    // Seed core departments
    $pdo->exec("INSERT IGNORE INTO departments (id, name, code) VALUES (1, 'General', 'GEN')");
    $departments = [
        ['HR', 'HR'],
        ['Finance and Admin', 'FIN'],
        ['SPPME', 'SPPME'],
        ['ICT', 'ICT'],
        ['Internal Audit', 'IAU'],
        ['Skills Audit', 'SA'],
        ['Research', 'RCH'],
    ];
    $insDept = $pdo->prepare('INSERT IGNORE INTO departments (name, code) VALUES (?, ?)');
    foreach ($departments as $d) { $insDept->execute([$d[0], $d[1]]); }

    // Seed admin user
    $adminUser = 'admin';
    $adminPass = 'admin123'; // change after first login
    $hash = password_hash($adminPass, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT IGNORE INTO users (username, password_hash, class, department_id) VALUES (?,?, 'E', 1)");
    $stmt->execute([$adminUser, $hash]);

    // Seed sample users (A/B/C/D) mapped to departments
    $samples = [
        ['clerkA',   'pass', 'A', 'HR'],
        ['officerB', 'pass', 'B', 'FIN'],
        ['directorC','pass', 'C', 'SPPME'],
        ['psD',      'pass', 'D', 'ICT'],
    ];
    $findDept = $pdo->prepare('SELECT id FROM departments WHERE code = ?');
    $insUser  = $pdo->prepare('INSERT IGNORE INTO users (username, password_hash, class, department_id) VALUES (?,?,?,?)');
    foreach ($samples as [$uname,$pwd,$cls,$code]) {
        $findDept->execute([$code]);
        $row = $findDept->fetch();
        $deptId = $row ? (int)$row['id'] : 1; // default General
        $insUser->execute([$uname, password_hash($pwd, PASSWORD_DEFAULT), $cls, $deptId]);
    }

    header('Location: /index.html?initialized=1');
    exit;
} catch (Throwable $e) {
    http_response_code(500);
    echo "Database initialization failed: " . htmlspecialchars($e->getMessage());
    exit;
}

?>