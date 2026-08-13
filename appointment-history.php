<?php
session_start();
include "db.php";

if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "patient") {
    header("Location: login.php");
    exit();
}

$patientEmail = $_SESSION["email"] ?? "";
$patientId    = $_SESSION["id"] ?? 0;

/* Fetch Appointments */
$query = "SELECT * FROM appointments WHERE patient_id = '$patientId' OR patient_email = '$patientEmail' ORDER BY id DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>MediCare | My Appointments</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<style>
body { background: #f0f8ff; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
.sidebar { width: 250px; position: fixed; top: 0; bottom: 0; background: #0b78a6; color: white; padding-top: 20px; }
.sidebar a { padding: 12px 25px; color: white; text-decoration: none; display: block; font-weight: 500; }
.sidebar a:hover, .sidebar a.active { background: #085a7d; }
.main-content { margin-left: 250px; padding: 30px; }
.card-custom { background: white; border-radius: 12px; padding: 25px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
.badge-pending { background: #ffeeba; color: #856404; }
.badge-confirmed { background: #d4edda; color: #155724; }
</style>
</head>
<body>

<div class="sidebar">
    <h3 class="text-center fw-bold mb-4">MediCare</h3>
    <a href="patient-dashboard.php"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
    <a href="appointment-booking.php"><i class="bi bi-calendar-plus me-2"></i> Book Appointment</a>
    <a href="appointment-history.php" class="active"><i class="bi bi-calendar-check me-2"></i> My Appointments</a>
    <a href="logout.php"><i class="bi bi-box-arrow-right me-2"></i> Logout</a>
</div>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>My Appointments</h2>
        <a href="appointment-booking.php" class="btn btn-primary" style="background:#0b78a6;"><i class="bi bi-plus-lg me-1"></i> Book New Appointment</a>
    </div>

    <div class="card-custom">
        <?php if(isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                Appointment booked successfully!
            </div>
        <?php endif; ?>

        <?php if ($result && mysqli_num_rows($result) > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
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
                        <?php 
                        while($row = mysqli_fetch_assoc($result)): 
                            $status = $row['status'] ?? 'Pending';
                            $badgeClass = ($status == 'Confirmed') ? 'badge-confirmed' : 'badge-pending';
                            
                            // Doctor Name Formatting (Dr. duplicate වෙන එක නවත්වයි)
                            $docName = $row['doctor_name'] ?? 'Doctor';
                            if (strpos(strtolower($docName), 'dr.') === false) {
                                $docName = "Dr. " . $docName;
                            }
                        ?>
                        <tr>
                            <td class="fw-bold">#<?php echo $row['id']; ?></td>
                            <td><?php echo htmlspecialchars($docName); ?></td>
                            <td><i class="bi bi-calendar-event me-1 text-primary"></i><?php echo htmlspecialchars($row['appointment_date']); ?></td>
                            <td><i class="bi bi-clock me-1 text-info"></i><?php echo htmlspecialchars($row['appointment_time']); ?></td>
                            <td><?php echo htmlspecialchars($row['reason']); ?></td>
                            <td><span class="badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($status); ?></span></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="bi bi-calendar-x text-muted" style="font-size: 3rem;"></i>
                <h4 class="mt-3">No appointments found</h4>
                <p class="text-muted">You haven't booked any appointments yet.</p>
                <a href="appointment-booking.php" class="btn btn-primary" style="background:#0b78a6;">Book Your First Appointment</a>
            </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
