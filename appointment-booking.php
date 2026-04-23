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

/* FORM GROUP */
.form-group{
    margin-bottom:15px;
}

label{
    display:block;
    font-weight:bold;
    color:#0b78a6;
    margin-bottom:5px;
}

input, select, textarea{
    width:100%;
    padding:10px;
    border-radius:6px;
    border:1px solid #ccc;
}

textarea{
    resize:none;
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
</style>
</head>

<body>

<div class="container">
<h2>📅 Book Appointment</h2>

<form method="POST">

<!-- Patient Name -->
<div class="form-group">
<label>Patient Name</label>
<input type="text" name="patient_name"
value="<?php echo htmlspecialchars($patientName); ?>" required>
</div>

<!-- Email -->
<div class="form-group">
<label>Email</label>
<input type="email" name="email"
value="<?php echo htmlspecialchars($patientEmail); ?>" required>
</div>

<!-- Doctor -->
<div class="form-group">
<label>Select Doctor</label>
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
</div>

<!-- Date -->
<div class="form-group">
<label>Appointment Date</label>
<input type="date" name="date" required>
</div>

<!-- Time -->
<div class="form-group">
<label>Appointment Time</label>
<input type="time" name="time" required>
</div>

<!-- Reason -->
<div class="form-group">
<label>Reason for Visit</label>
<textarea name="reason" placeholder="Enter reason"></textarea>
</div>

<button type="submit" class="btn">Confirm Appointment</button>

</form>
</div>

</body>
</html>
