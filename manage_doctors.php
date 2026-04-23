<?php
ob_start();
session_start();
include 'db.php';

// 1. SECURITY: Admin Only
if(!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// 2. DELETE DOCTOR LOGIC
if(isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    $delete_query = "DELETE FROM doctors WHERE id = $id";
    if(mysqli_query($conn, $delete_query)) {
        header("Location: manage_doctors.php?msg=Doctor removed from database");
        exit();
    }
}

// 3. ADD DOCTOR LOGIC (Matching your screenshot columns)
if(isset($_POST['add_doctor'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $spec = mysqli_real_escape_string($conn, $_POST['specialization']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $exp  = mysqli_real_escape_string($conn, $_POST['experience']);

    $insert_query = "INSERT INTO doctors (name, specialization, phone, email, experience) 
                    VALUES ('$name', '$spec', '$phone', '$email', '$exp')";
    
    if(mysqli_query($conn, $insert_query)) {
        header("Location: manage_doctors.php?msg=New doctor added successfully");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>MedCore | Manage Doctors</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@800&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root { --navy: #1e1b4b; --blue: #3b82f6; --danger: #ef4444; --bg: #f8fafc; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: var(--bg); display: flex; color: var(--navy); }

        /* Sidebar Navigation */
        nav { width: 280px; background: var(--navy); height: 100vh; position: fixed; padding: 40px 20px; }
        .logo { font-family: 'Syne'; color: white; font-size: 24px; margin-bottom: 50px; }
        .nav-link { display: block; padding: 15px; color: #94a3b8; text-decoration: none; font-weight: 700; border-radius: 12px; margin-bottom: 10px; }
        .nav-link.active { background: rgba(255,255,255,0.1); color: white; }

        /* Main Section */
        main { flex: 1; margin-left: 280px; padding: 50px; }
        .grid { display: grid; grid-template-columns: 350px 1fr; gap: 30px; align-items: start; }

        /* Cards and Forms */
        .card { background: white; padding: 30px; border-radius: 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.02); }
        .form-group { margin-bottom: 18px; }
        label { display: block; font-size: 11px; font-weight: 800; color: #94a3b8; text-transform: uppercase; margin-bottom: 8px; }
        input { width: 100%; padding: 14px; border-radius: 12px; border: 1px solid #e2e8f0; font-family: inherit; font-weight: 600; font-size: 14px; }
        .btn-submit { width: 100%; padding: 16px; background: var(--blue); color: white; border: none; border-radius: 12px; font-weight: 800; cursor: pointer; transition: 0.3s; }
        .btn-submit:hover { background: #2563eb; transform: translateY(-2px); }

        /* Table Styling */
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; font-size: 11px; color: #cbd5e1; text-transform: uppercase; padding-bottom: 15px; }
        td { padding: 18px 0; border-top: 1px solid #f1f5f9; font-size: 14px; font-weight: 600; }
        .spec-badge { background: #eff6ff; color: var(--blue); padding: 4px 10px; border-radius: 8px; font-size: 12px; font-weight: 800; }
        .btn-delete { color: var(--danger); text-decoration: none; font-size: 11px; font-weight: 800; border: 1.5px solid #fee2e2; padding: 8px 12px; border-radius: 10px; transition: 0.3s; }
        .btn-delete:hover { background: var(--danger); color: white; border-color: var(--danger); }
        
        .alert { background: #f0fdf4; color: #16a34a; padding: 15px; border-radius: 12px; margin-bottom: 25px; font-weight: 700; border: 1px solid #bbf7d0; }
    </style>
</head>
<body>

<nav>
    <div class="logo">+ MedCore</div>
    <a href="admin_dashboard.php" class="nav-link">Dashboard</a>
</nav>

<main>
    <header style="margin-bottom: 40px;">
        <h1 style="font-family: 'Syne'; font-size: 32px;">Doctor Database</h1>
        <p style="color: #64748b; font-weight: 600;">Add, view, or remove medical staff from the system.</p>
    </header>

    <?php if(isset($_GET['msg'])): ?>
        <div class="alert"><?php echo htmlspecialchars($_GET['msg']); ?></div>
    <?php endif; ?>

    <div class="grid">
        <div class="card">
            <h2 style="font-family: 'Syne'; font-size: 20px; margin-bottom: 25px;">Register Doctor</h2>
            <form method="POST">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="name" placeholder="Dr. Aarav Sharma" required>
                </div>
                <div class="form-group">
                    <label>Specialization</label>
                    <input type="text" name="specialization" placeholder="e.g. Neurology" required>
                </div>
                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="text" name="phone" placeholder="+91 9876543210" required>
                </div>
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" placeholder="doctor@medcore.com" required>
                </div>
                <div class="form-group">
                    <label>Experience (Years)</label>
                    <input type="number" name="experience" placeholder="e.g. 8" required>
                </div>
                <button type="submit" name="add_doctor" class="btn-submit">Add to Database</button>
            </form>
        </div>

        <div class="card">
            <h2 style="font-family: 'Syne'; font-size: 20px; margin-bottom: 25px;">Active Personnel</h2>
            <table>
                <thead>
                    <tr>
                        <th>Doctor Information</th>
                        <th>Specialty</th>
                        <th>Experience</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $docs = mysqli_query($conn, "SELECT * FROM doctors ORDER BY id DESC");
                    if(mysqli_num_rows($docs) > 0):
                        while($row = mysqli_fetch_assoc($docs)):
                    ?>
                    <tr>
                        <td>
                            <div style="font-weight: 800;"><?php echo htmlspecialchars($row['name']); ?></div>
                            <div style="font-size: 12px; color: #64748b;"><?php echo $row['email']; ?> | <?php echo $row['phone']; ?></div>
                        </td>
                        <td><span class="spec-badge"><?php echo htmlspecialchars($row['specialization']); ?></span></td>
                        <td><?php echo $row['experience']; ?> Years</td>
                        <td>
                            <a href="?delete_id=<?php echo $row['id']; ?>" 
                               class="btn-delete" 
                               onclick="return confirm('Permanently delete this doctor from the database?')">Delete Record</a>
                        </td>
                    </tr>
                    <?php endwhile; else: ?>
                    <tr>
                        <td colspan="4" style="text-align:center; padding: 40px; color: #94a3b8; font-weight:700;">No doctors found in database.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

</body>
</html>