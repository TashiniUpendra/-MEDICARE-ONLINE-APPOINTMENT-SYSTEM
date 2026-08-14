<?php
session_start();
include "db.php";

/* Login check for Doctor */
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "doctor") {
    header("Location: login.php");
    exit();
}

$doctorName = $_SESSION["name"] ?? "";

// Doctor Name Formatting (Dr. prefix safely handle කිරීම)
$docSearchName = $doctorName;
if (strpos($docSearchName, 'Dr.') === false) {
    $docSearchName = "Dr. " . $docSearchName;
}

$msg = "";

/* Handle Status Update (Confirm / Cancel) */
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["update_status"])) {
    $appointment_id = intval($_POST["appointment_id"]);
    $new_status     = mysqli_real_escape_string($conn, $_POST["status"]);

    $update_sql = "UPDATE appointments SET status = '$new_status' WHERE id = '$appointment_id'";
    if (mysqli_query($conn, $update_sql)) {
        $msg = "Appointment status updated to '$new_status' successfully!";
    }
}

/* Fetch All Appointments for this Doctor */
$query = "SELECT * FROM appointments 
          WHERE doctor_name LIKE '%$doctorName%' 
             OR doctor_name LIKE '%$docSearchName%' 
          ORDER BY id DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MediCare | Patient Appointments</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<style>
body { background: #f0f8ff; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
.sidebar { width: 250px; position: fixed; top: 0; bottom: 0; background: #0b78a6; color: white; padding-top: 20px; }
.sidebar a { padding: 12px 25px; color: white; text-decoration: none; display: block; font-weight: 500; }
.sidebar a:hover, .sidebar a.active { background: #085a7d; }
.main-content { margin-left: 250px; padding: 30px; }
.card-custom { background: white; border-radius: 12px; padding: 25px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
.badge-pending { background: #ffeeba; color: #856404; }
.badge-confirmed { background: #d4edda; color: #155724; }
.badge-cancelled { background: #f8d7da; color: #721c24; }
</style>
</head>
<body>

<div class="sidebar">
    <h3 class="text-center fw-bold mb-4">MediCare</h3>
    <a href="doctor-dashboard.php"><i class="bi bi-grid-fill me-2"></i> Dashboard</a>
    <a href="doctor-appointments.php" class="active"><i class="bi bi-calendar-check me-2"></i> Appointments</a>
    <a href="doctor-profile.php"><i class="bi bi-person me-2"></i> My Profile</a>
    <a href="logout.php"><i class="bi bi-box-arrow-right me-2"></i> Logout</a>
</div>

<div class="main-content">
    <div class="mb-4">
        <h2>Patient Appointments 📅</h2>
        <p class="text-muted">View and update status for all booked patient appointments.</p>
    </div>

    <?php if (!empty($msg)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-1"></i> <?php echo $msg; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card-custom">
        <?php if ($result && mysqli_num_rows($result) > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#ID</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Reason</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = mysqli_fetch_assoc($result)): 
                            $status = $row['status'] ?? 'Pending';
                            
                            $badgeClass = 'badge-pending';
                            if ($status == 'Confirmed') { $badgeClass = 'badge-confirmed'; }
                            if ($status == 'Cancelled') { $badgeClass = 'badge-cancelled'; }
                        ?>
                        <tr>
                            <td class="fw-bold">#<?php echo $row['id']; ?></td>
                            <td><i class="bi bi-calendar-event me-1 text-primary"></i><?php echo htmlspecialchars($row['appointment_date']); ?></td>
                            <td><i class="bi bi-clock me-1 text-info"></i><?php echo htmlspecialchars($row['appointment_time']); ?></td>
                            <td><?php echo htmlspecialchars($row['reason']); ?></td>
                            <td><span class="badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($status); ?></span></td>
                            <td>
                                <form method="POST" class="d-flex gap-1">
                                    <input type="hidden" name="appointment_id" value="<?php echo $row['id']; ?>">
                                    <input type="hidden" name="update_status" value="1">
                                    <button type="submit" name="status" value="Confirmed" class="btn btn-sm btn-success" <?php echo ($status == 'Confirmed') ? 'disabled' : ''; ?>>
                                        <i class="bi bi-check-lg"></i> Confirm
                                    </button>
                                    <button type="submit" name="status" value="Cancelled" class="btn btn-sm btn-outline-danger" <?php echo ($status == 'Cancelled') ? 'disabled' : ''; ?>>
                                        <i class="bi bi-x-lg"></i> Cancel
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="bi bi-calendar-x text-muted" style="font-size: 3rem;"></i>
                <h4 class="mt-3">No appointments found</h4>
                <p class="text-muted">No appointments found for your account.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
