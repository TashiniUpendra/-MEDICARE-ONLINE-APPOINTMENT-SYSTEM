<?php
session_start();

if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "doctor") {
    header("Location: login.php");
    exit();
}

$doctorName = $_SESSION["name"];
?>

<!DOCTYPE html>
<html>
<head>
<title>Doctor Dashboard</title>

<style>
body{font-family:Arial;background:#eef6fb;}
header{background:#0b78a6;color:white;padding:15px;text-align:center;}
.container{width:90%;margin:30px auto;}
.card{background:white;padding:20px;margin:10px;text-align:center;}
.btn{padding:10px;background:#0b78a6;color:white;text-decoration:none;}
</style>
</head>

<body>

<header>Welcome Dr. <?php echo htmlspecialchars($doctorName); ?></header>

<div class="container">

<div class="card">
<h3>My Profile</h3>
<a href="doctor-profile.php" class="btn">View</a>
</div>

<div class="card">
<h3>Logout</h3>
<a href="logout.php" class="btn">Logout</a>
</div>

</div>

</body>
</html>
