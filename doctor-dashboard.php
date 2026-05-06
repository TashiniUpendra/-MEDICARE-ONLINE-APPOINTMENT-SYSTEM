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
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:"Poppins", sans-serif;
}

body{
    background: linear-gradient(135deg,#e3f2fd,#f0faff);
}

/* HEADER */
header{
    background: linear-gradient(135deg,#0b78a6,#4fc3f7);
    color:white;
    padding:20px;
    text-align:center;
    font-size:22px;
    font-weight:bold;
    box-shadow:0 4px 10px rgba(0,0,0,0.2);
}

/* CONTAINER */
.container{
    width:90%;
    margin:40px auto;
}

/* GRID */
.cards{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(250px,1fr));
    gap:25px;
}

/* CARD */
.card{
    background:white;
    padding:30px;
    border-radius:15px;
    text-align:center;
    box-shadow:0 6px 20px rgba(0,0,0,0.1);
    transition:0.3s;
}

.card:hover{
    transform:translateY(-8px);
    box-shadow:0 10px 25px rgba(0,0,0,0.2);
}

/* ICON */
.icon{
    font-size:40px;
    margin-bottom:10px;
}

/* TITLE */
.card h3{
    color:#0b78a6;
    margin-bottom:10px;
}

/* TEXT */
.card p{
    color:#555;
    margin-bottom:15px;
}

/* BUTTON */
.btn{
    display:inline-block;
    padding:10px 18px;
    background:#0b78a6;
    color:white;
    text-decoration:none;
    border-radius:6px;
    transition:0.3s;
}

.btn:hover{
    background:#095c80;
}

/* LOGOUT SPECIAL */
.logout{
    background:#e53935;
}

.logout:hover{
    background:#c62828;
}
</style>

</head>

<body>

<header>
    👨‍⚕️ Welcome Dr. <?php echo htmlspecialchars($doctorName); ?>
</header>

<div class="container">

<div class="cards">

<!-- PROFILE -->
<div class="card">
<div class="icon">👤</div>
<h3>My Profile</h3>
<p>View and manage your personal details</p>
<a href="doctor-profile.php" class="btn">View Profile</a>
</div>

<!-- APPOINTMENTS -->
<div class="card">
<div class="icon">📅</div>
<h3>My Appointments</h3>
<p>Check your patient bookings</p>
<a href="doctor-appointments.php" class="btn">View Appointments</a>
</div>

<!-- LOGOUT -->
<div class="card">
<div class="icon">🚪</div>
<h3>Logout</h3>
<p>Securely exit your account</p>
<a href="logout.php" class="btn logout">Logout</a>
</div>

</div>

</div>

</body>
</html>
