<?php
session_start();

/* Admin-only access */
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
    header("Location: login.php");
    exit();
}

/* Initialize doctors list in session (demo storage) */
if (!isset($_SESSION["doctors"])) {
    $_SESSION["doctors"] = [
        ["name" => "Dr. John Silva", "specialization" => "Cardiology", "time" => "9:00 AM - 2:00 PM"],
        ["name" => "Dr. Maya Fernando", "specialization" => "Dermatology", "time" => "10:00 AM - 4:00 PM"]
    ];
}

/* Add doctor (POST) */
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["doctorName"] ?? "");
    $spec = trim($_POST["specialization"] ?? "");
    $time = trim($_POST["time"] ?? "");

    if ($name && $spec && $time) {
        $_SESSION["doctors"][] = ["name" => $name, "specialization" => $spec, "time" => $time];
    }

    header("Location: manage-doctors.php");
    exit();
}

/* Delete doctor (GET index) */
if (isset($_GET["delete"])) {
    $index = (int)$_GET["delete"];

    if (isset($_SESSION["doctors"][$index])) {
        array_splice($_SESSION["doctors"], $index, 1);
    }

    header("Location: manage-doctors.php");
    exit();
}

$doctors = $_SESSION["doctors"];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>MediCare | Manage Doctors</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Poppins", sans-serif;
        }

        body {
            background: #f2f8fc;
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

        header a {
            background: white;
            color: #0b78a6;
            border: none;
            padding: 8px 14px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            text-decoration: none;
            display: inline-block;
        }

        header a:hover {
            background: #e6f4fb;
        }

        .container {
            padding: 30px;
        }

        .form-box, .table-box {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            margin-bottom: 25px;
        }

        .form-box h2, .table-box h2 {
            color: #0b78a6;
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-top: 10px;
            font-weight: 600;
        }

        input {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border-radius: 6px;
            border: 1px solid #b7dbea;
        }

        button.add-btn {
            margin-top: 15px;
            background: #0b78a6;
            color: white;
            border: none;
            padding: 10px 16px;
            border-radius: 6px;
            cursor: pointer;
        }

        button.add-btn:hover {
            background: #095c80;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th, td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
            text-align: center;
        }

        th {
            background: #e6f4fb;
        }

        .delete-btn {
            background: #e74c3c;
            color: white;
            border: none;
            padding: 6px 10px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }

        .delete-btn:hover {
            background: #c0392b;
        }
    </style>
</head>
<body>

<header>
    <h1>Manage Doctors</h1>
    <a href="admin-dashboard.php">Back</a>
</header>

<div class="container">

    <!-- Add Doctor Form -->
    <div class="form-box">
        <h2>Add New Doctor</h2>

        <form method="POST">
            <label>Doctor Name</label>
            <input type="text" name="doctorName" required>

            <label>Specialization</label>
            <input type="text" name="specialization" required>

            <label>Available Time</label>
            <input type="text" name="time" required>

            <button type="submit" class="add-btn">Add Doctor</button>
        </form>
    </div>

    <!-- Doctor List Table -->
    <div class="table-box">
        <h2>Doctor List</h2>

        <table>
            <tr>
                <th>Name</th>
                <th>Specialization</th>
                <th>Available Time</th>
                <th>Action</th>
            </tr>

            <?php foreach ($doctors as $i => $doc): ?>
                <tr>
                    <td><?php echo htmlspecialchars($doc["name"]); ?></td>
                    <td><?php echo htmlspecialchars($doc["specialization"]); ?></td>
                    <td><?php echo htmlspecialchars($doc["time"]); ?></td>
                    <td>
                        <a class="delete-btn" href="manage-doctors.php?delete=<?php echo $i; ?>"
                           onclick="return confirm('Delete this doctor?')">
                           Delete
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>

        </table>
    </div>

</div>

</body>
</html>
