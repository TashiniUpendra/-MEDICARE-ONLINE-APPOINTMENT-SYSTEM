<?php
session_start();
include "db.php";

/* Doctor session check */
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "doctor") {
    header("Location: login.php");
    exit();
}

$doctor_id  = $_SESSION["id"] ?? 0;
$doctorName = $_SESSION["name"] ?? "Doctor";

/* Update Appointment Status (Confirm / Cancel / Complete) */
if (isset($_GET['action']) && isset($_GET['id'])) {
    $appointment_id = intval($_GET['id']);
    $action = $_GET['action'];

    $status_map = [
        'confirm'  => 'Confirmed',
        'cancel'   => 'Cancelled',
        'complete' => 'Completed'
    ];

    if (array_key_exists($action, $status_map)) {
        $new_status = $status_map[$action];
        $update_stmt = $conn->prepare("UPDATE appointments SET status = ? WHERE id = ? AND doctor_id = ?");
        $update_stmt->bind_param("sii", $new_status, $appointment_id, $doctor_id);
        $update_stmt->execute();
        header("Location: doctor-appointments.php?msg=updated");
        exit();
    }
}

/* Fetch All Appointments for logged-in Doctor */
$query = "SELECT a.*, p.name AS patient_name, p.email AS patient_email 
          FROM appointments a 
          LEFT JOIN users p ON a.patient_id = p.id 
          WHERE a.doctor_id = ? 
          ORDER BY a.appointment_date DESC, a.appointment_time ASC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $doctor_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MediCare | My Appointments</title>

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

/* Appointments Table Card */
.card { background: white; padding: 24px; border-radius: 16px; border: 1px solid #f1f5f9; box-shadow: 0 4px 20px rgba(0,0,0,0.02); }

table { width: 100%; border-collapse: collapse; margin-top: 10px; }
th, td { padding: 14px 16px; text-align: left; font-size: 13.5px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
th { color: #64748b; font-weight: 600; background: #f8fafc; }

/* Status Badges */
.badge { padding: 5px 12px; border-radius: 20px; font-size: 11px; font-weight: 600; display: inline-block; }
.badge-pending { background: #fef3c7; color: #b45309; }
.badge-confirmed { background: #dcfce7; color: #15803d; }
.badge-completed { background: #e0f2fe; color: #0369a1; }
.badge-cancelled { background: #fee2e2; color: #b91c1c; }

/* Action Buttons */
.btn-sm { display: inline-flex; align-items: center; gap: 5px; padding: 6px 12px; border-radius: 8px; font-size: 12px; font-weight: 600; text-decoration: none; transition: 0.2s; }
.btn-confirm { background: #dcfce7; color: #15803d; }
.btn-confirm:hover { background: #bbf7d0; }
.btn-cancel { background: #fee2e2; color: #b91c1c; }
.btn-cancel:hover { background: #fecaca; }
.btn-complete { background: #e0f2fe; color: #0369a1; }
.btn-complete:hover { background: #bae6fd; }

.actions-cell { display: flex; gap: 6px; }
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
                <li><a href="doctor-dashboard.php"><i class="fa-solid fa-grip"></i> Dashboard</a></li>
                <li><a href="doctor-appointments.php" class="active"><i class="fa-solid fa-calendar-check"></i> Appointments</a></li>
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
                <h2>Patient Appointments 📅</h2>
                <p>View and update status for all booked patient appointments.</p>
            </div>
        </div>

        <!-- Appointments List -->
        <div class="card">
            <?php if ($result && $result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>#ID</th>
                        <th>Patient Name</th>
                        <th>Email</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): 
                        $status = $row['status'] ?? 'Pending';
                    ?>
                    <tr>
                        <td><strong>#<?php echo $row['id']; ?></strong></td>
                        <td><strong><?php echo htmlspecialchars($row['patient_name'] ?? 'Guest Patient'); ?></strong></td>
                        <td style="color:#64748b;"><?php echo htmlspecialchars($row['patient_email'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($row['appointment_date']); ?></td>
                        <td><?php echo htmlspecialchars($row['appointment_time']); ?></td>
                        <td>
                            <span class="badge badge-<?php echo strtolower($status); ?>">
                                <?php echo htmlspecialchars($status); ?>
                            </span>
                        </td>
                        <td class="actions-cell">
                            <?php if ($status === 'Pending'): ?>
                                <a href="doctor-appointments.php?action=confirm&id=<?php echo $row['id']; ?>" class="btn-sm btn-confirm" onclick="return confirm('Confirm this appointment?');">
                                    <i class="fa-solid fa-check"></i> Confirm
                                </a>
                                <a href="doctor-appointments.php?action=cancel&id=<?php echo $row['id']; ?>" class="btn-sm btn-cancel" onclick="return confirm('Cancel this appointment?');">
                                    <i class="fa-solid fa-xmark"></i> Cancel
                                </a>
                            <?php elseif ($status === 'Confirmed'): ?>
                                <a href="doctor-appointments.php?action=complete&id=<?php echo $row['id']; ?>" class="btn-sm btn-complete">
                                    <i class="fa-solid fa-check-double"></i> Mark Done
                                </a>
                                <a href="doctor-appointments.php?action=cancel&id=<?php echo $row['id']; ?>" class="btn-sm btn-cancel" onclick="return confirm('Cancel this appointment?');">
                                    <i class="fa-solid fa-xmark"></i> Cancel
                                </a>
                            <?php else: ?>
                                <span style="color:#94a3b8; font-size:12px;">No Actions</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?>
                <div style="text-align:center; padding: 40px 0;">
                    <i class="fa-regular fa-calendar-xmark" style="font-size:40px; color:#cbd5e1; margin-bottom:10px;"></i>
                    <p style="color:#64748b;">No appointments found for your account.</p>
                </div>
            <?php endif; ?>
        </div>

    </div>

</body>
</html>