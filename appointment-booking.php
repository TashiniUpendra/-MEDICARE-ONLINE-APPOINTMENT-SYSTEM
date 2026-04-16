<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include "db.php";

/* Patient-only access */
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "patient") {
    header("Location: login.php");
    exit();
}

$patientName = $_SESSION["name"];
$patientEmail = $_SESSION["email"];
$patientId = $_SESSION["user_id"] ?? 1; // temp if not set

/* Handle form */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $doctor = $_POST["doctor"];
    $date = $_POST["date"];
    $time = $_POST["time"];
    $reason = $_POST["reason"];

    // get doctor id from name
    $docQuery = mysqli_query($conn, "SELECT id FROM doctors WHERE name='$doctor'");
    $doc = mysqli_fetch_assoc($docQuery);
    $doctorId = $doc["id"] ?? 1;

    // insert into DB
    $sql = "INSERT INTO appointments (patient_id, doctor_id, appointment_date, appointment_time, reason)
            VALUES ('$patientId','$doctorId','$date','$time','$reason')";

    if (mysqli_query($conn, $sql)) {

        $_SESSION["appointment"] = [
            "patient" => $patientName,
            "email"   => $patientEmail,
            "doctor"  => $doctor,
            "date"    => $date,
            "time"    => $time,
            "reason"  => $reason
        ];

        header("Location: appointment-confirmation.php");
        exit();

    } else {
        echo "DB Error: " . mysqli_error($conn);
    }
}
?>
