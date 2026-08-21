<?php
session_start();
include "db.php";

if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "patient") {
    header("Location: login.php");
    exit();
}

$patient_id = $_SESSION["user_id"] ?? $_SESSION["id"];

// Fetch Appointments with actual Doctor Name using JOIN
$query = "SELECT a.id, a.appointment_date, a.appointment_time, a.reason, a.status, u.name as doctor_name 
          FROM appointments a 
          JOIN users u ON a.doctor_id = u.id 
          WHERE a.patient_id = '$patient_id' 
          ORDER BY a.id DESC";

$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediCare | My Appointments</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { background: #f4f7fe; display: flex; min-height: 100vh; }

        /* Sidebar Styling */
        .sidebar { width: 260px; background: #0b78a6; color: white; padding: 30px 20px; display: flex; flex-direction: column; justify-content: space-between; }
        .sidebar h2 { font-size: 24px; font-weight: 700; margin-bottom: 40px; }
        .sidebar nav a { display: flex; align-items: center; gap: 12px; color: rgba(255,255,255,0.8); text-decoration: none; padding: 12px 16px; border-radius: 8px; margin-bottom: 10px; font-weight: 500; font-size: 15px; transition: 0.3s; }
        .sidebar nav a:hover, .sidebar nav a.active { background: rgba(255,255,255,0.15); color: white; }

        /* Main Content Area */
        .main-content { flex: 1; padding: 40px; }
        .header-action { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .header-action h1 { font-size: 28px; color: #1e293b; font-weight: 700; }
        
        .btn-book { background: #0b78a6; color: white; text-decoration: none; padding: 12px 20px; border-radius: 8px; font-weight: 600; font-size: 14px; transition: 0.3s; display: inline-flex; align-items: center; gap: 8px; }
        .btn-book:hover { background: #085a7d; }

        /* Table Card Container */
        .table-card { background: white; border-radius: 14px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); padding: 25px; border: 1px solid #e2e8f0; }
        
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th { font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b; font-weight: 600; padding: 14px 16px; border-bottom: 2px solid #f1f5f9; }
        td { padding: 18px 16px; font-size: 14px; color: #334155; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
        tr:last-child td { border-bottom: none; }

        /* Badges */
        .badge { display: inline-block; padding: 6px 14px; border-radius: 20px; font-size: 12px; font-weight: 600; text-transform: capitalize; }
        .badge-pending { background: #fef3c7; color: #d97706; }
        .badge-confirmed { background: #dcfce7; color: #15803d; }
        .badge-cancelled { background: #fee2e2; color: #b91c1c; }

        .date-text, .time-text { display: inline-flex; align-items: center; gap: 6px; }
        .date-text i { color: #0b78a6; }
        .time-text i { color: #0b78a6; }
    </style>
</head>
<body>

    <!-- Sidebar -->
    <div class="sidebar">
        <div>
            <h2>MediCare</h2>
            <nav>
                <a href="patient-dashboard.php"><i class="fa-solid fa-chart-pie"></i> Dashboard</a>
                <a href="appointment-booking.php"><i class="fa-solid fa-calendar-plus"></i> Book Appointment</a>
                <a href="appointment-history.php" class="active"><i class="fa-solid fa-calendar-check"></i> My Appointments</a>
            </nav>
        </div>
        <nav>
            <a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="header-action">
            <h1>My Appointments</h1>
            <a href="appointment-booking.php" class="btn-book"><i class="fa-solid fa-plus"></i> Book New Appointment</a>
        </div>

        <div class="table-card">
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
                    <?php if($result && $result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><strong>#<?php echo $row['id']; ?></strong></td>
                            <td>Dr. <?php echo htmlspecialchars($row['doctor_name']); ?></td>
                            <td><span class="date-text"><i class="fa-regular fa-calendar"></i> <?php echo $row['appointment_date']; ?></span></td>
                            <td><span class="time-text"><i class="fa-regular fa-clock"></i> <?php echo $row['appointment_time']; ?></span></td>
                            <td style="max-width: 280px;"><?php echo htmlspecialchars($row['reason']); ?></td>
                            <td>
                                <?php 
                                    $status = strtolower($row['status']);
                                    if($status == 'confirmed') {
                                        echo '<span class="badge badge-confirmed">Confirmed</span>';
                                    } else if($status == 'cancelled') {
                                        echo '<span class="badge badge-cancelled">Cancelled</span>';
                                    } else {
                                        echo '<span class="badge badge-pending">Pending</span>';
                                    }
                                ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center; color: #94a3b8; padding: 30px;">No appointments found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>
