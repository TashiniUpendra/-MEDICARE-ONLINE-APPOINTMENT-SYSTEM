<?php
session_start();
/*
  OPTIONAL: Only patients can view doctor profile
  Uncomment if you want.
*/
/*
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "patient") {
    header("Location: login.php");
    exit();
}
*/

/* Demo doctor data (later connect to database) */
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

/* Get doctor ID from URL (example: doctor-profile.php?id=1) */
$id = isset($_GET["id"]) ? (int)$_GET["id"] : 1;

/* If ID not found, default to 1 */
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
            margin: 30px auto;
        }

        .profile-box {
            background: white;
            padding: 25px;
            border-radius: 12px;
            display: flex;
            gap: 25px;
            box-shadow: 0 4px 18px rgba(0,0,0,0.12);
        }

        .profile-box img {
            width: 140px;
            height: 140px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #0b78a6;
        }

        .info h2 {
            color: #0b78a6;
            margin-bottom: 8px;
        }

        .info p {
            margin: 5px 0;
            color: #444;
        }

        .schedule-box {
            margin-top: 25px;
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 18px rgba(0,0,0,0.12);
        }

        .schedule-box h3 {
            color: #0b78a6;
            margin-bottom: 15px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
        }

        th, td {
            padding: 12px;
            text-align: center;
            border-bottom: 1px solid #d9e9f2;
        }

        th {
            background: #0b78a6;
            color: white;
        }

        .btn {
            background: #0b78a6;
            padding: 10px 14px;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            display: inline-block;
            margin-top: 20px;
        }

        .btn:hover {
            background: #095c80;
        }
    </style>
</head>

<body>

<header>MediCare | Doctor Profile</header>
<div class="container">

    <!-- Doctor Profile Section -->
    <div class="profile-box">
        <img src="<?php echo htmlspecialchars($doctor["image"]); ?>" alt="Doctor">

        <div class="info">
            <h2><?php echo htmlspecialchars($doctor["name"]); ?></h2>
            <p><strong>Specialty:</strong> <?php echo htmlspecialchars($doctor["specialty"]); ?></p>
            <p><strong>Experience:</strong> <?php echo htmlspecialchars($doctor["experience"]); ?></p>
            <p><strong>Qualifications:</strong> MBBS, MD, Consultant Physician</p>
        </div>
    </div>

    <!-- Doctor Schedule Section -->
    <div class="schedule-box">
        <h3>Weekly Schedule</h3>

        <table>
            <tr>
                <th>Day</th>
                <th>Available Time</th>
            </tr>
            <tbody>
                <?php foreach ($doctor["schedule"] as $s): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($s["day"]); ?></td>
                        <td><?php echo htmlspecialchars($s["time"]); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <a class="btn"
           href="appointment-booking.php?doctor=<?php echo urlencode($doctor['name']); ?>">
           Book Appointment
        </a>
    </div>

</div>

</body>
</html>
