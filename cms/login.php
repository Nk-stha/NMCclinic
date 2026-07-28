<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';

start_cms_session();

try {
    if (current_admin() !== null) {
        header('Location: dashboard.php');
        exit;
    }
} catch (Throwable $exception) {
    // Allow the login page to render even before the schema is imported.
}

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if (!csrf_is_valid($_POST['csrf_token'] ?? null)) {
        $error = 'Invalid request. Please reload the page and try again.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
        $error = 'Enter a valid email and password.';
    } else {
        try {
            if (login_admin($email, $password)) {
                header('Location: dashboard.php');
                exit;
            }

            $error = 'Invalid email or password.';
        } catch (Throwable $exception) {
            $error = 'Login is not available. Please check the database configuration.';
        }
    }
}

$token = csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Login | NMC Clinic CMS</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Public+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
:root{--bg:#f8fafa;--surface:#fff;--soft:#f2f4f4;--border:#bec9c8;--text:#191c1d;--muted:#3f4948;--primary:#005051;--primary2:#136a6a;--warn:#fff8d9;--warn-text:#5d4200;--error:#93000a;--error-bg:#ffdad6;--shadow:0 18px 52px rgba(19,106,106,.12)}
*{box-sizing:border-box} body{min-height:100vh;margin:0;display:flex;flex-direction:column;background:linear-gradient(135deg,#f8fafa,#edf5f4);color:var(--text);font-family:"Public Sans",Arial,sans-serif}
.login-header,.login-footer{padding:18px 40px}.inner{width:min(100%,1180px);margin:auto;display:flex;align-items:center;justify-content:space-between;gap:20px}
.brand{display:flex;align-items:center;gap:12px;color:var(--primary);font-weight:800;text-decoration:none}.brand img{width:42px;height:42px;border-radius:50%;object-fit:contain;background:var(--surface);border:1px solid var(--border);padding:4px}.brand small{display:block;color:var(--muted);font-size:12px}
.secure{color:var(--muted);font:600 14px "Inter",Arial,sans-serif}.login-main{flex:1;display:flex;align-items:center;justify-content:center;padding:32px 16px 48px}
.card{width:min(100%,440px);padding:40px;background:rgba(255,255,255,.96);border:1px solid rgba(190,201,200,.55);border-radius:12px;box-shadow:var(--shadow)}
.card-head{text-align:center;margin-bottom:30px}.logo{width:82px;height:82px;margin:0 auto 18px;display:grid;place-items:center;background:var(--soft);border:1px solid var(--border);border-radius:50%;overflow:hidden}.logo img{width:100%;height:100%;object-fit:contain;padding:8px}
h1{margin:0 0 8px;font-size:32px;line-height:1.2;letter-spacing:0}.subtitle{margin:0;color:var(--muted);font-size:16px;line-height:1.5}.form{display:grid;gap:20px}.field{display:grid;gap:8px}.field label{color:var(--muted);font:700 14px "Inter",Arial,sans-serif}
.input-wrap{position:relative}.input-icon{position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#6f7979;font-weight:800}.input{width:100%;min-height:50px;padding:13px 44px 13px 42px;border:1px solid var(--border);border-radius:8px;background:var(--bg);color:var(--text);font:500 16px "Public Sans",Arial,sans-serif;outline:none}.input:focus{background:var(--surface);border-color:var(--primary);box-shadow:0 0 0 3px rgba(0,80,81,.12)}
.toggle{position:absolute;right:9px;top:8px;width:42px;height:34px;border:0;border-radius:8px;background:transparent;color:var(--muted);cursor:pointer;font-weight:800}.toggle:hover{background:#e8f6f5;color:var(--primary)}
.submit{width:100%;min-height:52px;border:0;border-radius:8px;background:var(--primary);color:#fff;cursor:pointer;font:800 14px "Inter",Arial,sans-serif;letter-spacing:.04em;text-transform:uppercase}.submit:hover{background:var(--primary2)}
.alert{border-radius:8px;padding:12px 14px;font-size:14px;line-height:1.45}.alert.error{border:1px solid #ffb4ab;background:var(--error-bg);color:var(--error)}.badge{text-align:center;color:rgba(63,73,72,.72);font:800 12px "Inter",Arial,sans-serif;letter-spacing:.08em;text-transform:uppercase}
.login-footer{border-top:1px solid rgba(190,201,200,.55);background:rgba(255,255,255,.8)}.foot{color:var(--muted);font:600 13px "Inter",Arial,sans-serif}.links{display:flex;gap:18px;color:var(--muted);font:600 13px "Inter",Arial,sans-serif}
@media(max-width:640px){.login-header,.login-footer{padding:16px}.inner{align-items:flex-start;flex-direction:column}.secure{display:none}.card{padding:28px 22px}h1{font-size:28px}}
</style>
</head>
<body>
<header class="login-header">
  <div class="inner">
    <a class="brand" href="../index.html" aria-label="NMC Clinic home">
      <img src="../uploads/logo.jpeg" alt="NMC Clinic logo">
      <span>NMC Clinic<small>CMS</small></span>
    </a>
    <span class="secure">Admin access</span>
  </div>
</header>

<main class="login-main">
  <section class="card" aria-labelledby="login-title">
    <div class="card-head">
      <div class="logo"><img src="../uploads/logo.jpeg" alt="NMC Clinic logo"></div>
      <h1 id="login-title">Welcome back</h1>
      <p class="subtitle">Sign in to manage NMC Clinic CMS</p>
    </div>

    <form class="form" method="post" action="login.php" novalidate>
      <input type="hidden" name="csrf_token" value="<?= e($token) ?>">

      <?php if ($error !== ''): ?>
        <div class="alert error" role="alert"><?= e($error) ?></div>
      <?php endif; ?>

      <div class="field">
        <label for="email">Work Email</label>
        <div class="input-wrap">
          <span class="input-icon" aria-hidden="true">@</span>
          <input class="input" id="email" name="email" type="email" autocomplete="username" value="<?= e($email) ?>" placeholder="name@nmcclinic.com" required>
        </div>
      </div>

      <div class="field">
        <label for="password">Password</label>
        <div class="input-wrap">
          <span class="input-icon" aria-hidden="true">•</span>
          <input class="input" id="password" name="password" type="password" autocomplete="current-password" placeholder="Password" required>
          <button class="toggle" type="button" id="passwordToggle" aria-label="Show password">Show</button>
        </div>
      </div>

      <button class="submit" type="submit">Sign In</button>
      <div class="badge">Secure admin login</div>
    </form>
  </section>
</main>

<footer class="login-footer">
  <div class="inner">
    <div class="foot">© <?= date('Y') ?> NMC Clinic. All rights reserved.</div>
    <div class="links" aria-label="CMS support links"><span>Help Center</span><span>Privacy Policy</span><span>Terms of Service</span></div>
  </div>
</footer>

<script>
(function () {
  var password = document.getElementById('password');
  var toggle = document.getElementById('passwordToggle');
  if (!password || !toggle) return;

  toggle.addEventListener('click', function () {
    var visible = password.type === 'text';
    password.type = visible ? 'password' : 'text';
    toggle.textContent = visible ? 'Show' : 'Hide';
    toggle.setAttribute('aria-label', visible ? 'Show password' : 'Hide password');
  });
})();
</script>
</body>
</html>
