<?php
require_once __DIR__ . '/includes/init.php';
require_once __DIR__ . '/includes/layout.php';

if (Auth::check()) {
    redirect(Auth::userRole() === 'student' ? '/pages/student/dashboard.php' : '/pages/admin/dashboard.php');
}

$errors = [];
$vals   = ['firstName'=>'','lastName'=>'','email'=>'','role'=>'student'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Auth::verifyCsrf(post('csrf_token'))) {
        $errors[] = 'Invalid CSRF token. Please try again.';
    } else {
        $vals['firstName'] = sanitize(post('firstName'));
        $vals['lastName']  = sanitize(post('lastName'));
        $vals['email']     = sanitize(post('email'));
        $vals['role']      = in_array(post('role'), ['student','registrar']) ? post('role') : 'student';
        $password  = post('password');
        $password2 = post('password2');

        if (!$vals['firstName']) $errors[] = 'First name is required.';
        if (!$vals['lastName'])  $errors[] = 'Last name is required.';
        if (!filter_var($vals['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';
        if ($password !== $password2) $errors[] = 'Passwords do not match.';

        $pwError = Auth::validatePassword($password);
        if ($pwError) $errors[] = $pwError;

        if (!$errors) {
            // Check duplicate email
            $exists = DB::fetchOne('SELECT id FROM users WHERE email = ?', [$vals['email']]);
            if ($exists) {
                $errors[] = 'An account with this email already exists.';
            } else {
                $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
                DB::query(
                    'INSERT INTO users (firstName, lastName, email, password, role) VALUES (?,?,?,?,?)',
                    [$vals['firstName'], $vals['lastName'], $vals['email'], $hash, $vals['role']]
                );
                Auth::logActivity(null, 'register', 'New user registered: ' . $vals['email']);
                flash('login_success', 'Account created! Please sign in.', 'success');
                redirect('/index.php');
            }
        }
    }
}

authLayout('Register', function() use ($errors, $vals) {
    ?>
<h2 class="text-center mb-1" style="font-size:1.3rem;font-weight:700">Create Account</h2>
<p class="text-center text-muted mb-3" style="font-size:.9rem">Join the XWZ School SRS</p>

<?php foreach ($errors as $e): ?>
<div class="alert-error">
  <i class="bi bi-exclamation-circle"></i> <?= h($e) ?>
</div>
<?php endforeach; ?>

<form method="POST">
  <?= Auth::csrfField() ?>
  <div class="form-row">
    <div class="form-group">
      <label class="form-label">First Name</label>
      <input type="text" name="firstName" class="form-control" value="<?= h($vals['firstName']) ?>" required>
    </div>
    <div class="form-group">
      <label class="form-label">Last Name</label>
      <input type="text" name="lastName" class="form-control" value="<?= h($vals['lastName']) ?>" required>
    </div>
  </div>
  <div class="form-group">
    <label class="form-label">Email Address</label>
    <input type="email" name="email" class="form-control" value="<?= h($vals['email']) ?>" required>
  </div>
  <div class="form-group">
    <label class="form-label">Role</label>
    <select name="role" class="form-control">
      <option value="student"    <?= $vals['role']==='student'    ? 'selected' : '' ?>>Student</option>
      <option value="registrar"  <?= $vals['role']==='registrar'  ? 'selected' : '' ?>>Registrar</option>
    </select>
  </div>
  <div class="form-group">
    <label class="form-label">Password</label>
    <input type="password" name="password" class="form-control" id="pw1" required>
    <small class="text-muted" style="font-size:.75rem">Min 8 chars, uppercase, lowercase, number, special char</small>
  </div>
  <div class="form-group">
    <label class="form-label">Confirm Password</label>
    <input type="password" name="password2" class="form-control" id="pw2" required>
  </div>
  <button type="submit" class="btn-primary-custom btn-full">
    <i class="bi bi-person-plus"></i> Create Account
  </button>
</form>
<p class="text-center mt-3 text-muted" style="font-size:.82rem">
  Already have an account? <a href="index.php" style="color:var(--primary);text-decoration:none">Sign in</a>
</p>
<?php
});
