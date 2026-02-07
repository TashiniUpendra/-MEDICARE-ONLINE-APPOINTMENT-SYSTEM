<?php
session_start();

/*
  OPTIONAL ACCESS CONTROL
  Uncomment this block if you want ONLY logged-in patients to see doctors
*/
/*
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "patient") {
    header("Location: login.php");
    exit();
}
*/

/* Demo doctor data (later replace with database) */
$doctors = [
    [
        "name" => "Dr. John Silva",
        "specialty" => "Cardiologist",
        "experience" => "12 Years Experience",
        "image" => "https://via.placeholder.com/110"
    ],
    [
        "name" => "Dr. Maya Fernando",
        "specialty" => "Dermatologist",
        "experience" => "8 Years Experience",
        "image" => "https://via.placeholder.com/110"
    ],
    [
        "name" => "Dr. Ruwan Perera",
        "specialty" => "Neurologist",
        "experience" => "15 Years Experience",
        "image" => "https://via.placeholder.com/110"
    ],
    [
        "name" => "Dr. Nadeesha Karun",
        "specialty" => "Pediatrician",
        "experience" => "10 Years Experience",
        "image" => "https://via.placeholder.com/110"
    ]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediCare | Doctor List</title>

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
            margin: 40px auto;
        }

        .doctor-card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 18px rgba(0,0,0,0.12);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .doctor-card img {
            width: 110px;
            height: 110px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #0b78a6;
        }

        .doctor-info {
            flex: 1;
        }

        .doctor-info h3 {
            font-size: 22px;
            color: #0b78a6;
        }

        .doctor-info p {
            margin-top: 6px;
            color: #444;
        }

        .btn {
            background: #0b78a6;
            color: white;
            padding: 10px 14px;
            border-radius: 6px;
            font-size: 15px;
            text-decoration: none;
        }

        .btn:hover {
            background: #095c80;
        }
    </style>
</head>
<body>

<header>MediCare | Doctor List</header>

<div class="container">

    <?php foreach ($doctors as $doc): ?>
        <div class="doctor-card">
            <img src="<?php echo htmlspecialchars($doc["image"]); ?>" alt="Doctor Image">

            <div class="doctor-info">
                <h3><?php echo htmlspecialchars($doc["name"]); ?></h3>
                <p><strong>Specialty:</strong> <?php echo htmlspecialchars($doc["specialty"]); ?></p>
                <p><strong>Experience:</strong> <?php echo htmlspecialchars($doc["experience"]); ?></p>
            </div>

            <a href="appointment-booking.php" class="btn">Book Appointment</a>
        </div>
    <?php endforeach; ?>

</div>

</body>
</html>
