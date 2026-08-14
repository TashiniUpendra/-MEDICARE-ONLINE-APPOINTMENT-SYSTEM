<?php
session_start();
include "db.php";

/* Login check for Doctor */
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "doctor") {
    header("Location: login.php");
    exit();
}

$doctorName = $_SESSION["name"] ?? "";
$doctorId   = $_SESSION["id"] ?? 0;

// Doctor Name Formatting (Dr. prefix handle කිරීම)
$docSearchName = $doctorName;
if (strpos($docSearchName, 'Dr.') === false) {
    $docSearchName = "Dr. " . $docSearchName;
}

/* Today's Date */
$todayDate = date("Y-m-d");

/* 1. Fetch Today's Patients Count */
$today_sql = "SELECT COUNT(*) as count FROM appointments WHERE (doctor_name LIKE '%$doctorName%' OR doctor_name LIKE '%$docSearchName%') AND appointment_date = '$todayDate'";
$today_res = mysqli_query($conn, $today_sql);
$today_count = ($today_res) ? mysqli_fetch_assoc($today_res)['count'] : 0;

/* 2. Fetch Pending Requests Count */
$pending_sql = "SELECT COUNT(*) as count FROM appointments WHERE (doctor_name LIKE '%$doctorName%' OR doctor_name LIKE '%$docSearchName%') AND status = 'Pending'";
$pending_res = mysqli_query($conn, $pending_sql);
$pending_count = ($pending_res) ? mysqli_fetch_assoc($pending_res)['count'] : 0;

/* 3. Fetch Total Bookings Count */
$total_sql = "SELECT COUNT(*) as count FROM appointments WHERE (doctor_name LIKE '%$doctorName%' OR doctor_name LIKE '%$docSearchName%')";
$total_res = mysqli_query($conn, $total_sql);
$total_count = ($total_res) ? mysqli_fetch_assoc($total_res)['count'] : 0;

/* 4. Fetch Recent Bookings List */
$recent_sql = "SELECT * FROM appointments WHERE (doctor_name LIKE '%$doctorName%' OR doctor_name LIKE '%$docSearchName%') ORDER BY id DESC LIMIT 5";
$recent_result = mysqli_query($conn, $recent_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MediCare | Doctor Dashboard</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<style>
body { background: #f0f8ff; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
.sidebar { width: 250px; position: fixed; top: 0; bottom: 0; background: #0b78a6; color: white; padding-top: 20px; }
.sidebar a { padding: 12px 25px; color: white; text-decoration: none; display: block; font-weight: 500; }
.sidebar a:hover, .sidebar a.active { background: #085a7d; }
.main-content { margin-left: 250px; padding: 30px; }
.stat-card { background: white; border-radius: 12px; padding: 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); display: flex; align-items: center; justify-content: space-between; }
.stat-icon { width: 50px; height: 50px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; }
.card-custom { background: white; border-radius: 12px; padding: 25px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
.badge-pending { background: #ffeeba; color: #856404; }
.badge-confirmed { background: #d4edda; color: #155724; }
</style>
</head>
<body>

<div class="sidebar">
    <h3 class="text-center fw-bold mb-4">MediCare</h3>
    <a href="doctor-dashboard.php" class="active"><i class="bi bi-grid-fill me-2"></i> Dashboard</a>
    <a href="doctor-appointments.php"><i class="bi bi-calendar-check me-2"></i> Appointments</a>
    <a href="doctor-profile.php"><i class="bi bi-person me-2"></i> My Profile</a>
    <a href="logout.php"><i class="bi bi-box-arrow-right me-2"></i> Logout</a>
</div>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>Welcome, <?php echo htmlspecialchars($docSearchName); ?> 👋</h2>
            <p class="text-muted mb-0">Here is your daily overview and patient schedules.</p>
        </div>
        <div class="btn btn-light border"><i class="bi bi-calendar me-2"></i><?php echo date("F d, Y"); ?></div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="stat-card">
                <div>
                    <span class="text-muted d-block mb-1">Today's Patients</span>
                    <h2 class="mb-0 fw-bold"><?php echo $today_count; ?></h2>
                </div>
                <div class="stat-icon bg-info bg-opacity-10 text-info"><i class="bi bi-person-clock"></i></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <div>
                    <span class="text-muted d-block mb-1">Pending Requests</span>
                    <h2 class="mb-0 fw-bold"><?php echo $pending_count; ?></h2>
                </div>
                <div class="stat-icon bg-warning bg-opacity-10 text-warning"><i class="bi bi-hourglass-split"></i></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <div>
                    <span class="text-muted d-block mb-1">Total Bookings</span>
                    <h2 class="mb-0 fw-bold"><?php echo $total_count; ?></h2>
                </div>
                <div class="stat-icon bg-success bg-opacity-10 text-success"><i class="bi bi-calendar-check"></i></div>
            </div>
        </div>
    </div>

    <!-- Recent Bookings Table -->
    <div class="card-custom">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold mb-0"><i class="bi bi-list-task me-2"></i>Recent Bookings</h5>
        </div>

        <?php if ($recent_result && mysqli_num_rows($recent_result) > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#ID</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Reason</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = mysqli_fetch_assoc($recent_result)): 
                            $status = $row['status'] ?? 'Pending';
                            $badgeClass = ($status == 'Confirmed') ? 'badge-confirmed' : 'badge-pending';
                        ?>
                        <tr>
                            <td class="fw-bold">#<?php echo $row['id']; ?></td>
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
            <div class="text-center py-4 text-muted">
                No recent appointments found for you.
            </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
