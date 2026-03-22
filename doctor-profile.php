<?php
session_start();

/* Doctor Data */
$doctors = [
    1 => [
        "name" => "Dr. John Silva",
        "specialty" => "Cardiologist",
        "experience" => "12 Years Experience",
        "image" => "images/doctor1.jpg", // 🔥 local image
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
        "image" => "images/doctor2.jpg",
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

/* Get doctor ID */
$id = isset($_GET['id']) ? (int)$_GET['id'] : 1;

/* Validate */
if (!array_key_exists($id, $doctors)) {
    $id = 1;
}

$doctor = $doctors[$id];
?>

<!DOCTYPE html>
<html>
<head>
<title>Doctor Profile</title>

<style>
body {
    background:#f0faff;
    font-family: Arial;
}

.container {
    width:90%;
    max-width:900px;
    margin:30px auto;
}

.box {
    background:white;
    padding:20px;
    border-radius:12px;
    box-shadow:0 4px 15px rgba(0,0,0,0.1);
}

/* Profile */
.profile {
    display:flex;
    gap:20px;
    align-items:center;
}

.profile img {
    width:140px;
    height:140px;
    border-radius:50%;
    object-fit:cover;
    border:4px solid #0b78a6;
}

h2 {
    color:#0b78a6;
}

/* Table */
table {
    width:100%;
    margin-top:15px;
    border-collapse:collapse;
}

th {
    background:#0b78a6;
    color:white;
}

td, th {
    padding:10px;
    text-align:center;
    border-bottom:1px solid #ddd;
}

/* Button */
.btn {
    background:#0b78a6;
    color:white;
    padding:10px 15px;
    display:inline-block;
    margin-top:15px;
    text-decoration:none;
    border-radius:6px;
}

.btn:hover {
    background:#095c80;
}
</style>
</head>

<body>

<div class="container">

    <!-- Profile -->
    <div class="box profile">
        <img src="<?= htmlspecialchars($doctor['image']); ?>" alt="https://i.pinimg.com/736x/16/96/71/169671343ef815d20808e6c9e43c5c19.jpg">

        <div>
            <h2><?= htmlspecialchars($doctor['name']); ?></h2>
            <p><b>Specialty:</b> <?= htmlspecialchars($doctor['specialty']); ?></p>
            <p><b>Experience:</b> <?= htmlspecialchars($doctor['experience']); ?></p>
            <p><b>Qualifications:</b> MBBS, MD</p>
        </div>
    </div>

    <!-- Schedule -->
    <div class="box" style="margin-top:20px;">
        <h3>Weekly Schedule</h3>

        <table>
            <tr>
                <th>Day</th>
                <th>Available Time</th>
            </tr>

            <?php foreach ($doctor['schedule'] as $s): ?>
            <tr>
                <td><?= htmlspecialchars($s['day']); ?></td>
                <td><?= htmlspecialchars($s['time']); ?></td>
            </tr>
            <?php endforeach; ?>
        </table>

        <a class="btn"
           href="appointment-booking.php?doctor=<?= urlencode($doctor['name']); ?>">
           Book Appointment
        </a>
    </div>

</div>

</body>
</html>
