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

            $_SESSION["name"]  = $user["name"];
            $_SESSION["email"] = $user["email"]; // 🔥 VERY IMPORTANT
            $_SESSION["role"]  = $user["role"];

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
<title>Login</title>

<style>
body{
    margin:0;
    font-family:Arial;
    background:linear-gradient(135deg,#0b78a6,#6dd5fa);
}

.container{
    display:flex;
    justify-content:center;
    align-items:center;
    height:100vh;
}

.box{
    background:white;
    padding:30px;
    border-radius:10px;
    width:350px;
    text-align:center;
}

input,select{
    width:100%;
    padding:10px;
    margin-top:10px;
}

button{
    width:100%;
    margin-top:15px;
    padding:10px;
    background:#0b78a6;
    color:white;
    border:none;
}

.error{
    color:red;
}
</style>
</head>

<body>

<div class="container">
<div class="box">

<h2>Login</h2>

<?php if ($error) echo "<p class='error'>$error</p>"; ?>

<form method="POST">

<select name="role" required>
<option value="">Select Role</option>
<option value="admin">Admin</option>
<option value="doctor">Doctor</option>
<option value="patient">Patient</option>
</select>

<input type="email" name="email" placeholder="Email" required>
<input type="password" name="password" placeholder="Password" required>

<button type="submit">Login</button>

</form>

<p><a href="register.php">Register</a></p>

</div>
</div>

</body>
</html>
