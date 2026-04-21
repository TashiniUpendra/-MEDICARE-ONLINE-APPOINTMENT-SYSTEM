<?php
session_start();

/* Admin check */
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
    header("Location: login.php");
    exit();
}

/* DB connect */
$conn = new mysqli("localhost", "root", "", "medicare_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

/* Get appointments */
$sql = "SELECT a.*, u.name AS patient_name, d.name AS doctor_name 
        FROM appointments a
        JOIN users u ON a.patient_id = u.id
        JOIN doctors d ON a.doctor_id = d.id
        ORDER BY a.id DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
<title>Admin - Appointments</title>

<style>
body{font-family:Arial;background:#eef6fb;}

.container{
    width:90%;
    margin:30px auto;
    background:white;
    padding:20px;
    border-radius:10px;
}

h2{color:#0b78a6;}

table{
    width:100%;
    border-collapse:collapse;
}

th,td{
    padding:10px;
    border-bottom:1px solid #ccc;
    text-align:center;
}

th{
    background:#0b78a6;
    color:white;
}

.back{
    display:inline-block;
    margin-top:15px;
    padding:8px 12px;
    background:#0b78a6;
    color:white;
    text-decoration:none;
    border-radius:5px;
}
</style>
</head>

<body>

<div class="container">

<h2>All Appointments</h2>

<table>
<tr>
<th>Patient</th>
<th>Doctor</th>
<th>Date</th>
<th>Time</th>
<th>Reason</th>
<th>Status</th>
</tr>

<?php if ($result->num_rows > 0): ?>
<?php while($row = $result->fetch_assoc()): ?>
<tr>
<td><?php echo $row["patient_name"]; ?></td>
<td><?php echo $row["doctor_name"]; ?></td>
<td><?php echo $row["appointment_date"]; ?></td>
<td><?php echo $row["appointment_time"]; ?></td>
<td><?php echo $row["reason"]; ?></td>
<td><?php echo $row["status"]; ?></td>
</tr>
<?php endwhile; ?>
<?php else: ?>
<tr><td colspan="6">No appointments found</td></tr>
<?php endif; ?>

</table>

<a href="admin-dashboard.php" class="back">⬅ Back</a>

</div>

</body>
</html>