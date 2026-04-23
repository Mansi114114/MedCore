<?php
require 'db.php'; 

if(!isset($_GET['id'])) {
    header("Location: manage_patients.php");
    exit();
}

$p_id = mysqli_real_escape_string($conn, $_GET['id']);
$res = mysqli_query($conn, "SELECT * FROM patient_profiles WHERE id = '$p_id'");
$data = mysqli_fetch_assoc($res);

if(!$data) { die("Record not found."); }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>MedCore | Patient Details</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #6366f1; --glass: rgba(255, 255, 255, 0.9); }
        body { font-family: 'DM Sans', sans-serif; background: #f0f4ff; margin: 0; display: flex; }
        
        nav { width: 260px; background: var(--glass); backdrop-filter: blur(10px); padding: 40px; height: 100vh; position: fixed; border-right: 1px solid #eef2ff; }
        main { flex: 1; margin-left: 260px; padding: 60px; }
        
        .card { background: white; padding: 40px; border-radius: 30px; box-shadow: 0 20px 40px rgba(0,0,0,0.03); }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 30px; }
        
        .box { padding: 20px; background: #f8faff; border-radius: 20px; border: 1px solid #eef2ff; }
        label { display: block; font-size: 11px; font-weight: 800; color: var(--primary); text-transform: uppercase; margin-bottom: 5px; }
        .val { font-size: 16px; font-weight: 700; color: #1e293b; }
        
        .history-box { grid-column: span 2; background: #fff; border: 1.5px dashed #e2e8f0; min-height: 100px; }
    </style>
</head>
<body>

<nav>
    <div style="font-family:'Syne'; font-weight:800; font-size:24px; color:var(--primary); margin-bottom:40px;">✚ MedCore</div>
    <a href="manage_patients.php" style="text-decoration:none; color:#64748b; font-weight:700;">← Back to Directory</a>
</nav>

<main>
    <div class="card">
        <h1 style="font-family:'Syne'; margin:0;">Medical Record</h1>
        <p style="color:#94a3b8; font-weight:600;">Patient ID: #<?php echo $data['id']; ?> | User: <?php echo $data['username']; ?></p>

        <div class="grid">
            <div class="box">
                <label>Full Name</label>
                <div class="val"><?php echo htmlspecialchars($data['full_name']); ?></div>
            </div>
            <div class="box">
                <label>Blood Group</label>
                <div class="val" style="color:#ef4444;"><?php echo $data['blood_group']; ?></div>
            </div>
            <div class="box">
                <label>Date of Birth</label>
                <div class="val"><?php echo $data['dob']; ?></div>
            </div>
            <div class="box">
                <label>Emergency Contact</label>
                <div class="val"><?php echo htmlspecialchars($data['emergency_contact']); ?></div>
            </div>
            <div class="box history-box">
                <label>Medical History</label>
                <div class="val" style="font-weight:400; line-height:1.6;">
                    <?php echo nl2br(htmlspecialchars($data['medical_history'])); ?>
                </div>
            </div>
        </div>
    </div>
</main>

</body>
</html>