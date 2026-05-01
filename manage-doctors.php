<?php
session_start();
include "db.php";

/* Admin check */
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
    header("Location: login.php");
    exit();
}

/* ADD DOCTOR */
if (isset($_POST["addDoctor"])) {

    $name  = $_POST["name"];
    $email = $_POST["email"];
    $pass  = password_hash($_POST["password"], PASSWORD_DEFAULT);

    $sql = "INSERT INTO users (name,email,password,role)
            VALUES ('$name','$email','$pass','doctor')";

    mysqli_query($conn, $sql);

    header("Location: manage-doctors.php");
    exit();
}

/* DELETE */
if (isset($_GET["delete"])) {
    $id = $_GET["delete"];
    mysqli_query($conn, "DELETE FROM users WHERE id=$id AND role='doctor'");
    header("Location: manage-doctors.php");
    exit();
}

/* FETCH */
$result = mysqli_query($conn, "SELECT * FROM users WHERE role='doctor'");
?>

<!DOCTYPE html>
<html>
<head>
<title>Manage Doctors</title>

<style>
body{font-family:Arial;background:#eef6fb;}
.container{width:90%;margin:30px auto;}
.box{
    background:white;
    padding:20px;
    border-radius:10px;
    margin-bottom:20px;
    box-shadow:0 3px 10px rgba(0,0,0,0.1);
}
input{
    width:100%;
    padding:10px;
    margin-top:10px;
}
button{
    margin-top:10px;
    padding:10px;
    background:#0b78a6;
    color:white;
    border:none;
}
table{
    width:100%;
    border-collapse:collapse;
}
th,td{
    padding:10px;
    border-bottom:1px solid #ccc;
    text-align:center;
}
.delete{
    background:red;
    color:white;
    padding:5px 10px;
    text-decoration:none;
}
</style>

</head>
<body>

<div class="container">

<div class="box">
<h3>Add Doctor</h3>

<form method="POST">
<input type="text" name="name" placeholder="Doctor Name" required>
<input type="email" name="email" placeholder="Email" required>
<input type="password" name="password" placeholder="Password" required>

<button name="addDoctor">Add Doctor</button>
</form>
</div>

<div class="box">
<h3>Doctor List</h3>

<table>
<tr>
<th>ID</th>
<th>Name</th>
<th>Email</th>
<th>Action</th>
</tr>

<?php while($row = mysqli_fetch_assoc($result)): ?>
<tr>
<td><?php echo $row["id"]; ?></td>
<td><?php echo $row["name"]; ?></td>
<td><?php echo $row["email"]; ?></td>
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
