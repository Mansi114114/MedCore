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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>MedCore | Consultation History</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@800&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --vibrant-purple: #7c69ef;
            --dark-bg: #f0f2ff;
            --success-green: #10b981;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--dark-bg);
            background-image: 
                radial-gradient(at 0% 0%, rgba(124, 105, 239, 0.1) 0px, transparent 50%);
            min-height: 100vh;
            display: flex;
        }

        /* --- SIDEBAR --- */
        nav {
            width: 280px; background: #fff; padding: 40px 25px;
            display: flex; flex-direction: column; height: 100vh;
            position: fixed; border-right: 1px solid #e2e8f0;
        }
        .logo { font-family: 'Syne'; font-size: 26px; font-weight: 800; color: var(--vibrant-purple); margin-bottom: 40px; }
        
        .btn-back {
            text-decoration: none; background: #f7f6ff; color: var(--vibrant-purple);
            padding: 18px; border-radius: 20px; font-weight: 800; font-size: 14px;
            text-align: center; margin-bottom: 30px; border: 1px solid #eceaff; display: block;
        }

        /* --- MAIN CONTENT --- */
        main { flex: 1; margin-left: 280px; padding: 45px 60px; }

        header { margin-bottom: 45px; }
        header h1 { font-family: 'Syne'; font-size: 34px; font-weight: 800; color: #1e1b4b; }
        header p { color: #64748b; font-weight: 700; }

        /* --- HISTORY TABLE --- */
        .card {
            background: #fff; border-radius: 35px; padding: 40px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.02);
        }
        
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; font-size: 11px; text-transform: uppercase; color: #cbd5e1; padding-bottom: 25px; font-weight: 800; }
        td { padding: 22px 0; border-top: 1px solid #f8f9ff; vertical-align: middle; }

        .patient-cell { display: flex; align-items: center; gap: 15px; }
        .avatar { 
            width: 45px; height: 45px; background: #f0fdf4; border-radius: 16px; 
            display: flex; align-items: center; justify-content: center; color: var(--success-green); font-weight: 800; 
        }

        .reason-box {
            background: #f8fafc; padding: 12px 20px; border-radius: 15px;
            font-size: 13px; font-weight: 600; color: #4e4491; max-width: 300px;
            line-height: 1.4;
        }

        .status-badge {
            background: rgba(16, 185, 129, 0.1); color: var(--success-green);
            padding: 6px 12px; border-radius: 10px; font-size: 10px; font-weight: 800; text-transform: uppercase;
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
            <h1>Consultation History</h1>
            <p>A detailed record of all patients you have attended.</p>
        </header>

        <div class="card">
            <table>
                <thead>
                    <tr>
                        <th>Patient Name</th>
                        <th>Date of Visit</th>
                        <th>Reason / Symptoms</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Fetch only COMPLETED appointments for this doctor
                    // Make sure your table has a column named 'reason' or 'symptoms'
                    $res = mysqli_query($conn, "SELECT * FROM appointments 
                        WHERE doctor_name = '$doctor_name' 
                        AND status = 'Completed' 
                        ORDER BY appt_date DESC, appt_time DESC");

                    if(mysqli_num_rows($res) > 0):
                        while($row = mysqli_fetch_assoc($res)):
                    ?>
                    <tr>
                        <td>
                            <div class="patient-cell">
                                <div class="avatar">✓</div>
                                <div style="font-weight:800; color:#1e1b4b;"><?php echo htmlspecialchars($row['patient_name']); ?></div>
                            </div>
                        </td>
                        <td>
                            <div style="font-weight:800; font-size:14px; color:#1e1b4b;"><?php echo date('d M, Y', strtotime($row['appt_date'])); ?></div>
                            <div style="font-size:12px; color:#94a3b8; font-weight:700;"><?php echo date('h:i A', strtotime($row['appt_time'])); ?></div>
                        </td>
                        <td>
                            <div class="reason-box">
                                <?php echo !empty($row['reason']) ? htmlspecialchars($row['reason']) : "General Checkup"; ?>
                            </div>
                        </td>
                        <td>
                            <span class="status-badge">Attended</span>
                        </td>
                    </tr>
                    <?php endwhile; else: ?>
                    <tr>
                        <td colspan="4" style="text-align:center; padding: 50px; color: #94a3b8; font-weight: 700;">
                            No attended records found yet.
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

</body>
</html>