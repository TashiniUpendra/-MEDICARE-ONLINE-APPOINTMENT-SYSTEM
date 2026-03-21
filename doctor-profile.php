<?php
session_start();

/* Optional: Only patient can view */
/*
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "patient") {
    header("Location: login.php");
    exit();
}
*/

/* Demo doctor data (later DB connect karanna puluwan) */
$doctors = [
    1 => [
        "name" => "Dr. John Silva",
        "specialty" => "Cardiologist",
        "experience" => "12 Years Experience",
        "image" => "https://via.placeholder.com/140",
        "schedule" => [
            ["day" => "Monday", "time" => "9:00 AM - 2:00 PM"],
            ["day" => "Tuesday", "time" => "10:00 AM - 4:00 PM"],
            ["day" => "Wednesday", "time" => "Holiday"],
            ["day" => "Thursday", "time" => "9:00 AM - 2:00 PM"],
            ["day" => "Friday", "time" => "10:00 AM - 5:00 PM"],
            ["day" => "Saturday", "time" => "9:00 AM - 12:00 PM"],
            ["day" => "Sunday", "time" => "Holiday"]
        ]
    ],
    2 => [
        "name" => "Dr. Maya Fernando",
        "specialty" => "Dermatologist",
        "experience" => "8 Years Experience",
        "image" => "https://via.placeholder.com/140",
        "schedule" => [
            ["day" => "Monday", "time" => "10:00 AM - 1:00 PM"],
            ["day" => "Tuesday", "time" => "Holiday"],
            ["day" => "Wednesday", "time" => "9:00 AM - 3:00 PM"],
            ["day" => "Thursday", "time" => "11:00 AM - 4:00 PM"],
            ["day" => "Friday", "time" => "Holiday"],
            ["day" => "Saturday", "time" => "9:00 AM - 12:00 PM"],
            ["day" => "Sunday", "time" => "Holiday"]
        ]
    ]
];

/* Get doctor ID from URL */
$id = isset($_GET["id"]) ? (int)$_GET["id"] : 1;

/* Validate ID */
if (!isset($doctors[$id])) {
    $id = 1;
}

$doctor = $doctors[$id];
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MediCare | Doctor Profile</title>

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

/* Header */
header {
    background: #0b78a6;
    color: white;
    text-align: center;
    padding: 18px;
    font-size: 22px;
}

/* Container */
.container {
    width: 90%;
    max-width: 900px;
    margin: 30px auto;
}

/* Profile Box */
.profile-box {
    background: white;
    padding: 25px;
    border-radius: 12px;
    display: flex;
    gap: 20px;
    align-items: center;
    box-shadow: 0 4px 18px rgba(0,0,0,0.1);
}

.profile-box img {
    width: 140px;
    height: 140px;
    border-radius: 50%;
    border: 4px solid #0b78a6;
}

/* Info */
.info h2 {
    color: #0b78a6;
}

.info p {
    margin: 6px 0;
}

/* Schedule */
.schedule-box {
    margin-top: 25px;
    background: white;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 4px 18px rgba(0,0,0,0.1);
}

.schedule-box h3 {
    color: #0b78a6;
    margin-bottom: 15px;
}

/* Table */
table {
    width: 100%;
    border-collapse: collapse;
}

th, td {
    padding: 12px;
    text-align: center;
    border-bottom: 1px solid #ddd;
}

th {
    background: #0b78a6;
    color: white;
}

/* Button */
.btn {
    display: inline-block;
    margin-top: 20px;
    background: #0b78a6;
    color: white;
    padding: 10px 16px;
    border-radius: 6px;
    text-decoration: none;
}

.btn:hover {
    background: #095c80;
}
</style>
</head>

<body>

<header>MediCare | Doctor Profile</header>

<div class="container">

    <!-- Doctor Profile -->
    <div class="profile-box">
        <img src="<?php echo htmlspecialchars($doctor["image"]); ?>" alt="Doctor">

        <div class="info">
            <h2><?php echo htmlspecialchars($doctor["name"]); ?></h2>
            <p><strong>Specialty:</strong> <?php echo htmlspecialchars($doctor["specialty"]); ?></p>
            <p><strong>Experience:</strong> <?php echo htmlspecialchars($doctor["experience"]); ?></p>
            <p><strong>Qualifications:</strong> MBBS, MD</p>
        </div>
    </div>

    <!-- Schedule -->
    <div class="schedule-box">
        <h3>Weekly Schedule</h3>

        <table>
            <tr>
                <th>Day</th>
                <th>Available Time</th>
            </tr>

            <?php foreach ($doctor["schedule"] as $s): ?>
                <tr>
                    <td><?php echo htmlspecialchars($s["day"]); ?></td>
                    <td><?php echo htmlspecialchars($s["time"]); ?></td>
                </tr>
            <?php endforeach; ?>
        </table>

        <a class="btn"
           href="appointment-booking.php?doctor=<?php echo urlencode($doctor['name']); ?>">
           Book Appointment
        </a>
    </div>

</div>

</body>
</html>
