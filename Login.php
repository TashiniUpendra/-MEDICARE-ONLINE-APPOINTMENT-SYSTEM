<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include "db.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $role = $_POST["role"];
    $email = $_POST["email"];
    $password = $_POST["password"];

    $sql = "SELECT * FROM users WHERE email='$email' AND role='$role'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) === 1) {

        $user = mysqli_fetch_assoc($result);

        if (password_verify($password, $user["password"])) {

            $_SESSION["name"] = $user["name"];
            $_SESSION["role"] = $user["role"];

            if ($user["role"] === "admin") {
                header("Location: admin-dashboard.php");
            } elseif ($user["role"] === "doctor") {
                header("Location: doctor-dashboard.php");
            } else {
                header("Location: patient-dashboard.php");
            }
            exit();
        } else {
            $error = "Wrong password!";
        }

    } else {
        $error = "User not found!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>MediCare | Login</title>

<style>

/* ===== BODY ANIMATION ===== */
body {
    margin:0;
    font-family: "Poppins", sans-serif;
    background: linear-gradient(-45deg, #0b78a6, #6dd5fa, #2980b9, #00c6ff);
    background-size: 400% 400%;
    animation: gradientBG 10s ease infinite;
}

/* Gradient animation */
@keyframes gradientBG {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

/* ===== BUBBLES ===== */
.bubbles{
    position: fixed;
    width:100%;
    height:100%;
    top:0;
    left:0;
    overflow:hidden;
    z-index:-1;
}

.bubbles span{
    position:absolute;
    display:block;
    width:20px;
    height:20px;
    background:rgba(255,255,255,0.3);
    animation: animate 20s linear infinite;
    bottom:-150px;
    border-radius:50%;
}

.bubbles span:nth-child(1){ left:10%; animation-delay:0s; }
.bubbles span:nth-child(2){ left:20%; animation-delay:2s; }
.bubbles span:nth-child(3){ left:25%; animation-delay:4s; }
.bubbles span:nth-child(4){ left:40%; animation-delay:0s; }
.bubbles span:nth-child(5){ left:70%; animation-delay:3s; }
.bubbles span:nth-child(6){ left:80%; animation-delay:5s; }
.bubbles span:nth-child(7){ left:50%; animation-delay:7s; }
.bubbles span:nth-child(8){ left:60%; animation-delay:1s; }
.bubbles span:nth-child(9){ left:30%; animation-delay:6s; }
.bubbles span:nth-child(10){ left:90%; animation-delay:8s; }

@keyframes animate{
    0%{
        transform:translateY(0) scale(0);
        opacity:0;
    }
    50%{
        opacity:1;
    }
    100%{
        transform:translateY(-1000px) scale(1);
        opacity:0;
    }
}

/* ===== CENTER BOX ===== */
.container {
    display:flex;
    justify-content:center;
    align-items:center;
    height:100vh;
}

/* GLASS UI */
.box {
    background: rgba(255,255,255,0.15);
    backdrop-filter: blur(12px);
    padding:35px;
    border-radius:12px;
    width:360px;
    box-shadow:0 8px 25px rgba(0,0,0,0.3);
    text-align:center;
    color:white;
}

/* TITLE */
h2 {
    margin-bottom:20px;
}

/* INPUTS */
input, select {
    width:100%;
    padding:12px;
    margin-top:10px;
    border-radius:6px;
    border:none;
    outline:none;
}

/* BUTTON */
button {
    width:100%;
    padding:12px;
    margin-top:15px;
    background:#0b78a6;
    color:white;
    border:none;
    border-radius:6px;
    cursor:pointer;
    font-size:15px;
}

button:hover {
    background:#095c80;
}

/* LINKS */
a {
    color:#fff;
    text-decoration:none;
    font-weight:bold;
}

/* HOME BUTTON */
.home-btn {
    display:block;
    margin-top:10px;
    padding:10px;
    background:white;
    color:#0b78a6;
    border-radius:6px;
}

/* ERROR */
.error {
    color:#ffcccc;
    margin-bottom:10px;
}
</style>

</head>

<body>

<!-- BUBBLES -->
<div class="bubbles">
    <span></span><span></span><span></span><span></span><span></span>
    <span></span><span></span><span></span><span></span><span></span>
</div>

<div class="container">
<div class="box">

<h2>🔐 MediCare Login</h2>

<?php if ($error) echo "<p class='error'>$error</p>"; ?>

<form method="POST">

<select name="role" required>
<option value="">-- Select Role --</option>
<option value="admin">Admin</option>
<option value="doctor">Doctor</option>
<option value="patient">Patient</option>
</select>

<input type="email" name="email" placeholder="Enter Email" required>
<input type="password" name="password" placeholder="Enter Password" required>

<button type="submit">Login</button>

</form>

<p style="margin-top:15px;">
Don't have an account? 
<a href="register.php">Register</a>
</p>

<a href="home.php" class="home-btn">🏠 Go to Home</a>

</div>
</div>

</body>
</html>
