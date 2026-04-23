<?php
session_start();
include "db.php";

/* Admin-only access */
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
    header("Location: login.php");
    exit();
}

/* ADD DOCTOR */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = $_POST["doctorName"];
    $email = $_POST["email"];
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);
    $spec = $_POST["specialization"];
    $time = $_POST["time"];

    // Insert into users (login)
    $sql1 = "INSERT INTO users (name,email,password,role)
             VALUES ('$name','$email','$password','doctor')";
    mysqli_query($conn, $sql1);

    // Insert into doctors (details)
    $sql2 = "INSERT INTO doctors (name,specialization,available_time)
             VALUES ('$name','$spec','$time')";
    mysqli_query($conn, $sql2);

    header("Location: manage-doctors.php");
    exit();
}

/* DELETE DOCTOR */
if (isset($_GET["delete"])) {

    $id = $_GET["delete"];

    mysqli_query($conn, "DELETE FROM doctors WHERE id='$id'");

    header("Location: manage-doctors.php");
    exit();
}

/* FETCH DOCTORS */
$result = mysqli_query($conn, "SELECT * FROM doctors");
?>

<!DOCTYPE html>
<html>
<head>
<title>Manage Doctors</title>

<style>
body{
    font-family:"Poppins", sans-serif;
    background:#eef7fb;
}

/* Header */
header{
    background:#1f7ea5;
    color:white;
    padding:15px 30px;
    display:flex;
    justify-content:space-between;
}

header a{
    background:white;
    color:#1f7ea5;
    padding:8px 14px;
    border-radius:6px;
    text-decoration:none;
    font-weight:bold;
}

/* Container */
.container{
    padding:30px;
}

/* Box */
.box{
    background:white;
    padding:20px;
    border-radius:12px;
    margin-bottom:20px;
    box-shadow:0 3px 10px rgba(0,0,0,0.1);
}

/* Inputs */
input{
    width:100%;
    padding:10px;
    margin-top:5px;
    border-radius:6px;
    border:1px solid #ccc;
}

/* Button */
.add-btn{
    margin-top:15px;
    background:#1f7ea5;
    color:white;
    padding:10px;
    border:none;
    border-radius:6px;
    cursor:pointer;
}

.add-btn:hover{
    background:#155d7a;
}

/* Table */
table{
    width:100%;
    border-collapse:collapse;
    margin-top:10px;
}

th,td{
    padding:12px;
    border-bottom:1px solid #ddd;
    text-align:center;
}

th{
    background:#dff2fb;
}

/* Delete */
.delete{
    background:red;
    color:white;
    padding:6px 10px;
    border-radius:5px;
    text-decoration:none;
}
</style>

</head>

<body>

<header>
    <h2>Manage Doctors</h2>
    <a href="admin-dashboard.php">Back</a>
</header>

<div class="container">

<!-- ADD DOCTOR -->
<div class="box">
    <h3>Add Doctor</h3>

    <form method="POST">
        <input type="text" name="doctorName" placeholder="Doctor Name" required>
        <input type="email" name="email" placeholder="Doctor Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <input type="text" name="specialization" placeholder="Specialization" required>
        <input type="text" name="time" placeholder="Available Time" required>

        <button class="add-btn">Add Doctor</button>
    </form>
</div>

<!-- DOCTOR LIST -->
<div class="box">
    <h3>Doctor List</h3>

    <table>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Specialization</th>
            <th>Time</th>
            <th>Action</th>
        </tr>

        <?php while($row = mysqli_fetch_assoc($result)): ?>
        <tr>
            <td><?php echo $row["id"]; ?></td>
            <td><?php echo htmlspecialchars($row["name"]); ?></td>
            <td><?php echo htmlspecialchars($row["specialization"]); ?></td>
            <td><?php echo htmlspecialchars($row["available_time"]); ?></td>
            <td>
                <a class="delete"
                   href="?delete=<?php echo $row["id"]; ?>"
                   onclick="return confirm('Delete doctor?')">
                   Delete
                </a>
            </td>
        </tr>
        <?php endwhile; ?>

    </table>
</div>

</div>

</body>
</html>
