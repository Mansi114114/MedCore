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
    <title>MedCore | Upcoming Schedule</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@800&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --vibrant-purple: #7c69ef;
            --grad: linear-gradient(135deg, #7c69ef 0%, #a287f4 100%);
            --dark-bg: #f0f2ff;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background: var(--dark-bg); 
            display: flex; 
            color: #1a163a; 
        }

        /* Sidebar */
        nav { width: 280px; background: #fff; padding: 40px 25px; height: 100vh; position: fixed; border-right: 1px solid #e2e8f0; }
        .logo { font-family: 'Syne'; font-size: 26px; font-weight: 800; color: var(--vibrant-purple); margin-bottom: 40px; }
        .btn-back { 
            text-decoration: none; background: #f7f6ff; color: var(--vibrant-purple); 
            padding: 18px; border-radius: 20px; font-weight: 800; font-size: 14px; 
            text-align: center; margin-bottom: 30px; display: block; border: 1px solid #eceaff; 
        }

        /* Main Content */
        main { flex: 1; margin-left: 280px; padding: 45px 60px; }
        header { margin-bottom: 40px; }
        header h1 { font-family: 'Syne'; font-size: 34px; font-weight: 800; }
        header p { color: #64748b; font-weight: 700; }

        /* Schedule Grid */
        .schedule-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 25px; }
        .schedule-card {
            background: #fff; border-radius: 30px; padding: 25px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.02); border: 1px solid #f1f5f9;
            transition: 0.3s ease;
        }
        .schedule-card:hover { transform: translateY(-5px); box-shadow: 0 15px 35px rgba(124, 105, 239, 0.1); }

        .date-chip {
            display: inline-block; background: #f1f0ff; color: var(--vibrant-purple);
            padding: 8px 15px; border-radius: 12px; font-weight: 800; font-size: 11px; margin-bottom: 20px;
            text-transform: uppercase;
        }

        .patient-info { display: flex; align-items: center; gap: 12px; margin-bottom: 20px; }
        .avatar { 
            width: 45px; height: 45px; background: var(--grad); color: #fff; 
            border-radius: 14px; display: flex; align-items: center; justify-content: center; font-weight: 800; 
        }
        
        .info-group { background: #f8fafc; padding: 15px; border-radius: 18px; }
        .info-row { display: flex; justify-content: space-between; margin-bottom: 5px; font-size: 13px; }
        .label { color: #94a3b8; font-weight: 700; }
        .value { color: #1e1b4b; font-weight: 800; }

        .problem-text {
            margin-top: 10px; font-size: 12px; font-weight: 600; color: #4e4491;
            padding-top: 10px; border-top: 1px dashed #e2e8f0;
        }
    </style>
</head>
<body>

    <nav>
        <div class="logo">+ MedCore</div>
        <a href="doctor_dashboard.php" class="btn-back">← Back to Dashboard</a>
    </nav>

    <main>
        <header>
            <h1>Upcoming Schedule</h1>
            <p>List of all confirmed appointments scheduled for future dates.</p>
        </header>

        <div class="schedule-grid">
            <?php
            // SQL: Confirmed status AND date is strictly GREATER than today
            $res = mysqli_query($conn, "SELECT * FROM appointments 
                WHERE doctor_name = '$doctor_name' 
                AND status = 'Confirmed'
                AND appt_date > CURDATE() 
                ORDER BY appt_date ASC, appt_time ASC");

            if(mysqli_num_rows($res) > 0):
                while($row = mysqli_fetch_assoc($res)):
            ?>
            <div class="schedule-card">
                <div class="date-chip">
                    📅 <?php echo date('D, M d', strtotime($row['appt_date'])); ?>
                </div>

                <div class="patient-info">
                    <div class="avatar"><?php echo substr($row['patient_name'], 0, 1); ?></div>
                    <div>
                        <div style="font-weight:800; color:#1e1b4b;"><?php echo htmlspecialchars($row['patient_name']); ?></div>
                        <div style="font-size:11px; color:#10b981; font-weight:700;">Status: Confirmed</div>
                    </div>
                </div>

                <div class="info-group">
                    <div class="info-row">
                        <span class="label">TIME SLOT</span>
                        <span class="value"><?php echo date('h:i A', strtotime($row['appt_time'])); ?></span>
                    </div>
                    <div class="problem-text">
                        <b>Reason:</b> <?php echo !empty($row['reason']) ? htmlspecialchars($row['reason']) : "Regular Checkup"; ?>
                    </div>
                </div>
            </div>
            <?php endwhile; else: ?>
            <div style="grid-column: 1/-1; text-align:center; padding-top: 100px;">
                <p style="font-weight: 800; color: #94a3b8;">No future confirmed appointments found.</p>
            </div>
            <?php endif; ?>
        </div>
    </main>

</body>
</html>