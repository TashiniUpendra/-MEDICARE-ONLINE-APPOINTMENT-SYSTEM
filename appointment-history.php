<?php
session_start();
include "db.php";

/* Patient only check */
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "patient") {
    header("Location: login.php");
    exit();
}

$patient_id = $_SESSION["id"] ?? 0;

/* Fetch appointments from database for this specific patient */
$sql = "SELECT a.*, COALESCE(d.name, 'Doctor') AS doctor_name 
        FROM appointments a 
        LEFT JOIN users d ON a.doctor_id = d.id 
        WHERE a.patient_id = ? 
        ORDER BY a.id DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MediCare | My Appointments</title>

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

/* Card Box */
.card { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.03); }
.card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }

/* Table Styling */
table { width: 100%; border-collapse: collapse; }
th, td { padding: 14px 12px; border-bottom: 1px solid #f1f5f9; text-align: left; font-size: 13px; }
th { background: #f8fafc; color: #64748b; font-weight: 600; }

/* Status Badges */
.badge { padding: 5px 12px; border-radius: 20px; font-size: 11px; font-weight: 600; display: inline-block; }
.badge-pending { background: #fef3c7; color: #d97706; }
.badge-confirmed { background: #dcfce7; color: #15803d; }
.badge-cancelled { background: #fee2e2; color: #b91c1c; }

/* Action Button */
.btn-primary { background: #0b78a6; color: white; padding: 10px 18px; border-radius: 8px; text-decoration: none; font-size: 13px; font-weight: 500; transition: 0.3s; display: inline-flex; align-items: center; gap: 8px; }
.btn-primary:hover { background: #085a7d; }

.empty-state { text-align: center; padding: 40px 20px; color: #64748b; }
.empty-state i { font-size: 45px; color: #cbd5e1; margin-bottom: 15px; }
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
                <li><a href="patient-dashboard.php"><i class="fa-solid fa-chart-pie"></i> Dashboard</a></li>
                <li><a href="doctor-list.php"><i class="fa-solid fa-user-doctor"></i> Book Appointment</a></li>
                <li><a href="appointment-history.php" class="active"><i class="fa-solid fa-calendar-check"></i> My Appointments</a></li>
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
            <h2>My Appointments</h2>
            <a href="doctor-list.php" class="btn-primary">
                <i class="fa-solid fa-plus"></i> Book New Appointment
            </a>
        </div>

        <div class="card">
            <div class="card-header">
                <h3><i class="fa-solid fa-clock-rotate-left" style="color:#0b78a6;"></i> Appointment History</h3>
            </div>

            <?php if ($result && $result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Appointment ID</th>
                        <th>Doctor</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Reason</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><strong>#<?php echo htmlspecialchars($row["id"]); ?></strong></td>
                        <td>Dr. <?php echo htmlspecialchars($row["doctor_name"]); ?></td>
                        <td><i class="fa-regular fa-calendar" style="color:#0b78a6;"></i> <?php echo htmlspecialchars($row["appointment_date"]); ?></td>
                        <td><i class="fa-regular fa-clock" style="color:#0b78a6;"></i> <?php echo htmlspecialchars($row["appointment_time"]); ?></td>
                        <td><?php echo htmlspecialchars($row["reason"] ?? 'General Checkup'); ?></td>
                        <td>
                            <span class="badge badge-<?php echo strtolower($row["status"] ?? 'pending'); ?>">
                                <?php echo htmlspecialchars($row["status"] ?? 'Pending'); ?>
                            </span>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="empty-state">
                <i class="fa-solid fa-calendar-xmark"></i>
                <p style="font-size: 16px; font-weight: 500; color: #475569;">No appointments found</p>
                <p style="font-size: 13px; margin-top: 5px;">You haven't booked any appointments yet.</p>
                <br>
                <a href="doctor-list.php" class="btn-primary">Book Your First Appointment</a>
            </div>
            <?php endif; ?>
        </div>

    </div>

</body>
</html><?php
session_start();
include "db.php";

/* Patient only check */
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "patient") {
    header("Location: login.php");
    exit();
}

$patient_id = $_SESSION["id"] ?? 0;

/* Fetch appointments from database for this specific patient */
$sql = "SELECT a.*, COALESCE(d.name, 'Doctor') AS doctor_name 
        FROM appointments a 
        LEFT JOIN users d ON a.doctor_id = d.id 
        WHERE a.patient_id = ? 
        ORDER BY a.id DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MediCare | My Appointments</title>

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

/* Card Box */
.card { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.03); }
.card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }

/* Table Styling */
table { width: 100%; border-collapse: collapse; }
th, td { padding: 14px 12px; border-bottom: 1px solid #f1f5f9; text-align: left; font-size: 13px; }
th { background: #f8fafc; color: #64748b; font-weight: 600; }

/* Status Badges */
.badge { padding: 5px 12px; border-radius: 20px; font-size: 11px; font-weight: 600; display: inline-block; }
.badge-pending { background: #fef3c7; color: #d97706; }
.badge-confirmed { background: #dcfce7; color: #15803d; }
.badge-cancelled { background: #fee2e2; color: #b91c1c; }

/* Action Button */
.btn-primary { background: #0b78a6; color: white; padding: 10px 18px; border-radius: 8px; text-decoration: none; font-size: 13px; font-weight: 500; transition: 0.3s; display: inline-flex; align-items: center; gap: 8px; }
.btn-primary:hover { background: #085a7d; }

.empty-state { text-align: center; padding: 40px 20px; color: #64748b; }
.empty-state i { font-size: 45px; color: #cbd5e1; margin-bottom: 15px; }
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
                <li><a href="patient-dashboard.php"><i class="fa-solid fa-chart-pie"></i> Dashboard</a></li>
                <li><a href="doctor-list.php"><i class="fa-solid fa-user-doctor"></i> Book Appointment</a></li>
                <li><a href="appointment-history.php" class="active"><i class="fa-solid fa-calendar-check"></i> My Appointments</a></li>
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
            <h2>My Appointments</h2>
            <a href="doctor-list.php" class="btn-primary">
                <i class="fa-solid fa-plus"></i> Book New Appointment
            </a>
        </div>

        <div class="card">
            <div class="card-header">
                <h3><i class="fa-solid fa-clock-rotate-left" style="color:#0b78a6;"></i> Appointment History</h3>
            </div>

            <?php if ($result && $result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Appointment ID</th>
                        <th>Doctor</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Reason</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><strong>#<?php echo htmlspecialchars($row["id"]); ?></strong></td>
                        <td>Dr. <?php echo htmlspecialchars($row["doctor_name"]); ?></td>
                        <td><i class="fa-regular fa-calendar" style="color:#0b78a6;"></i> <?php echo htmlspecialchars($row["appointment_date"]); ?></td>
                        <td><i class="fa-regular fa-clock" style="color:#0b78a6;"></i> <?php echo htmlspecialchars($row["appointment_time"]); ?></td>
                        <td><?php echo htmlspecialchars($row["reason"] ?? 'General Checkup'); ?></td>
                        <td>
                            <span class="badge badge-<?php echo strtolower($row["status"] ?? 'pending'); ?>">
                                <?php echo htmlspecialchars($row["status"] ?? 'Pending'); ?>
                            </span>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="empty-state">
                <i class="fa-solid fa-calendar-xmark"></i>
                <p style="font-size: 16px; font-weight: 500; color: #475569;">No appointments found</p>
                <p style="font-size: 13px; margin-top: 5px;">You haven't booked any appointments yet.</p>
                <br>
                <a href="doctor-list.php" class="btn-primary">Book Your First Appointment</a>
            </div>
            <?php endif; ?>
        </div>

    </div>

</body>
</html>
