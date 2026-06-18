<?php
require_once __DIR__ . '/../../includes/init.php';
require_once __DIR__ . '/../../includes/layout.php';

Auth::requireRole(['admin','registrar']);

$id = (int)get('id');
if (!$id) redirect('/pages/admin/students.php');

$student = DB::fetchOne('SELECT * FROM students WHERE id = ?', [$id]);
if (!$student) { flash('main','Student not found.','error'); redirect('/pages/admin/students.php'); }

$errors = [];
$vals = $student;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Auth::verifyCsrf(post('csrf_token'))) {
        $errors[] = 'Invalid CSRF token.';
    } else {
        foreach (['studentId','name','course','year','contact','email','address'] as $k) $vals[$k] = sanitize(post($k));
        if (!$vals['studentId']) $errors[] = 'Student ID is required.';
        if (!$vals['name'])      $errors[] = 'Full name is required.';
        if (!$vals['course'])    $errors[] = 'Course is required.';
        if (!in_array($vals['year'],['1','2','3','4','5'])) $errors[] = 'Invalid year.';
        if (!$vals['contact'])   $errors[] = 'Contact is required.';
        if ($vals['email'] && !filter_var($vals['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email.';

        if (!$errors) {
            $dup = DB::fetchOne('SELECT id FROM students WHERE studentId = ? AND id != ?', [$vals['studentId'], $id]);
            if ($dup) {
                $errors[] = 'Student ID "' . h($vals['studentId']) . '" is already used by another student.';
            } else {
                DB::query('UPDATE students SET studentId=?, name=?, course=?, year=?, contact=?, email=?, address=?, updated_at=NOW() WHERE id=?',
                    [$vals['studentId'],$vals['name'],$vals['course'],$vals['year'],$vals['contact'],$vals['email'],$vals['address'],$id]);
                Auth::logActivity(Auth::userId(), 'edit_student', "Updated student ID $id");
                flash('main', 'Student record updated successfully.', 'success');
                redirect('/pages/admin/students.php');
            }
        }
    }
}

$courses = ['Computer Science','Information Technology','Software Engineering',
  'Business Administration','Accounting','Economics',
  'Civil Engineering','Electrical Engineering','Mechanical Engineering',
  'Medicine','Nursing','Pharmacy','Law','Education','Psychology'];

dashboardLayout('Edit Student', function() use ($errors, $vals, $id, $courses) {
    ?>
<div class="centered-form">
<div class="card">
  <div class="card-header-custom">
    <h5><i class="bi bi-pencil"></i> Edit Student Record</h5>
    <a href="students.php" class="btn-outline" style="padding:.35rem .9rem;font-size:.82rem">
      <i class="bi bi-arrow-left"></i> Back
    </a>
  </div>
  <div class="card-body">

  <?php foreach ($errors as $e): ?>
  <div class="alert-error"><i class="bi bi-exclamation-circle"></i> <?= h($e) ?></div>
  <?php endforeach; ?>

  <form method="POST">
    <?= Auth::csrfField() ?>

    <div class="form-row">
      <div class="form-group">
        <label class="form-label">Student ID <span style="color:var(--danger)">*</span></label>
        <input type="text" name="studentId" class="form-control" value="<?= h($vals['studentId']) ?>" required>
      </div>
      <div class="form-group">
        <label class="form-label">Full Name <span style="color:var(--danger)">*</span></label>
        <input type="text" name="name" class="form-control" value="<?= h($vals['name']) ?>" required>
      </div>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label class="form-label">Course / Programme <span style="color:var(--danger)">*</span></label>
        <select name="course" class="form-control" required>
          <?php foreach ($courses as $c): ?>
          <option value="<?= h($c) ?>" <?= $vals['course']===$c?'selected':'' ?>><?= h($c) ?></option>
          <?php endforeach; ?>
          <?php if (!in_array($vals['course'], $courses)): ?>
          <option value="<?= h($vals['course']) ?>" selected><?= h($vals['course']) ?></option>
          <?php endif; ?>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Contact <span style="color:var(--danger)">*</span></label>
        <input type="tel" name="contact" class="form-control" value="<?= h($vals['contact']) ?>" required>
      </div>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label class="form-label">Year <span style="color:var(--danger)">*</span></label>
        <select name="year" class="form-control" required>
          <?php foreach (['1','2','3','4','5'] as $y): ?>
          <option value="<?= $y ?>" <?= $vals['year']===$y?'selected':'' ?>>Year <?= $y ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Email <small style="color:#94a3b8">(optional)</small></label>
        <input type="email" name="email" class="form-control" value="<?= h($vals['email']) ?>">
      </div>
    </div>

    <div class="form-group">
      <label class="form-label">Address</label>
      <textarea name="address" class="form-control" rows="2"><?= h($vals['address']) ?></textarea>
    </div>

    <div style="display:flex;gap:.75rem;margin-top:1.25rem">
      <button type="submit" class="btn-primary-custom">
        <i class="bi bi-check-circle"></i> Save Changes
      </button>
      <a href="students.php" class="btn-outline">Cancel</a>
    </div>
  </form>
  </div>
</div>
</div>
<?php
});
