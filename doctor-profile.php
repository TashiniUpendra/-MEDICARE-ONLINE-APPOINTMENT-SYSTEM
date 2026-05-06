<?php
session_start();
include "db.php";

/* Doctor only */
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "doctor") {
    header("Location: login.php");
    exit();
}

$email = $_SESSION["email"] ?? "";

/* Get doctor */
$sql = "SELECT * FROM users WHERE email='$email'";
$result = mysqli_query($conn, $sql);
$user = mysqli_fetch_assoc($result);

/* Safe fallback */
if (!$user) {
    die("User not found!");
}

/* Image handling */
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
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:"Poppins", sans-serif;
}

body{
    height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
    background: linear-gradient(135deg,#0b78a6,#6dd5fa);
}

/* PROFILE CARD */
.container{
    width:350px;
    background:rgba(255,255,255,0.95);
    padding:30px;
    border-radius:15px;
    text-align:center;
    box-shadow:0 10px 30px rgba(0,0,0,0.2);
    animation:fadeIn 0.8s ease;
}

/* IMAGE */
img{
    width:130px;
    height:130px;
    border-radius:50%;
    object-fit:cover;
    border:4px solid #0b78a6;
    margin-bottom:15px;
}

/* TEXT */
h2{
    color:#0b78a6;
    margin-bottom:10px;
}

h3{
    margin-top:10px;
    color:#333;
}

p{
    color:#666;
    margin-top:5px;
}

/* BUTTON */
a{
    display:inline-block;
    margin-top:20px;
    padding:10px 18px;
    background:#0b78a6;
    color:white;
    text-decoration:none;
    border-radius:6px;
    transition:0.3s;
}

a:hover{
    background:#095c80;
}

/* ANIMATION */
@keyframes fadeIn{
    from{
        opacity:0;
        transform:translateY(20px);
    }
    to{
        opacity:1;
        transform:translateY(0);
    }
}
</style>

</head>

<body>

<div class="container">

<h2>👨‍⚕️ Doctor Profile</h2>

<img src="<?php echo $image; ?>">

<h3><?php echo htmlspecialchars($user["name"]); ?></h3>
<p><?php echo htmlspecialchars($user["email"]); ?></p>

<!-- Optional specialization -->
<?php if(!empty($user["specialization"])): ?>
<p><strong>Specialization:</strong> <?php echo htmlspecialchars($user["specialization"]); ?></p>
<?php endif; ?>

<a href="doctor-dashboard.php">⬅ Back to Dashboard</a>

</div>

</body>
</html>
