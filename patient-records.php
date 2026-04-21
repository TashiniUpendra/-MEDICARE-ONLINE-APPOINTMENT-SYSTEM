<?php
session_start();

/* Admin only */
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
    header("Location: login.php");
    exit();
}

/* DB connection */
$conn = new mysqli("localhost", "root", "", "medicare_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

/* Get patients only */
$sql = "SELECT * FROM users WHERE role='patient' ORDER BY id DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
<title>Patient Records</title>

<style>
body{
    font-family:Arial;
    background:#eef6fb;
}

.container{
    width:90%;
    margin:30px auto;
    background:white;
    padding:20px;
    border-radius:10px;
    box-shadow:0 4px 10px rgba(0,0,0,0.1);
}

h2{
    color:#0b78a6;
}

table{
    width:100%;
    border-collapse:collapse;
    margin-top:15px;
}

th,td{
    padding:10px;
    border-bottom:1px solid #ccc;
    text-align:center;
}

th{
    background:#0b78a6;
    color:white;
}

.btn{
    padding:5px 10px;
    background:red;
    color:white;
    border:none;
    border-radius:5px;
    text-decoration:none;
}

.back{
    display:inline-block;
    margin-top:15px;
    padding:8px 12px;
    background:#0b78a6;
    color:white;
    text-decoration:none;
    border-radius:5px;
}
</style>
</head>

<body>

<div class="container">

<h2>Patient Records</h2>

<table>
<tr>
<th>ID</th>
<th>Name</th>
<th>Email</th>
<th>Action</th>
</tr>

<?php if ($result->num_rows > 0): ?>
<?php while($row = $result->fetch_assoc()): ?>
<tr>
<td><?php echo $row["id"]; ?></td>
<td><?php echo $row["name"]; ?></td>
<td><?php echo $row["email"]; ?></td>
<td>
<a class="btn" href="patient-records.php?delete=<?php echo $row["id"]; ?>" 
onclick="return confirm('Delete this patient?')">Delete</a>
</td>
</tr>
<?php endwhile; ?>
<?php else: ?>
<tr><td colspan="4">No patients found</td></tr>
<?php endif; ?>

</table>

<a href="admin-dashboard.php" class="back">⬅ Back</a>

</div>

</body>
</html>