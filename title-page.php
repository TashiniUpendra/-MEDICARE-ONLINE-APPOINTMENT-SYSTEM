<?php
// Title page – PHP (simple & safe)
session_start();

$projectTitle = "MediCare Online Appointment System";
$course = "Diploma in Software Engineering";
$subject = "Developing Modern Web Applications";

// Optional student name (if logged in)
$studentName = $_SESSION["name"] ?? "Your Name";
$institute = "Your Institute Name";
$date = date("Y");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $projectTitle; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
            font-family:"Poppins", sans-serif;
        }

        body{
            background:#f2f8fc;
            height:100vh;
            display:flex;
            justify-content:center;
            align-items:center;
        }

        .cover{
            background:white;
            width:80%;
            max-width:800px;
            padding:60px 40px;
            border-radius:14px;
            box-shadow:0 10px 30px rgba(0,0,0,0.12);
            text-align:center;
        }

        .logo{
            font-size:50px;
            margin-bottom:10px;
            color:#0b78a6;
        }

        h1{
            color:#0b78a6;
            font-size:32px;
            margin-bottom:15px;
        }

        h2{
            font-size:20px;
            color:#333;
            margin-bottom:10px;
        }

        p{
            font-size:16px;
            margin:6px 0;
            color:#444;
        }

        .line{
            width:120px;
            height:4px;
            background:#0b78a6;
            margin:20px auto;
            border-radius:4px;
        }

        .footer{
            margin-top:30px;
            font-size:14px;
            color:#666;
        }
    </style>
</head>
<body>

<div class="cover">

    <div class="logo">🩺</div>

    <h1><?php echo $projectTitle; ?></h1>

    <div class="line"></div>

    <h2><?php echo $subject; ?></h2>

    <p><strong>Course:</strong> <?php echo $course; ?></p>
    <p><strong>Submitted By:</strong> <?php echo htmlspecialchars($studentName); ?></p>
    <p><strong>Institute:</strong> <?php echo $institute; ?></p>

    <div class="footer">
        © <?php echo $date; ?>
    </div>

</div>

</body>
</html>
