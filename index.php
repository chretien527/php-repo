<?php
require_once __DIR__ . '/includes/init.php';
require_once __DIR__ . '/includes/layout.php';

if (Auth::check()) {
    $role = Auth::userRole();
    redirect($role === 'student' ? '/pages/student/dashboard.php' : '/pages/admin/dashboard.php');
}

$errors = [];
$email  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Auth::verifyCsrf(post('csrf_token'))) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        $email    = sanitize(post('email'));
        $password = post('password');

        if (!$email || !$password) {
            $errors[] = 'Please enter both email and password.';
        } else {
            $result = Auth::login($email, $password);
            if ($result['success']) {
                redirect($result['role'] === 'student' ? '/pages/student/dashboard.php' : '/pages/admin/dashboard.php');
            } else {
                $errors[] = $result['message'];
            }
        }
    }
}

authLayout('Login', function() use ($errors, $email) {
    ?>
<h2 style="text-align:center;font-size:1.3rem;font-weight:700;margin:0 0 .25rem">Welcome Back</h2>
<p style="text-align:center;color:#64748b;font-size:.9rem;margin:0 0 1.5rem">Sign in to your account</p>

<?php foreach ($errors as $e): ?>
<div class="alert-error"><i class="bi bi-exclamation-circle"></i> <?= h($e) ?></div>
<?php endforeach; ?>

<form method="POST" action="">
  <?= Auth::csrfField() ?>
  <div class="form-group">
    <label class="form-label" for="email">Email Address</label>
    <input type="email" id="email" name="email" class="form-control"
           value="<?= h($email) ?>" placeholder="you@example.com" required autofocus>
  </div>
  <div class="form-group">
    <label class="form-label" for="password">Password</label>
    <input type="password" id="password" name="password" class="form-control"
           placeholder="••••••••" required>
  </div>
  <button type="submit" class="btn-primary-custom btn-full">
    <i class="bi bi-box-arrow-in-right"></i> Sign In
  </button>
</form>
<p style="text-align:center;margin-top:1rem;font-size:.82rem;color:#64748b">
  Don't have an account? <a href="register.php" style="color:var(--primary);text-decoration:none;font-weight:600">Register here</a>
</p>
<p style="text-align:center;margin-top:.5rem;font-size:.75rem;color:#94a3b8">
  Default admin: admin@xwzschool.ac / Admin@1234
</p>
<?php
});
