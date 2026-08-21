<?php
session_start();
include "db.php";

// Doctor Session Validation
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "doctor") {
    header("Location: login.php");
    exit();
}

$doctor_id = $_SESSION["user_id"] ?? $_SESSION["id"];
$today = date('Y-m-d');

// Handle Status Updates (Confirm / Cancel)
if (isset($_GET['action']) && isset($_GET['id'])) {
    $appointment_id = (int)$_GET['id'];
    $new_status = ($_GET['action'] == 'confirm') ? 'Confirmed' : 'Cancelled';

    $update_stmt = $conn->prepare("UPDATE appointments SET status = ? WHERE id = ? AND doctor_id = ?");
    $update_stmt->bind_param("sii", $new_status, $appointment_id, $doctor_id);
    if ($update_stmt->execute()) {
        // Notification to Patient
        $p_res = $conn->query("SELECT patient_id FROM appointments WHERE id = '$appointment_id'");
        if ($p_row = $p_res->fetch_assoc()) {
            $patient_id = $p_row['patient_id'];
            $msg = "Your appointment (#$appointment_id) has been $new_status by Dr. " . ($_SESSION['name'] ?? '');
            $conn->query("INSERT INTO notifications (user_id, message) VALUES ('$patient_id', '$msg')");
        }
        header("Location: doctor-dashboard.php");
        exit();
    }
}

// Fetch Stats
$today_count = $conn->query("SELECT COUNT(*) as count FROM appointments WHERE doctor_id = '$doctor_id' AND appointment_date = '$today'")->fetch_assoc()['count'] ?? 0;
$pending_count = $conn->query("SELECT COUNT(*) as count FROM appointments WHERE doctor_id = '$doctor_id' AND LOWER(status) = 'pending'")->fetch_assoc()['count'] ?? 0;
$total_count = $conn->query("SELECT COUNT(*) as count FROM appointments WHERE doctor_id = '$doctor_id'")->fetch_assoc()['count'] ?? 0;

// Fetch Recent Appointments with Patient Name
$query = "SELECT a.id, a.appointment_date, a.appointment_time, a.reason, a.status, a.payment_status, u.name as patient_name 
          FROM appointments a 
          JOIN users u ON a.patient_id = u.id 
          WHERE a.doctor_id = '$doctor_id' 
          ORDER BY a.id DESC";

$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediCare | Doctor Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { background: #f4f7fe; display: flex; min-height: 100vh; }

        .sidebar { width: 260px; background: #0b78a6; color: white; padding: 30px 20px; display: flex; flex-direction: column; justify-content: space-between; }
        .sidebar h2 { font-size: 24px; font-weight: 700; margin-bottom: 40px; }
        .sidebar nav a { display: flex; align-items: center; gap: 12px; color: rgba(255,255,255,0.8); text-decoration: none; padding: 12px 16px; border-radius: 8px; margin-bottom: 10px; font-weight: 500; transition: 0.3s; }
        .sidebar nav a:hover, .sidebar nav a.active { background: rgba(255,255,255,0.15); color: white; }

        .main-content { flex: 1; padding: 40px; }
        .top-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .top-bar h1 { font-size: 26px; color: #1e293b; font-weight: 700; }
        
        .stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 20px; border-radius: 12px; border: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; }
        .stat-info p { font-size: 13px; color: #64748b; font-weight: 500; }
        .stat-info h2 { font-size: 28px; color: #0b78a6; font-weight: 700; }
        .stat-icon { width: 50px; height: 50px; background: #e0f2fe; color: #0b78a6; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 20px; }

        .table-card { background: white; border-radius: 14px; padding: 25px; border: 1px solid #e2e8f0; box-shadow: 0 4px 20px rgba(0,0,0,0.03); }
        .table-card h3 { font-size: 18px; color: #1e293b; margin-bottom: 20px; }
        
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th { font-size: 12px; text-transform: uppercase; color: #64748b; padding: 12px 14px; border-bottom: 2px solid #f1f5f9; }
        td { padding: 16px 14px; font-size: 14px; color: #334155; border-bottom: 1px solid #f1f5f9; }

        .badge { padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; display: inline-block; }
        .badge-pending { background: #fef3c7; color: #d97706; }
        .badge-confirmed { background: #dcfce7; color: #15803d; }
        .badge-cancelled { background: #fee2e2; color: #b91c1c; }

        .btn-action { text-decoration: none; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 600; margin-right: 5px; display: inline-block; }
        .btn-confirm { background: #16a34a; color: white; }
        .btn-cancel { background: #dc2626; color: white; }
    </style>
</head>
<body>

    <div class="sidebar">
        <div>
            <h2>MediCare</h2>
            <nav>
                <a href="doctor-dashboard.php" class="active"><i class="fa-solid fa-chart-pie"></i> Dashboard</a>
                <a href="doctor-dashboard.php"><i class="fa-solid fa-calendar-check"></i> Appointments</a>
            </nav>
        </div>
        <nav><a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></nav>
    </div>

    <div class="main-content">
        <div class="top-bar">
            <div>
                <h1>Welcome, Dr. <?php echo htmlspecialchars($_SESSION['name'] ?? 'Doctor'); ?> 👋</h1>
                <p style="color: #64748b; font-size: 14px;">Here is your daily overview and patient schedules.</p>
            </div>
            <div style="background: white; padding: 8px 16px; border-radius: 8px; border: 1px solid #e2e8f0; font-size: 14px; font-weight: 500;">
                <i class="fa-regular fa-calendar"></i> <?php echo date('F d, Y'); ?>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-info"><p>Today's Patients</p><h2><?php echo $today_count; ?></h2></div>
                <div class="stat-icon"><i class="fa-solid fa-user-clock"></i></div>
            </div>
            <div class="stat-card">
                <div class="stat-info"><p>Pending Requests</p><h2><?php echo $pending_count; ?></h2></div>
                <div class="stat-icon"><i class="fa-solid fa-hourglass-half"></i></div>
            </div>
            <div class="stat-card">
                <div class="stat-info"><p>Total Bookings</p><h2><?php echo $total_count; ?></h2></div>
                <div class="stat-icon"><i class="fa-solid fa-calendar-check"></i></div>
            </div>
        </div>

        <div class="table-card">
            <h3>Recent Bookings</h3>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Patient Name</th>
                        <th>Date & Time</th>
                        <th>Reason</th>
                        <th>Payment Status</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($result && $result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><strong>#<?php echo $row['id']; ?></strong></td>
                            <td><?php echo htmlspecialchars($row['patient_name']); ?></td>
                            <td><?php echo $row['appointment_date'] . ' (' . $row['appointment_time'] . ')'; ?></td>
                            <td><?php echo htmlspecialchars($row['reason']); ?></td>
                            <td><span style="font-weight:600; color:#0b78a6;"><?php echo $row['payment_status'] ?? 'Pending'; ?></span></td>
                            <td>
                                <?php 
                                    $st = strtolower($row['status']);
                                    if($st == 'confirmed') echo '<span class="badge badge-confirmed">Confirmed</span>';
                                    else if($st == 'cancelled') echo '<span class="badge badge-cancelled">Cancelled</span>';
                                    else echo '<span class="badge badge-pending">Pending</span>';
                                ?>
                            </td>
                            <td>
                                <?php if(strtolower($row['status']) == 'pending'): ?>
                                    <a href="doctor-dashboard.php?action=confirm&id=<?php echo $row['id']; ?>" class="btn-action btn-confirm" onclick="return confirm('Confirm this appointment?')"><i class="fa-solid fa-check"></i> Confirm</a>
                                    <a href="doctor-dashboard.php?action=cancel&id=<?php echo $row['id']; ?>" class="btn-action btn-cancel" onclick="return confirm('Cancel this appointment?')"><i class="fa-solid fa-xmark"></i> Cancel</a>
                                <?php else: ?>
                                    <span style="font-size: 12px; color: #94a3b8;">No Action Required</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="7" style="text-align: center; color: #94a3b8; padding: 25px;">No recent appointments found for you.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>
