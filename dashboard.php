<?php
require_once __DIR__ . '/../../includes/init.php';
require_once __DIR__ . '/../../includes/layout.php';

Auth::requireRole(['admin','registrar']);

$totalStudents  = (int)(DB::fetchOne('SELECT COUNT(*) AS c FROM students')['c'] ?? 0);
$totalUsers     = (int)(DB::fetchOne('SELECT COUNT(*) AS c FROM users')['c'] ?? 0);
$todayStudents  = (int)(DB::fetchOne('SELECT COUNT(*) AS c FROM students WHERE DATE(created_at) = CURDATE()')['c'] ?? 0);
$weekStudents   = (int)(DB::fetchOne('SELECT COUNT(*) AS c FROM students WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)')['c'] ?? 0);

$dailyData = DB::fetchAll(
    'SELECT DATE(created_at) AS day, COUNT(*) AS cnt FROM students
     WHERE created_at >= DATE_SUB(NOW(), INTERVAL 14 DAY)
     GROUP BY DATE(created_at) ORDER BY day ASC'
);

$recentStudents = DB::fetchAll(
    'SELECT s.*, u.firstName AS reg_first, u.lastName AS reg_last
     FROM students s LEFT JOIN users u ON u.id = s.registered_by
     ORDER BY s.created_at DESC LIMIT 8'
);

dashboardLayout('Dashboard', function() use (
    $totalStudents, $totalUsers, $todayStudents, $weekStudents, $dailyData, $recentStudents
) {
    showFlash('login_success');
    showFlash('main');
    ?>

<div class="grid grid-4" style="margin-bottom:1.5rem">
  <div class="stat-card">
    <div class="stat-icon blue"><i class="bi bi-people"></i></div>
    <div><div class="stat-label">Total Students</div><div class="stat-value"><?= number_format($totalStudents) ?></div></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon green"><i class="bi bi-person-check"></i></div>
    <div><div class="stat-label">Registered Today</div><div class="stat-value"><?= $todayStudents ?></div></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon orange"><i class="bi bi-calendar-week"></i></div>
    <div><div class="stat-label">This Week</div><div class="stat-value"><?= $weekStudents ?></div></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon purple"><i class="bi bi-person-gear"></i></div>
    <div><div class="stat-label">System Users</div><div class="stat-value"><?= $totalUsers ?></div></div>
  </div>
</div>

<div class="grid grid-2-1" style="margin-bottom:1.5rem">
  <div class="card">
    <div class="card-header-custom">
      <h5><i class="bi bi-bar-chart"></i> Registrations – Last 14 Days</h5>
    </div>
    <div class="card-body">
      <canvas id="regChart" height="100"></canvas>
    </div>
  </div>
  <div class="card">
    <div class="card-header-custom"><h5>Quick Actions</h5></div>
    <div class="card-body" style="display:flex;flex-direction:column;gap:.65rem">
      <a href="student_add.php" class="btn-primary-custom" style="justify-content:center">
        <i class="bi bi-person-plus"></i> Add New Student
      </a>
      <a href="students.php" class="btn-outline" style="justify-content:center">
        <i class="bi bi-people"></i> View All Students
      </a>
      <?php if (Auth::hasRole('admin')): ?>
      <a href="reports.php" class="btn-outline" style="justify-content:center">
        <i class="bi bi-bar-chart"></i> Generate Report
      </a>
      <a href="users.php" class="btn-outline" style="justify-content:center">
        <i class="bi bi-person-gear"></i> Manage Users
      </a>
      <?php endif; ?>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-header-custom">
    <h5><i class="bi bi-clock-history"></i> Recently Registered</h5>
    <a href="students.php" class="btn-outline" style="padding:.35rem .9rem;font-size:.82rem">View All</a>
  </div>
  <div class="card-body" style="padding:0">
    <div class="table-wrapper">
    <table class="data-table">
      <thead><tr><th>Student ID</th><th>Name</th><th>Course</th><th>Year</th><th>Registered</th><th>By</th></tr></thead>
      <tbody>
      <?php if ($recentStudents): ?>
        <?php foreach ($recentStudents as $s): ?>
        <tr>
          <td><?= h($s['studentId']) ?></td>
          <td><?= h($s['name']) ?></td>
          <td><?= h($s['course']) ?></td>
          <td>Year <?= h($s['year']) ?></td>
          <td><?= fmtDateTime($s['created_at']) ?></td>
          <td><?= h($s['reg_first'].' '.$s['reg_last']) ?></td>
        </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr><td colspan="6" style="text-align:center;color:#94a3b8;padding:2rem">No students registered yet.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
<script>
const labels = <?= json_encode(array_column($dailyData, 'day')) ?>;
const data   = <?= json_encode(array_map('intval', array_column($dailyData, 'cnt'))) ?>;
new Chart(document.getElementById('regChart'), {
  type: 'bar',
  data: { labels, datasets: [{ label: 'Students Registered', data,
      backgroundColor: 'rgba(22,163,74,.7)', borderColor: 'rgba(22,163,74,1)',
      borderWidth: 1, borderRadius: 6 }] },
  options: { responsive: true, plugins: { legend: { display: false } },
    scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
});
</script>
<?php
});
