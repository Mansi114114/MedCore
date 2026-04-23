<?php
session_start();
include 'db.php';

if(!isset($_SESSION['user']) || $_SESSION['role'] !== 'patient') {
    header("Location: register.php");
    exit();
}

$username = $_SESSION['user'];

// Fetching past data in decreasing order
$query = "SELECT a.appt_date, a.appt_time, a.doctor_name, a.status, a.reason, d.specialization 
          FROM appointments a 
          LEFT JOIN doctors d ON a.doctor_name = d.name 
          WHERE a.patient_name = '$username' 
          AND a.appt_date < CURDATE() 
          ORDER BY a.appt_date DESC, a.appt_time DESC";

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Appointment History | MedCore</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Syne:wght@700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #6366f1;
            --secondary: #a855f7;
            --bg-main: #f4f7ff;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --card-hover-bg: #ffffff;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-main);
            color: var(--text-main);
            display: flex;
            min-height: 100vh;
        }

        /* --- SIDEBAR --- */
        .sidebar {
            width: 260px;
            background: #fff;
            border-right: 1px solid rgba(0,0,0,0.05);
            padding: 40px 30px;
            position: fixed;
            height: 100vh;
        }

        .brand {
            font-family: 'Syne';
            font-weight: 800;
            font-size: 26px;
            color: var(--primary);
            margin-bottom: 50px;
            display: block;
            text-decoration: none;
        }

        .back-nav {
            text-decoration: none;
            color: var(--text-muted);
            font-weight: 600;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }
        .back-nav:hover { color: var(--primary); transform: translateX(-5px); }

        /* --- CONTENT --- */
        .main-content {
            margin-left: 260px;
            flex: 1;
            padding: 60px 50px;
        }

        .header-section { margin-bottom: 40px; }

        .header-title {
            font-family: 'Syne';
            font-size: 32px;
            color: #000;
            margin-bottom: 8px;
        }
        .header-subtitle {
            font-size: 15px;
            color: var(--text-muted);
            font-weight: 500;
        }

        /* --- INTERACTIVE CARDS --- */
        .history-card {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 28px;
            padding: 25px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.02);
            border: 1px solid rgba(0,0,0,0.03);
            cursor: pointer;
            /* The Magic Transition */
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        /* Touch & Hover Effect */
        .history-card:hover, .history-card:active {
            transform: translateY(-8px) scale(1.01);
            background: var(--card-hover-bg);
            box-shadow: 0 20px 40px rgba(99, 102, 241, 0.12);
            border-color: var(--primary);
        }

        .date-box {
            background: #f8fafc;
            border-radius: 18px;
            padding: 12px;
            text-align: center;
            min-width: 80px;
            margin-right: 25px;
            border: 1px solid #f1f5f9;
            transition: background 0.3s ease;
        }
        .history-card:hover .date-box { background: #fff; }

        .date-day { font-size: 24px; font-weight: 800; color: var(--primary); display: block; }
        .date-month { font-size: 10px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; }

        .info-body { flex: 1; }
        .spec-tag {
            font-size: 10px;
            font-weight: 800;
            color: var(--secondary);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 4px;
            display: block;
        }

        .doc-name { font-size: 18px; font-weight: 700; color: #1e293b; margin-bottom: 12px; }

        .reason-box {
            background: rgba(99, 102, 241, 0.04);
            padding: 12px 18px;
            border-radius: 15px;
            font-size: 13.5px;
            color: #475569;
            border-left: 3px solid var(--primary);
            transition: 0.3s;
        }
        .history-card:hover .reason-box { background: rgba(99, 102, 241, 0.07); }

        .status-box { text-align: right; margin-left: 25px; }
        .time-label { font-size: 13px; font-weight: 700; color: var(--text-muted); display: block; margin-bottom: 10px; }

        .badge {
            padding: 6px 16px;
            border-radius: 100px;
            font-size: 11px;
            font-weight: 700;
            text-transform: capitalize;
        }
        .completed { background: #dcfce7; color: #15803d; }
    </style>
</head>
<body>

    <div class="sidebar">
        <a href="patient_dashboard.php" class="brand">+ MedCore</a>
        <a href="patient_dashboard.php" class="back-nav">
            <span>←</span> <span>Back to Dashboard</span>
        </a>
    </div>

    <div class="main-content">
        <div class="header-section">
            <h1 class="header-title">Appointment History</h1>
            <p class="header-subtitle">Review your past medical encounters and diagnoses.</p>
        </div>

        <?php if(mysqli_num_rows($result) > 0): ?>
            <?php while($row = mysqli_fetch_assoc($result)): ?>
                <div class="history-card">
                    <div class="date-box">
                        <span class="date-day"><?php echo date("d", strtotime($row['appt_date'])); ?></span>
                        <span class="date-month"><?php echo date("M Y", strtotime($row['appt_date'])); ?></span>
                    </div>

                    <div class="info-body">
                        <span class="spec-tag"><?php echo htmlspecialchars($row['specialization'] ?? 'GENERAL CARE'); ?></span>
                        <h2 class="doc-name">
                            <?php 
                                // Logic to prevent "Dr. Dr."
                                $name = htmlspecialchars($row['doctor_name']);
                                echo (strpos($name, 'Dr.') === 0) ? $name : "Dr. " . $name; 
                            ?>
                        </h2>
                        <div class="reason-box">
                            <strong>Reason:</strong> <?php echo htmlspecialchars($row['reason'] ?? 'Routine Clinical Evaluation'); ?>
                        </div>
                    </div>

                    <div class="status-box">
                        <span class="time-label">🕒 <?php echo date("h:i A", strtotime($row['appt_time'])); ?></span>
                        <span class="badge <?php echo strtolower($row['status']); ?>">
                            <?php echo $row['status']; ?>
                        </span>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div style="background: #fff; padding: 60px; border-radius: 30px; text-align: center; color: #94a3b8; border: 2px dashed #e2e8f0;">
                No history records found.
            </div>
        <?php endif; ?>
    </div>

</body>
</html>