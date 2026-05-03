<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include "db.php";

$error = "";
$success = "";

$uploadDir = "uploads/";
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = $_POST["name"];
    $email = $_POST["email"];
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);
    $role = $_POST["role"];
    $specialization = $_POST["specialization"] ?? "";

    $imageName = "";

    /* Image upload */
    if ($role === "doctor" && isset($_FILES["image"]["name"])) {

        $fileName = time() . "_" . $_FILES["image"]["name"];
        $targetFile = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
            $imageName = $fileName;
        }
    }

    if ($name && $email && $password && $role) {

        $check = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");

        if (mysqli_num_rows($check) > 0) {
            $error = "Email already exists!";
        } else {

            $sql = "INSERT INTO users (name,email,password,role,image,specialization)
                    VALUES ('$name','$email','$password','$role','$imageName','$specialization')";

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
body{
    background:#f0faff;
    font-family:Arial;
}
.container{
    display:flex;
    justify-content:center;
    align-items:center;
    height:100vh;
}
.box{
    background:#fff;
    padding:30px;
    border-radius:10px;
    width:350px;
}
input,select{
    width:100%;
    padding:10px;
    margin-top:8px;
}
button{
    margin-top:15px;
    width:100%;
    padding:10px;
    background:#0b78a6;
    color:#fff;
}
</style>

<script>
function showDoctorFields() {
    let role = document.getElementById("role").value;

    let imageField = document.getElementById("imageField");
    let specField = document.getElementById("specField");

    if (role === "doctor") {
        imageField.style.display = "block";
        specField.style.display = "block";
    } else {
        imageField.style.display = "none";
        specField.style.display = "none";
    }
}
</script>

</head>

<body>

<div class="container">
<div class="box">

<h2>Register</h2>

<?php if ($error) echo "<p style='color:red'>$error</p>"; ?>
<?php if ($success) echo "<p style='color:green'>$success</p>"; ?>

<form method="POST" enctype="multipart/form-data">

<input type="text" name="name" placeholder="Name" required>
<input type="email" name="email" placeholder="Email" required>
<input type="password" name="password" placeholder="Password" required>

<select name="role" id="role" onchange="showDoctorFields()" required>
<option value="">Select Role</option>
<option value="admin">Admin</option>
<option value="doctor">Doctor</option>
<option value="patient">Patient</option>
</select>

<!-- 🔥 Specialization -->
<div id="specField" style="display:none;">
<select name="specialization">
<option value="">Select Specialization</option>
<option>Cardiologist</option>
<option>Dermatologist</option>
<option>Neurologist</option>
<option>Pediatrician</option>
<option>General Physician</option>
</select>
</div>

<!-- 🔥 Image -->
<div id="imageField" style="display:none;">
<input type="file" name="image" accept="image/*">
</div>

<button type="submit">Register</button>

</form>

<p><a href="login.php">Login</a></p>

</div>
</div>

</body>
</html>
