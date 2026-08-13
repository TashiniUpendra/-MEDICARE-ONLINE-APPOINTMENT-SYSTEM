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

/* Fetch Dynamic Stats */
// Total Appointments
$total_stmt = $conn->prepare("SELECT COUNT(*) AS total FROM appointments WHERE doctor_id = ?");
$total_stmt->bind_param("i", $doctor_id);
$total_stmt->execute();
$total_appointments = $total_stmt->get_result()->fetch_assoc()['total'] ?? 0;

// Today's Appointments
$today_date = date("Y-m-d");
$today_stmt = $conn->prepare("SELECT COUNT(*) AS today_total FROM appointments WHERE doctor_id = ? AND appointment_date = ?");
$today_stmt->bind_param("is", $doctor_id, $today_date);
$today_stmt->execute();
$today_appointments = $today_stmt->get_result()->fetch_assoc()['today_total'] ?? 0;

// Pending Appointments
$pending_stmt = $conn->prepare("SELECT COUNT(*) AS pending_total FROM appointments WHERE doctor_id = ? AND status = 'Pending'");
$pending_stmt->bind_param("i", $doctor_id);
$pending_stmt->execute();
$pending_appointments = $pending_stmt->get_result()->fetch_assoc()['pending_total'] ?? 0;

/* Fetch Recent 5 Appointments */
$recent_sql = "SELECT a.*, p.name AS p_name 
                FROM appointments a 
                LEFT JOIN users p ON a.patient_id = p.id 
                WHERE a.doctor_id = ? 
                ORDER BY a.appointment_date DESC, a.appointment_time ASC LIMIT 5";
$recent_stmt = $conn->prepare($recent_sql);
$recent_stmt->bind_param("i", $doctor_id);
$recent_stmt->execute();
$recent_result = $recent_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MediCare | Doctor Dashboard</title>

<!-- FontAwesome & Google Fonts -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>
* { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; }
body { display: flex; background-color: #f8fafc; color: #1e293b; min-height: 100vh; }

/* Sidebar Navigation */
.sidebar { width: 270px; background: linear-gradient(180deg, #0f172a 0%, #1e293b 100%); color: #fff; padding: 24px 20px; display: flex; flex-direction: column; justify-content: space-between; border-top-right-radius: 20px; border-bottom-right-radius: 20px; }
.sidebar .brand { font-size: 22px; font-weight: 700; display: flex; align-items: center; gap: 12px; color: #38bdf8; margin-bottom: 40px; }
.sidebar-menu { list-style: none; }
.sidebar-menu li { margin-bottom: 8px; }
.sidebar-menu a { display: flex; align-items: center; gap: 14px; color: #94a3b8; text-decoration: none; padding: 12px 16px; border-radius: 12px; font-weight: 500; font-size: 14px; transition: all 0.3s ease; }
.sidebar-menu a:hover, .sidebar-menu a.active { background: #0284c7; color: #ffffff; box-shadow: 0 4px 12px rgba(2, 132, 199, 0.3); }

/* Main Content Area */
.main-content { flex: 1; padding: 35px 40px; overflow-y: auto; }
.header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
.welcome-text h2 { font-size: 26px; font-weight: 700; color: #0f172a; }
.welcome-text p { font-size: 14px; color: #64748b; margin-top: 4px; }
.date-badge { background: #ffffff; padding: 10px 18px; border-radius: 30px; border: 1px solid #e2e8f0; font-size: 13px; font-weight: 600; color: #0284c7; display: flex; align-items: center; gap: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.02); }

/* Advanced Stats Grid */
.stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 35px; }
.stat-card { background: white; padding: 22px; border-radius: 16px; border: 1px solid #f1f5f9; box-shadow: 0 4px 20px rgba(0,0,0,0.02); display: flex; align-items: center; justify-content: space-between; transition: transform 0.2s ease; }
.stat-card:hover { transform: translateY(-3px); }
.stat-info p { font-size: 13px; font-weight: 500; color: #64748b; margin-bottom: 4px; }
.stat-info h3 { font-size: 28px; color: #0f172a; font-weight: 700; }
.stat-icon { width: 54px; height: 54px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 22px; }

/* Icon Theme Colors */
.icon-blue { background: #e0f2fe; color: #0284c7; }
.icon-green { background: #dcfce7; color: #16a34a; }
.icon-orange { background: #fef3c7; color: #d97706; }

/* Section Layout */
.dashboard-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 25px; }

/* Recent Table Card */
.card { background: white; padding: 24px; border-radius: 16px; border: 1px solid #f1f5f9; box-shadow: 0 4px 20px rgba(0,0,0,0.02); }
.card-title { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
.card-title h3 { font-size: 17px; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 10px; }
.card-title a { font-size: 13px; color: #0284c7; text-decoration: none; font-weight: 600; }

table { width: 100%; border-collapse: collapse; }
th, td { padding: 12px 14px; text-align: left; font-size: 13px; border-bottom: 1px solid #f1f5f9; }
th { color: #64748b; font-weight: 600; background: #f8fafc; border-radius: 6px; }

.badge { padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; display: inline-block; }
.badge-pending { background: #fef3c7; color: #b45309; }
.badge-confirmed { background: #dcfce7; color: #15803d; }
.badge-cancelled { background: #fee2e2; color: #b91c1c; }

/* Action Panel Cards */
.quick-actions { display: flex; flex-direction: column; gap: 15px; }
.action-box { background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%); color: white; padding: 22px; border-radius: 16px; text-align: left; }
.action-box h4 { font-size: 16px; font-weight: 700; margin-bottom: 6px; }
.action-box p { font-size: 12px; color: #bae6fd; margin-bottom: 16px; }
.btn-action { display: inline-block; background: white; color: #0369a1; padding: 10px 16px; border-radius: 10px; text-decoration: none; font-size: 13px; font-weight: 700; transition: 0.3s; }
.btn-action:hover { background: #f0f9ff; }
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
                <li><a href="doctor-dashboard.php" class="active"><i class="fa-solid fa-grip"></i> Dashboard</a></li>
                <li><a href="doctor-appointments.php"><i class="fa-solid fa-calendar-check"></i> Appointments</a></li>
                <li><a href="doctor-profile.php"><i class="fa-solid fa-user-doctor"></i> My Profile</a></li>
            </ul>
        </div>
        <div>
            <a href="logout.php" style="color: #f87171; text-decoration:none;"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="main-content">
        
        <!-- Header -->
        <div class="header">
            <div class="welcome-text">
                <h2>Welcome, Dr. <?php echo htmlspecialchars($doctorName); ?> 👋</h2>
                <p>Here is your daily overview and patient schedules.</p>
            </div>
            <div class="date-badge">
                <i class="fa-regular fa-calendar"></i> <?php echo date("F d, Y"); ?>
            </div>
        </div>

        <!-- Dynamic Stats Row -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-info">
                    <p>Today's Patients</p>
                    <h3><?php echo $today_appointments; ?></h3>
                </div>
                <div class="stat-icon icon-blue">
                    <i class="fa-solid fa-user-clock"></i>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-info">
                    <p>Pending Requests</p>
                    <h3><?php echo $pending_appointments; ?></h3>
                </div>
                <div class="stat-icon icon-orange">
                    <i class="fa-solid fa-hourglass-half"></i>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-info">
                    <p>Total Bookings</p>
                    <h3><?php echo $total_appointments; ?></h3>
                </div>
                <div class="stat-icon icon-green">
                    <i class="fa-solid fa-calendar-check"></i>
                </div>
            </div>
        </div>

        <!-- Dashboard Layout Grid -->
        <div class="dashboard-grid">
            
            <!-- Recent Appointments Table -->
            <div class="card">
                <div class="card-title">
                    <h3><i class="fa-solid fa-list-check" style="color:#0284c7;"></i> Recent Bookings</h3>
                    <a href="doctor-appointments.php">View All →</a>
                </div>

                <?php if ($recent_result && $recent_result->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Patient</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $recent_result->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($row['p_name'] ?? 'Patient'); ?></strong></td>
                            <td><?php echo htmlspecialchars($row['appointment_date']); ?></td>
                            <td><?php echo htmlspecialchars($row['appointment_time']); ?></td>
                            <td>
                                <span class="badge badge-<?php echo strtolower($row['status'] ?? 'pending'); ?>">
                                    <?php echo htmlspecialchars($row['status'] ?? 'Pending'); ?>
                                </span>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php else: ?>
                    <p style="text-align:center; color:#94a3b8; padding:20px 0; font-size:13px;">No recent appointments found.</p>
                <?php endif; ?>
            </div>

            <!-- Quick Action Box & Info -->
            <div class="quick-actions">
                <div class="action-box">
                    <h4>Manage Appointments</h4>
                    <p>Confirm or cancel pending patient appointment requests.</p>
                    <a href="doctor-appointments.php" class="btn-action"><i class="fa-solid fa-calendar"></i> Check Schedule</a>
                </div>

                <div class="card">
                    <div class="card-title">
                        <h3><i class="fa-solid fa-user-gear" style="color:#0284c7;"></i> Quick Settings</h3>
                    </div>
                    <p style="font-size:12px; color:#64748b; margin-bottom:15px;">Update your availability or profile info anytime.</p>
                    <a href="doctor-profile.php" class="btn-action" style="background:#f1f5f9; color:#0f172a; width:100%; text-align:center;">Edit Profile</a>
                </div>
            </div>

        </div>

    </div>

</body>
</html>
