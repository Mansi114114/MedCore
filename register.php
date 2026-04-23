<?php
session_start();
include 'db.php';

$error = "";
$login_error = "";

if (isset($_POST['submit_register'])) {
    $name     = trim($_POST['name']);
    $email    = trim($_POST['email']);
    $password = $_POST['password'];
    $role     = $_POST['role'];

    $check = mysqli_query($conn, "SELECT id FROM users WHERE email='".mysqli_real_escape_string($conn,$email)."'");

    if (mysqli_num_rows($check) > 0) {
        $error = "This email is already registered.";
    } else {
        $query = "INSERT INTO users (name, email, password, role) VALUES (
            '".mysqli_real_escape_string($conn,$name)."',
            '".mysqli_real_escape_string($conn,$email)."',
            '$password',
            '".mysqli_real_escape_string($conn,$role)."'
        )";

        if (mysqli_query($conn, $query)) {
            $_SESSION['user'] = $name;
            $_SESSION['role'] = $role;

            if($role == 'admin'){
                header("Location: admin_dashboard.php");
            } elseif($role == 'doctor'){
                header("Location: doctor_dashboard.php");
            } else {
                header("Location: patient_dashboard.php");
            }
            exit();
        } else {
            $error = "Something went wrong";
        }
    }
}

if (isset($_POST['submit_login'])) {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $query = mysqli_query($conn, "
        SELECT * FROM users 
        WHERE email = '".mysqli_real_escape_string($conn,$email)."'
    ");

    if (mysqli_num_rows($query) == 1) {
        $row = mysqli_fetch_assoc($query);

        if ($password == $row['password']) {

            $_SESSION['user'] = $row['name'];
            $_SESSION['role'] = $row['role'];

            if($row['role'] == 'admin'){
                header("Location: admin_dashboard.php");
            } elseif($row['role'] == 'doctor'){
                header("Location: doctor_dashboard.php");
            } else {
                header("Location: patient_dashboard.php");
            }
            exit();

        } else {
            $login_error = "Incorrect password";
        }

    } else {
        $login_error = "Email not found";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>MedCore – Register</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">

  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 2rem 1rem;
      background:#f0f4ff;
      font-family: 'DM Sans', sans-serif;
      position: relative;
      overflow: hidden;
    }

    /* ── Background blobs ── */
    body::before, body::after {
      content: '';
      position: fixed;
      border-radius: 50%;
      pointer-events: none;
      z-index: 0;
    }
    body::before {
      width: 500px; height: 500px;
      background: radial-gradient(circle, rgba(139,92,246,0.12) 0%, transparent 65%);
      top: -180px; left: -120px;
    }
    body::after {
      width: 420px; height: 420px;
      background: radial-gradient(circle, rgba(236,72,153,0.1) 0%, transparent 65%);
      bottom: -120px; right: -80px;
    }
    .blob3 {
      position: fixed;
      width: 300px; height: 300px; border-radius: 50%;
      background: radial-gradient(circle, rgba(6,182,212,0.1) 0%, transparent 65%);
      top: 30%; right: 20%; pointer-events: none; z-index: 0;
    }

    /* ── Card ── */
    .card {
      position: relative; z-index: 2;
      display: flex;
      width: 860px; max-width: 100%; min-height: 490px;
      border-radius: 32px; overflow: hidden;
      box-shadow: 0 30px 80px rgba(99,102,241,0.15), 0 2px 0 rgba(255,255,255,0.9) inset;
      animation: fadeUp 0.6s cubic-bezier(.22,1,.36,1) both;
    }
    @keyframes fadeUp {
      from { opacity: 0; transform: translateY(24px); }
      to   { opacity: 1; transform: none; }
    }

    /* ── Left Panel ── */
    .panel-left {
      width: 42%; flex-shrink: 0;
      position: relative; overflow: hidden;
      background: linear-gradient(145deg, #6366f1 0%, #8b5cf6 50%, #a78bfa 100%);
      padding: 36px 32px;
      display: flex; flex-direction: column;
    }
    .pl-shine {
      position: absolute; width: 260px; height: 260px; border-radius: 50%;
      background: rgba(255,255,255,0.12);
      top: -60px; left: -60px; pointer-events: none;
    }
    .pl-shine2 {
      position: absolute; width: 200px; height: 200px; border-radius: 50%;
      background: rgba(255,255,255,0.08);
      bottom: -40px; right: -40px; pointer-events: none;
    }
    .pl-ring {
      position: absolute; border-radius: 50%;
      border: 1px solid rgba(255,255,255,0.15);
    }

    .logo-row { display: flex; align-items: center; gap: 10px; position: relative; z-index: 1; }
    .logo-icon {
      width: 36px; height: 36px; border-radius: 10px;
      background: rgba(255,255,255,0.25);
      display: flex; align-items: center; justify-content: center;
    }
    .logo-icon svg { width: 18px; height: 18px; }
    .logo-text { font-family: 'Syne', sans-serif; font-weight: 700; font-size: 16px; color: #fff; }
    .logo-sub  { font-size: 11px; color: rgba(255,255,255,0.65); font-weight: 300; margin-top: 1px; }

    .left-body { position: relative; z-index: 1; margin-top: auto; }

    .badge {
      display: inline-flex; align-items: center; gap: 6px;
      background: rgba(255,255,255,0.18);
      border: 1px solid rgba(255,255,255,0.3);
      border-radius: 20px; padding: 5px 12px; margin-bottom: 14px;
    }
    .badge-dot {
      width: 6px; height: 6px; border-radius: 50%; background: #fff;
      animation: blink 2s infinite;
    }
    @keyframes blink { 0%,100%{opacity:1;} 50%{opacity:0.4;} }
    .badge span { font-size: 11px; color: #fff; font-weight: 500; letter-spacing: 0.4px; }

    .hl-title {
      font-family: 'Syne', sans-serif; font-size: 28px; font-weight: 800;
      color: #fff; line-height: 1.2; letter-spacing: -0.5px;
    }
    .hl-title em { font-style: normal; color: rgba(255,255,255,0.75); }
    .hl-desc { font-size: 13px; color: rgba(255,255,255,0.7); margin-top: 10px; line-height: 1.7; font-weight: 300; }

    /* Wave divider */
    .wave { position: absolute; top: 0; right: -1px; bottom: 0; width: 54px; overflow: hidden; z-index: 3; }
    .wave svg { position: absolute; right: 0; top: 0; height: 100%; }

    /* ── Right Panel ── */
    .panel-right {
      flex: 1; background: #fff;
      display: flex; align-items: center; justify-content: center;
      padding: 36px 30px;
    }
    .form-wrap { width: 100%; max-width: 310px; }

    /* Tabs */
    .tabs {
      display: flex; background: #f1f5f9;
      border-radius: 14px; padding: 4px; gap: 4px; margin-bottom: 26px;
    }
    .tab {
      flex: 1; padding: 9px; border: none; border-radius: 10px;
      font-family: 'DM Sans', sans-serif; font-size: 13px; font-weight: 500;
      cursor: pointer; transition: all 0.25s;
      background: transparent; color: #94a3b8;
    }
    .tab.active {
      background: #fff; color: #6366f1; font-weight: 600;
      box-shadow: 0 2px 12px rgba(99,102,241,0.15);
    }

    .fh-title { font-family: 'Syne', sans-serif; font-size: 22px; font-weight: 800; color: #1e1b4b; letter-spacing: -0.3px; }
    .fh-sub   { font-size: 13px; color: #94a3b8; margin-top: 4px; font-weight: 300; margin-bottom: 20px; }

    /* Fields */
    .field { margin-bottom: 14px; }
    .field label {
      display: block; font-size: 11px; font-weight: 600; color: #6366f1;
      letter-spacing: 0.4px; text-transform: uppercase; margin-bottom: 6px;
    }
    .field-wrap { position: relative; }
    .fic {
      position: absolute; left: 12px; top: 50%; transform: translateY(-50%);
      width: 15px; height: 15px; opacity: 0.4; pointer-events: none;
    }
    .field-wrap input,
    .field-wrap select {
      width: 100%; padding: 11px 12px 11px 36px;
      background: #f8faff; border: 1.5px solid #e2e8f7; border-radius: 12px;
      font-family: 'DM Sans', sans-serif; font-size: 13px; color: #1e1b4b;
      outline: none; transition: border-color 0.2s, background 0.2s;
    }
    .field-wrap select { padding-left: 12px; color: #475569; }
    .field-wrap select option { color: #1e1b4b; }
    .field-wrap input::placeholder { color: #c0c9e0; }
    .field-wrap input:focus,
    .field-wrap select:focus {
      border-color: #6366f1; background: #fafaff;
      box-shadow: 0 0 0 3px rgba(99,102,241,0.08);
    }

    /* Button */
    .btn {
      width: 100%; padding: 13px; border: none; border-radius: 13px;
      font-family: 'Syne', sans-serif; font-size: 14px; font-weight: 700;
      color: #fff; cursor: pointer; margin-top: 4px;
      background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
      box-shadow: 0 6px 20px rgba(99,102,241,0.35);
      transition: transform 0.15s, box-shadow 0.2s;
    }
    .btn:hover   { transform: translateY(-1px); box-shadow: 0 10px 28px rgba(99,102,241,0.4); }
    .btn:active  { transform: scale(0.98); }

    /* Bottom link */
    .bottom { text-align: center; margin-top: 16px; font-size: 12px; color: #94a3b8; }
    .bottom a { color: #6366f1; font-weight: 600; text-decoration: none; }
    .bottom a:hover { color: #4f46e5; }

    /* Messages */
    .msg {
      margin-top: 10px; font-size: 12px; font-weight: 500;
      padding: 9px 12px; border-radius: 10px; text-align: center;
    }
    .msg-success { color: #059669; background: #ecfdf5; border: 1px solid #a7f3d0; }
    .msg-error   { color: #dc2626; background: #fef2f2; border: 1px solid #fecaca; }

    /* Pane switching */
    .pane { display: none; }
    .pane.active { display: block; }

    /* Responsive */
    @media (max-width: 640px) {
      .card { flex-direction: column; }
      .panel-left { width: 100%; min-height: 200px; padding: 24px; }
      .notif { display: none; }
      .wave { display: none; }
      .stats { gap: 8px; }
    }
  </style>
</head>

<body>
<div class="blob3"></div>

<div class="card">

  <!-- ═══════════ LEFT PANEL ═══════════ -->
  <div class="panel-left">
    <div class="pl-shine"></div>
    <div class="pl-shine2"></div>
    <div class="pl-ring" style="width:180px;height:180px;top:-60px;right:20px;"></div>
    <div class="pl-ring" style="width:100px;height:100px;bottom:80px;left:10px;"></div>

    <div class="logo-row">
      <div class="logo-icon">
        <svg viewBox="0 0 18 18" fill="none">
          <path d="M9 2v14M2 9h14" stroke="#fff" stroke-width="2.4" stroke-linecap="round"/>
        </svg>
      </div>
      <div>
        <div class="logo-text">MedCore</div>
        <div class="logo-sub">Hospital Management</div>
      </div>
    </div>

    <div class="left-body">
      <div class="badge">
        <div class="badge-dot"></div>
        <span>Trusted by 500+ hospitals</span>
      </div>
      <div class="hl-title">Better care,<br><em>brighter</em> future</div>
      <div class="hl-desc">One platform for patients, doctors, and admins — beautiful, fast, and reliable.</div>
    </div>

    <div class="wave">
      <svg viewBox="0 0 54 500" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M54 0 Q18 125 36 250 Q54 375 54 500 L54 0Z" fill="#fff"/>
      </svg>
    </div>
  </div>

  <!-- ═══════════ RIGHT PANEL ═══════════ -->
  <div class="panel-right">
    <div class="form-wrap">

      <!-- Tab switcher -->
      <div class="tabs">
        <button class="tab active" id="tab-up" onclick="switchTab('up')">Create Account</button>
        <button class="tab"        id="tab-in" onclick="switchTab('in')">Sign In</button>
      </div>

      <!-- ── SIGN UP PANE ── -->
      <div class="pane active" id="pane-up">
        <div class="fh-title">Get started 👋</div>
        <div class="fh-sub">Join thousands of healthcare professionals</div>

        <form method="POST" action="">

          <div class="field">
            <label>Full Name</label>
            <div class="field-wrap">
              <svg class="fic" viewBox="0 0 16 16">
                <path fill="#6366f1" d="M8 8a3 3 0 100-6 3 3 0 000 6zm-5 6a5 5 0 0110 0H3z"/>
              </svg>
              <input type="text" name="name" placeholder="Dr. Aanya Sharma"
                     value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" required>
            </div>
          </div>

          <div class="field">
            <label>Email</label>
            <div class="field-wrap">
              <svg class="fic" viewBox="0 0 16 16">
                <path fill="#6366f1" d="M2 4a2 2 0 012-2h8a2 2 0 012 2v.217l-6 3.5-6-3.5V4zm0 1.383V12a2 2 0 002 2h8a2 2 0 002-2V5.383l-6 3.5-6-3.5z"/>
              </svg>
              <input type="email" name="email" placeholder="aanya@hospital.com"
                     value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
            </div>
          </div>

          <div class="field">
            <label>Password</label>
            <div class="field-wrap">
              <svg class="fic" viewBox="0 0 16 16">
                <path fill="#6366f1" d="M8 1a4 4 0 00-4 4v1H3a1 1 0 00-1 1v7a1 1 0 001 1h10a1 1 0 001-1V7a1 1 0 00-1-1h-1V5a4 4 0 00-4-4zm0 10a1.5 1.5 0 110-3 1.5 1.5 0 010 3zM5 5a3 3 0 016 0v1H5V5z"/>
              </svg>
              <input type="password" name="password" placeholder="Min. 4 characters" required>
            </div>
          </div>

          <div class="field">
            <label>Role</label>
            <div class="field-wrap">
              <select name="role" required>
                <option value="">Select your role</option>
                <option value="admin"   <?php echo (isset($_POST['role']) && $_POST['role']==='admin')   ? 'selected' : ''; ?>>Admin</option>
                <option value="doctor"  <?php echo (isset($_POST['role']) && $_POST['role']==='doctor')  ? 'selected' : ''; ?>>Doctor</option>
                <option value="patient" <?php echo (isset($_POST['role']) && $_POST['role']==='patient') ? 'selected' : ''; ?>>Patient</option>
              </select>
            </div>
          </div>

          <button type="submit" name="submit_register" class="btn">Create Account →</button>
        </form>
        <?php if(!empty($error)): ?>
        <div class="msg msg-error"><?php echo $error; ?></div>
        <?php endif; ?>
      </div>

      <!-- ── SIGN IN PANE ── -->
      <div class="pane" id="pane-in">
        <div class="fh-title">Welcome back 🏥</div>
        <div class="fh-sub">Sign in to your MedCore account</div>

        <form method="POST" action="">
          <div class="field">
            <label>Email</label>
            <div class="field-wrap">
              <svg class="fic" viewBox="0 0 16 16">
                <path fill="#6366f1" d="M2 4a2 2 0 012-2h8a2 2 0 012 2v.217l-6 3.5-6-3.5V4zm0 1.383V12a2 2 0 002 2h8a2 2 0 002-2V5.383l-6 3.5-6-3.5z"/>
              </svg>
              <input type="email" name="email" placeholder="aanya@hospital.com" required>
            </div>
          </div>

          <div class="field">
            <label>Password</label>
            <div class="field-wrap">
              <svg class="fic" viewBox="0 0 16 16">
                <path fill="#6366f1" d="M8 1a4 4 0 00-4 4v1H3a1 1 0 00-1 1v7a1 1 0 001 1h10a1 1 0 001-1V7a1 1 0 00-1-1h-1V5a4 4 0 00-4-4zm0 10a1.5 1.5 0 110-3 1.5 1.5 0 010 3zM5 5a3 3 0 016 0v1H5V5z"/>
              </svg>
              <input type="password" name="password" placeholder="Enter password" required>
            </div>
          </div>

          <button type="submit" name="submit_login" class="btn">Sign In →</button>
          <?php if(!empty($login_error)): ?>
  <div class="msg msg-error"><?php echo $login_error; ?></div>
<?php endif; ?>
        </form>

    </div><!-- /form-wrap -->
  </div><!-- /panel-right -->

</div><!-- /card -->

<script>
function switchTab(t) {
  document.getElementById('pane-up').classList.toggle('active', t === 'up');
  document.getElementById('pane-in').classList.toggle('active', t === 'in');
  document.getElementById('tab-up').classList.toggle('active', t === 'up');
  document.getElementById('tab-in').classList.toggle('active', t === 'in');
}

// Signup case
<?php if (isset($_POST['submit_register'])): ?>
document.addEventListener('DOMContentLoaded', function(){ switchTab('up'); });
<?php endif; ?>

// Login case
<?php if (isset($_POST['submit_login'])): ?>
document.addEventListener('DOMContentLoaded', function(){ switchTab('in'); });
<?php endif; ?>
</script>

</body>
</html>