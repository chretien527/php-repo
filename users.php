<?php
require_once __DIR__ . '/../../includes/init.php';
require_once __DIR__ . '/../../includes/layout.php';

Auth::requireRole('admin');

// ── Handle actions ─────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Auth::verifyCsrf(post('csrf_token'))) {
        flash('main', 'Invalid CSRF token.', 'error');
        redirect('/pages/admin/users.php');
    }

    $action = post('action');
    $uid    = (int)post('user_id');

    if ($action === 'toggle_active' && $uid !== Auth::userId()) {
        $u = DB::fetchOne('SELECT is_active FROM users WHERE id = ?', [$uid]);
        if ($u) {
            $new = $u['is_active'] ? 0 : 1;
            DB::query('UPDATE users SET is_active=? WHERE id=?', [$new, $uid]);
            flash('main', 'User status updated.', 'success');
            Auth::logActivity(Auth::userId(), 'toggle_user', "User $uid set active=$new");
        }
    } elseif ($action === 'change_role' && $uid !== Auth::userId()) {
        $role = in_array(post('role'),['admin','registrar','student']) ? post('role') : null;
        if ($role) {
            DB::query('UPDATE users SET role=? WHERE id=?', [$role, $uid]);
            flash('main', 'User role updated.', 'success');
            Auth::logActivity(Auth::userId(), 'change_role', "User $uid role => $role");
        }
    } elseif ($action === 'reset_password' && $uid) {
        $newPw  = post('new_password');
        $pwErr  = Auth::validatePassword($newPw);
        if ($pwErr) {
            flash('main', $pwErr, 'error');
        } else {
            $hash = password_hash($newPw, PASSWORD_BCRYPT, ['cost'=>12]);
            DB::query('UPDATE users SET password=? WHERE id=?', [$hash, $uid]);
            flash('main', 'Password reset successfully.', 'success');
            Auth::logActivity(Auth::userId(), 'reset_password', "Reset password for user $uid");
        }
    } elseif ($action === 'delete' && $uid !== Auth::userId()) {
        DB::query('DELETE FROM users WHERE id=?', [$uid]);
        flash('main', 'User deleted.', 'success');
        Auth::logActivity(Auth::userId(), 'delete_user', "Deleted user $uid");
    }
    redirect('/pages/admin/users.php');
}

$users = DB::fetchAll(
    'SELECT u.*, (SELECT COUNT(*) FROM students s WHERE s.registered_by = u.id) AS reg_count
     FROM users u ORDER BY u.created_at DESC'
);

dashboardLayout('Manage Users', function() use ($users) {
    showFlash('main');
    ?>
<div class="card">
  <div class="card-header-custom">
    <h5><i class="bi bi-person-gear"></i>System Users (<?= count($users) ?>)</h5>
    <a href="../../register.php" class="btn-primary-custom">
      <i class="bi bi-person-plus"></i> Add User
    </a>
  </div>
  <div class="table-wrapper" style="padding:0 1.5rem 1.5rem">
  <table class="data-table">
    <thead>
      <tr><th>#</th><th>Name</th><th>Email</th><th>Role</th><th>Students Reg.</th><th>Status</th><th>Joined</th><th>Actions</th></tr>
    </thead>
    <tbody>
    <?php $n=1; foreach ($users as $u): ?>
    <tr>
      <td class="text-muted"><?= $n++ ?></td>
      <td><?= h($u['firstName'].' '.$u['lastName']) ?></td>
      <td><?= h($u['email']) ?></td>
      <td>
        <form method="POST" style="display:inline">
          <?= Auth::csrfField() ?>
          <input type="hidden" name="action" value="change_role">
          <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
          <select name="role" class="form-control" style="padding:.2rem .5rem;font-size:.82rem;width:auto;display:inline-block"
                  onchange="this.form.submit()" <?= $u['id']===Auth::userId()?'disabled':'' ?>>
            <?php foreach (['admin','registrar','student'] as $r): ?>
            <option value="<?= $r ?>" <?= $u['role']===$r?'selected':'' ?>><?= ucfirst($r) ?></option>
            <?php endforeach; ?>
          </select>
        </form>
      </td>
      <td><?= $u['reg_count'] ?></td>
      <td>
        <form method="POST" style="display:inline">
          <?= Auth::csrfField() ?>
          <input type="hidden" name="action" value="toggle_active">
          <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
          <button type="submit" class="badge-role <?= $u['is_active']?'badge-registrar':'badge-student' ?>"
                  style="border:none;cursor:pointer" <?= $u['id']===Auth::userId()?'disabled':'' ?>>
            <?= $u['is_active'] ? '✅ Active' : '🚫 Inactive' ?>
          </button>
        </form>
      </td>
      <td><?= fmtDate($u['created_at']) ?></td>
      <td style="white-space:nowrap">
        <!-- Reset password modal trigger -->
        <button class="btn-outline" style="padding:.3rem .7rem;font-size:.8rem"
                onclick="showResetModal(<?= $u['id'] ?>, '<?= h($u['firstName']) ?>')">
          <i class="bi bi-key"></i> Reset PW
        </button>
        <?php if ($u['id'] !== Auth::userId()): ?>
        <form method="POST" style="display:inline"
              onsubmit="return confirm('Delete user <?= h($u['firstName']) ?>? This cannot be undone.')">
          <?= Auth::csrfField() ?>
          <input type="hidden" name="action" value="delete">
          <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
          <button type="submit" class="btn-danger-custom" style="padding:.3rem .7rem;font-size:.8rem">
            <i class="bi bi-trash"></i>
          </button>
        </form>
        <?php endif; ?>
      </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  </div>
</div>

<!-- Reset password modal -->
<div id="resetModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:999;align-items:center;justify-content:center">
  <div style="background:#fff;border-radius:12px;padding:2rem;width:100%;max-width:400px;margin:1rem">
    <h5 style="margin-top:0">Reset Password for <span id="resetName"></span></h5>
    <form method="POST" id="resetForm">
      <?= Auth::csrfField() ?>
      <input type="hidden" name="action" value="reset_password">
      <input type="hidden" name="user_id" id="resetUserId">
      <div class="form-group">
        <label class="form-label">New Password</label>
        <input type="password" name="new_password" class="form-control" id="resetPw" required>
        <small class="text-muted" style="font-size:.75rem">Min 8 chars, upper, lower, number, special</small>
      </div>
      <div class="d-flex gap-2 mt-3">
        <button type="submit" class="btn-primary-custom"><i class="bi bi-check"></i> Reset</button>
        <button type="button" class="btn-outline" onclick="closeResetModal()">Cancel</button>
      </div>
    </form>
  </div>
</div>
<script>
function showResetModal(uid, name) {
  document.getElementById('resetUserId').value = uid;
  document.getElementById('resetName').textContent = name;
  document.getElementById('resetPw').value = '';
  document.getElementById('resetModal').style.display = 'flex';
}
function closeResetModal() {
  document.getElementById('resetModal').style.display = 'none';
}
</script>
<?php
});
