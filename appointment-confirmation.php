<?php
session_start();

if (!isset($_SESSION["appointment"])) {
    header("Location: doctor-list.php");
    exit();
}

$app = $_SESSION["appointment"];
?>

<!DOCTYPE html>
<html>
<head>
<title>Confirmed</title>
</head>

<body>

<h2>Appointment Confirmed ✅</h2>

<p>Name: <?php echo $app["patient"]; ?></p>
<p>Email: <?php echo $app["email"]; ?></p>
<p>Doctor: <?php echo $app["doctor"]; ?></p>
<p>Date: <?php echo $app["date"]; ?></p>
<p>Time: <?php echo $app["time"]; ?></p>
<p>Reason: <?php echo $app["reason"]; ?></p>

<a href="patient-dashboard.php">Go Dashboard</a>

</body>
</html>
