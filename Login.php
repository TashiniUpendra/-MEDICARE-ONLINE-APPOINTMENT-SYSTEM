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
body {
    margin:0;
    font-family: "Poppins", sans-serif;
    background: linear-gradient(135deg,#0b78a6,#6dd5fa);
}

/* CENTER BOX */
.container {
    display:flex;
    justify-content:center;
    align-items:center;
    height:100vh;
}

.box {
    background:white;
    padding:35px;
    border-radius:12px;
    width:360px;
    box-shadow:0 8px 25px rgba(0,0,0,0.2);
    text-align:center;
}

h2 {
    margin-bottom:20px;
    color:#0b78a6;
}

/* INPUTS */
input, select {
    width:100%;
    padding:12px;
    margin-top:10px;
    border-radius:6px;
    border:1px solid #ccc;
    font-size:14px;
}

/* BUTTONS */
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

/* HOME BUTTON */
.home-btn {
    display:block;
    margin-top:10px;
    padding:10px;
    background:#eee;
    color:#333;
    text-decoration:none;
    border-radius:6px;
}

.home-btn:hover {
    background:#ddd;
}

/* ERROR */
.error {
    color:red;
    margin-bottom:10px;
}
</style>

</head>

<body>

<div class="container">
<div class="box">

<h2>🔐 Login</h2>

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

<!-- Register -->
<p style="margin-top:15px;">
Don't have an account? 
<a href="register.php" style="color:#0b78a6;font-weight:bold;">Register</a>
</p>

<!-- 🔥 NEW HOME BUTTON -->
<a href="home.php" class="home-btn">🏠 Go to Home</a>

</div>
</div>

</body>
</html>
