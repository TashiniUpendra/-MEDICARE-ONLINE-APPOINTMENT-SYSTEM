<?php
session_start();
include "db.php"; // Database Connection

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

/* Fetch Dynamic Doctors safely from Database */
$doctors_query = "SELECT * FROM users WHERE role = 'doctor' ORDER BY name ASC";
$doctors_result = mysqli_query($conn, $doctors_query);

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
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Book Appointment</title>

<style>
body{
    font-family: Arial, sans-serif;
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
    box-sizing: border-box;
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
    font-weight: bold;
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

<!-- Doctor (Dynamic Database Dropdown) -->
<div class="form-group">
<label>Select Doctor</label>
<select name="doctor" id="doctorSelect" onchange="updateDoctorTime()" required>

<option value="" data-time="">-- Select Doctor --</option>

<?php 
if ($doctors_result && mysqli_num_rows($doctors_result) > 0) {
    while ($doc = mysqli_fetch_assoc($doctors_result)) {
        // Fix Name Duplicate Issue
        $doc_name = $doc['name'];
        if (strpos($doc_name, 'Dr.') === false) {
            $doc_name = "Dr. " . $doc_name;
        }

        // Available Time (DB එකේ 'available_time' හෝ 'time' column එකක් නැත්නම් Default time එකක් වැටේ)
        $doc_time = !empty($doc['available_time']) ? $doc['available_time'] : "16:30"; // Default 04:30 PM
        
        // Specialization Label Check
        $spec = (!empty($doc['specialization'])) ? " (" . $doc['specialization'] . ")" : "";
        
        // Match check with URL parameter
        $isSelected = ($selectedDoctor == $doc_name || $selectedDoctor == $doc['name']) ? "selected" : "";
        
        echo "<option value='" . htmlspecialchars($doc_name) . "' data-time='" . htmlspecialchars($doc_time) . "' $isSelected>" . htmlspecialchars($doc_name . $spec) . "</option>";
    }
}
?>

</select>
</div>

<!-- Date -->
<div class="form-group">
<label>Appointment Date</label>
<input type="date" name="date" required>
</div>

<!-- Time (Auto-filled on Doctor Select) -->
<div class="form-group">
<label>Appointment Time</label>
<input type="time" name="time" id="appointmentTime" required>
</div>

<!-- Reason -->
<div class="form-group">
<label>Reason for Visit</label>
<textarea name="reason" rows="3" placeholder="Enter reason"></textarea>
</div>

<button type="submit" class="btn">Confirm Appointment</button>

</form>
</div>

<!-- JavaScript for Auto-filling Time -->
<script>
function updateDoctorTime() {
    var doctorSelect = document.getElementById("doctorSelect");
    var timeInput = document.getElementById("appointmentTime");
    
    // Select කරපු Option එකේ data-time attribute එක ලබාගැනීම
    var selectedOption = doctorSelect.options[doctorSelect.selectedIndex];
    var doctorTime = selectedOption.getAttribute("data-time");
    
    if (doctorTime) {
        timeInput.value = doctorTime;
    } else {
        timeInput.value = "";
    }
}

// Page එක Load වෙද්දි කලින්ම Doctor කෙනෙක් Select වෙලා හිටියොත් Time එක Auto set කිරීමට
window.onload = function() {
    updateDoctorTime();
};
</script>

</body>
</html>
