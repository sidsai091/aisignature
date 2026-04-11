<?php
require_once 'includes/auth.php';

if (is_logged_in()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = trim($_POST['username'] ?? '');
    $pass = $_POST['password'] ?? '';
    if ($user === ADMIN_USER && $pass === ADMIN_PASS) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_user']      = $user;
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Invalid username or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title>Admin Login — Ameerul Iskandar</title>
  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="assets/admin.css"/>
</head>
<body class="login-body">

  <div class="login-wrap">
    <div class="login-card">
      <div class="login-logo">Ameerul<span>Iskandar</span></div>
      <p class="login-subtitle">Admin Dashboard</p>
      <div class="login-divider"></div>

      <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form method="POST" action="login.php" class="login-form">
        <div class="form-group">
          <label for="username">Username</label>
          <input type="text" id="username" name="username" placeholder="admin"
                 value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" autocomplete="username" required/>
        </div>
        <div class="form-group">
          <label for="password">Password</label>
          <div class="input-pw-wrap">
            <input type="password" id="password" name="password" placeholder="••••••••"
                   autocomplete="current-password" required/>
            <button type="button" class="pw-toggle" onclick="togglePw()" aria-label="Show password">
              <svg id="eyeIcon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
              </svg>
            </button>
          </div>
        </div>
        <button type="submit" class="btn-login">Sign In</button>
      </form>

      <p class="login-hint">Demo: <code>admin</code> / <code>admin123</code></p>
    </div>
  </div>

  <script>
    function togglePw() {
      const pw = document.getElementById('password');
      pw.type = pw.type === 'password' ? 'text' : 'password';
    }
  </script>
</body>
</html>
