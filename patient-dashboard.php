<?php
session_start();

/* Patient-only access */
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "patient") {
    header("Location: login.php");
    exit();
}

$patientName = $_SESSION["name"] ?? "Patient";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediCare | Patient Dashboard</title>

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
            color: white;
            padding: 18px;
            text-align: center;
            font-size: 22px;
            font-weight: bold;
        }

        .container {
            width: 90%;
            margin: 30px auto;
        }

        .welcome {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 18px rgba(0,0,0,0.12);
            margin-bottom: 25px;
        }

        .welcome h2 {
            color: #0b78a6;
        }

        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
        }

        .card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 18px rgba(0,0,0,0.12);
            text-align: center;
            transition: 0.3s;
        }

        .card:hover {
            transform: translateY(-6px);
        }

        .card h3 {
            color: #0b78a6;
            margin-bottom: 10px;
        }

        .card p {
            color: #444;
            margin-bottom: 15px;
        }

        .btn {
            background: #0b78a6;
            color: white;
            padding: 10px 16px;
            text-decoration: none;
            border-radius: 6px;
            display: inline-block;
        }

        .btn:hover {
            background: #095c80;
        }

        footer {
            margin-top: 40px;
            background: #0b78a6;
            color: white;
            text-align: center;
            padding: 12px;
        }
    </style>
</head>
<body>

<header>MediCare | Patient Dashboard</header>

<div class="container">

    <!-- Welcome Section -->
    <div class="welcome">
        <h2>Welcome, <?php echo htmlspecialchars($patientName); ?> 👋</h2>
        <p>Manage your appointments and view your medical schedule easily.</p>
    </div>

    <!-- Dashboard Cards -->
    <div class="cards">

        <div class="card">
            <h3>Book Appointment</h3>
            <p>Schedule a new appointment with a doctor.</p>
            <a href="doctor-list.php" class="btn">Book Now</a>
        </div>

        <div class="card">
            <h3>My Appointments</h3>
            <p>View upcoming and past appointments.</p>
            <a href="appointment-history.php" class="btn">View</a>
        </div>

        <div class="card">
            <h3>Profile</h3>
            <p>View and update your personal information.</p>
            <a href="#" class="btn">View Profile</a>
        </div>

        <div class="card">
            <h3>Logout</h3>
            <p>Securely log out from your account.</p>
            <a href="logout.php" class="btn">Logout</a>
        </div>

    </div>

</div>

<footer>
    MediCare Online Appointment System © <?php echo date("Y"); ?>
</footer>

</body>
</html>
