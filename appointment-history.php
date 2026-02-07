<?php
session_start();

/* Patient-only access */
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "patient") {
    header("Location: login.php");
    exit();
}

/*
  DEMO appointment history
  Later you can replace this with database data (MySQL)
*/
$appointments = [
    [
        "doctor" => "Dr. John Silva",
        "specialty" => "Cardiologist",
        "date" => "2025-01-10",
        "time" => "10:30 AM",
        "status" => "Completed"
    ],
    [
        "doctor" => "Dr. Maya Fernando",
        "specialty" => "Dermatologist",
        "date" => "2025-01-18",
        "time" => "2:00 PM",
        "status" => "Pending"
    ],
    [
        "doctor" => "Dr. Ruwan Perera",
        "specialty" => "Neurologist",
        "date" => "2024-12-22",
        "time" => "11:00 AM",
        "status" => "Cancelled"
    ]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>MediCare | Appointment History</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
            font-family: "Poppins", sans-serif;
        }

        body{
            background:#f0faff;
        }

        header{
            background:#0b78a6;
            color:white;
            padding:18px;
            text-align:center;
            font-size:22px;
            font-weight:bold;
        }

        .container{
            width:90%;
            margin:40px auto;
            background:white;
            padding:25px;
            border-radius:12px;
            box-shadow:0 4px 18px rgba(0,0,0,0.12);
        }

        h2{
            color:#0b78a6;
            margin-bottom:20px;
            text-align:center;
        }

        table{
            width:100%;
            border-collapse:collapse;
        }

        th, td{
            padding:12px;
            text-align:center;
            border-bottom:1px solid #d9e9f2;
        }

        th{
            background:#0b78a6;
            color:white;
        }

        tr:hover{
            background:#f2fbff;
        }

        .status{
            padding:6px 10px;
            border-radius:6px;
            color:white;
            font-size:14px;
        }

        .completed{
            background:#28a745;
        }

        .pending{
            background:#ffc107;
            color:#000;
        }

        .cancelled{
            background:#dc3545;
        }
    </style>
</head>
<body>

<header>MediCare | Appointment History</header>

<div class="container">
    <h2>Your Appointments</h2>

    <table>
        <thead>
            <tr>
                <th>Doctor</th>
                <th>Specialty</th>
                <th>Date</th>
                <th>Time</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($appointments as $a): 
            $statusClass = strtolower($a["status"]);
        ?>
            <tr>
                <td><?php echo htmlspecialchars($a["doctor"]); ?></td>
                <td><?php echo htmlspecialchars($a["specialty"]); ?></td>
                <td><?php echo htmlspecialchars($a["date"]); ?></td>
                <td><?php echo htmlspecialchars($a["time"]); ?></td>
                <td>
                    <span class="status <?php echo $statusClass; ?>">
                        <?php echo htmlspecialchars($a["status"]); ?>
                    </span>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

</body>
</html>
