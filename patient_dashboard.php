<?php
ob_start();
session_start();
include 'db.php'; 

// Security Check
if(!isset($_SESSION['user']) || $_SESSION['role'] !== 'patient') {
    header("Location: register.php");
    exit();
}
$username = $_SESSION['user']; 
// 1. Fetch User ID and Blood Group
$profile_query = mysqli_query($conn, "SELECT blood_group FROM patient_profiles WHERE username = '$username'");
$profile_data = mysqli_fetch_assoc($profile_query);
$blood_group = $profile_data['blood_group'] ?? 'Not Set';

// 2. Count ONLY Completed Visits
// We check for 'Completed' status (make sure this matches your DB spelling)
$completed_res = mysqli_query($conn, "SELECT COUNT(*) as total FROM appointments 
                                     WHERE patient_name = '$username' 
                                     AND status = 'Completed'");
$completed_data = mysqli_fetch_assoc($completed_res);
$completed_count = $completed_data['total'] ?? 0;
// 2. Fetch the ID using the correct column name 'name'
$user_id_query = mysqli_query($conn, "SELECT id FROM users WHERE name = '$username'");
$user_data = mysqli_fetch_assoc($user_id_query);
$raw_id = $user_data['id'] ?? 0; 

// 3. Format the ID with leading zeros (e.g., 7 becomes 007)
$formatted_id = str_pad($raw_id, 3, '0', STR_PAD_LEFT);
$msg = "";
if (isset($_GET['success'])) {
    $msg = "<div class='alert' id='success-alert'>Appointment booked successfully!</div>";
}
if (isset($_POST['book_now'])) {
    $doctor = mysqli_real_escape_string($conn, $_POST['doctor']);
    $date = mysqli_real_escape_string($conn, $_POST['date']);
    $time = mysqli_real_escape_string($conn, $_POST['time']);
    $reason = mysqli_real_escape_string($conn, $_POST['reason']);

    $query = "INSERT INTO appointments (patient_name, doctor_name, appt_date, appt_time, status, reason) 
              VALUES ('$username', '$doctor', '$date', '$time', 'Pending', '$reason')";
    
    if (mysqli_query($conn, $query)) {
        // Redirect back to the same page to prevent duplicate submission on refresh
        header("Location: patient_dashboard.php?success=1");
        exit(); // Always exit after a header redirect
    }
}
$username = $_SESSION['user'];
$msg = "";

// Handle New Booking
if (isset($_POST['book_now'])) {
    $p_name = mysqli_real_escape_string($conn, $username);
    $doc    = mysqli_real_escape_string($conn, $_POST['doctor']);
    $date   = mysqli_real_escape_string($conn, $_POST['date']);
    $time   = mysqli_real_escape_string($conn, $_POST['time']);

    $sql = "INSERT INTO appointments (patient_name, doctor_name, appt_date, appt_time, status) 
            VALUES ('$p_name', '$doc', '$date', '$time', 'Pending')";

}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>MedCore | Patient Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #6366f1;
            --secondary: #8b5cf6;
            --accent: #a78bfa;
            --glass: rgba(255, 255, 255, 0.8);
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'DM Sans', sans-serif;
            background: #f0f4ff;
            min-height: 100vh;
            display: flex;
            overflow: hidden; /* For fixed sidebar layout */
        }

        /* --- UI DECORATION (Restoring the 'vibe') --- */
        .bg-blob {
            position: fixed; border-radius: 50%; filter: blur(80px); z-index: -1; opacity: 0.4;
        }
        .blob-1 { width: 400px; height: 400px; background: var(--secondary); top: -100px; right: -100px; }
        .blob-2 { width: 300px; height: 300px; background: var(--accent); bottom: -50px; left: -50px; }

        /* --- SIDEBAR --- */
        nav {
            width: 280px; background: var(--glass); backdrop-filter: blur(10px);
            border-right: 1px solid rgba(255,255,255,0.3); padding: 40px 30px;
            display: flex; flex-direction: column; height: 100vh;
        }

        .logo { font-family: 'Syne', sans-serif; font-weight: 800; font-size: 24px; color: var(--primary); margin-bottom: 50px; }

        .nav-links { list-style: none; flex: 1; }
        .nav-links li { margin-bottom: 8px; }
        .nav-links a {
            text-decoration: none; color: #64748b; font-weight: 600; padding: 14px 20px;
            display: block; border-radius: 16px; transition: 0.3s;
        }
        .nav-links a.active, .nav-links a:hover {
            background: #fff; color: var(--primary); box-shadow: 0 10px 20px rgba(99,102,241,0.1);
        }

        /* --- MAIN CONTENT --- */
        main { flex: 1; padding: 40px; overflow-y: auto; height: 100vh; }

        header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px; }
        .welcome h1 { font-family: 'Syne', sans-serif; font-size: 32px; color: #1e1b4b; }
        .time-badge { background: #fff; padding: 10px 20px; border-radius: 50px; font-weight: 700; color: var(--primary); box-shadow: 0 4px 15px rgba(0,0,0,0.05); }

        /* --- DASHBOARD CARDS --- */
        .card {
            background: var(--glass); backdrop-filter: blur(10px);
            border-radius: 32px; padding: 30px; border: 1px solid rgba(255,255,255,0.5);
            box-shadow: 0 20px 40px rgba(0,0,0,0.03); margin-bottom: 30px;
        }

        .grid-layout { display: grid; grid-template-columns: 1.8fr 1fr; gap: 30px; }

        /* Booking Form UI */
        .booking-row { display: grid; grid-template-columns: 2fr 1fr 1fr auto; gap: 20px; align-items: end; }
        .field label { display: block; font-size: 11px; font-weight: 800; color: var(--primary); text-transform: uppercase; margin-bottom: 8px; letter-spacing: 0.5px; }
        .field select, .field input {
            width: 100%; padding: 14px; background: #fff; border: 1.5px solid #eef2ff; border-radius: 16px; outline: none; transition: 0.3s;
        }
        .field select:focus, .field input:focus { border-color: var(--primary); }

        .btn-grad {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: #fff; border: none; padding: 16px 30px; border-radius: 16px;
            font-weight: 700; font-family: 'Syne'; cursor: pointer; transition: 0.3s;
        }
        .btn-grad:hover { transform: translateY(-3px); box-shadow: 0 15px 30px rgba(99,102,241,0.3); }

        /* Appointment Items */
        .appt-item { display: flex; align-items: center; padding: 20px 0; border-bottom: 1px solid rgba(0,0,0,0.05); }
        .date-box {
            width: 60px; height: 60px; background: #fff; border-radius: 18px;
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            color: var(--primary); font-weight: 800; margin-right: 20px; box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        .date-box span { font-size: 20px; line-height: 1; }
        .date-box small { font-size: 10px; text-transform: uppercase; }

        .status { padding: 6px 14px; border-radius: 50px; font-size: 12px; font-weight: 700; }
        .pending { background: #fff7ed; color: #f59e0b; }
        .confirmed { background: #ecfdf5; color: #10b981; }

        .alert { background: #ecfdf5; color: #10b981; padding: 15px; border-radius: 16px; margin-bottom: 25px; font-weight: 600; text-align: center; }

        .sidebar-card {
            background: linear-gradient(145deg, var(--primary), var(--secondary));
            border-radius: 24px; padding: 25px; color: #fff; position: relative; overflow: hidden;
        }
        .sidebar-card h3 { font-family: 'Syne'; font-size: 20px; margin-bottom: 10px; }
        .sidebar-card .circle { position: absolute; width: 100px; height: 100px; background: rgba(255,255,255,0.1); border-radius: 50%; right: -20px; bottom: -20px; }

        .logout { margin-top: auto; text-decoration: none; color: #ef4444; font-weight: 700; padding: 15px; border-radius: 16px; text-align: center; background: #fff; }
    </style>
</head>
<body>

    <div class="bg-blob blob-1"></div>
    <div class="bg-blob blob-2"></div>

    <nav>
        <div class="logo">✚ MedCore</div>
        <ul class="nav-links">
            <li><a href="#" class="active">Dashboard Overview</a></li>
            <li><a href="patient_profile.php">Personal-Information</a></li>
            <li><a href="visits_history.php">Appointments History</a></li>
        </ul>
        <div class="sidebar-card">
    <div class="circle"></div>
    <h3>Premium Care</h3>
    <p>Your ID: <b>MC-<?php echo $formatted_id; ?></b></p>
</div>
        <a href="logout.php" class="logout">Log Out</a>
    </nav>

    <main>
        <header>
            <div class="welcome">
                <h1>Welcome, <?php echo explode(' ', $username)[0]; ?> 👋</h1>
                <p style="color: #64748b;">Managing your health with "Better care, brighter future".</p>
            </div>
            <div class="time-badge" id="clock">00:00:00 AM</div>
        </header>

        <?php echo $msg; ?>

        <div class="card">
    <h2 style="font-family:'Syne'; font-size:22px; margin-bottom:25px;">Reserve an Appointment</h2>
    <form method="POST" class="booking-row">
        <div class="field">
            <label>Specialist (<?php 
                $count_res = mysqli_query($conn, "SELECT COUNT(*) as total FROM doctors");
                $count = mysqli_fetch_assoc($count_res);
                echo $count['total']; 
            ?> available)</label>
            <select name="doctor" required>
                <option value="">Choose a verified doctor...</option>
                <?php
                $dr_sql = "SELECT name, specialization FROM doctors ORDER BY name ASC";
                $dr_res = mysqli_query($conn, $dr_sql);
                while($dr = mysqli_fetch_assoc($dr_res)) {
                    echo "<option value='".htmlspecialchars($dr['name'])."'>".htmlspecialchars($dr['name'])." — ".htmlspecialchars($dr['specialization'])."</option>";
                }
                ?>
            </select>
        </div>
        <div class="field">
            <label>Date</label>
            <input type="date" name="date" min="<?php echo date('Y-m-d'); ?>" required>
        </div>
        <div class="field">
            <label>Preferred Time</label>
            <input type="time" name="time" required>
        </div>

        <div class="field">
            <label>Reason for Visit</label>
            <input type="text" name="reason" placeholder="e.g. Annual checkup, Fever" required>
        </div>

        <button type="submit" name="book_now" class="btn-grad">Book Now →</button>
    </form>
</div>

        <div class="grid-layout">
            <div class="card">
                <h2 style="font-family:'Syne'; font-size:22px; margin-bottom:20px;">Upcoming Visits</h2>
                <?php
// Added: AND appt_date >= CURDATE() to filter out old dates
// Added: appt_time to the ORDER BY to keep today's schedule accurate
$appt_sql = "SELECT * FROM appointments 
             WHERE patient_name = '$username' 
             AND appt_date >= CURDATE() 
             ORDER BY appt_date ASC, appt_time ASC";

$appt_res = mysqli_query($conn, $appt_sql);

if (mysqli_num_rows($appt_res) > 0) {
    while($row = mysqli_fetch_assoc($appt_res)) {
        $d = date("d", strtotime($row['appt_date']));
        $m = strtoupper(date("M", strtotime($row['appt_date'])));
        ?>
        <div class="appt-item">
            <div class="date-box">
                <span><?php echo $d; ?></span>
                <small><?php echo $m; ?></small>
            </div>
            <div style="flex:1">
                <div style="font-weight:800; font-size:16px;">
                    <?php echo htmlspecialchars($row['doctor_name']); ?>
                </div>
                <div style="font-size:13px; color:#64748b; margin-top:2px;">
                    Scheduled at <?php echo date("h:i A", strtotime($row['appt_time'])); ?>
                </div>
            </div>
            <span class="status <?php echo strtolower($row['status']); ?>">
                <?php echo $row['status']; ?>
            </span>
        </div>
        <?php
    }
} else {
    echo "<div style='text-align:center; padding:40px; color:#94a3b8;'>No upcoming appointments found.</div>";
}
?>
            </div>

           <div class="card" style="background: rgba(99, 102, 241, 0.05); border: none;">
    <h2 style="font-family:'Syne'; font-size:20px; color: var(--primary); margin-bottom: 20px;">Medical Summary</h2>
    
    <div style="margin-top: 20px;">
        <div style="background:#fff; padding:15px; border-radius:20px; margin-bottom:12px; display:flex; justify-content:space-between; align-items:center; box-shadow: 0 4px 10px rgba(0,0,0,0.02);">
            <div style="display:flex; align-items:center; gap:10px;">
                <span style="font-size:16px;">✅</span>
                <span style="font-size:13px; font-weight:700;">Total Visits</span>
            </div>
            <span style="color:var(--primary); font-weight:800;">
                <?php echo $completed_count; ?>
            </span>
        </div>

        <div style="background:#fff; padding:15px; border-radius:20px; margin-bottom:25px; display:flex; justify-content:space-between; align-items:center; box-shadow: 0 4px 10px rgba(0,0,0,0.02);">
            <div style="display:flex; align-items:center; gap:10px;">
                <span style="font-size:16px;">🩸</span>
                <span style="font-size:13px; font-weight:700;">Blood Group</span>
            </div>
            <span style="color:var(--secondary); font-weight:800;">
                <?php echo htmlspecialchars($blood_group); ?>
            </span>
        </div>
    </div>

    <div style="border-top: 1px solid rgba(0,0,0,0.05); padding-top:20px;">
        <h4 style="font-size:11px; text-transform:uppercase; color:#94a3b8; margin-bottom:10px; letter-spacing:0.5px;">Today's Advice</h4>
        <p id="tip-text" style="font-size:14px; line-height:1.6; font-weight:500; color: #475569;">
            Vitamin D is essential! Try to get 15 minutes of morning sunlight.
        </p>
    </div>
</div>
            </div>
        </div>
    </main>

    <script>
        function updateClock() {
            const now = new Date();
            document.getElementById('clock').innerText = now.toLocaleTimeString();
        }
        setInterval(updateClock, 1000);
        updateClock();

        const tips = [
            "Consistent sleep schedules improve mood and focus.",
            "Green tea is packed with antioxidants for heart health.",
            "Remember to take short breaks from screens every 20 minutes.",
            "A balanced diet includes a variety of colorful vegetables."
        ];
        setInterval(() => {
            document.getElementById('tip-text').innerText = tips[Math.floor(Math.random()*tips.length)];
        }, 10000);
        // Wait for the document to load
document.addEventListener('DOMContentLoaded', () => {
    const alert = document.getElementById('success-alert');
    if (alert) {
        // Wait 3 seconds, then fade out
        setTimeout(() => {
            alert.style.transition = "opacity 0.5s ease";
            alert.style.opacity = "0";
            // Remove from layout after fade
            setTimeout(() => alert.remove(), 500);
        }, 3000); 
    }
});
    </script>
</body>
</html>