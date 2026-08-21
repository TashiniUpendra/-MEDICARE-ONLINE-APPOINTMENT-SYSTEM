<?php
session_start();
include "db.php";

/* Doctor Session Check */
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "doctor") {
    header("Location: login.php");
    exit();
}

$doctor_id = $_SESSION["id"] ?? $_SESSION["user_id"] ?? 0;

/* Fetch Doctor Information */
$stmt = $conn->prepare("SELECT name, specialization, image FROM users WHERE id = ? AND role = 'doctor'");
$stmt->bind_param("i", $doctor_id);
$stmt->execute();
$doctor = $stmt->get_result()->fetch_assoc();

$doc_name = $doctor['name'] ?? "Doctor";
$doc_spec = $doctor['specialization'] ?? "General Practitioner";
$doc_img  = (!empty($doctor["image"]) && file_exists("uploads/" . $doctor["image"])) 
            ? "uploads/" . $doctor["image"] 
            : "https://cdn-icons-png.flaticon.com/512/387/387561.png";

/* Status Updates (Approve, Complete, Cancel) */
if (isset($_GET['action']) && isset($_GET['id'])) {
    $appointment_id = intval($_GET['id']);
    $action = $_GET['action'];
    $new_status = "";

    if ($action === 'approve') $new_status = 'Approved';
    if ($action === 'complete') $new_status = 'Completed';
    if ($action === 'cancel') $new_status = 'Cancelled';

    if (!empty($new_status)) {
        $update_stmt = $conn->prepare("UPDATE appointments SET status = ? WHERE id = ? AND doctor_id = ?");
        $update_stmt->bind_param("sii", $new_status, $appointment_id, $doctor_id);
        $update_stmt->execute();
        header("Location: doctor-dashboard.php");
        exit();
    }
}

/* Dashboard Analytics / Statistics Query */
$total_appointments = $conn->query("SELECT COUNT(*) as count FROM appointments WHERE doctor_id = '$doctor_id'")->fetch_assoc()['count'] ?? 0;
$today_appointments = $conn->query("SELECT COUNT(*) as count FROM appointments WHERE doctor_id = '$doctor_id' AND DATE(appointment_date) = CURDATE()")->fetch_assoc()['count'] ?? 0;
$pending_requests   = $conn->query("SELECT COUNT(*) as count FROM appointments WHERE doctor_id = '$doctor_id' AND status = 'Pending'")->fetch_assoc()['count'] ?? 0;

/* Fetch Recent/Upcoming Appointments List */
$appointments_query = "SELECT a.*, u.name as patient_name, u.phone as patient_phone 
                       FROM appointments a 
                       JOIN users u ON a.user_id = u.id 
                       WHERE a.doctor_id = ? 
                       ORDER BY a.appointment_date DESC LIMIT 10";
$app_stmt = $conn->prepare($appointments_query);
$app_stmt->bind_param("i", $doctor_id);
$app_stmt->execute();
$appointments = $app_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediCare | Doctor Dashboard</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        * { font-family: 'Plus Jakarta Sans', sans-serif; }
        body { background: #f8fafc; color: #1e293b; min-height: 100vh; }

        /* Topbar */
        .topbar {
            background: #0f172a;
            padding: 16px 35px;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 3px solid #0284c7;
        }

        .user-nav {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .user-avatar {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #0284c7;
        }

        .nav-btn {
            background: rgba(255,255,255,0.1);
            color: white;
            padding: 8px 16px;
            border-radius: 10px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            transition: 0.3s;
        }
        .nav-btn:hover { background: #0284c7; color: white; }

        .main { padding: 35px 40px; }

        /* Welcome Header */
        .welcome-card {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            border-radius: 20px;
            padding: 30px;
            color: white;
            margin-bottom: 30px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
        }

        /* Stat Cards */
        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 24px;
            border: 1px solid #f1f5f9;
            box-shadow: 0 4px 20px rgba(0,0,0,0.02);
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 26px;
        }

        .icon-blue { background: #e0f2fe; color: #0284c7; }
        .icon-green { background: #dcfce7; color: #16a34a; }
        .icon-orange { background: #ffedd5; color: #ea580c; }

        /* Table Style */
        .table-card {
            background: white;
            border-radius: 18px;
            padding: 28px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.02);
            border: 1px solid #f1f5f9;
        }

        .badge-status {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .bg-pending { background: #fef3c7; color: #d97706; }
        .bg-approved { background: #dcfce7; color: #15803d; }
        .bg-completed { background: #e0f2fe; color: #0369a1; }
        .bg-cancelled { background: #fee2e2; color: #b91c1c; }
    </style>
</head>

<body>

    <!-- Topbar -->
    <div class="topbar">
        <h4 class="m-0 fw-bold">
            <i class="bi bi-heart-pulse-fill text-info me-2"></i>MediCare Doctor Panel
        </h4>
        <div class="user-nav">
            <img src="<?php echo $doc_img; ?>" class="user-avatar" alt="Doctor Profile">
            <div class="d-none d-md-block text-end">
                <div class="fw-bold fs-6">Dr. <?php echo htmlspecialchars($doc_name); ?></div>
                <small class="text-info"><?php echo htmlspecialchars($doc_spec); ?></small>
            </div>
            <a href="doctor-profile.php" class="nav-btn ms-2"><i class="bi bi-person-circle me-1"></i> Profile</a>
            <a href="logout.php" class="nav-btn bg-danger border-0"><i class="bi bi-box-arrow-right me-1"></i> Logout</a>
        </div>
    </div>

    <div class="main">

        <!-- Welcome Banner -->
        <div class="welcome-card d-flex align-items-center justify-content-between">
            <div>
                <h2 class="fw-bold mb-1">Welcome back, Dr. <?php echo htmlspecialchars($doc_name); ?>! 👋</h2>
                <p class="text-secondary m-0" style="color: #94a3b8 !important;">Here is a overview of your patient appointments and schedules today.</p>
            </div>
            <a href="doctor-profile.php" class="btn btn-info text-white fw-bold rounded-3 px-4 py-2">Edit Profile</a>
        </div>

        <!-- Quick Stats Cards -->
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-icon icon-blue">
                        <i class="bi bi-calendar-check"></i>
                    </div>
                    <div>
                        <small class="text-muted fw-semibold">Total Appointments</small>
                        <h3 class="fw-bold m-0"><?php echo $total_appointments; ?></h3>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-icon icon-green">
                        <i class="bi bi-clock-history"></i>
                    </div>
                    <div>
                        <small class="text-muted fw-semibold">Today's Appointments</small>
                        <h3 class="fw-bold m-0"><?php echo $today_appointments; ?></h3>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-icon icon-orange">
                        <i class="bi bi-hourglass-split"></i>
                    </div>
                    <div>
                        <small class="text-muted fw-semibold">Pending Requests</small>
                        <h3 class="fw-bold m-0"><?php echo $pending_requests; ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Appointments Table -->
        <div class="table-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="fw-bold m-0"><i class="bi bi-list-task text-primary me-2"></i> Recent Patient Appointments</h5>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Patient Name</th>
                            <th>Phone</th>
                            <th>Date & Time</th>
                            <th>Status</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($appointments->num_rows > 0): ?>
                            <?php $count = 1; while($row = $appointments->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $count++; ?></td>
                                    <td class="fw-semibold"><?php echo htmlspecialchars($row['patient_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['patient_phone']); ?></td>
                                    <td>
                                        <i class="bi bi-calendar3 me-1 text-muted"></i>
                                        <?php echo date('Y-m-d', strtotime($row['appointment_date'])); ?> 
                                        <span class="text-muted ms-1"><?php echo date('h:i A', strtotime($row['appointment_time'] ?? '10:00:00')); ?></span>
                                    </td>
                                    <td>
                                        <?php 
                                            $st = $row['status'] ?? 'Pending';
                                            $badge_cls = 'bg-pending';
                                            if($st == 'Approved') $badge_cls = 'bg-approved';
                                            if($st == 'Completed') $badge_cls = 'bg-completed';
                                            if($st == 'Cancelled') $badge_cls = 'bg-cancelled';
                                        ?>
                                        <span class="badge-status <?php echo $badge_cls; ?>"><?php echo strtoupper($st); ?></span>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($st == 'Pending'): ?>
                                            <a href="doctor-dashboard.php?action=approve&id=<?php echo $row['id']; ?>" class="btn btn-sm btn-success me-1"><i class="bi bi-check-lg"></i> Approve</a>
                                            <a href="doctor-dashboard.php?action=cancel&id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-danger"><i class="bi bi-x-lg"></i> Cancel</a>
                                        <?php elseif ($st == 'Approved'): ?>
                                            <a href="doctor-dashboard.php?action=complete&id=<?php echo $row['id']; ?>" class="btn btn-sm btn-primary me-1"><i class="bi bi-check-all"></i> Mark Complete</a>
                                            <a href="doctor-dashboard.php?action=cancel&id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-danger"><i class="bi bi-x-lg"></i> Cancel</a>
                                        <?php else: ?>
                                            <span class="text-muted small">No Actions</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">No appointments found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
