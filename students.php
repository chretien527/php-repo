<?php
require_once __DIR__ . '/../../includes/init.php';
require_once __DIR__ . '/../../includes/layout.php';

Auth::requireRole(['admin','registrar']);

// ── Handle delete ──────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && post('action') === 'delete') {
    if (!Auth::verifyCsrf(post('csrf_token'))) {
        flash('main', 'Invalid CSRF token.', 'error');
    } else {
        $sid = (int)post('student_id');
        DB::query('DELETE FROM students WHERE id = ?', [$sid]);
        Auth::logActivity(Auth::userId(), 'delete_student', "Deleted student ID $sid");
        flash('main', 'Student record deleted.', 'success');
    }
    redirect('/pages/admin/students.php');
}

// ── Filters & pagination ───────────────────────────────────────────────────
$search = sanitize(get('q'));
$course = sanitize(get('course'));
$year   = sanitize(get('year'));
$page   = max(1, (int)get('page', 1));
$perPage = 15;

$where  = '1=1';
$params = [];

if ($search) {
    $where  .= ' AND (s.studentId LIKE ? OR s.name LIKE ? OR s.contact LIKE ?)';
    $params  = array_merge($params, ["%$search%", "%$search%", "%$search%"]);
}
if ($course) { $where .= ' AND s.course = ?'; $params[] = $course; }
if ($year)   { $where .= ' AND s.year = ?';   $params[] = $year; }

$total  = (int)(DB::fetchOne("SELECT COUNT(*) AS c FROM students s WHERE $where", $params)['c'] ?? 0);
$pag    = paginate($total, $perPage, $page);

$students = DB::fetchAll(
    "SELECT s.*, u.firstName AS reg_first, u.lastName AS reg_last
     FROM students s
     LEFT JOIN users u ON u.id = s.registered_by
     WHERE $where
     ORDER BY s.created_at DESC
     LIMIT {$pag['per_page']} OFFSET {$pag['offset']}",
    $params
);

// Distinct courses for filter
$courses = DB::fetchAll('SELECT DISTINCT course FROM students ORDER BY course');
$qstring = http_build_query(['q'=>$search,'course'=>$course,'year'=>$year]);

dashboardLayout('All Students', function() use ($students, $search, $course, $year, $courses, $pag, $total, $qstring) {
    showFlash('main');
    ?>
<div class="card">
  <div class="card-header-custom">
    <h5><i class="bi bi-people"></i>Student Records <span style="font-size:.82rem;font-weight:400;color:#6c757d">(<?= number_format($total) ?> total)</span></h5>
    <a href="student_add.php" class="btn-primary-custom">
      <i class="bi bi-person-plus"></i> Add Student
    </a>
  </div>
  <div class="card-body" style="padding-bottom:.5rem">
    <!-- Search / Filter -->
    <form method="GET" class="search-bar mb-3">
      <div class="search-input-wrap">
        <span class="search-icon"><i class="bi bi-search"></i></span>
        <input type="text" name="q" class="form-control" placeholder="Search name, ID, contact…" value="<?= h($search) ?>">
      </div>
      <select name="course" class="form-control" style="max-width:200px">
        <option value="">All Courses</option>
        <?php foreach ($courses as $c): ?>
        <option value="<?= h($c['course']) ?>" <?= $course===$c['course']?'selected':'' ?>><?= h($c['course']) ?></option>
        <?php endforeach; ?>
      </select>
      <select name="year" class="form-control" style="max-width:120px">
        <option value="">All Years</option>
        <?php foreach (['1','2','3','4','5'] as $y): ?>
        <option value="<?= $y ?>" <?= $year===$y?'selected':'' ?>>Year <?= $y ?></option>
        <?php endforeach; ?>
      </select>
      <button type="submit" class="btn-primary-custom"><i class="bi bi-funnel"></i> Filter</button>
      <a href="students.php" class="btn-outline"><i class="bi bi-x"></i> Clear</a>
    </form>
  </div>

  <div class="table-wrapper" style="padding:0 1.5rem 1.5rem">
  <table class="data-table">
    <thead>
      <tr>
        <th>#</th><th>Student ID</th><th>Name</th><th>Course</th>
        <th>Year</th><th>Contact</th><th>Registered</th><th>Actions</th>
      </tr>
    </thead>
    <tbody>
    <?php if ($students): ?>
      <?php $n = $pag['offset'] + 1; foreach ($students as $s): ?>
      <tr>
        <td class="text-muted"><?= $n++ ?></td>
        <td><code><?= h($s['studentId']) ?></code></td>
        <td><?= h($s['name']) ?></td>
        <td><?= h($s['course']) ?></td>
        <td>Year <?= h($s['year']) ?></td>
        <td><?= h($s['contact']) ?></td>
        <td><?= fmtDate($s['created_at']) ?></td>
        <td style="white-space:nowrap">
          <a href="student_edit.php?id=<?= $s['id'] ?>" class="btn-outline" style="padding:.3rem .7rem;font-size:.8rem">
            <i class="bi bi-pencil"></i> Edit
          </a>
          <form method="POST" style="display:inline"
                onsubmit="return confirm('Delete this student record? This cannot be undone.')">
            <?= Auth::csrfField() ?>
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="student_id" value="<?= $s['id'] ?>">
            <button type="submit" class="btn-danger-custom" style="padding:.3rem .7rem;font-size:.8rem">
              <i class="bi bi-trash"></i> Delete
            </button>
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
    <?php else: ?>
      <tr>
        <td colspan="8">
          <div class="empty-state">
            <div class="empty-icon">📋</div>
            <p>No students found<?= $search ? ' for "' . h($search) . '"' : '' ?>.</p>
            <a href="student_add.php" class="btn-primary-custom">Add First Student</a>
          </div>
        </td>
      </tr>
    <?php endif; ?>
    </tbody>
  </table>

  <!-- Pagination -->
  <?php if ($pag['total_pages'] > 1): ?>
  <nav class="mt-3">
    <ul class="pagination">
      <li>
        <a href="?<?= $qstring ?>&page=<?= $pag['current']-1 ?>"
           class="page-link <?= !$pag['has_prev']?'disabled':'' ?>">
          <i class="bi bi-chevron-left"></i>
        </a>
      </li>
      <?php for ($p = max(1,$pag['current']-2); $p <= min($pag['total_pages'],$pag['current']+2); $p++): ?>
      <li>
        <a href="?<?= $qstring ?>&page=<?= $p ?>"
           class="page-link <?= $p===$pag['current']?'active':'' ?>"><?= $p ?></a>
      </li>
      <?php endfor; ?>
      <li>
        <a href="?<?= $qstring ?>&page=<?= $pag['current']+1 ?>"
           class="page-link <?= !$pag['has_next']?'disabled':'' ?>">
          <i class="bi bi-chevron-right"></i>
        </a>
      </li>
    </ul>
  </nav>
  <?php endif; ?>
  </div>
</div>
<?php
});
