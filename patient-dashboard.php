<?php
session_start();
include "db.php";

/* Only patient can access */
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "patient") {
    header("Location: login.php");
    exit();
}

/* Get patient info */
$patientId = $_SESSION["user_id"] ?? $_SESSION["id"] ?? 0;
$patientName = $_SESSION["name"] ?? "Patient";

/* Fetch Summary Stats for Patient */
$total_appointments = 0;
$pending_appointments = 0;
$confirmed_appointments = 0;
$unread_notif_count = 0;

if ($patientId) {
    // Total Appointments Count
    $stmt1 = $conn->prepare("SELECT COUNT(*) as count FROM appointments WHERE patient_id = ?");
    $stmt1->bind_param("i", $patientId);
    $stmt1->execute();
    $total_appointments = $stmt1->get_result()->fetch_assoc()['count'] ?? 0;
    $stmt1->close();

    // Pending Appointments Count
    $stmt2 = $conn->prepare("SELECT COUNT(*) as count FROM appointments WHERE patient_id = ? AND status = 'Pending'");
    $stmt2->bind_param("i", $patientId);
    $stmt2->execute();
    $pending_appointments = $stmt2->get_result()->fetch_assoc()['count'] ?? 0;
    $stmt2->close();

    // Confirmed Appointments Count
    $stmt3 = $conn->prepare("SELECT COUNT(*) as count FROM appointments WHERE patient_id = ? AND status = 'Confirmed'");
    $stmt3->bind_param("i", $patientId);
    $stmt3->execute();
    $confirmed_appointments = $stmt3->get_result()->fetch_assoc()['count'] ?? 0;
    $stmt3->close();

    // Fetch Unread Notifications Count
    $stmt4 = $conn->prepare("SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0");
    $stmt4->bind_param("i", $patientId);
    $stmt4->execute();
    $unread_notif_count = $stmt4->get_result()->fetch_assoc()['count'] ?? 0;
    $stmt4->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MediCare | Patient Dashboard</title>

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
.sidebar-menu a { display: flex; align-items: center; gap: 12px; color: #e0f2fe; text-decoration: none; padding: 12px 15px; border-radius: 8px; font-weight: 500; transition: 0.3s; position: relative; }
.sidebar-menu a:hover, .sidebar-menu a.active { background: rgba(255, 255, 255, 0.2); color: #fff; }

/* Notification Badge */
.badge { background: #ef4444; color: white; border-radius: 20px; padding: 2px 8px; font-size: 11px; font-weight: 700; margin-left: auto; }

/* Main Content Area */
.main-content { flex: 1; padding: 30px; overflow-y: auto; }
.header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }

/* Header Notification Icon */
.notif-btn { position: relative; font-size: 20px; color: #0b78a6; background: white; width: 42px; height: 42px; border-radius: 50%; display: flex; align-items: center; justify-content: center; text-decoration: none; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
.notif-btn-badge { position: absolute; top: -2px; right: -2px; background: #ef4444; color: white; border-radius: 50%; width: 18px; height: 18px; font-size: 10px; font-weight: bold; display: flex; align-items: center; justify-content: center; }

/* Welcome Card */
.welcome-card { background: linear-gradient(135deg, #0b78a6, #0284c7); color: white; padding: 25px; border-radius: 12px; margin-bottom: 25px; display: flex; justify-content: space-between; align-items: center; }
.welcome-card h2 { font-size: 24px; font-weight: 600; margin-bottom: 5px; }
.welcome-card p { opacity: 0.9; font-size: 14px; }

/* Stats Grid */
.stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 25px; }
.stat-card { background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.03); display: flex; align-items: center; gap: 15px; }
.stat-icon { width: 50px; height: 50px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 22px; }
.stat-icon.blue { background: #e0f2fe; color: #0284c7; }
.stat-icon.orange { background: #ffedd5; color: #ea580c; }
.stat-icon.green { background: #dcfce7; color: #16a34a; }
.stat-info h3 { font-size: 22px; font-weight: 700; color: #1e293b; }
.stat-info p { font-size: 12px; color: #64748b; }

/* Dashboard Actions Grid */
.actions-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; }
.action-card { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.03); text-align: center; transition: 0.3s; }
.action-card:hover { transform: translateY(-5px); box-shadow: 0 8px 20px rgba(0,0,0,0.06); }
.action-card i { font-size: 36px; color: #0b78a6; margin-bottom: 15px; }
.action-card h3 { font-size: 18px; color: #1e293b; margin-bottom: 8px; }
.action-card p { font-size: 13px; color: #64748b; margin-bottom: 20px; line-height: 1.4; }
.btn { display: inline-block; background: #0b78a6; color: white; padding: 9px 20px; border-radius: 6px; text-decoration: none; font-size: 13px; font-weight: 600; transition: 0.3s; }
.btn:hover { background: #085a7d; }

.btn-logout { background: #fee2e2; color: #ef4444; }
.btn-logout:hover { background: #ef4444; color: white; }
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
                <li><a href="patient-dashboard.php" class="active"><i class="fa-solid fa-chart-pie"></i> Dashboard</a></li>
                <li><a href="doctor-list.php"><i class="fa-solid fa-user-doctor"></i> Book Appointment</a></li>
                <li><a href="appointment-history.php"><i class="fa-solid fa-calendar-check"></i> My Appointments</a></li>
                <li>
                    <a href="notifications.php">
                        <i class="fa-solid fa-bell"></i> Notifications
                        <?php if ($unread_notif_count > 0): ?>
                            <span class="badge"><?php echo $unread_notif_count; ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <li><a href="profile.php"><i class="fa-solid fa-user-gear"></i> Profile</a></li>
            </ul>
        </div>
        <div>
            <a href="logout.php" style="color: #f87171; text-decoration:none;"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        
        <div class="header">
            <h2>Patient Portal</h2>
            <div style="display: flex; align-items: center; gap: 20px;">
                <!-- Notifications Bell Icon -->
                <a href="notifications.php" class="notif-btn" title="View Notifications">
                    <i class="fa-solid fa-bell"></i>
                    <?php if ($unread_notif_count > 0): ?>
                        <span class="notif-btn-badge"><?php echo $unread_notif_count; ?></span>
                    <?php endif; ?>
                </a>
                
                <div style="font-size: 13px; color: #64748b;">
                    <i class="fa-solid fa-calendar-day"></i> <?php echo date("l, F j, Y"); ?>
                </div>
            </div>
        </div>

        <!-- Welcome Banner -->
        <div class="welcome-card">
            <div>
                <h2>Welcome back, <?php echo htmlspecialchars($patientName); ?>! 👋</h2>
                <p>Manage your appointments, view schedule, and stay up to date with your health.</p>
            </div>
            <i class="fa-solid fa-hospital-user" style="font-size: 50px; opacity: 0.8;"></i>
        </div>

        <!-- Stats Overview -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon blue">
                    <i class="fa-solid fa-calendar-alt"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $total_appointments; ?></h3>
                    <p>Total Bookings</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon orange">
                    <i class="fa-solid fa-clock"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $pending_appointments; ?></h3>
                    <p>Pending Confirmation</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon green">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $confirmed_appointments; ?></h3>
                    <p>Confirmed Appointments</p>
                </div>
            </div>
        </div>

        <!-- Action Cards Grid -->
        <div class="actions-grid">
            
            <div class="action-card">
                <i class="fa-solid fa-calendar-plus"></i>
                <h3>Book Appointment</h3>
                <p>Find available doctors and schedule a new appointment easily.</p>
                <a href="doctor-list.php" class="btn">Book Now</a>
            </div>

            <div class="action-card">
                <i class="fa-solid fa-clock-rotate-left"></i>
                <h3>My Appointments</h3>
                <p>Check status, details, and history of your doctor consultations.</p>
                <a href="appointment-history.php" class="btn">View Appointments</a>
            </div>

            <div class="action-card">
                <i class="fa-solid fa-bell"></i>
                <h3>Notifications</h3>
                <p>Check your appointment updates and system alerts.</p>
                <a href="notifications.php" class="btn">View Notifications</a>
            </div>

            <div class="action-card">
                <i class="fa-solid fa-id-card"></i>
                <h3>My Profile</h3>
                <p>View and edit your personal details and account settings.</p>
                <a href="profile.php" class="btn">View Profile</a>
            </div>

        </div>

    </div>

</body>
</html>
