<?php
session_start();

// If already logged in as admin, go directly to dashboard
if (isset($_SESSION["role"]) && $_SESSION["role"] === "admin") {
    header("Location: admin-dashboard.php");
    exit();
}

$error = "";

// Handle form submit
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"] ?? "");
    $password = trim($_POST["password"] ?? "");

    // Demo admin credentials (later can be replaced with database)
    if ($email === "admin@medicare.com" && $password === "admin123") {
        $_SESSION["name"] = "Admin";
        $_SESSION["role"] = "admin";

        header("Location: admin-dashboard.php");
        exit();
    } else {
        $error = "Invalid admin credentials!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>MediCare | Admin Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Poppins", sans-serif;
        }

        body {
            background: #eaf6fb;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .login-box {
            background: white;
            width: 380px;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 18px rgba(0,0,0,0.12);
        }

        h2 {
            text-align: center;
            color: #0b78a6;
            margin-bottom: 10px;
        }

        .error {
            color: #e74c3c;
            background: #ffecec;
            border: 1px solid #ffd1d1;
            padding: 10px;
            border-radius: 8px;
            text-align: center;
            margin-bottom: 15px;
            font-size: 14px;
        }

        label {
            display: block;
            margin: 12px 0 6px;
            font-weight: 600;
            color: #333;
        }

        input {
            width: 100%;
            padding: 10px;
            border-radius: 6px;
            border: 1px solid #b7dbea;
            font-size: 14px;
        }

        button {
            width: 100%;
            margin-top: 20px;
            padding: 12px;
            background: #0b78a6;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
        }

        button:hover {
            background: #095c80;
        }

        .note {
            text-align: center;
            margin-top: 15px;
            font-size: 13px;
            color: #555;
        }

        .note a {
            color: #0b78a6;
            text-decoration: none;
            font-weight: 600;
        }
    </style>
</head>
<body>

<div class="login-box">
    <h2>Admin Login</h2>

    <?php if ($error): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST" autocomplete="off">
        <label>Admin Email</label>
        <input type="email" name="email" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <button type="submit">Login</button>
    </form>

    <p class="note">Authorized administrators only</p>
    <p class="note"><a href="home.php">Back to Home</a></p>
</div>

</body>
</html>
