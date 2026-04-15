<?php
session_start();

$error = "";
$success = "";

/* Handle Register */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = trim($_POST["password"] ?? "");
    $role = $_POST["role"] ?? "";

    if ($name && $email && $password && $role) {

        /* Create users array if not exists */
        if (!isset($_SESSION["users"])) {
            $_SESSION["users"] = [];
        }

        /* Check duplicate email */
        foreach ($_SESSION["users"] as $user) {
            if ($user["email"] === $email) {
                $error = "Email already registered!";
                break;
            }
        }

        /* If no error, save user */
        if (!$error) {

            $_SESSION["users"][] = [
                "name" => $name,
                "email" => $email,
                "password" => $password,
                "role" => $role
            ];

            $success = "Registration successful! You can login now.";
        }

    } else {
        $error = "Please fill all fields!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>MediCare | Register</title>

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

.box {
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

.msg {
    text-align: center;
    margin-top: 10px;
}

.success {
    color: green;
}

.error {
    color: red;
}
</style>
</head>

<body>

<div class="container">
<div class="box">

<h2>Register</h2>

<?php if ($error): ?>
<p class="msg error"><?php echo $error; ?></p>
<?php endif; ?>

<?php if ($success): ?>
<p class="msg success"><?php echo $success; ?></p>
<?php endif; ?>

<form method="POST">

<div class="input-group">
<label>Name</label>
<input type="text" name="name" required>
</div>

<div class="input-group">
<label>Email</label>
<input type="email" name="email" required>
</div>

<div class="input-group">
<label>Password</label>
<input type="password" name="password" required>
</div>

<div class="input-group">
<label>Role</label>
<select name="role" required>
<option value="">-- Select Role --</option>
<option value="admin">Admin</option>
<option value="doctor">Doctor</option>
<option value="patient">Patient</option>
</select>
</div>

<button type="submit" class="btn">Register</button>

</form>

<p style="text-align:center; margin-top:15px;">
Already have an account? 
<a href="login.php">Login</a>
</p>

</div>
</div>

</body>
</html>