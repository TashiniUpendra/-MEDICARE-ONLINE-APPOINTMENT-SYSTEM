<?php
session_start();
include "db.php";

/* Admin Check */
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
    header("Location: login.php");
    exit();
}

$admin_name = $_SESSION["name"] ?? "Admin";

$total_doctors = $conn->query("SELECT COUNT(*) as count FROM users WHERE role='doctor'")->fetch_assoc()['count'] ?? 0;
$total_patients = $conn->query("SELECT COUNT(*) as count FROM users WHERE role='patient'")->fetch_assoc()['count'] ?? 0;
$total_appointments = $conn->query("SELECT COUNT(*) as count FROM appointments")->fetch_assoc()['count'] ?? 0;
$pending_appointments = $conn->query("SELECT COUNT(*) as count FROM appointments WHERE status='Pending'")->fetch_assoc()['count'] ?? 0;

$recent_sql = "SELECT a.*, u.name AS patient_name, d.name AS doctor_name 
              FROM appointments a
              JOIN users u ON a.patient_id = u.id
              JOIN doctors d ON a.doctor_id = d.id
              ORDER BY a.id DESC LIMIT 5";
$recent_result = $conn->query($recent_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MediCare | Admin Dashboard</title>

<!-- FontAwesome Icons & Google Fonts -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>
* { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
body { display: flex; background-color: #f4f7fe; color: #333; min-height: 100vh; }

.sidebar { width: 260px; background: #0b78a6; color: #fff; padding: 20px; display: flex; flex-direction: column; justify-content: space-between; }
.sidebar .brand { font-size: 22px; font-weight: 700; display: flex; align-items: center; gap: 10px; margin-bottom: 30px; }
.sidebar-menu { list-style: none; }
.sidebar-menu li { margin-bottom: 10px; }
.sidebar-menu a { display: flex; align-items: center; gap: 12px; color: #e0f2fe; text-decoration: none; padding: 12px 15px; border-radius: 8px; font-weight: 500; transition: 0.3s; }
.sidebar-menu a:hover, .sidebar-menu a.active { background: rgba(255, 255, 255, 0.2); color: #fff; }

.main-content { flex: 1; padding: 30px; overflow-y: auto; }

.header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
.user-info { display: flex; align-items: center; gap: 15px; }
.user-info i { font-size: 24px; color: #0b78a6; background: #e0f2fe; padding: 10px; border-radius: 50%; }
.logout-btn { background: #ef4444; color: white; border: none; padding: 8px 16px; border-radius: 6px; cursor: pointer; text-decoration: none; font-weight: 500; }
.logout-btn:hover { background: #dc2626; }

.cards-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 30px; }
.card { background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.03); display: flex; align-items: center; justify-content: space-between; }
.card-info h3 { font-size: 28px; font-weight: 700; color: #1e293b; }
.card-info p { font-size: 14px; color: #64748b; }
.card-icon { font-size: 30px; width: 60px; height: 60px; border-radius: 12px; display: flex; align-items: center; justify-content: center; }
.icon-blue { background: #e0f2fe; color: #0284c7; }
.icon-green { background: #dcfce7; color: #16a34a; }
.icon-purple { background: #f3e8ff; color: #9333ea; }
.icon-orange { background: #ffedd5; color: #ea580c; }

.table-container { background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.03); }
.table-container h3 { margin-bottom: 15px; color: #1e293b; }
table { width: 100%; border-collapse: collapse; text-align: left; }
th, td { padding: 12px 15px; border-bottom: 1px solid #f1f5f9; font-size: 14px; }
th { background: #f8fafc; color: #64748b; font-weight: 600; }
.badge { padding: 5px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; }
.badge-pending { background: #fef3c7; color: #d97706; }
.badge-confirmed { background: #dcfce7; color: #15803d; }
.badge-cancelled { background: #fee2e2; color: #b91c1c; }
</style>
</head>
<body>

    
    <div class="sidebar">
        <div>
            <div class="brand">
                <i class="fa-solid fa-user-doctor"></i> MediCare
            </div>
            <ul class="sidebar-menu">
                <li><a href="admin-dashboard.php" class="active"><i class="fa-solid fa-chart-pie"></i> Dashboard</a></li>
                <li><a href="manage-doctors.php"><i class="fa-solid fa-user-md"></i> Manage Doctors</a></li>
                <li><a href="patient-records.php"><i class="fa-solid fa-procedures"></i> Patient Records</a></li>
                <li><a href="view-appointments.php"><i class="fa-solid fa-calendar-check"></i> Appointments</a></li>
            </ul>
        </div>
        <div>
            <a href="logout.php" style="color: #f87171;"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        
        <!-- Header -->
        <div class="header">
            <h2>Admin Dashboard</h2>
            <div class="user-info">
                <i class="fa-solid fa-user"></i>
                <div>
                    <strong><?php echo htmlspecialchars($admin_name); ?></strong>
                    <p style="font-size: 12px; color: #64748b;">Administrator</p>
                </div>
                <a href="logout.php" class="logout-btn">Logout</a>
            </div>
        </div>

        <!-- Metrics Cards -->
        <div class="cards-grid">
            <div class="card">
                <div class="card-info">
                    <h3><?php echo $total_doctors; ?></h3>
                    <p>Total Doctors</p>
                </div>
                <div class="card-icon icon-blue">
                    <i class="fa-solid fa-user-md"></i>
                </div>
            </div>

            <div class="card">
                <div class="card-info">
                    <h3><?php echo $total_patients; ?></h3>
                    <p>Total Patients</p>
                </div>
                <div class="card-icon icon-green">
                    <i class="fa-solid fa-users"></i>
                </div>
            </div>

            <div class="card">
                <div class="card-info">
                    <h3><?php echo $total_appointments; ?></h3>
                    <p>Total Appointments</p>
                </div>
                <div class="card-icon icon-purple">
                    <i class="fa-solid fa-calendar-alt"></i>
                </div>
            </div>

            <div class="card">
                <div class="card-info">
                    <h3><?php echo $pending_appointments; ?></h3>
                    <p>Pending Requests</p>
                </div>
                <div class="card-icon icon-orange">
                    <i class="fa-solid fa-clock"></i>
                </div>
            </div>
        </div>

        
        <div class="table-container">
            <h3>Recent Appointment Requests</h3>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Patient</th>
                        <th>Doctor</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($recent_result && $recent_result->num_rows > 0): ?>
                        <?php while($row = $recent_result->fetch_assoc()): ?>
                        <tr>
                            <td>#<?php echo $row["id"]; ?></td>
                            <td><?php echo htmlspecialchars($row["patient_name"]); ?></td>
                            <td>Dr. <?php echo htmlspecialchars($row["doctor_name"]); ?></td>
                            <td><?php echo htmlspecialchars($row["appointment_date"]); ?></td>
                            <td><?php echo htmlspecialchars($row["appointment_time"]); ?></td>
                            <td>
                                <span class="badge badge-<?php echo strtolower($row["status"]); ?>">
                                    <?php echo htmlspecialchars($row["status"]); ?>
                                </span>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="6" style="text-align:center;">No recent appointments found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>

</body>
</html>
