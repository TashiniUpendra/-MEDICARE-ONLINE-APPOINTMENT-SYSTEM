<?php
session_start();

/* Admin-only protection */
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
    header("Location: login.php");
    exit();
}

$adminName = $_SESSION["name"] ?? "Admin";
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MediCare | Admin Dashboard</title>

<style>
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:"Poppins", sans-serif;
}

body{
    background:#eef6fb;
}

/* HEADER */
header{
    background:#0b78a6;
    color:white;
    padding:15px 25px;
    display:flex;
    justify-content:space-between;
    align-items:center;
}

.right{
    display:flex;
    align-items:center;
    gap:15px;
}

.logout-btn{
    background:white;
    color:#0b78a6;
    padding:8px 14px;
    border-radius:6px;
    text-decoration:none;
    font-weight:bold;
}

/* DASHBOARD */
.dashboard{
    padding:30px;
}

/* CARDS */
.cards{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
    gap:20px;
    margin-bottom:30px;
}

.card{
    background:white;
    padding:25px;
    border-radius:12px;
    text-align:center;
    box-shadow:0 4px 12px rgba(0,0,0,0.1);
}

.card h2{
    color:#0b78a6;
    font-size:30px;
}

/* ACTION BOXES */
.actions{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(200px,1fr));
    gap:20px;
}

.action-box{
    background:white;
    padding:25px;
    border-radius:12px;
    text-align:center;
    box-shadow:0 4px 12px rgba(0,0,0,0.1);
}

.action-box h3{
    color:#0b78a6;
    margin-bottom:15px;
}

.btn{
    display:inline-block;
    background:#0b78a6;
    color:white;
    padding:10px 16px;
    border-radius:6px;
    text-decoration:none;
}

.btn:hover{
    background:#095c80;
}
</style>
</head>

<body>

<header>
    <h2>MediCare | Admin Dashboard</h2>

    <div class="right">
        <span>👤 <?php echo htmlspecialchars($adminName); ?></span>
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>
</header>

<div class="dashboard">

    <!-- STAT CARDS -->
    <div class="cards">
        <div class="card">
            <h2>12</h2>
            <p>Total Doctors</p>
        </div>

        <div class="card">
            <h2>38</h2>
            <p>Total Patients</p>
        </div>

        <div class="card">
            <h2>25</h2>
            <p>Appointments Today</p>
        </div>

        <div class="card">
            <h2>5</h2>
            <p>Pending Appointments</p>
        </div>
    </div>

    <!-- ACTIONS -->
    <div class="actions">

        <div class="action-box">
            <h3>Manage Doctors</h3>
            <a href="manage_doctors.php" class="btn">Open</a>
        </div>

        <div class="action-box">
            <h3>View Appointments</h3>
            <a href="view-appointments.php" class="btn">Open</a>
        </div>

        <div class="action-box">
            <h3>Patient Records</h3>
            <a href="patient-records.php" class="btn">Open</a>
        </div>

    </div>

</div>

</body>
</html>
