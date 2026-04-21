<?php
session_start();

/* Login check */
if (!isset($_SESSION["role"])) {
    header("Location: login.php");
    exit();
}

/* Safe values */
$name  = $_SESSION["name"] ?? "No Name";
$email = $_SESSION["email"] ?? "No Email";
$role  = $_SESSION["role"] ?? "";

/* Update */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $_SESSION["name"]  = $_POST["name"] ?? $name;
    $_SESSION["email"] = $_POST["email"] ?? $email;

    // reload values
    $name  = $_SESSION["name"];
    $email = $_SESSION["email"];

    $msg = "Profile Updated Successfully!";
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Profile</title>

<style>
body{
    font-family:Arial;
    background:#eef6fb;
}

.box{
    width:350px;
    margin:80px auto;
    background:white;
    padding:25px;
    border-radius:10px;
    box-shadow:0 4px 10px rgba(0,0,0,0.1);
}

h2{
    text-align:center;
    color:#0b78a6;
}

input{
    width:100%;
    padding:10px;
    margin-top:10px;
    border-radius:5px;
    border:1px solid #ccc;
}

.btn{
    width:100%;
    margin-top:15px;
    padding:10px;
    background:#0b78a6;
    color:white;
    border:none;
    border-radius:5px;
}

.msg{
    text-align:center;
    color:green;
}

.back{
    display:block;
    text-align:center;
    margin-top:10px;
}
</style>
</head>

<body>

<div class="box">

<h2>My Profile</h2>

<?php if(isset($msg)) echo "<p class='msg'>$msg</p>"; ?>

<form method="POST">

<input type="text" name="name" value="<?php echo $name; ?>" required>

<input type="email" name="email" value="<?php echo $email; ?>" required>

<input type="text" value="<?php echo $role; ?>" disabled>

<button class="btn">Update</button>

</form>

<a href="patient-dashboard.php" class="back">⬅ Back</a>

</div>

</body>
</html>
