<?php
session_start();

/* Admin-only protection */
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
    header("Location: login.php");
    exit();
}

$adminName = $_SESSION["name"] ?? "Admin";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>MediCare | Admin Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Poppins", sans-serif;
        }

        body {
            background: #f4f9fc;
        }

        header {
            background: #0b78a6;
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        header h1 {
            font-size: 22px;
        }

        header .right {
            display: flex;
            gap: 12px;
            align-items: center;
        }

        header .admin-name {
            font-weight: 600;
            font-size: 14px;
            opacity: 0.95;
        }

        header a.logout-btn {
            background: white;
            color: #0b78a6;
            text-decoration: none;
            padding: 8px 14px;
            border-radius: 6px;
            font-weight: bold;
            display: inline-block;
        }

        header a.logout-btn:hover {
            background: #e6f4fb;
        }

        .dashboard {
            padding: 30px;
        }

        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            text-align: center;
        }

        .card h2 {
            color: #0b78a6;
            font-size: 28px;
            margin-bottom: 10px;
        }

        .card p {
            font-weight: 600;
            color: #444;
        }

        .actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }

        .action-box {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            text-align: center;
        }

        .action-box h3 {
            color: #0b78a6;
            margin-bottom: 15px;
        }

        .action-box button {
            background: #0b78a6;
            color: white;
            border: none;
            padding: 10px 18px;
            border-radius: 6px;
            cursor: pointer;
        }

        .action-box button:hover {
            background: #095c80;
        }
    </style>
</head>
<body>

<header>
    <h1>Admin Dashboard</h1>

    <div class="right">
        <div class="admin-name">
            Logged in as: <?php echo htmlspecialchars($adminName); ?>
        </div>
        <a class="logout-btn" href="logout.php">Logout</a>
    </div>
</header>

<div class="dashboard">

    <!-- Summary Cards (demo numbers for now) -->
    <div class="cards">
        <div class="card">
            <h2>12</h2>
            <p>Total Doctors</p>
        </div>

        <div class="card">
            <h2>38</h2>
            <p>Total Patients</p>
        </div>

        <div class="card">
            <h2>25</h2>
            <p>Appointments Today</p>
        </div>

        <div class="card">
            <h2>5</h2>
            <p>Pending Appointments</p>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="actions">
        <div class="action-box">
            <h3>Manage Doctors</h3>
            <button onclick="goDoctors()">View Doctors</button>
        </div>

        <div class="action-box">
            <h3>View Appointments</h3>
            <button onclick="goAppointments()">View Appointments</button>
        </div>

        <div class="action-box">
            <h3>Patient Records</h3>
            <button onclick="goPatients()">View Patients</button>
        </div>
    </div>

</div>

<script>
    function goDoctors() {
        window.location.href = "manage-doctors.php";
    }

    function goAppointments() {
        window.location.href = "view-appointments.php";
    }

    function goPatients() {
        window.location.href = "patient-records.php"; // you can create later
    }
</script>

</body>
</html>
