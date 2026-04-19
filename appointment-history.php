<?php
session_start();

/* Patient only */
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "patient") {
    header("Location: login.php");
    exit();
}

/* Get appointments */
$appointments = [];

/* Demo: session එකේ last booking එක show කරනවා */
if (isset($_SESSION["appointment"])) {
    $appointments[] = $_SESSION["appointment"];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>My Appointments</title>

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
    padding:15px;
    text-align:center;
    font-size:20px;
}

/* CONTAINER */
.container{
    width:90%;
    max-width:900px;
    margin:30px auto;
}

/* TABLE BOX */
.table-box{
    background:white;
    padding:20px;
    border-radius:12px;
    box-shadow:0 4px 15px rgba(0,0,0,0.1);
}

/* TABLE */
table{
    width:100%;
    border-collapse:collapse;
    margin-top:10px;
}

th, td{
    padding:12px;
    text-align:center;
    border-bottom:1px solid #ddd;
}

th{
    background:#e6f4fb;
    color:#0b78a6;
}

/* BUTTON */
.btn{
    display:inline-block;
    margin-top:20px;
    padding:10px 15px;
    background:#0b78a6;
    color:white;
    text-decoration:none;
    border-radius:6px;
}

.btn:hover{
    background:#095c80;
}

/* EMPTY */
.empty{
    text-align:center;
    color:#777;
    margin-top:20px;
}
</style>
</head>

<body>

<header>My Appointments</header>

<div class="container">

<div class="table-box">

<?php if (count($appointments) > 0): ?>

<table>
<tr>
    <th>Name</th>
    <th>Email</th>
    <th>Doctor</th>
    <th>Date</th>
    <th>Time</th>
    <th>Reason</th>
</tr>

<?php foreach ($appointments as $app): ?>
<tr>
    <td><?php echo htmlspecialchars($app["patient"]); ?></td>
    <td><?php echo htmlspecialchars($app["email"]); ?></td>
    <td><?php echo htmlspecialchars($app["doctor"]); ?></td>
    <td><?php echo htmlspecialchars($app["date"]); ?></td>
    <td><?php echo htmlspecialchars($app["time"]); ?></td>
    <td><?php echo htmlspecialchars($app["reason"]); ?></td>
</tr>
<?php endforeach; ?>

</table>

<?php else: ?>
<p class="empty">No appointments found.</p>
<?php endif; ?>

<a href="patient-dashboard.php" class="btn">⬅ Back to Dashboard</a>

</div>

</div>

</body>
</html>
