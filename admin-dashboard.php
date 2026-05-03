<?php
session_start();

/* Doctor only */
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "doctor") {
    header("Location: login.php");
    exit();
}

$doctorName = $_SESSION["name"] ?? "Doctor";
?>

<!DOCTYPE html>
<html>
<head>
<title>Doctor Dashboard</title>

<style>
body{
    font-family:Arial;
    background:#eef6fb;
}

header{
    background:#0b78a6;
    color:white;
    padding:15px;
    text-align:center;
}

.container{
    width:90%;
    margin:30px auto;
}

.cards{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
    gap:20px;
}

.card{
    background:white;
    padding:25px;
    border-radius:10px;
    text-align:center;
    box-shadow:0 4px 10px rgba(0,0,0,0.1);
}

.card h3{
    color:#0b78a6;
}

.btn{
    display:inline-block;
    margin-top:10px;
    padding:10px;
    background:#0b78a6;
    color:white;
    text-decoration:none;
    border-radius:5px;
}
</style>
</head>

<body>

<header>
    Welcome Dr. <?php echo htmlspecialchars($doctorName); ?>
</header>

<div class="container">

<div class="cards">

<div class="card">
<h3>My Appointments</h3>
<p>View your appointments</p>
<a href="doctor-appointments.php" class="btn">View</a>
</div>

<div class="card">
<h3>My Profile</h3>
<p>View your details</p>
<a href="doctor-profile.php" class="btn">View</a> <!-- 🔥 FIX -->
</div>

<div class="card">
<h3>Logout</h3>
<p>Secure logout</p>
<a href="logout.php" class="btn">Logout</a>
</div>

</div>

</div>

</body>
</html>
