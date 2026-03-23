<?php
session_start();
include "db.php";

// Get doctor ID from URL
if (!isset($_GET['id'])) {
    die("Doctor not found!");
}

$id = (int) $_GET['id'];

// Fetch doctor from database
$sql = "SELECT * FROM doctors WHERE id = $id";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    die("Doctor not found!");
}

$doctor = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>MediCare | Doctor Profile</title>

<style>
body {
    font-family: Arial;
    background: #f0faff;
}

header {
    background: #0b78a6;
    color: white;
    padding: 15px;
    text-align: center;
}

.container {
    width: 90%;
    max-width: 900px;
    margin: 30px auto;
}

.profile {
    background: white;
    padding: 20px;
    border-radius: 10px;
    display: flex;
    gap: 20px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
}

.profile img {
    width: 140px;
    height: 140px;
    border-radius: 50%;
}

.schedule {
    margin-top: 20px;
    background: white;
    padding: 20px;
    border-radius: 10px;
}

.btn {
    display: inline-block;
    margin-top: 15px;
    background: #0b78a6;
    color: white;
    padding: 10px;
    text-decoration: none;
    border-radius: 5px;
}
</style>
</head>

<body>

<header>Doctor Profile</header>

<div class="container">

<div class="profile">
    <img src="https://via.placeholder.com/140" alt="Doctor">

    <div>
        <h2><?php echo htmlspecialchars($doctor['name']); ?></h2>
        <p><strong>Specialty:</strong> <?php echo htmlspecialchars($doctor['specialization']); ?></p>
        <p><strong>Experience:</strong> <?php echo htmlspecialchars($doctor['experience']); ?></p>
        <p><strong>Available Time:</strong> <?php echo htmlspecialchars($doctor['available_time']); ?></p>
    </div>
</div>

<div class="schedule">
    <h3>Book Appointment</h3>

    <a class="btn" href="appointment-booking.php?doctor_id=<?php echo $doctor['id']; ?>">
        Book Now
    </a>
</div>

</div>

</body>
</html>
