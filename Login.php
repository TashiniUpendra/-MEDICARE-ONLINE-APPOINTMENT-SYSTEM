<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include "db.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $role     = trim($_POST["role"]);
    $email    = trim($_POST["email"]);
    $password = $_POST["password"];

    // Prepared Statement used for Security against SQL Injection
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND role = ?");
    $stmt->bind_param("ss", $email, $role);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows === 1) {

        $user = $result->fetch_assoc();

        if (password_verify($password, $user["password"])) {

            $_SESSION["id"]    = $user["id"];
            $_SESSION["name"]  = $user["name"];
            $_SESSION["email"] = $user["email"];
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
            $error = "Incorrect password. Please try again.";
        }

    } else {
        $error = "No user found with this email and role.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MediCare | Login</title>
<!-- Bootstrap 5 & Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<style>
    body {
        margin: 0;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: linear-gradient(135deg, #0b78a6 0%, #6dd5fa 100%);
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
    }

    /* Top Left Home Button */
    .btn-home {
        position: absolute;
        top: 20px;
        left: 20px;
        background: rgba(255, 255, 255, 0.2);
        color: white;
        border: 1px solid rgba(255, 255, 255, 0.4);
        padding: 8px 18px;
        border-radius: 30px;
        text-decoration: none;
        font-weight: 500;
        backdrop-filter: blur(5px);
        transition: all 0.3s ease;
    }
    .btn-home:hover {
        background: white;
        color: #0b78a6;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }

    /* Card Styling */
    .login-card {
        background: #ffffff;
        padding: 40px 35px;
        border-radius: 16px;
        width: 100%;
        max-width: 400px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
    }

    .brand-title {
        color: #0b78a6;
        font-weight: 700;
        margin-bottom: 5px;
    }

    .form-control, .form-select {
        padding: 12px 15px;
        border-radius: 8px;
        border: 1px solid #ced4da;
        font-size: 0.95rem;
    }

    .form-control:focus, .form-select:focus {
        border-color: #0b78a6;
        box-shadow: 0 0 0 0.25rem rgba(11, 120, 166, 0.25);
    }

    .btn-submit {
        width: 100%;
        padding: 12px;
        background: #0b78a6;
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        font-size: 1rem;
        transition: background 0.3s ease;
    }

    .btn-submit:hover {
        background: #085a7d;
    }

    .register-link {
        color: #0b78a6;
        text-decoration: none;
        font-weight: 600;
    }
    .register-link:hover {
        text-decoration: underline;
    }
</style>
</head>

<body>

<!-- Back to Home Button linked to home.php -->
<a href="home.php" class="btn-home">
    <i class="bi bi-house-door-fill me-1"></i> Back to Home
</a>

<div class="login-card">
    <div class="text-center mb-4">
        <h2 class="brand-title"><i class="bi bi-heart-pulse-fill me-2"></i>MediCare</h2>
        <p class="text-muted">Welcome back! Please login to your account.</p>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger py-2 text-center" role="alert">
            <small><i class="bi bi-exclamation-triangle-fill me-1"></i> <?php echo $error; ?></small>
        </div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label class="form-label text-secondary fw-semibold">Select Role</label>
            <select name="role" class="form-select" required>
                <option value="">-- Choose Role --</option>
                <option value="admin">Admin</option>
                <option value="doctor">Doctor</option>
                <option value="patient">Patient</option>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label text-secondary fw-semibold">Email Address</label>
            <input type="email" name="email" class="form-control" placeholder="name@example.com" required>
        </div>

        <div class="mb-4">
            <label class="form-label text-secondary fw-semibold">Password</label>
            <input type="password" name="password" class="form-control" placeholder="••••••••" required>
        </div>

        <button type="submit" class="btn-submit mb-3">
            Login <i class="bi bi-arrow-right-short ms-1"></i>
        </button>
    </form>

    <div class="text-center mt-3">
        <p class="mb-0 text-muted">Don't have an account? <a href="register.php" class="register-link">Register Here</a></p>
    </div>
</div>

</body>
</html>
