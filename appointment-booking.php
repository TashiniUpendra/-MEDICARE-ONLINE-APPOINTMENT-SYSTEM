<?php
session_start();

/* Patient-only access */
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "patient") {
    header("Location: login.php");
    exit();
}

/* Get patient info from session */
$patientName = $_SESSION["name"] ?? "";
$patientEmail = $_SESSION["email"] ?? "";

/* Handle form submission */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $_SESSION["appointment"] = [
        "patient" => $_POST["patient_name"],
        "email"   => $_POST["email"],
        "doctor"  => $_POST["doctor"],
        "date"    => $_POST["date"],
        "time"    => $_POST["time"],
        "reason"  => $_POST["reason"]
    ];

    // Redirect to confirmation page
    header("Location: appointment-confirmation.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediCare | Book Appointment</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Poppins", sans-serif;
        }

        body {
            background: #f0faff;
        }

        header {
            background: #0b78a6;
            padding: 18px;
            text-align: center;
            color: white;
            font-size: 22px;
            font-weight: bold;
        }

        .container {
            width: 90%;
            max-width: 650px;
            margin: 40px auto;
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 18px rgba(0,0,0,0.12);
        }

        h2 {
            text-align: center;
            color: #0b78a6;
            margin-bottom: 25px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            font-weight: 600;
            color: #0b78a6;
            display: block;
            margin-bottom: 6px;
        }

        input, select, textarea {
            width: 100%;
            padding: 10px;
            border-radius: 6px;
            border: 1px solid #cce4f1;
            font-size: 14px;
        }

        textarea {
            resize: none;
        }

        .btn {
            width: 100%;
            background: #0b78a6;
            color: white;
            padding: 12px;
            font-size: 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            margin-top: 10px;
        }

        .btn:hover {
            background: #095c80;
        }
    </style>
</head>

<body>

<header>MediCare | Appointment Booking</header>

<div class="container">
    <h2>Book Your Appointment</h2>

    <form method="POST">

        <div class="form-group">
            <label>Patient Name</label>
            <input type="text" name="patient_name" value="<?php echo htmlspecialchars($patientName); ?>" required>
        </div>

        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($patientEmail); ?>" required>
        </div>

        <div class="form-group">
            <label>Doctor</label>
            <select name="doctor" required>
                <option value="">-- Select Doctor --</option>
                <option>Dr. John Silva (Cardiologist)</option>
                <option>Dr. Maya Fernando (Dermatologist)</option>
                <option>Dr. Ruwan Perera (Neurologist)</option>
                <option>Dr. Nadeesha Karun (Pediatrician)</option>
            </select>
        </div>

        <div class="form-group">
            <label>Appointment Date</label>
            <input type="date" name="date" required>
        </div>

        <div class="form-group">
            <label>Appointment Time</label>
            <input type="time" name="time" required>
        </div>

        <div class="form-group">
            <label>Reason for Visit</label>
            <textarea rows="3" name="reason"></textarea>
        </div>

        <button type="submit" class="btn">Confirm Appointment</button>
    </form>
</div>

</body>
</html>
