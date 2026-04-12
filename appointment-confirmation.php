<?php
session_start();

// Only patient access
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "patient") {
    header("Location: login.php");
    exit();
}

// Get appointment data from session
if (!isset($_SESSION["appointment"])) {
    header("Location: appointment-booking.php");
    exit();
}

$appointment = $_SESSION["appointment"];

// Generate appointment ID
$appointmentId = "APT-" . date("Ymd") . "-" . rand(1000, 9999);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>MediCare | Appointment Confirmation</title>

<style>
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:"Poppins", sans-serif;
}

body{
    background:#f0faff;
}

header{
    background:#0b78a6;
    color:white;
    text-align:center;
    padding:18px;
    font-size:22px;
    font-weight:bold;
}

.container{
    width:90%;
    max-width:600px;
    margin:40px auto;
    background:white;
    padding:30px;
    border-radius:12px;
    box-shadow:0 4px 18px rgba(0,0,0,0.12);
}

h2{
    color:#0b78a6;
    text-align:center;
    margin-bottom:20px;
}

.success{
    color:green;
    font-weight:bold;
    text-align:center;
    margin-bottom:20px;
}

.details p{
    margin:10px 0;
    font-size:16px;
}

.badge{
    background:#e6f4fb;
    color:#0b78a6;
    padding:6px 10px;
    border-radius:6px;
    font-weight:bold;
}

.btn{
    display:block;
    text-align:center;
    margin-top:20px;
    text-decoration:none;
    background:#0b78a6;
    color:white;
    padding:12px;
    border-radius:6px;
}

.btn:hover{
    background:#095c80;
}
</style>
</head>

<body>

<header>MediCare | Appointment Confirmation</header>

<div class="container">

<h2>Appointment Confirmed</h2>

<p class="success">✅ Your appointment has been successfully booked!</p>

<div class="details">
    <p><strong>Appointment ID:</strong> <span class="badge"><?php echo $appointmentId; ?></span></p>
    <p><strong>Patient Name:</strong> <?php echo htmlspecialchars($appointment["patient"]); ?></p>
    <p><strong>Email:</strong> <?php echo htmlspecialchars($appointment["email"]); ?></p>
    <p><strong>Doctor:</strong> <?php echo htmlspecialchars($appointment["doctor"]); ?></p>
    <p><strong>Date:</strong> <?php echo htmlspecialchars($appointment["date"]); ?></p>
    <p><strong>Time:</strong> <?php echo htmlspecialchars($appointment["time"]); ?></p>
    <p><strong>Reason:</strong> <?php echo htmlspecialchars($appointment["reason"]); ?></p>
</div>

<a href="patient-dashboard.php" class="btn">Go to Dashboard</a>

</div>

</body>
</html>
