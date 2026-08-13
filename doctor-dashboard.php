<?php
session_start();
include "db.php";

/* Doctor session check */
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "doctor") {
    header("Location: login.php");
    exit();
}

$doctor_id   = $_SESSION["id"] ?? 0;
$doctorName  = $_SESSION["name"] ?? "Doctor";

/* Fetch Stats from Database */
// Total Appointments
$total_stmt = $conn->prepare("SELECT COUNT(*) AS total FROM appointments WHERE doctor_id = ?");
$total_stmt->bind_param("i", $doctor_id);
$total_stmt->execute();
$total_result = $total_stmt->get_result()->fetch_assoc();
$total_appointments = $total_result['total'] ?? 0;

// Today's Appointments
$today_date = date("Y-m-d");
$today_stmt = $conn->prepare("SELECT COUNT(*) AS today_total FROM appointments WHERE doctor_id = ? AND appointment_date = ?");
$today_stmt->bind_param("is", $doctor_id, $today_date);
$today_stmt->execute();
$today_result = $today_stmt->get_result()->fetch_assoc();
$today_appointments = $today_result['today_total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MediCare | Doctor Dashboard</title>

<!-- FontAwesome Icons & Google Fonts -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>
* { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
body { display: flex; background-color: #f4f7fe; color: #333; min-height: 100vh; }

/* Sidebar Navigation */
.sidebar { width: 260px; background: #0b78a6; color: #fff; padding: 20px; display: flex; flex-direction: column; justify-content: space-between; }
.sidebar .brand { font-size: 22px; font-weight: 700; display: flex; align-items: center; gap: 10px; margin-bottom: 30px; }
.sidebar-menu { list-style: none; }
.sidebar-menu li { margin-bottom: 10px; }
.sidebar-menu a { display: flex; align-items: center; gap: 12px; color: #e0f2fe; text-decoration: none; padding: 12px 15px; border-radius: 8px; font-weight: 500; transition: 0.3s; }
.sidebar-menu a:hover, .sidebar-menu a.active { background: rgba(255, 255, 255, 0.2); color: #fff; }

/* Main Content Area */
.main-content { flex: 1; padding: 30px; overflow-y: auto; }
.header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
.welcome-text h2 { font-size: 24px; color: #1e293b; }
.welcome-text p { font-size: 14px; color: #64748b; margin-top: 2px; }

/* Stats Widgets Grid */
.stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 30px; }
.stat-card { background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.03); display: flex; align-items: center; gap: 20px; }
.stat-icon { width: 60px; height: 60px; border-radius: 12px; background: #e0f2fe; color: #0b78a6; display: flex; align-items: center; justify-content: center; font-size: 26px; }
.stat-info h3 { font-size: 24px; color: #1e293b; font-weight: 700; }
.stat-info p { font-size: 13px; color: #64748b; }

/* Quick Action Cards Grid */
.cards-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; }
.action-card { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.03); transition: 0.3s; text-align: center; }
.action-card:hover { transform: translateY(-5px); box-shadow: 0 8px 20px rgba(0,0,0,0.06); }

.card-icon { width: 70px; height: 70px; background: #e0f2fe; color: #0b78a6; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 30px; margin: 0 auto 15px; }
.action-card h3 { font-size: 18px; font-weight: 600; color: #1e293b; margin-bottom: 8px; }
.action-card p { font-size: 13px; color: #64748b; margin-bottom: 20px; }

.btn-action { display: inline-block; width: 100%; background: #0b78a6; color: white; padding: 10px; border-radius: 8px; text-decoration: none; font-size: 13px; font-weight: 600; transition: 0.3s; }
.btn-action:hover { background: #085a7d; }
</style>
</head>
<body>

    <!-- Sidebar Navigation -->
    <div class="sidebar">
        <div>
            <div class="brand">
                <i class="fa-solid fa-heart-pulse"></i> MediCare
            </div>
            <ul class="sidebar-menu">
                <li><a href="doctor-dashboard.php" class="active"><i class="fa-solid fa-chart-pie"></i> Dashboard</a></li>
                <li><a href="doctor-appointments.php"><i class="fa-solid fa-calendar-check"></i> My Appointments</a></li>
                <li><a href="doctor-profile.php"><i class="fa-solid fa-user-doctor"></i> Profile Settings</a></li>
            </ul>
        </div>
        <div>
            <a href="logout.php" style="color: #f87171; text-decoration:none;"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="main-content">
        
        <div class="header">
            <div class="welcome-text">
                <h2>Welcome, Dr. <?php echo htmlspecialchars($doctorName); ?> 👋</h2>
                <p>Manage your appointments and patient schedules easily.</p>
            </div>
        </div>

        <!-- Quick Stats Overview -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fa-solid fa-calendar-days"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $today_appointments; ?></h3>
                    <p>Today's Appointments</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fa-solid fa-users"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $total_appointments; ?></h3>
                    <p>Total Bookings</p>
                </div>
            </div>
        </div>

        <!-- Quick Action Cards -->
        <div class="cards-grid">
            
            <!-- Appointments Card -->
            <div class="action-card">
                <div class="card-icon">
                    <i class="fa-solid fa-calendar-check"></i>
                </div>
                <h3>Patient Appointments</h3>
                <p>View and manage patient bookings and schedules.</p>
                <a href="doctor-appointments.php" class="btn-action">
                    <i class="fa-solid fa-eye"></i> View Appointments
                </a>
            </div>

            <!-- Profile Card -->
            <div class="action-card">
                <div class="card-icon">
                    <i class="fa-solid fa-user-gear"></i>
                </div>
                <h3>Profile Settings</h3>
                <p>Update your personal details, email and profile picture.</p>
                <a href="doctor-profile.php" class="btn-action">
                    <i class="fa-solid fa-gear"></i> Manage Profile
                </a>
            </div>

        </div>

    </div>

</body>
</html>
