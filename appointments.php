<?php
session_start();
include "db.php";

/* Session Check */
if (!isset($_SESSION["role"])) {
    header("Location: login.php");
    exit();
}

$user_role  = $_SESSION["role"];
$user_email = $_SESSION["email"] ?? '';
$user_id    = $_SESSION["id"] ?? $_SESSION["user_id"] ?? 0;

$message = "";
$msg_type = "";

/* Dynamic Dashboard Link */
$dashboard_link = "patient-dashboard.php"; 
if ($user_role === 'admin') {
    $dashboard_link = "admin-dashboard.php";
} elseif ($user_role === 'doctor') {
    $dashboard_link = "doctor-dashboard.php";
}

/* Handle Payment Status Update */
if (isset($_GET['action']) && $_GET['action'] === 'update_payment' && isset($_GET['id'])) {
    if ($user_role === 'admin' || $user_role === 'doctor') {
        $app_id = intval($_GET['id']);
        $new_status = $_GET['status'] ?? 'Paid';

        $update_stmt = $conn->prepare("UPDATE appointments SET payment_status = ? WHERE id = ?");
        $update_stmt->bind_param("si", $new_status, $app_id);

        if ($update_stmt->execute()) {
            $message = "Payment status updated to " . htmlspecialchars($new_status) . "!";
            $msg_type = "success";
        } else {
            $message = "Failed to update payment status.";
            $msg_type = "danger";
        }
    }
}

/* 
  SQL Query based on exact columns:
  a.doctor_name, a.patient_name, a.patient_email, a.doctor_id, a.patient_id
*/
$base_query = "SELECT a.*, 
                      IF(a.doctor_name IS NOT NULL AND a.doctor_name != '', a.doctor_name, COALESCE(d.name, 'Dr. Assigned')) AS doc_fullname,
                      IF(a.patient_name IS NOT NULL AND a.patient_name != '', a.patient_name, COALESCE(u.name, 'Patient')) AS pat_fullname
               FROM appointments a
               LEFT JOIN doctors d ON a.doctor_id = d.id
               LEFT JOIN users u ON (a.patient_id = u.id OR a.patient_email = u.email)";

if ($user_role === 'admin') {
    $sql = $base_query . " ORDER BY a.id DESC";
    $stmt = $conn->prepare($sql);
} elseif ($user_role === 'doctor') {
    $sql = $base_query . " WHERE a.doctor_id = ? OR d.email = ? ORDER BY a.id DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $user_id, $user_email);
} else {
    $sql = $base_query . " WHERE a.patient_id = ? OR a.patient_email = ? ORDER BY a.id DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $user_id, $user_email);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediCare | Appointments</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        * { font-family: 'Plus Jakarta Sans', sans-serif; box-sizing: border-box; }
        body { background-color: #f8fafc; margin: 0; min-height: 100vh; }

        .wrapper { display: flex; min-height: 100vh; }

        .sidebar {
            width: 270px;
            background: linear-gradient(180deg, #0284c7 0%, #0369a1 100%);
            color: #ffffff;
            display: flex; flex-direction: column;
            padding: 32px 24px; flex-shrink: 0;
        }

        .brand-title { font-size: 26px; font-weight: 800; margin-bottom: 40px; display: flex; align-items: center; gap: 10px; }
        .nav-menu { display: flex; flex-direction: column; gap: 10px; flex-grow: 1; }

        .nav-item-link {
            display: flex; align-items: center; gap: 14px;
            color: rgba(255, 255, 255, 0.82); text-decoration: none;
            padding: 13px 18px; border-radius: 12px; font-weight: 600; font-size: 14px;
            transition: all 0.2s ease;
        }
        .nav-item-link:hover { background: rgba(255, 255, 255, 0.15); color: #fff; }
        .nav-item-link.active { background: rgba(255, 255, 255, 0.22); color: #fff; }

        .logout-link { margin-top: auto; color: #fff; }

        .main-content { flex-grow: 1; padding: 40px 50px; overflow-y: auto; }
        .page-title { font-size: 28px; font-weight: 800; color: #0f172a; margin-bottom: 25px; }

        .card-table {
            background: #ffffff; border-radius: 20px; padding: 25px;
            border: 1px solid #e2e8f0; box-shadow: 0 10px 30px rgba(0,0,0,0.02);
        }

        .table > :not(caption) > * > * { padding: 16px 20px; vertical-align: middle; }
        .badge-paid { background-color: #dcfce7; color: #15803d; font-weight: 700; font-size: 12px; padding: 6px 14px; border-radius: 20px; }
        .badge-pending { background-color: #fef9c3; color: #a16207; font-weight: 700; font-size: 12px; padding: 6px 14px; border-radius: 20px; }
    </style>
</head>

<body>

<div class="wrapper">
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="brand-title"><i class="bi bi-hospital-fill"></i> MediCare</div>
        <div class="nav-menu">
            <a href="<?php echo $dashboard_link; ?>" class="nav-item-link">
                <i class="bi bi-grid-1x2-fill"></i> Dashboard
            </a>
            
            <a href="appointments.php" class="nav-item-link active">
                <i class="bi bi-calendar2-check-fill"></i> Appointments
            </a>

            <?php if ($user_role === 'admin'): ?>
                <a href="doctors.php" class="nav-item-link">
                    <i class="bi bi-person-badge-fill"></i> Doctors
                </a>
            <?php else: ?>
                <a href="profile.php" class="nav-item-link">
                    <i class="bi bi-person-badge-fill"></i> Profile
                </a>
            <?php endif; ?>
        </div>
        <a href="logout.php" class="nav-item-link logout-link">
            <i class="bi bi-box-arrow-left"></i> Logout
        </a>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <h1 class="page-title">Manage Appointments</h1>

        <?php if (!empty($message)): ?>
            <div class="alert alert-<?php echo $msg_type; ?> alert-dismissible fade show rounded-3 mb-4" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i><?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card-table">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#ID</th>
                            <th>Doctor</th>
                            <th>Patient</th>
                            <th>Date & Time</th>
                            <th>Room No</th>
                            <th>Status</th>
                            <th>Payment Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><strong>#<?php echo $row['id']; ?></strong></td>
                                    <td>
                                        <div class="fw-bold text-dark"><?php echo htmlspecialchars($row['doc_fullname']); ?></div>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark"><?php echo htmlspecialchars($row['pat_fullname']); ?></div>
                                        <?php if(!empty($row['patient_email'])): ?>
                                            <small class="text-muted"><?php echo htmlspecialchars($row['patient_email']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div><i class="bi bi-calendar3 me-1"></i><?php echo htmlspecialchars($row['appointment_date'] ?? 'N/A'); ?></div>
                                        <small class="text-muted"><i class="bi bi-clock me-1"></i><?php echo htmlspecialchars($row['appointment_time'] ?? 'N/A'); ?></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border"><?php echo htmlspecialchars($row['room_no'] ?? 'Not Set'); ?></span>
                                    </td>
                                    <td>
                                        <span class="badge bg-info text-dark"><?php echo htmlspecialchars($row['status'] ?? 'Pending'); ?></span>
                                    </td>
                                    <td>
                                        <?php if (($row['payment_status'] ?? 'Pending') === 'Paid'): ?>
                                            <span class="badge-paid"><i class="bi bi-check-circle-fill me-1"></i> Paid</span>
                                        <?php else: ?>
                                            <span class="badge-pending"><i class="bi bi-clock-history me-1"></i> Pending</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($user_role === 'admin' || $user_role === 'doctor'): ?>
                                            <?php if (($row['payment_status'] ?? 'Pending') !== 'Paid'): ?>
                                                <a href="appointments.php?action=update_payment&id=<?php echo $row['id']; ?>&status=Paid" 
                                                   class="btn btn-sm btn-outline-success fw-bold"
                                                   onclick="return confirm('Mark this appointment payment as Paid?');">
                                                    <i class="bi bi-check-circle me-1"></i> Mark as Paid
                                                </a>
                                            <?php else: ?>
                                                <a href="appointments.php?action=update_payment&id=<?php echo $row['id']; ?>&status=Pending" 
                                                   class="btn btn-sm btn-outline-secondary fw-bold"
                                                   onclick="return confirm('Change payment status back to Pending?');">
                                                    <i class="bi bi-arrow-counterclockwise me-1"></i> Mark Unpaid
                                                </a>
                                            <?php endif; ?>
                                        <?php elseif ($user_role === 'patient' && ($row['payment_status'] ?? 'Pending') !== 'Paid'): ?>
                                            <a href="pay.php?appointment_id=<?php echo $row['id']; ?>" class="btn btn-sm btn-primary fw-bold">
                                                <i class="bi bi-credit-card me-1"></i> Pay Now
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">No appointments found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
