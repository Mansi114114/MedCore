<?php
ob_start();
session_start();
include 'db.php';

// 1. SECURITY: Only Admin allowed access
if(!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php"); // Redirect to login if not admin
    exit();
}

$admin_name = $_SESSION['user'];

// 2. GLOBAL SYSTEM ANALYTICS
// Count Total Doctors
$res_docs = mysqli_query($conn, "SELECT COUNT(*) as total FROM doctors");
$total_doctors = mysqli_fetch_assoc($res_docs)['total'] ?? 0;

// Count Total Registered Patients
$res_pats = mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role = 'patient'");
$total_patients = mysqli_fetch_assoc($res_pats)['total'] ?? 0;

// Count ACTIVE Appointments (Today and Future)
$res_appts = mysqli_query($conn, "SELECT COUNT(*) as total FROM appointments 
    WHERE appt_date >= CURDATE() 
    AND status != 'Cancelled'");
$total_active_appts = mysqli_fetch_assoc($res_appts)['total'] ?? 0;

// Count Pending Requests System-wide (Needs Attention)
$res_pend = mysqli_query($conn, "SELECT COUNT(*) as total FROM appointments WHERE status = 'Pending'");
$total_pending = mysqli_fetch_assoc($res_pend)['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MedCore | Admin Command Center</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@800&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --admin-navy: #1e1b4b;
            --accent-blue: #3b82f6;
            --warning-orange: #f59e0b;
            --bg-gray: #f8fafc;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background: var(--bg-gray); 
            display: flex; 
            color: #1e1b4b;
            min-height: 100vh;
        }

        /* --- SIDEBAR --- */
        nav { 
            width: 280px; background: var(--admin-navy); padding: 45px 30px; 
            height: 100vh; position: fixed; display: flex; flex-direction: column;
        }
        .logo { font-family: 'Syne'; font-size: 26px; font-weight: 800; color: #fff; margin-bottom: 50px; }
        
        .nav-links { flex: 1; }
        .nav-link { 
            display: block; padding: 16px 20px; color: rgba(255,255,255,0.6); 
            text-decoration: none; font-weight: 700; border-radius: 18px; 
            margin-bottom: 8px; transition: 0.3s;
        }
        .nav-link:hover, .nav-link.active { background: rgba(255,255,255,0.1); color: #fff; }

        .btn-logout { 
            margin-top: auto; padding: 18px; background: rgba(239, 68, 68, 0.1); 
            color: #ef4444; border-radius: 18px; text-align: center; 
            text-decoration: none; font-weight: 800; font-size: 14px;
        }

        /* --- MAIN CONTENT --- */
        main { flex: 1; margin-left: 280px; padding: 50px 60px; }
        
        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 45px; }
        .header h1 { font-family: 'Syne'; font-size: 36px; margin-bottom: 8px; }
        .header p { color: #64748b; font-weight: 600; }

        /* --- STAT CARDS --- */
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 24px; margin-bottom: 45px; }
        .stat-card { 
            background: #fff; padding: 30px; border-radius: 30px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.02); border: 1px solid #f1f5f9;
        }
        .stat-card span { font-size: 11px; color: #94a3b8; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; }
        .stat-card .val { font-size: 34px; font-weight: 800; margin-top: 10px; display: block; }

        /* --- TABLE AREA --- */
        .content-card { 
            background: #fff; border-radius: 35px; padding: 40px; 
            box-shadow: 0 20px 50px rgba(0,0,0,0.02); 
        }
        .table-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; font-size: 11px; text-transform: uppercase; color: #cbd5e1; padding-bottom: 22px; font-weight: 800; }
        td { padding: 20px 0; border-top: 1px solid #f8f9ff; vertical-align: middle; }

        .patient-name { font-weight: 800; color: var(--admin-navy); }
        .doc-tag { font-weight: 700; color: var(--accent-blue); background: rgba(59, 130, 246, 0.08); padding: 5px 12px; border-radius: 8px; font-size: 12px; }
        
        .badge { 
            padding: 6px 14px; border-radius: 10px; font-size: 10px; 
            font-weight: 800; text-transform: uppercase; 
        }
        .badge-pending { background: #fff7ed; color: var(--warning-orange); }
        .badge-confirmed { background: #eeebff; color: #7c69ef; }
        .badge-completed { background: #f0fdf4; color: #10b981; }
    </style>
</head>
<body>

    <nav>
        <div class="logo">+ MedCore</div>
        <div class="nav-links">
            <a href="admin_dashboard.php" class="nav-link active">System Overview</a>
            <a href="manage_doctors.php" class="nav-link">Manage Doctors</a>
            <a href="manage_patients.php" class="nav-link">Manage Patients</a>
        </div>
        <a href="logout.php" class="btn-logout">Exit Admin Panel</a>
    </nav>

    <main>
        <div class="header">
            <div>
                <h1>Global Dashboard</h1>
                <p>Monitoring system-wide medical operations and users.</p>
            </div>
            <div style="background:#fff; padding:15px 30px; border-radius:20px; font-weight:800; box-shadow:0 10px 20px rgba(0,0,0,0.02);">
                Admin: <?php echo htmlspecialchars($admin_name); ?>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <span>Total Doctors</span>
                <div class="val"><?php echo $total_doctors; ?></div>
            </div>
            <div class="stat-card">
                <span>Total Patients</span>
                <div class="val"><?php echo $total_patients; ?></div>
            </div>
            <div class="stat-card">
             <span>Active Load</span>
            <div class="val"><?php echo $total_active_appts; ?></div>
            </div>
            <div class="stat-card" style="border: 2px solid var(--warning-orange);">
                <span style="color:var(--warning-orange);">Pending Actions</span>
                <div class="val" style="color:var(--warning-orange);"><?php echo $total_pending; ?></div>
            </div>
        </div>

        <div class="content-card">
    <div class="table-header">
        <h2 style="font-family:'Syne';">System Audit (Today's Appointment Updates)</h2>
        <span style="font-size:12px; color:#94a3b8; font-weight:700;">Live Database Logs</span>
    </div>

    <table>
        <thead>
            <tr>
                <th>Patient</th>
                <th>Doctor</th>
                <th>Activity Time</th> <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // NEW SQL: Shows anything where the 'last_updated' timestamp is TODAY
            $res_recent = mysqli_query($conn, "SELECT * FROM appointments 
                WHERE DATE(last_updated) = CURDATE() 
                ORDER BY last_updated DESC");
            
            if(mysqli_num_rows($res_recent) > 0):
                while($row = mysqli_fetch_assoc($res_recent)):
                    $status = $row['status'];
                    // (Keep your existing badge logic here...)
            ?>
            <tr>
                <td>
                    <div class="patient-name"><?php echo htmlspecialchars($row['patient_name']); ?></div>
                    <div style="font-size:11px; color:#94a3b8; font-weight:600;">Updated ID: #<?php echo $row['id']; ?></div>
                </td>
                <td><span class="doc-tag"><?php echo htmlspecialchars($row['doctor_name']); ?></span></td>
                <td>
                    <div style="font-weight:800; font-size:14px; color:#7c69ef;">
                        Updated at <?php echo date('h:i A', strtotime($row['last_updated'])); ?>
                    </div>
                    <div style="font-size:11px; color:#94a3b8; font-weight:700;">
                        Appt Date: <?php echo date('d M', strtotime($row['appt_date'])); ?>
                    </div>
                </td>
                <td><span class="badge <?php echo $badge_class; ?>"><?php echo $status; ?></span></td>
            </tr>
            <?php endwhile; else: ?>
            <tr>
                <td colspan="4" style="text-align:center; padding: 50px; color:#94a3b8; font-weight:700;">
                    No database modifications detected today.
                </td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
    </main>

</body>
</html>