<?php
session_start();

/* login check */
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "patient") {
    header("Location: login.php");
    exit();
}

$patientName = $_SESSION["name"];
$patientEmail = $_SESSION["email"];

/* doctor from URL */
$selectedDoctor = $_GET["doctor"] ?? "";

/* submit */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $_SESSION["appointment"] = [
        "patient" => $_POST["patient_name"],
        "email"   => $_POST["email"],
        "doctor"  => $_POST["doctor"],
        "date"    => $_POST["date"],
        "time"    => $_POST["time"],
        "reason"  => $_POST["reason"]
    ];

    header("Location: appointment-confirmation.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Book Appointment</title>

<style>
body{font-family:Arial;background:#f0faff}
.container{width:400px;margin:40px auto;background:white;padding:20px;border-radius:10px}
input,select,textarea{width:100%;padding:10px;margin-top:10px}
.btn{margin-top:15px;background:#0b78a6;color:white;padding:10px;border:none}
</style>
</head>

<body>

<div class="container">
<h2>Book Appointment</h2>

<form method="POST">

<input type="text" name="patient_name" value="<?php echo $patientName; ?>" required>

<input type="email" name="email" value="<?php echo $patientEmail; ?>" required>

<select name="doctor" required>

<option value="">-- Select Doctor --</option>

<option value="Dr. John Silva" <?php if($selectedDoctor=="Dr. John Silva") echo "selected"; ?>>
Dr. John Silva
</option>

<option value="Dr. Maya Fernando" <?php if($selectedDoctor=="Dr. Maya Fernando") echo "selected"; ?>>
Dr. Maya Fernando
</option>

<option value="Dr. Ruwan Perera" <?php if($selectedDoctor=="Dr. Ruwan Perera") echo "selected"; ?>>
Dr. Ruwan Perera
</option>

<option value="Dr. Nadeesha Karun" <?php if($selectedDoctor=="Dr. Nadeesha Karun") echo "selected"; ?>>
Dr. Nadeesha Karun
</option>

</select>

<input type="date" name="date" required>
<input type="time" name="time" required>

<textarea name="reason" placeholder="Reason"></textarea>

<button class="btn">Confirm Appointment</button>

</form>
</div>

</body>
</html>
