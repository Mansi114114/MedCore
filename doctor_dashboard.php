<?php
ob_start();
session_start();
include 'db.php'; 

// 1. SECURITY CHECK
if(!isset($_SESSION['user']) || $_SESSION['role'] !== 'doctor') {
    header("Location: register.php");
    exit();
}
$doctor_name = $_SESSION['user']; 
// --- AUTOMATIC COMPLETION LOGIC ---
// This finds appointments from PAST DAYS that are still 'Confirmed' or 'Pending'
// and marks them as 'Completed' (unless they were already 'Cancelled')

$today = date('Y-m-d');
$cleanup_sql = "UPDATE appointments 
                SET status = 'Completed' 
                WHERE doctor_name = '$doctor_name' 
                AND appt_date < '$today' 
                AND status != 'Cancelled' 
                AND status != 'Completed'";

mysqli_query($conn, $cleanup_sql);

// 3. LOGIC FOR COUNTERS (Stats)
$attended_res = mysqli_query($conn, "SELECT COUNT(*) as total FROM appointments WHERE doctor_name = '$doctor_name' AND status = 'Completed'");
$attended_count = mysqli_fetch_assoc($attended_res)['total'] ?? 0;

$upcoming_res = mysqli_query($conn, "SELECT COUNT(*) as total FROM appointments WHERE doctor_name = '$doctor_name' AND status = 'Confirmed' AND appt_date >= CURDATE()");
$upcoming_count = mysqli_fetch_assoc($upcoming_res)['total'] ?? 0;

$pending_res = mysqli_query($conn, "SELECT COUNT(*) as total FROM appointments WHERE doctor_name = '$doctor_name' AND status = 'Pending'");
$pending_count = mysqli_fetch_assoc($pending_res)['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>MedCore | Physician Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@800&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --vibrant-purple: #7c69ef;
            --deep-lavender: #4e4491;
            --grad: linear-gradient(135deg, #7c69ef 0%, #a287f4 100%);
            --dark-bg: #f0f2ff;
            --glass-white: rgba(255, 255, 255, 0.9);
            --success-green: #10b981;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--dark-bg);
            background-image: 
                radial-gradient(at 0% 0%, rgba(124, 105, 239, 0.15) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(162, 135, 244, 0.1) 0px, transparent 50%);
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
        .logo { font-family: 'Syne'; font-size: 26px; font-weight: 800; color: var(--vibrant-purple); margin-bottom: 50px; letter-spacing: -1px; }
        
        .nav-links a {
            text-decoration: none; color: #94a3b8; font-weight: 800; padding: 16px 20px;
            display: block; border-radius: 20px; transition: 0.3s; margin-bottom: 8px;
            font-size: 14px;
        }
        .nav-links a.active, .nav-links a:hover {
            background: #f7f6ff; color: var(--vibrant-purple); border: 1px solid #eceaff;
        }

        /* --- MAIN CONTENT --- */
        main { flex: 1; margin-left: 280px; padding: 45px 60px; }

        header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 45px; }
        .welcome h1 { font-family: 'Syne'; font-size: 34px; font-weight: 800; color: #1e1b4b; }
        .welcome p { color: #64748b; font-weight: 700; font-size: 14px; }

        /* --- STATS --- */
        .stats-row { display: grid; grid-template-columns: repeat(3, 1fr); gap: 25px; margin-bottom: 40px; }
        .stat-card {
            background: var(--glass-white); padding: 30px; border-radius: 30px;
            border: 1.5px solid #fff; box-shadow: 0 15px 35px rgba(124, 105, 239, 0.05);
            backdrop-filter: blur(10px);
        }
        .stat-card h3 { font-size: 11px; text-transform: uppercase; color: #94a3b8; letter-spacing: 1px; font-weight: 800; margin-bottom: 10px; }
        .stat-card .val { font-family: 'Syne'; font-size: 32px; font-weight: 800; color: #1e1b4b; }
        .stat-card .trend { font-size: 12px; font-weight: 800; color: #10b981; margin-top: 5px; }

        /* --- CONTENT GRID --- */
        .grid { display: grid; grid-template-columns: 1.9fr 1fr; gap: 30px; }
        .card { background: #fff; border-radius: 35px; padding: 40px; box-shadow: 0 20px 50px rgba(0,0,0,0.02); }
        .card-title { font-family: 'Syne'; font-size: 24px; font-weight: 800; margin-bottom: 30px; display: flex; align-items: center; gap: 10px; }
        
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; font-size: 11px; text-transform: uppercase; color: #cbd5e1; padding-bottom: 25px; font-weight: 800; }
        td { padding: 22px 0; border-top: 1px solid #f8f9ff; }

        .patient-cell { display: flex; align-items: center; gap: 15px; }
        .avatar { width: 45px; height: 45px; background: #eeebff; border-radius: 16px; display: flex; align-items: center; justify-content: center; color: var(--vibrant-purple); font-weight: 800; }

        /* Buttons */
        .btn-action {
            display: inline-block; padding: 10px 20px; border-radius: 14px;
            font-size: 11px; font-weight: 800; text-decoration: none; border: none; cursor: pointer;
            transition: 0.3s; text-transform: uppercase;
        }
        .btn-confirm { background: var(--grad); color: #fff; box-shadow: 0 8px 15px rgba(124, 105, 239, 0.2); }
        .btn-complete { background: var(--success-green); color: #fff; box-shadow: 0 8px 15px rgba(16, 185, 129, 0.2); }
        .btn-action:hover { transform: translateY(-2px); filter: brightness(1.1); }

        /* --- SUMMARY SIDE --- */
        .summary-card {
            background: #fff; border: 2px solid #f0f2ff;
            background-image: linear-gradient(rgba(124, 105, 239, 0.03) 2px, transparent 2px), linear-gradient(90deg, rgba(124, 105, 239, 0.03) 2px, transparent 2px);
            background-size: 20px 20px;
        }
        .pill-stat {
            background: #fff; padding: 18px; border-radius: 22px; margin-bottom: 15px;
            display: flex; justify-content: space-between; align-items: center;
            border: 1px solid #f1f5f9;
        }
        .pill-label { font-size: 13px; font-weight: 800; color: #64748b; }
        .pill-value { color: var(--vibrant-purple); font-weight: 800; font-size: 16px; }

        .premium-badge {
            margin-top: auto; background: #1e1b4b; padding: 25px; border-radius: 25px; color: #fff;
        }
    </style>
</head>
<body>

    <nav>
        <div class="logo">+ MedCore</div>
        <div class="nav-links">
            <a href="#" class="active">Overview</a>
            <a href="pending_requests.php">Pending Requests</a>
            <a href="consultant_history.php">Consultation History</a>
            <a href="upcoming_appointments.php">Upcoming Appointments</a>
        </div>
        
        <div class="premium-badge">
            <p style="font-size: 10px; font-weight: 800; opacity: 0.6; margin-bottom: 5px;">VERIFIED Doctor</p>
            <p style="font-size: 16px; font-weight: 800;"><?php echo htmlspecialchars($doctor_name); ?></p>
        </div>
        <a href="logout.php" style="text-align:center; color:#ff5c5c; text-decoration:none; font-weight:800; font-size:14px; margin-top:20px;">Exit System</a>
    </nav>

    <main>
        <header>
            <div class="welcome">
                <h1>Doctor's Hub</h1>
                <p>Welcome back! You have <?php echo $pending_count; ?> pending requests.</p>
            </div>
            <div id="clock" style="font-weight: 800; background: #fff; padding: 12px 30px; border-radius: 50px; box-shadow: 0 10px 30px rgba(0,0,0,0.03);">00:00:00</div>
        </header>

        <div class="stats-row">
            <div class="stat-card">
                <h3>Patients Attended</h3>
                <div class="val"><?php echo $attended_count; ?></div>
                <div class="trend">Patients Treated</div>
            </div>
            <div class="stat-card">
                <h3>Upcoming Appts</h3>
                <div class="val"><?php echo $upcoming_count; ?></div>
                <div class="trend" style="color:#7c69ef;">Scheduled Appointments</div>
            </div>
            <div class="stat-card" style="background: var(--grad);">
                <h3 style="color:rgba(255,255,255,0.8);">Pending Requests</h3>
                <div class="val" style="color:#fff;"><?php echo $pending_count; ?></div>
                <div class="trend" style="color:rgba(255,255,255,0.9);">Needs Confirmation</div>
            </div>
        </div>

        <div class="grid">
            <div class="card">
                <div class="card-title">Today's Appointments</div>
                <table>
                    <thead>
                        <tr>
                            <th>Patient</th>
                            <th>Schedule</th>
                            <th>Problem</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // FETCH ONLY TODAY'S APPOINTMENTS
                        $res = mysqli_query($conn, "SELECT * FROM appointments 
                            WHERE doctor_name = '$doctor_name' 
                            AND appt_date = CURDATE() 
                            ORDER BY appt_time ASC");

                        if(mysqli_num_rows($res) > 0):
                            while($row = mysqli_fetch_assoc($res)):
                        ?>
                        <tr>
                            <td>
                                <div class="patient-cell">
                                    <div class="avatar"><?php echo substr($row['patient_name'], 0, 1); ?></div>
                                    <div style="font-weight:800; color:#1e1b4b;"><?php echo htmlspecialchars($row['patient_name']); ?></div>
                                </div>
                            </td>
                            <td>
                                <div style="font-weight:800; font-size:14px;"><?php echo date('h:i A', strtotime($row['appt_time'])); ?></div>
                                <div style="font-size:11px; color:#94a3b8; font-weight:700;">Today</div>
                            </td>
                            <td>
                        <div style="font-weight:600; font-size:13px; color:#4e4491; background:#f8fafc; padding:10px; border-radius:10px;">
                            <?php echo !empty($row['reason']) ? htmlspecialchars($row['reason']) : "General Consultation"; ?>
                        </div>
                        </td>
                        </tr>
                        <?php endwhile; else: ?>
                        <tr>
                            <td colspan="4" style="text-align:center; padding: 40px; color: #94a3b8; font-weight:700;">
                                No appointments found for today.
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="card summary-card">
                <div class="card-title" style="color:var(--vibrant-purple);">Daily Insights</div>
                
                <div class="pill-stat">
                    <span class="pill-label">✅ Completed Today</span>
                    <span class="pill-value">
                        <?php 
                        $today_comp = mysqli_query($conn, "SELECT COUNT(*) as t FROM appointments WHERE doctor_name='$doctor_name' AND status='Completed' AND appt_date=CURDATE()");
                        echo mysqli_fetch_assoc($today_comp)['t'];
                        ?>
                    </span>
                </div>

                <div class="pill-stat">
                    <span class="pill-label">📅 Remaining Today</span>
                    <span class="pill-value">
                        <?php 
                        $today_rem = mysqli_query($conn, "SELECT COUNT(*) as t FROM appointments WHERE doctor_name='$doctor_name' AND status='Confirmed' AND appt_date=CURDATE()");
                        echo mysqli_fetch_assoc($today_rem)['t'];
                        ?>
                    </span>
                </div>

                <div style="margin-top: 30px; padding: 20px; background: rgba(124, 105, 239, 0.05); border-radius: 25px;">
                    <h4 style="font-size: 11px; text-transform:uppercase; color:var(--vibrant-purple); margin-bottom: 10px;">Quick Tip</h4>
                    <p style="font-size: 13px; line-height: 1.6; font-weight: 700; color: #4e4491;">
                        Confirming pending requests early improves patient trust scores!
                    </p>
                </div>
            </div>
        </div>
    </main>

    <script>
        function updateClock() {
            document.getElementById('clock').innerText = new Date().toLocaleTimeString();
        }
        setInterval(updateClock, 1000); updateClock();
    </script>
</body>
</html>