<?php 
// 1. Session and Database Start
ob_start();
session_start();
include 'db.php'; // Ensure this file has your $conn variable

// 2. Redirect Logic Fix (Prevents the infinite loop)
// If the user isn't logged in, send them to login.php, NOT the same page
if(!isset($_SESSION['user'])) {
    header("Location: register.php"); 
    exit();
}

// 3. Search Logic
$search_query = "";
if(isset($_POST['search'])) {
    $term = mysqli_real_escape_string($conn, $_POST['search_term']);
    $search_query = " WHERE full_name LIKE '%$term%' OR id = '$term' ";
}

// 4. Fetching Data
$sql = "SELECT * FROM patient_profiles $search_query ORDER BY id ASC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>MedCore | Manage Patients</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        :root { 
            --primary: #6366f1; 
            --secondary: #8b5cf6; 
            --bg: #f0f4ff; 
            --glass: rgba(255, 255, 255, 0.8);
            --text-main: #1e293b;
            --text-sub: #64748b;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'DM Sans', sans-serif; background: var(--bg); display: flex; color: var(--text-main); }
        
        /* Sidebar Navigation */
        nav { 
            width: 280px; 
            background: var(--glass); 
            backdrop-filter: blur(15px); 
            padding: 40px; 
            height: 100vh; 
            position: fixed; 
            border-right: 1px solid rgba(255,255,255,0.4); 
            display: flex;
            flex-direction: column;
        }

        .nav-logo { 
            font-family: 'Syne'; 
            font-weight: 800; 
            font-size: 26px; 
            color: var(--primary); 
            margin-bottom: 50px; 
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .nav-link { 
            text-decoration: none; 
            color: var(--text-sub); 
            font-weight: 700; 
            display: block; 
            padding: 15px 20px; 
            border-radius: 15px;
            transition: 0.3s;
            margin-bottom: 5px;
        }
        
        .nav-link:hover { background: white; color: var(--primary); transform: translateX(5px); }
        .nav-link.active { background: white; color: var(--primary); box-shadow: 0 4px 15px rgba(0,0,0,0.03); }

        /* Main Content Area */
        main { flex: 1; margin-left: 280px; padding: 60px; min-height: 100vh; }

        .header-flex { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            margin-bottom: 40px; 
        }

        .header-flex h1 { font-family: 'Syne'; font-size: 32px; letter-spacing: -1px; }

        /* Search Bar */
        .search-wrapper { display: flex; gap: 10px; }
        .search-input { 
            padding: 15px 20px; 
            border-radius: 16px; 
            border: 1px solid #fff; 
            width: 320px; 
            outline: none; 
            font-family: inherit;
            box-shadow: 0 10px 25px rgba(0,0,0,0.02);
        }

        .btn-search { 
            background: linear-gradient(135deg, var(--primary), var(--secondary)); 
            color: white; 
            border: none; 
            padding: 0 30px; 
            border-radius: 16px; 
            font-weight: 700; 
            cursor: pointer; 
            transition: 0.3s;
        }
        .btn-search:hover { opacity: 0.9; transform: scale(1.02); }

        /* Modern Table Card */
        .card-container { 
            background: var(--glass); 
            border-radius: 35px; 
            border: 1px solid #fff; 
            box-shadow: 0 25px 50px rgba(0,0,0,0.04); 
            overflow: hidden;
            padding: 10px;
        }

        table { width: 100%; border-collapse: collapse; }
        th { 
            text-align: left; 
            padding: 25px 20px; 
            font-size: 11px; 
            text-transform: uppercase; 
            letter-spacing: 1.5px; 
            color: var(--text-sub); 
            border-bottom: 2px solid #f0f4ff;
        }

        td { padding: 25px 20px; border-bottom: 1px solid #f8fafc; vertical-align: middle; }

        .patient-info { display: flex; flex-direction: column; }
        .patient-info b { color: var(--text-main); font-size: 16px; }
        .patient-info span { color: var(--text-sub); font-size: 12px; }

        .blood-tag { 
            background: #fef2f2; 
            color: #ef4444; 
            padding: 6px 12px; 
            border-radius: 10px; 
            font-weight: 800; 
            font-size: 12px; 
        }

        .btn-view { 
            text-decoration: none; 
            color: var(--primary); 
            font-weight: 800; 
            font-size: 11px; 
            background: #f0eeff; 
            padding: 12px 24px; 
            border-radius: 14px; 
            transition: 0.3s;
            text-transform: uppercase;
        }
.trusted-text {
    color: #16a34a !important; /* Forces the green color */
    font-size: 12px !important;
    font-weight: 700 !important;
    background: none !important; /* Removes any background boxes */
    border: none !important;     /* Removes any borders */
    padding: 0 !important;      /* Removes any padding */
    display: block !important;
}

        .btn-view:hover { background: var(--primary); color: white; box-shadow: 0 8px 20px rgba(99, 102, 241, 0.2); }
    </style>
</head>
<body>

<nav>
    <div class="nav-logo">✚ MedCore</div>
    
    <a href="admin_dashboard.php" class="nav-link">← Back to Dashboard</a>
    
    <div style="margin-top: 30px; margin-bottom: 10px; font-size: 10px; font-weight: 800; color: #94a3b8; text-transform: uppercase; padding-left: 20px;">Management</div>
</nav>

<main>
    <div class="header-flex">
        <h1>Patient Directory</h1>
        
        <form method="POST" class="search-wrapper">
            <input type="text" name="search_term" class="search-input" placeholder="Search by name, ID or username...">
            <button type="submit" name="search" class="btn-search">Search</button>
        </form>
    </div>

    <div class="card-container">
        <table>
            <thead>
                <tr>
                    <th>Patient ID</th>
                    <th>Basic Details</th>
                    <th>Emergency Contact</th>
                    <th>Blood Group</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if(mysqli_num_rows($result) > 0): ?>
                    <?php while($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td style="color: var(--text-sub); font-weight: 800; font-size: 14px;">#<?php echo $row['id']; ?></td>
                        <td>
                            <div class="patient-info">
                                <b><?php echo htmlspecialchars($row['full_name']); ?></b>
                                <span class="trusted-text">Trusted Patient</span>
                            </div>
                        </td>
                        <td style="font-weight: 600; color: #475569;"><?php echo htmlspecialchars($row['emergency_contact']); ?></td>
                        <td>
                            <span class="blood-tag"><?php echo !empty($row['blood_group']) ? $row['blood_group'] : '??'; ?></span>
                        </td>
                        <td>
                            <a href="view_patient.php?id=<?php echo $row['id']; ?>" class="btn-view">
                                View Profile
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align:center; padding: 80px; color: var(--text-sub);">
                            <div style="font-size: 40px; margin-bottom: 10px;">📂</div>
                            <b>No patient records found.</b><br>Try searching for a different name.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

</body>
</html>