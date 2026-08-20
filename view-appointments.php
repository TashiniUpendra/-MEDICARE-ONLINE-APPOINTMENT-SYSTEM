<?php
session_start();
include "db.php";

/* Admin check */
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
    header("Location: login.php");
    exit();
}

$msg = "";

/* UPDATE APPOINTMENT STATUS */
if (isset($_GET['action']) && isset($_GET['id'])) {
    $appointment_id = intval($_GET['id']);
    $new_status = $_GET['action'] === 'confirm' ? 'Confirmed' : ($_GET['action'] === 'cancel' ? 'Cancelled' : '');

    if (!empty($new_status)) {
        $updateStmt = $conn->prepare("UPDATE appointments SET status = ? WHERE id = ?");
        $updateStmt->bind_param("si", $new_status, $appointment_id);
        if ($updateStmt->execute()) {
            $msg = "Appointment status updated to " . $new_status . "!";
        }
        $updateStmt->close();
    }
}

/* FETCH APPOINTMENTS WITH FILTER */
$filter = isset($_GET['status']) ? $_GET['status'] : 'all';

$sql = "SELECT a.*, u.name AS patient_name, u.email AS patient_email, 
               COALESCE(d.name, 'N/A') AS doctor_name 
        FROM appointments a
        LEFT JOIN users u ON a.patient_id = u.id
        LEFT JOIN users d ON a.doctor_id = d.id";

if ($filter !== 'all') {
    $sql .= " WHERE a.status = '" . $conn->real_escape_string($filter) . "'";
}

$sql .= " ORDER BY a.id DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MediCare | Manage Appointments</title>

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
.header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }

.card { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.03); }


.toolbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
.filter-btn { padding: 6px 14px; text-decoration: none; border-radius: 20px; font-size: 13px; font-weight: 500; background: #f1f5f9; color: #64748b; margin-left: 5px; }
.filter-btn.active { background: #0b78a6; color: white; }

table { width: 100%; border-collapse: collapse; }
th, td { padding: 12px; border-bottom: 1px solid #f1f5f9; text-align: left; font-size: 13px; }
th { background: #f8fafc; color: #64748b; font-weight: 600; }

.badge { padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; display: inline-block; }
.badge-pending { background: #fef3c7; color: #d97706; }
.badge-confirmed { background: #dcfce7; color: #15803d; }
.badge-cancelled { background: #fee2e2; color: #b91c1c; }


.btn-action { padding: 5px 10px; border-radius: 6px; text-decoration: none; font-size: 11px; font-weight: 600; margin-right: 3px; }
.btn-confirm { background: #dcfce7; color: #166534; }
.btn-confirm:hover { background: #166534; color: white; }
.btn-cancel { background: #fee2e2; color: #991b1b; }
.btn-cancel:hover { background: #991b1b; color: white; }

.alert { padding: 10px; border-radius: 6px; font-size: 13px; margin-bottom: 15px; background: #dcfce7; color: #166534; font-weight: 500; }
</style>
</head>
<body>

    
    <div class="sidebar">
        <div>
            <div class="brand">
                <i class="fa-solid fa-user-doctor"></i> MediCare
            </div>
            <ul class="sidebar-menu">
                <li><a href="admin-dashboard.php"><i class="fa-solid fa-chart-pie"></i> Dashboard</a></li>
                <li><a href="manage-doctors.php"><i class="fa-solid fa-user-md"></i> Manage Doctors</a></li>
                <li><a href="patient-records.php"><i class="fa-solid fa-procedures"></i> Patient Records</a></li>
                <li><a href="view-appointments.php" class="active"><i class="fa-solid fa-calendar-check"></i> Appointments</a></li>
            </ul>
        </div>
        <div>
            <a href="logout.php" style="color: #f87171; text-decoration:none;"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
        </div>
    </div>


    <div class="main-content">
        <div class="header">
            <h2>Manage Appointments</h2>
        </div>

        <div class="card">
            <?php if(!empty($msg)): ?>
                <div class="alert"><?php echo htmlspecialchars($msg); ?></div>
            <?php endif; ?>

            <div class="toolbar">
                <h3><i class="fa-solid fa-calendar-alt" style="color:#0b78a6;"></i> Appointment List</h3>
                <div>
                    <a href="?status=all" class="filter-btn <?php echo $filter === 'all' ? 'active' : ''; ?>">All</a>
                    <a href="?status=Pending" class="filter-btn <?php echo $filter === 'Pending' ? 'active' : ''; ?>">Pending</a>
                    <a href="?status=Confirmed" class="filter-btn <?php echo $filter === 'Confirmed' ? 'active' : ''; ?>">Confirmed</a>
                </div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Patient</th>
                        <th>Doctor</th>
                        <th>Date & Time</th>
                        <th>Reason</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td>#<?php echo $row["id"]; ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($row["patient_name"] ?? 'N/A'); ?></strong>
                            </td>
                            <td>Dr. <?php echo htmlspecialchars($row["doctor_name"] ?? 'N/A'); ?></td>
                            <td>
                                <?php echo htmlspecialchars($row["appointment_date"]); ?><br>
                                <small style="color:#64748b;"><?php echo htmlspecialchars($row["appointment_time"]); ?></small>
                            </td>
                            <td><?php echo htmlspecialchars($row["reason"] ?? 'Regular Checkup'); ?></td>
                            <td>
                                <span class="badge badge-<?php echo strtolower($row["status"] ?? 'pending'); ?>">
                                    <?php echo htmlspecialchars($row["status"] ?? 'Pending'); ?>
                                </span>
                            </td>
                            <td>
                                <a href="?action=confirm&id=<?php echo $row['id']; ?>" class="btn-action btn-confirm"><i class="fa-solid fa-check"></i> Confirm</a>
                                <a href="?action=cancel&id=<?php echo $row['id']; ?>" class="btn-action btn-cancel" onclick="return confirm('Cancel this appointment?')"><i class="fa-solid fa-xmark"></i> Cancel</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="7" style="text-align:center;">No appointments found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>
