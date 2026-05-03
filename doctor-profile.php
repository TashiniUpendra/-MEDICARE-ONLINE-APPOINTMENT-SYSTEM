<?php
session_start();
include "db.php";

/* Doctor only */
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "doctor") {
    header("Location: login.php");
    exit();
}

$email = $_SESSION["email"];

$sql = "SELECT * FROM users WHERE email='$email'";
$result = mysqli_query($conn, $sql);
$user = mysqli_fetch_assoc($result);

/* image fallback */
$image = "uploads/" . $user["image"];

if (empty($user["image"]) || !file_exists($image)) {
    $image = "https://cdn-icons-png.flaticon.com/512/387/387561.png";
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Doctor Profile</title>

<style>
body{
    font-family:Arial;
    background:#eef6fb;
}

.container{
    width:400px;
    margin:50px auto;
    background:white;
    padding:30px;
    border-radius:10px;
    text-align:center;
    box-shadow:0 4px 10px rgba(0,0,0,0.1);
}

img{
    width:120px;
    height:120px;
    border-radius:50%;
    object-fit:cover;
    margin-bottom:15px;
    border:3px solid #0b78a6;
}

a{
    display:inline-block;
    margin-top:15px;
    padding:8px 12px;
    background:#0b78a6;
    color:white;
    text-decoration:none;
    border-radius:6px;
}
</style>
</head>

<body>

<div class="container">

<h2>Doctor Profile</h2>

<img src="<?php echo $image; ?>">

<h3><?php echo htmlspecialchars($user["name"]); ?></h3>
<p><?php echo htmlspecialchars($user["email"]); ?></p>

<a href="doctor-dashboard.php">⬅ Back</a>

</div>

</body>
</html>
