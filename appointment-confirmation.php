<?php
session_start();

if (!isset($_SESSION["appointment"])) {
    header("Location: doctor-list.php");
    exit();
}

$app = $_SESSION["appointment"];
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Appointment Confirmed</title>

<style>
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:"Poppins", sans-serif;
}

body{
    background:#eef6fb;
    display:flex;
    justify-content:center;
    align-items:center;
    height:100vh;
}

/* CARD */
.card{
    background:white;
    padding:30px;
    width:400px;
    border-radius:12px;
    box-shadow:0 6px 20px rgba(0,0,0,0.1);
    text-align:center;
}

/* ICON */
.icon{
    font-size:50px;
    color:green;
}

/* TITLE */
h2{
    color:#0b78a6;
    margin:10px 0 20px;
}

/* DETAILS */
.details{
    text-align:left;
    margin-top:15px;
}

.details p{
    margin:8px 0;
    font-size:15px;
    color:#333;
}

/* BUTTON */
.btn{
    display:block;
    margin-top:20px;
    padding:12px;
    background:#0b78a6;
    color:white;
    text-decoration:none;
    border-radius:6px;
}

.btn:hover{
    background:#095c80;
}
</style>
</head>

<body>

<div class="card">

<div class="icon">✅</div>

<h2>Appointment Confirmed</h2>

<div class="details">
<p><strong>Name:</strong> <?php echo htmlspecialchars($app["patient"]); ?></p>
<p><strong>Email:</strong> <?php echo htmlspecialchars($app["email"]); ?></p>
<p><strong>Doctor:</strong> <?php echo htmlspecialchars($app["doctor"]); ?></p>
<p><strong>Date:</strong> <?php echo htmlspecialchars($app["date"]); ?></p>
<p><strong>Time:</strong> <?php echo htmlspecialchars($app["time"]); ?></p>
<p><strong>Reason:</strong> <?php echo htmlspecialchars($app["reason"]); ?></p>
</div>

<a href="patient-dashboard.php" class="btn">Go Dashboard</a>

</div>

</body>
</html>
