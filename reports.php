<?php
require_once __DIR__ . '/../../includes/init.php';
require_once __DIR__ . '/../../includes/layout.php';

Auth::requireRole('admin');

// ── Daily (last 30 days) ───────────────────────────────────────────────────
$daily = DB::fetchAll(
    'SELECT DATE(created_at) AS day, COUNT(*) AS cnt
     FROM students
     WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
     GROUP BY DATE(created_at) ORDER BY day'
);

// ── Weekly (last 12 weeks) ─────────────────────────────────────────────────
$weekly = DB::fetchAll(
    'SELECT YEARWEEK(created_at,1) AS wk,
            MIN(DATE(created_at)) AS week_start,
            COUNT(*) AS cnt
     FROM students
     WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 WEEK)
     GROUP BY YEARWEEK(created_at,1) ORDER BY wk'
);

// ── Monthly (last 12 months) ───────────────────────────────────────────────
$monthly = DB::fetchAll(
    'SELECT DATE_FORMAT(created_at, "%Y-%m") AS mo,
            DATE_FORMAT(created_at, "%b %Y") AS mo_label,
            COUNT(*) AS cnt
     FROM students
     WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
     GROUP BY mo ORDER BY mo'
);

// ── By course ──────────────────────────────────────────────────────────────
$byCourse = DB::fetchAll(
    'SELECT course, COUNT(*) AS cnt FROM students GROUP BY course ORDER BY cnt DESC'
);

// ── By year ────────────────────────────────────────────────────────────────
$byYear = DB::fetchAll(
    'SELECT year, COUNT(*) AS cnt FROM students GROUP BY year ORDER BY year'
);

// ── Summary totals ─────────────────────────────────────────────────────────
$totals = DB::fetchOne(
    'SELECT
       COUNT(*) AS total,
       SUM(DATE(created_at)=CURDATE()) AS today,
       SUM(YEARWEEK(created_at,1)=YEARWEEK(NOW(),1)) AS this_week,
       SUM(DATE_FORMAT(created_at,"%Y-%m")=DATE_FORMAT(NOW(),"%Y-%m")) AS this_month
     FROM students'
);

dashboardLayout('Reports', function() use ($daily, $weekly, $monthly, $byCourse, $byYear, $totals) {
    ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>

<!-- Summary row -->
<div class="grid grid-4" style="margin-bottom:1.5rem">
  <?php
  $cards = [
    ['Today',      $totals['today'],      'blue',   'bi-calendar-day'],
    ['This Week',  $totals['this_week'],  'green',  'bi-calendar-week'],
    ['This Month', $totals['this_month'], 'orange', 'bi-calendar-month'],
    ['All Time',   $totals['total'],      'purple', 'bi-people'],
  ];
  foreach ($cards as [$label,$val,$color,$icon]):
  ?>
  <div 
    <div class="stat-card">
      <div class="stat-icon <?= $color ?>"><i class="bi <?= $icon ?>"></i></div>
      <div>
        <div class="stat-label"><?= $label ?></div>
        <div class="stat-value"><?= number_format((int)$val) ?></div>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<!-- Charts row 1 -->
<div class="grid grid-4" style="margin-bottom:1.5rem">
  <div 
    <div class="card">
      <div class="card-header-custom"><h5>📅 Daily Registrations (Last 30 Days)</h5></div>
      <div class="card-body"><canvas id="dailyChart" height="110"></canvas></div>
    </div>
  </div>
  <div 
    <div class="card">
      <div class="card-header-custom"><h5>🎓 Students by Year</h5></div>
      <div class="card-body"><canvas id="yearChart" height="110"></canvas></div>
    </div>
  </div>
</div>

<!-- Charts row 2 -->
<div class="grid grid-4" style="margin-bottom:1.5rem">
  <div 
    <div class="card">
      <div class="card-header-custom"><h5>📆 Monthly Registrations (Last 12 Months)</h5></div>
      <div class="card-body"><canvas id="monthlyChart" height="120"></canvas></div>
    </div>
  </div>
  <div 
    <div class="card">
      <div class="card-header-custom"><h5>📚 Students by Course</h5></div>
      <div class="card-body"><canvas id="courseChart" height="120"></canvas></div>
    </div>
  </div>
</div>

<!-- Data table -->
<div class="card">
  <div class="card-header-custom">
    <h5>📋 Monthly Breakdown</h5>
  </div>
  <div class="table-wrapper" style="padding:0 1.5rem 1.5rem">
  <table class="data-table">
    <thead><tr><th>Month</th><th>Registrations</th><th>% of Total</th></tr></thead>
    <tbody>
    <?php foreach (array_reverse($monthly) as $m): ?>
    <tr>
      <td><?= h($m['mo_label']) ?></td>
      <td><?= number_format((int)$m['cnt']) ?></td>
      <td>
        <?php $pct = $totals['total'] > 0 ? round($m['cnt']/$totals['total']*100,1) : 0; ?>
        <div style="display:flex;align-items:center;gap:.5rem">
          <div style="flex:1;height:8px;background:#f0f2f5;border-radius:4px">
            <div style="width:<?= $pct ?>%;height:100%;background:var(--primary);border-radius:4px"></div>
          </div>
          <span style="font-size:.82rem;color:#6c757d"><?= $pct ?>%</span>
        </div>
      </td>
    </tr>
    <?php endforeach; ?>
    <?php if (!$monthly): ?>
    <tr><td colspan="3" class="text-center text-muted py-4">No data yet.</td></tr>
    <?php endif; ?>
    </tbody>
  </table>
  </div>
</div>

<script>
const COLORS = ['#1a6fc4','#198754','#f57c00','#7b1fa2','#dc3545','#00838f','#e91e63'];

new Chart(document.getElementById('dailyChart'), {
  type:'line',
  data:{
    labels:<?= json_encode(array_column($daily,'day')) ?>,
    datasets:[{label:'Registrations',data:<?= json_encode(array_map('intval',array_column($daily,'cnt'))) ?>,
      borderColor:'#1a6fc4',backgroundColor:'rgba(26,111,196,.1)',tension:.3,fill:true,pointRadius:3}]
  },
  options:{responsive:true,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true,ticks:{stepSize:1}}}}
});

new Chart(document.getElementById('monthlyChart'), {
  type:'bar',
  data:{
    labels:<?= json_encode(array_column($monthly,'mo_label')) ?>,
    datasets:[{label:'Students',data:<?= json_encode(array_map('intval',array_column($monthly,'cnt'))) ?>,
      backgroundColor:'rgba(26,111,196,.75)',borderRadius:6}]
  },
  options:{responsive:true,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true,ticks:{stepSize:1}}}}
});

new Chart(document.getElementById('yearChart'), {
  type:'doughnut',
  data:{
    labels:<?= json_encode(array_map(fn($r)=>'Year '.$r['year'], $byYear)) ?>,
    datasets:[{data:<?= json_encode(array_map('intval',array_column($byYear,'cnt'))) ?>,
      backgroundColor:COLORS}]
  },
  options:{responsive:true,plugins:{legend:{position:'bottom',labels:{font:{size:11}}}}}
});

new Chart(document.getElementById('courseChart'), {
  type:'bar',
  data:{
    labels:<?= json_encode(array_column($byCourse,'course')) ?>,
    datasets:[{label:'Students',data:<?= json_encode(array_map('intval',array_column($byCourse,'cnt'))) ?>,
      backgroundColor:COLORS}]
  },
  options:{
    indexAxis:'y',responsive:true,
    plugins:{legend:{display:false}},
    scales:{x:{beginAtZero:true,ticks:{stepSize:1}}}
  }
});
</script>
<?php
});
