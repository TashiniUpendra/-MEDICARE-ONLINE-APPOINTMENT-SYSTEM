<?php
session_start();

// If already logged in, redirect to home
if (isset($_SESSION["role"])) {
    header("Location: home.php");
    exit();
}

$error = "";

// Handle form submit
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $role = $_POST["role"];
    $email = $_POST["email"];
    $password = $_POST["password"];

    // SIMPLE DEMO LOGIN (no database yet)
    if ($role && $email && $password) {

        // Admin credentials
        if ($role === "admin" && $email === "admin@medicare.com" && $password === "admin123") {
            $_SESSION["name"] = "Admin";
            $_SESSION["role"] = "admin";
            header("Location: admin-dashboard.php");
            exit();
        }

        // Doctor login (demo)
        if ($role === "doctor") {
            $_SESSION["name"] = "Dr. User";
            $_SESSION["role"] = "doctor";
            header("Location: doctor-dashboard.php");
            exit();
        }

        // Patient login (demo)
        if ($role === "patient") {
            $_SESSION["name"] = "Patient User";
            $_SESSION["role"] = "patient";
            header("Location: patient-dashboard.php");
            exit();
        }

        $error = "Invalid login credentials!";
    } else {
        $error = "Please fill all fields!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediCare | Login</title>
    <link rel="stylesheet" href="login.css">
</head>
<body>

<div class="container">

    <div class="login-box">
        <h2>MediCare Login</h2>

        <?php if ($error): ?>
            <p style="color:red; text-align:center;"><?php echo $error; ?></p>
        <?php endif; ?>

        <form method="POST">
            
            <div class="input-group">
                <label>Select Role</label>
                <select name="role" required>
                    <option value="">-- Choose Role --</option>
                    <option value="admin">Admin</option>
                    <option value="doctor">Doctor</option>
                    <option value="patient">Patient</option>
                </select>
            </div>

            <div class="input-group">
                <label>Email</label>
                <input type="email" name="email" placeholder="Enter your email" required>
            </div>

            <div class="input-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Enter your password" required>
            </div>

            <button type="submit" class="btn">Login</button>
        </form>

        <p class="small-text">
            Don't have an account? <a href="register.php">Register Here</a>
        </p>
    </div>

</div>

</body>
</html>
