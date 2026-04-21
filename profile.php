<?php
session_start();

/* Login check */
if (!isset($_SESSION["role"])) {
    header("Location: login.php");
    exit();
}

/* Get user data */
$name  = $_SESSION["name"] ?? "";
$email = $_SESSION["email"] ?? "";
$role  = $_SESSION["role"] ?? "";

/* Update profile */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $_SESSION["name"]  = $_POST["name"];
    $_SESSION["email"] = $_POST["email"];

    // reload updated values
    $name  = $_SESSION["name"];
    $email = $_SESSION["email"];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>My Profile</title>

<style>
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:"Poppins", sans-serif;
}

body{
    background:#eef6fb;
}

/* HEADER */
header{
    background:#0b78a6;
    color:white;
    padding:15px;
    text-align:center;
    font-size:20px;
}

/* CONTAINER */
.container{
    width:90%;
    max-width:500px;
    margin:40px auto;
}

/* CARD */
.card{
    background:white;
    padding:25px;
    border-radius:12px;
    box-shadow:0 4px 15px rgba(0,0,0,0.1);
}

/* TITLE */
h2{
    text-align:center;
    color:#0b78a6;
    margin-bottom:20px;
}

/* INPUT */
input{
    width:100%;
    padding:10px;
    margin-top:10px;
    border-radius:6px;
    border:1px solid #ccc;
}

/* LABEL */
label{
    font-weight:bold;
    margin-top:10px;
    display:block;
}

/* BUTTON */
.btn{
    width:100%;
    margin-top:15px;
    padding:12px;
    background:#0b78a6;
    color:white;
    border:none;
    border-radius:6px;
    cursor:pointer;
}

.btn:hover{
    background:#095c80;
}

/* BACK */
.back{
    display:block;
    margin-top:15px;
    text-align:center;
    text-decoration:none;
    color:#0b78a6;
}
</style>
</head>

<body>

<header>My Profile</header>

<div class="container">
<div class="card">

<h2>Profile Details</h2>

<form method="POST">

<label>Name</label>
<input type="text" name="name" value="<?php echo htmlspecialchars($name); ?>" required>

<label>Email</label>
<input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>

<label>Role</label>
<input type="text" value="<?php echo htmlspecialchars($role); ?>" disabled>

<button type="submit" class="btn">Update Profile</button>

</form>

<a href="patient-dashboard.php" class="back">⬅ Back to Dashboard</a>

</div>
</div>

</body>
</html>