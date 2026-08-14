<?php
session_start();
include "db.php";

// Admin Session Validation
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
    header("Location: login.php");
    exit();
}

$admin_name = $_SESSION["name"] ?? "Admin";

// Fetch Counts for Cards
$total_doctors_query = mysqli_query($conn, "SELECT COUNT(*) AS total FROM users WHERE role = 'doctor'");
$total_doctors = mysqli_fetch_assoc($total_doctors_query)['total'] ?? 0;

$total_patients_query = mysqli_query($conn, "SELECT COUNT(*) AS total FROM users WHERE role = 'patient'");
$total_patients = mysqli_fetch_assoc($total_patients_query)['total'] ?? 0;

$total_appointments_query = mysqli_query($conn, "SELECT COUNT(*) AS total FROM appointments");
$total_appointments = mysqli_fetch_assoc($total_appointments_query)['total'] ?? 0;

$pending_requests_query = mysqli_query($conn, "SELECT COUNT(*) AS total FROM appointments WHERE status = 'pending'");
$pending_requests = mysqli_fetch_assoc($pending_requests_query)['total'] ?? 0;

// Fetch Recent Appointments with Patient and Doctor Names
$recent_query = "SELECT a.id, p.name AS patient_name, d.name AS doctor_name, a.appointment_date, a.appointment_time, a.status 
                FROM appointments a
                LEFT JOIN users p ON a.patient_id = p.id
                LEFT JOIN users d ON a.doctor_id = d.id
                ORDER BY a.id DESC LIMIT 5";

$recent_result = mysqli_query($conn, $recent_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediCare | Admin Dashboard</title>
    
    <!-- FontAwesome & Google Fonts -->
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
        
        /* Top Header */
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
        .header h2 { font-size: 24px; color: #1e293b; font-weight: 700; }
        .user-profile { display: flex; align-items: center; gap: 15px; }
        .admin-info { text-align: right; }
        .admin-info .name { font-weight: 600; color: #1e293b; }
        .admin-info .role { font-size: 12px; color: #64748b; }
        .btn-logout { background: #ef4444; color: white; padding: 8px 16px; border-radius: 6px; text-decoration: none; font-weight: 600; font-size: 14px; transition: 0.3s; }
        .btn-logout:hover { background: #dc2626; }

        /* Dashboard Stat Cards Grid */
        .cards-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .card { background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.03); display: flex; justify-content: space-between; align-items: center; }
        .card-info h3 { font-size: 28px; font-weight: 700; color: #1e293b; margin-bottom: 5px; }
        .card-info p { font-size: 13px; color: #64748b; font-weight: 500; }
        .card-icon { width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 22px; }
        
        /* Card Colors */
        .icon-doctors { background: #e0f2fe; color: #0284c7; }
        .icon-patients { background: #dcfce7; color: #16a34a; }
        .icon-appointments { background: #f3e8ff; color: #9333ea; }
        .icon-pending { background: #ffedd5; color: #ea580c; }

        /* Recent Appointments Section */
        .recent-section { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.03); }
        .recent-section h3 { font-size: 18px; color: #1e293b; margin-bottom: 20px; }
        
        /* Table Styling */
        .custom-table { width: 100%; border-collapse: collapse; text-align: left; }
        .custom-table th { background: #f8fafc; padding: 12px 15px; font-size: 13px; color: #64748b; font-weight: 600; text-transform: uppercase; }
        .custom-table td { padding: 15px; font-size: 14px; border-bottom: 1px solid #f1f5f9; color: #334155; }
        .custom-table tr:last-child td { border-bottom: none; }

        /* Status Badges */
        .badge { padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; display: inline-block; text-transform: capitalize; }
        .badge.pending { background: #ffedd5; color: #c2410c; }
        .badge.approved { background: #dcfce7; color: #15803d; }
        .badge.cancelled { background: #fee2e2; color: #b91c1c; }
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
                <li><a href="admin-dashboard.php" class="active"><i class="fa-solid fa-chart-pie"></i> Dashboard</a></li>
                <li><a href="manage-doctors.php"><i class="fa-solid fa-user-doctor"></i> Manage Doctors</a></li>
                <li><a href="patient-records.php"><i class="fa-solid fa-bed-pulse"></i> Patient Records</a></li>
                <li><a href="appointments.php"><i class="fa-solid fa-calendar-check"></i> Appointments</a></li>
            </ul>
        </div>
        <div>
            <a href="logout.php" style="color: #f87171; text-decoration:none;"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        
        <!-- Header -->
        <div class="header">
            <h2>Admin Dashboard</h2>
            <div class="user-profile">
                <i class="fa-solid fa-circle-user" style="font-size:38px; color:#0b78a6;"></i>
                <div class="admin-info">
                    <div class="name"><?php echo htmlspecialchars($admin_name); ?></div>
                    <div class="role">Administrator</div>
                </div>
                <a href="logout.php" class="btn-logout">Logout</a>
            </div>
        </div>

        <!-- Metric Cards -->
        <div class="cards-grid">
            <div class="card">
                <div class="card-info">
                    <h3><?php echo $total_doctors; ?></h3>
                    <p>Total Doctors</p>
                </div>
                <div class="card-icon icon-doctors">
                    <i class="fa-solid fa-user-doctor"></i>
                </div>
            </div>

            <div class="card">
                <div class="card-info">
                    <h3><?php echo $total_patients; ?></h3>
                    <p>Total Patients</p>
                </div>
                <div class="card-icon icon-patients">
                    <i class="fa-solid fa-users"></i>
                </div>
            </div>

            <div class="card">
                <div class="card-info">
                    <h3><?php echo $total_appointments; ?></h3>
                    <p>Total Appointments</p>
                </div>
                <div class="card-icon icon-appointments">
                    <i class="fa-solid fa-calendar-days"></i>
                </div>
            </div>

            <div class="card">
                <div class="card-info">
                    <h3><?php echo $pending_requests; ?></h3>
                    <p>Pending Requests</p>
                </div>
                <div class="card-icon icon-pending">
                    <i class="fa-solid fa-clock"></i>
                </div>
            </div>
        </div>

        <!-- Recent Appointment Requests Table -->
        <div class="recent-section">
            <h3>Recent Appointment Requests</h3>
            
            <table class="custom-table">
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
                    <?php if ($recent_result && mysqli_num_rows($recent_result) > 0): ?>
                        <?php while($row = mysqli_fetch_assoc($recent_result)): ?>
                            <tr>
                                <td>#<?php echo htmlspecialchars($row['id']); ?></td>
                                <td><?php echo htmlspecialchars($row['patient_name'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($row['doctor_name'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($row['appointment_date']); ?></td>
                                <td><?php echo htmlspecialchars($row['appointment_time']); ?></td>
                                <td>
                                    <?php 
                                        $status = strtolower($row['status']); 
                                        if($status == '' || $status == 'pending') { $status = 'pending'; }
                                    ?>
                                    <span class="badge <?php echo $status; ?>">
                                        <?php echo ucfirst($status); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align:center; padding:30px; color:#64748b;">
                                No recent appointments found.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>

</body>
</html>
