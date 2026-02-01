<?php
session_start();

/* Patient-only access */
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "patient") {
    header("Location: login.php");
    exit();
}

/* Get appointment details from session */
$appointment = $_SESSION["appointment"] ?? null;

if (!$appointment) {
    // If someone opens this page without booking
    header("Location: appointment-booking.php");
    exit();
}

/* Generate a simple appointment ID (demo) */
$appointmentId = "APT-" . rand(10000, 99999);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
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
.details p{
    margin:10px 0;
    font-size:16px;
    color:#222;
}
.success{
    color:green;
    font-weight:bold;
    text-align:center;
    margin-bottom:20px;
}
a{
    display:block;
    text-align:center;
    margin-top:20px;
    text-decoration:none;
    background:#0b78a6;
    color:white;
    padding:12px;
    border-radius:6px;
}
a:hover{
    background:#095c80;
}
.badge{
    display:inline-block;
    background:#e8f6ff;
    color:#0b78a6;
    font-weight:700;
    padding:6px 10px;
    border-radius:8px;
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

    <a href="home.php">Go to Home</a>
</div>

</body>
</html>
