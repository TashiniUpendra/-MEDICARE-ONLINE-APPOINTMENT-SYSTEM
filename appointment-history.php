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

<!-- HTML Display Table Inside Page -->
<table class="table">
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
        <?php while($row = $result->fetch_assoc()): ?>
        <tr>
            <td>#<?php echo $row['id']; ?></td>
            <!-- Real Doctor Name -->
            <td>Dr. <?php echo htmlspecialchars($row['doctor_name']); ?></td> 
            <td><i class="fa-regular fa-calendar"></i> <?php echo $row['appointment_date']; ?></td>
            <td><i class="fa-regular fa-clock"></i> <?php echo $row['appointment_time']; ?></td>
            <td><?php echo htmlspecialchars($row['reason']); ?></td>
            <td>
                <!-- Dynamic Status Colors -->
                <?php if(strtolower($row['status']) == 'confirmed'): ?>
                    <span class="badge bg-success" style="background:#dcfce7; color:#15803d; padding:4px 10px; border-radius:12px; font-weight:600;">Confirmed</span>
                <?php elseif(strtolower($row['status']) == 'cancelled'): ?>
                    <span class="badge bg-danger" style="background:#fee2e2; color:#b91c1c; padding:4px 10px; border-radius:12px; font-weight:600;">Cancelled</span>
                <?php else: ?>
                    <span class="badge bg-warning" style="background:#fef3c7; color:#b45309; padding:4px 10px; border-radius:12px; font-weight:600;">Pending</span>
                <?php endif; ?>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>
