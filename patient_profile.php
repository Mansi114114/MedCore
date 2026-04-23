<?php
ob_start();
session_start();
include 'db.php'; 

if(!isset($_SESSION['user']) || $_SESSION['role'] !== 'patient') {
    header("Location: register.php");
    exit();
}

$username = $_SESSION['user'];
$success_msg = "";

// --- HANDLE FORM SUBMISSION ---
if (isset($_POST['save_profile'])) {
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $blood_group = mysqli_real_escape_string($conn, $_POST['blood_group']);
    $dob = mysqli_real_escape_string($conn, $_POST['dob']);
    $emergency = mysqli_real_escape_string($conn, $_POST['emergency']);
    $history = mysqli_real_escape_string($conn, $_POST['history']);

    // Check if profile already exists for this user
    $check = mysqli_query($conn, "SELECT id FROM patient_profiles WHERE username = '$username'");
    
    if (mysqli_num_rows($check) > 0) {
        $query = "UPDATE patient_profiles SET 
                  full_name='$full_name', blood_group='$blood_group', 
                  dob='$dob', emergency_contact='$emergency', medical_history='$history' 
                  WHERE username='$username'";
    } else {
        $query = "INSERT INTO patient_profiles (username, full_name, blood_group, dob, emergency_contact, medical_history) 
                  VALUES ('$username', '$full_name', '$blood_group', '$dob', '$emergency', '$history')";
    }

if (mysqli_query($conn, $query)) {
    $success_msg = "Profile updated successfully!"; 
} else {
    $success_msg = ""; // Keep it empty if nothing happened
}
}

// --- FETCH EXISTING DATA ---
$res = mysqli_query($conn, "SELECT * FROM patient_profiles WHERE username = '$username'");
$data = mysqli_fetch_assoc($res);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>MedCore | Profile Settings</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #6366f1; --secondary: #8b5cf6; --glass: rgba(255, 255, 255, 0.8); }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'DM Sans', sans-serif; background: #f0f4ff; display: flex; }
        
        nav { width: 280px; background: var(--glass); backdrop-filter: blur(10px); padding: 40px; height: 100vh; position: fixed; border-right: 1px solid rgba(255,255,255,0.2); }
        main { flex: 1; margin-left: 280px; padding: 60px; }
        
        .card { background: var(--glass); padding: 40px; border-radius: 30px; border: 1px solid #fff; box-shadow: 0 20px 40px rgba(0,0,0,0.05); }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 25px; }
        
        .field { margin-bottom: 20px; }
        label { display: block; font-size: 12px; font-weight: 800; color: var(--primary); text-transform: uppercase; margin-bottom: 8px; }
        input, select, textarea { width: 100%; padding: 15px; border-radius: 15px; border: 1.5px solid #eef2ff; outline: none; font-family: inherit; }
        input:focus { border-color: var(--primary); }
        
        .btn-save { background: linear-gradient(135deg, var(--primary), var(--secondary)); color: white; border: none; padding: 18px 40px; border-radius: 15px; font-weight: 700; cursor: pointer; margin-top: 20px; }
        .alert { background: #ecfdf5; color: #10b981; padding: 20px; border-radius: 15px; margin-bottom: 30px; font-weight: 600; }
    </style>
</head>
<body>

<nav>
    <div style="font-family:'Syne'; font-weight:800; font-size:24px; color:var(--primary); margin-bottom:40px;">✚ MedCore</div>
    <a href="patient_dashboard.php" style="text-decoration:none; color:#64748b; font-weight:600; display:block; padding:15px 0;">← Back to Dashboard</a>
</nav>

<main>
    <?php if (!empty($success_msg)): ?>
    <div class="alert" id="profile-alert">
        <?php echo $success_msg; ?>
    </div>
<?php endif; ?>

    <div class="card">
        <h1 style="font-family:'Syne'; margin-bottom:30px;">Complete Your Profile</h1>
        
        <form method="POST">
            <div class="form-grid">
                <div class="field">
                    <label>Full Name</label>
                    <input type="text" name="full_name" value="<?php echo $data['full_name'] ?? ''; ?>" placeholder="John Doe" required>
                </div>
                <div class="field">
                    <label>Blood Group</label>
                    <select name="blood_group">
                        <option value="">Select Group</option>
                        <?php 
                        $groups = ['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-'];
                        foreach($groups as $g) {
                            $sel = ($data['blood_group'] == $g) ? 'selected' : '';
                            echo "<option value='$g' $sel>$g</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="field">
                    <label>Date of Birth</label>
                    <input type="date" name="dob" value="<?php echo $data['dob'] ?? ''; ?>">
                </div>
                <div class="field">
                    <label>Emergency Contact</label>
                    <input type="text" name="emergency" value="<?php echo $data['emergency_contact'] ?? ''; ?>" placeholder="+1 234 567 890">
                </div>
            </div>

            <div class="field">
                <label>Medical History / Past Conditions</label>
                <textarea name="history" rows="5" placeholder="List any allergies, past surgeries, or chronic conditions..."><?php echo $data['medical_history'] ?? ''; ?></textarea>
            </div>

            <button type="submit" name="save_profile" class="btn-save">Save Medical Profile →</button>
        </form>
    </div>
</main>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const alertBox = document.getElementById('profile-alert');
        
        if (alertBox) {
            // 1. Wait 3 seconds
            setTimeout(() => {
                // 2. Smooth fade out effect
                alertBox.style.transition = "opacity 0.5s ease";
                alertBox.style.opacity = "0";
                
                // 3. After fade finishes (0.5s), remove it from the layout
                setTimeout(() => {
                    alertBox.style.display = "none";
                }, 500);
            }, 3000);
        }
    });
</script>

</body>
</html>