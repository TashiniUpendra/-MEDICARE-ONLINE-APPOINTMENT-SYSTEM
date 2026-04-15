<?php
session_start();

/* If already logged in */
if (isset($_SESSION["role"])) {
    header("Location: home.php");
    exit();
}

$error = "";

/* Handle login */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $role = $_POST["role"] ?? "";
    $email = $_POST["email"] ?? "";
    $password = $_POST["password"] ?? "";

    if (!empty($role) && !empty($email) && !empty($password)) {

        /* ADMIN LOGIN */
        if ($role === "admin" && $email === "admin@medicare.com" && $password === "admin123") {
            $_SESSION["name"] = "Admin";
            $_SESSION["email"] = $email;
            $_SESSION["role"] = "admin";
            header("Location: admin-dashboard.php");
            exit();
        }

        /* DOCTOR LOGIN (Demo) */
        if ($role === "doctor") {
            $_SESSION["name"] = "Dr. User";
            $_SESSION["email"] = $email;
            $_SESSION["role"] = "doctor";
            header("Location: doctor-dashboard.php");
            exit();
        }

        /* PATIENT LOGIN (Demo) */
        if ($role === "patient") {
            $_SESSION["name"] = "Patient User";
            $_SESSION["email"] = $email;
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

<style>
body {
    font-family: Arial;
    background: #f0faff;
}

.container {
    width: 100%;
    height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
}

.login-box {
    background: white;
    padding: 30px;
    border-radius: 10px;
    width: 350px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

h2 {
    text-align: center;
    color: #0b78a6;
}

.input-group {
    margin-top: 15px;
}

.input-group label {
    font-weight: bold;
}

.input-group input,
.input-group select {
    width: 100%;
    padding: 10px;
    margin-top: 5px;
    border-radius: 5px;
    border: 1px solid #ccc;
}

.btn {
    width: 100%;
    margin-top: 20px;
    padding: 10px;
    background: #0b78a6;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}

.btn:hover {
    background: #095c80;
}

.error {
    color: red;
    text-align: center;
    margin-top: 10px;
}

.register-link {
    text-align: center;
    margin-top: 15px;
}
</style>
</head>

<body>

<div class="container">
    <div class="login-box">

        <h2>Login</h2>

        <?php if ($error): ?>
            <p class="error"><?php echo $error; ?></p>
        <?php endif; ?>

        <form method="POST">

            <div class="input-group">
                <label>Role</label>
                <select name="role" required>
                    <option value="">-- Select Role --</option>
                    <option value="admin">Admin</option>
                    <option value="doctor">Doctor</option>
                    <option value="patient">Patient</option>
                </select>
            </div>

            <div class="input-group">
                <label>Email</label>
                <input type="email" name="email" required>
            </div>

            <div class="input-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>

            <button type="submit" class="btn">Login</button>

        </form>

        <!-- ✅ REGISTER LINK ADDED HERE -->
        <div class="register-link">
            <p>
                Don't have an account?
                <a href="register.php" style="color:#0b78a6; font-weight:bold;">
                    Register Here
                </a>
            </p>
        </div>

    </div>
</div>

</body>
</html>
