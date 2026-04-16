<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include "db.php";

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = $_POST["name"];
    $email = $_POST["email"];
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);
    $role = $_POST["role"];

    if ($name && $email && $password && $role) {

        // check duplicate email
        $check = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");
        
        if (mysqli_num_rows($check) > 0) {
            $error = "Email already exists!";
        } else {
            $sql = "INSERT INTO users (name,email,password,role)
                    VALUES ('$name','$email','$password','$role')";

            if (mysqli_query($conn, $sql)) {
                $success = "Registration successful!";
            } else {
                $error = "Error: " . mysqli_error($conn);
            }
        }

    } else {
        $error = "Fill all fields!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Register</title>
<style>
body {background:#f0faff;font-family:Arial;}
.container {display:flex;justify-content:center;align-items:center;height:100vh;}
.box {background:#fff;padding:30px;border-radius:10px;width:350px;}
input,select {width:100%;padding:10px;margin-top:5px;}
button {margin-top:15px;width:100%;padding:10px;background:#0b78a6;color:#fff;}
</style>
</head>

<body>

<div class="container">
<div class="box">

<h2>Register</h2>

<?php if ($error) echo "<p style='color:red'>$error</p>"; ?>
<?php if ($success) echo "<p style='color:green'>$success</p>"; ?>

<form method="POST">
<input type="text" name="name" placeholder="Name" required>
<input type="email" name="email" placeholder="Email" required>
<input type="password" name="password" placeholder="Password" required>

<select name="role" required>
<option value="">Select Role</option>
<option value="admin">Admin</option>
<option value="doctor">Doctor</option>
<option value="patient">Patient</option>
</select>

<button type="submit">Register</button>
</form>

<p><a href="login.php">Login</a></p>

</div>
</div>

</body>
</html>
