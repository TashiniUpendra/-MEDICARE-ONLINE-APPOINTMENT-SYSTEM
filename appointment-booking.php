<?php
session_start();

/* Login check */
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "patient") {
    header("Location: login.php");
    exit();
}

/* Safe session values */
$patientName  = $_SESSION["name"] ?? "";
$patientEmail = $_SESSION["email"] ?? "";

/* Get doctor from URL */
$selectedDoctor = $_GET["doctor"] ?? "";

/* Handle form submit */
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
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Book Appointment</title>

<style>
body{
    font-family: Arial;
    background:#f0faff;
}

.container{
    width:90%;
    max-width:500px;
    margin:40px auto;
    background:white;
    padding:30px;
    border-radius:12px;
    box-shadow:0 4px 15px rgba(0,0,0,0.1);
}

h2{
    text-align:center;
    color:#0b78a6;
    margin-bottom:20px;
}

input, select, textarea{
    width:100%;
    padding:10px;
    margin-top:10px;
    border-radius:6px;
    border:1px solid #ccc;
}

textarea{
    resize:none;
}

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
</style>
</head>

<body>

<div class="container">
<h2>Book Appointment</h2>

<form method="POST">

<!-- Patient Name -->
<input type="text" name="patient_name"
value="<?php echo htmlspecialchars($patientName); ?>" required>

<!-- Email -->
<input type="email" name="email"
value="<?php echo htmlspecialchars($patientEmail); ?>" required>

<!-- Doctor -->
<select name="doctor" required>

<option value="">-- Select Doctor --</option>

<option value="Dr. John Silva"
<?php if($selectedDoctor=="Dr. John Silva") echo "selected"; ?>>
Dr. John Silva (Cardiologist)
</option>

<option value="Dr. Maya Fernando"
<?php if($selectedDoctor=="Dr. Maya Fernando") echo "selected"; ?>>
Dr. Maya Fernando (Dermatologist)
</option>

<option value="Dr. Ruwan Perera"
<?php if($selectedDoctor=="Dr. Ruwan Perera") echo "selected"; ?>>
Dr. Ruwan Perera (Neurologist)
</option>

<option value="Dr. Nadeesha Karun"
<?php if($selectedDoctor=="Dr. Nadeesha Karun") echo "selected"; ?>>
Dr. Nadeesha Karun (Pediatrician)
</option>

</select>

<!-- Date -->
<input type="date" name="date" required>

<!-- Time -->
<input type="time" name="time" required>

<!-- Reason -->
<textarea name="reason" placeholder="Reason for visit"></textarea>

<button type="submit" class="btn">Confirm Appointment</button>

</form>
</div>

</body>
</html>
