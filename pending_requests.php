<?php
ob_start();
session_start();
include 'db.php'; 

// 1. SECURITY: Only doctors allowed
if(!isset($_SESSION['user']) || $_SESSION['role'] !== 'doctor') {
    header("Location: register.php");
    exit();
}
$doctor_name = $_SESSION['user']; 

// 2. ACTION LOGIC: Process Confirm or Deny
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $action = $_GET['action'];
    
    if ($action === 'confirm') {
        $sql = "UPDATE appointments SET status = 'Confirmed' WHERE id = $id AND doctor_name = '$doctor_name'";
        mysqli_query($conn, $sql);
    } elseif ($action === 'deny') {
        $sql = "UPDATE appointments SET status = 'Cancelled' WHERE id = $id AND doctor_name = '$doctor_name'";
        mysqli_query($conn, $sql);
    }
    header("Location: pending_requests.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>MedCore | Pending Approvals</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@800&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --vibrant-purple: #7c69ef;
            --grad: linear-gradient(135deg, #7c69ef 0%, #a287f4 100%);
            --dark-bg: #f0f2ff;
            --danger: #ff5c5c;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--dark-bg);
            background-image: 
                radial-gradient(at 0% 0%, rgba(124, 105, 239, 0.1) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(162, 135, 244, 0.05) 0px, transparent 50%);
            min-height: 100vh;
            display: flex;
            color: #1a163a;
        }

        /* --- SIDEBAR --- */
        nav {
            width: 280px; background: #fff; padding: 40px 25px;
            display: flex; flex-direction: column; height: 100vh;
            position: fixed; border-right: 1px solid #e2e8f0;
            z-index: 100;
        }
        .logo { font-family: 'Syne'; font-size: 26px; font-weight: 800; color: var(--vibrant-purple); margin-bottom: 40px; letter-spacing: -1px; }
        
        /* Back to Dashboard Button */
        .btn-back {
            text-decoration: none;
            background: #f7f6ff;
            color: var(--vibrant-purple);
            padding: 18px;
            border-radius: 20px;
            font-weight: 800;
            font-size: 14px;
            text-align: center;
            margin-bottom: 30px;
            border: 1px solid #eceaff;
            transition: 0.3s;
        }
        .btn-back:hover { background: var(--vibrant-purple); color: #fff; transform: translateX(-5px); }

        .nav-links a {
            text-decoration: none; color: #94a3b8; font-weight: 800; padding: 16px 20px;
            display: block; border-radius: 20px; transition: 0.3s; margin-bottom: 8px;
            font-size: 14px;
        }
        .nav-links a.active { background: #f7f6ff; color: var(--vibrant-purple); border: 1px solid #eceaff; }

        /* --- MAIN CONTENT --- */
        main { flex: 1; margin-left: 280px; padding: 45px 60px; }

        header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 45px; }
        .welcome h1 { font-family: 'Syne'; font-size: 34px; font-weight: 800; color: #1e1b4b; }
        .welcome p { color: #64748b; font-weight: 700; font-size: 14px; }

        /* --- REQUEST GRID --- */
        .request-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 25px; }
        
        .request-card {
            background: rgba(255, 255, 255, 0.9);
            padding: 30px;
            border-radius: 35px;
            border: 1.5px solid #fff;
            box-shadow: 0 15px 35px rgba(124, 105, 239, 0.05);
            backdrop-filter: blur(10px);
            transition: 0.3s;
        }
        .request-card:hover { transform: translateY(-5px); box-shadow: 0 20px 40px rgba(124, 105, 239, 0.1); }

        .patient-header { display: flex; align-items: center; gap: 15px; margin-bottom: 25px; }
        .avatar { 
            width: 50px; height: 50px; background: #eeebff; border-radius: 18px; 
            display: flex; align-items: center; justify-content: center; 
            color: var(--vibrant-purple); font-weight: 800; font-size: 20px;
        }
        .patient-name { font-weight: 800; font-size: 18px; color: #1e1b4b; }

        .info-pill {
            background: #f8fafc; padding: 20px; border-radius: 22px; margin-bottom: 25px;
            display: flex; flex-direction: column; gap: 8px;
        }
        .info-row { display: flex; justify-content: space-between; font-size: 13px; font-weight: 700; }
        .info-label { color: #94a3b8; }
        .info-value { color: #1e1b4b; }

        /* Action Buttons */
        .actions { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        .btn-action {
            padding: 14px; border-radius: 16px; font-size: 11px; font-weight: 800;
            text-align: center; text-decoration: none; text-transform: uppercase; transition: 0.3s;
        }
        .btn-confirm { background: var(--grad); color: #fff; box-shadow: 0 10px 20px rgba(124, 105, 239, 0.2); }
        .btn-deny { background: #fff; color: var(--danger); border: 1.5px solid #fef2f2; }
        .btn-deny:hover { background: #fff1f1; }

        .premium-badge {
            margin-top: auto; background: #1e1b4b; padding: 25px; border-radius: 25px; color: #fff;
        }
    </style>
</head>
<body>

    <nav>
        <div class="logo">+ MedCore</div>
        
        <a href="doctor_dashboard.php" class="btn-back">← Dashboard</a>
    </nav>

    <main>
        <header>
            <div class="welcome">
                <h1>Pending Requests</h1>
                <p>Approve or decline incoming patient bookings.</p>
            </div>
            <div id="clock" style="font-weight: 800; background: #fff; padding: 12px 30px; border-radius: 50px; box-shadow: 0 10px 30px rgba(0,0,0,0.03);">
                <?php echo date('d M, Y'); ?>
            </div>
        </header>

        <div class="request-grid">
            <?php
            $res = mysqli_query($conn, "SELECT * FROM appointments WHERE doctor_name = '$doctor_name' AND status = 'Pending' ORDER BY appt_date ASC, appt_time ASC");
            
            if(mysqli_num_rows($res) > 0):
                while($row = mysqli_fetch_assoc($res)):
            ?>
            <div class="request-card">
                <div class="patient-header">
                    <div class="avatar"><?php echo substr($row['patient_name'], 0, 1); ?></div>
                    <div class="patient-name"><?php echo htmlspecialchars($row['patient_name']); ?></div>
                </div>

                <div class="info-pill">
                    <div class="info-row">
                        <span class="info-label">SCHEDULED DATE</span>
                        <span class="info-value"><?php echo date('d M Y', strtotime($row['appt_date'])); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">TIME SLOT</span>
                        <span class="info-value"><?php echo date('h:i A', strtotime($row['appt_time'])); ?></span>
                    </div>
                </div>

                <div class="actions">
                    <a href="?action=confirm&id=<?php echo $row['id']; ?>" class="btn-action btn-confirm">Accept</a>
                    <a href="?action=deny&id=<?php echo $row['id']; ?>" class="btn-action btn-deny" onclick="return confirm('Deny request?')">Deny</a>
                </div>
            </div>
            <?php endwhile; else: ?>
            <div style="grid-column: 1/-1; text-align:center; padding-top: 100px;">
                <p style="font-weight: 800; color: #94a3b8; font-size: 18px;">No pending requests found.</p>
            </div>
            <?php endif; ?>
        </div>
    </main>

</body>
</html>