<?php
session_start();

/* Admin-only access */
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
    header("Location: login.php");
    exit();
}

/* Initialize doctors (demo) */
if (!isset($_SESSION["doctors"])) {
    $_SESSION["doctors"] = [
        ["name" => "Dr. John Silva", "specialization" => "Cardiology", "time" => "9:00 AM - 2:00 PM"],
        ["name" => "Dr. Maya Fernando", "specialization" => "Dermatology", "time" => "10:00 AM - 4:00 PM"]
    ];
}

/* Add Doctor */
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["doctorName"]);
    $spec = trim($_POST["specialization"]);
    $time = trim($_POST["time"]);

    if ($name && $spec && $time) {
        $_SESSION["doctors"][] = [
            "name" => $name,
            "specialization" => $spec,
            "time" => $time
        ];
    }

    header("Location: manage-doctors.php");
    exit();
}

/* Delete Doctor */
if (isset($_GET["delete"])) {
    $index = (int)$_GET["delete"];

    if (isset($_SESSION["doctors"][$index])) {
        unset($_SESSION["doctors"][$index]);
        $_SESSION["doctors"] = array_values($_SESSION["doctors"]); // reindex
    }

    header("Location: manage-doctors.php");
    exit();
}

$doctors = $_SESSION["doctors"];
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
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

<!-- Add Form -->
<div class="box">
    <h3>Add Doctor</h3>

    <form method="POST">
        <input type="text" name="doctorName" placeholder="Doctor Name" required>
        <input type="text" name="specialization" placeholder="Specialization" required>
        <input type="text" name="time" placeholder="Available Time" required>

        <button class="add-btn">Add Doctor</button>
    </form>
</div>

<!-- Table -->
<div class="box">
    <h3>Doctor List</h3>

    <?php if (count($doctors) > 0): ?>

    <table>
        <tr>
            <th>Name</th>
            <th>Specialization</th>
            <th>Time</th>
            <th>Action</th>
        </tr>

        <?php foreach ($doctors as $i => $doc): ?>
        <tr>
            <td><?php echo htmlspecialchars($doc["name"]); ?></td>
            <td><?php echo htmlspecialchars($doc["specialization"]); ?></td>
            <td><?php echo htmlspecialchars($doc["time"]); ?></td>
            <td>
                <a class="delete"
                   href="?delete=<?php echo $i; ?>"
                   onclick="return confirm('Are you sure?')">
                   Delete
                </a>
            </td>
        </tr>
        <?php endforeach; ?>

    </table>

    <?php else: ?>
        <p>No doctors available.</p>
    <?php endif; ?>

</div>

</div>

</body>
</html>
