<?php
session_start();
include "db.php";

/* Admin Check */
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
    header("Location: login.php");
    exit();
}

$error = "";
$success = "";

/* ADD DOCTOR */
if (isset($_POST["addDoctor"])) {

    $name           = trim($_POST["name"]);
    $email          = trim($_POST["email"]);
    $passwordRaw    = $_POST["password"];
    $specialization = trim($_POST["specialization"]);
    $available_time = trim($_POST["available_time"]);

    if (!empty($name) && !empty($email) && !empty($passwordRaw)) {

        // Check if email already exists
        $checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $checkStmt->bind_param("s", $email);
        $checkStmt->execute();
        $checkStmt->store_result();

        if ($checkStmt->num_rows > 0) {
            $error = "Email address is already registered!";
        } else {
            $checkStmt->close();

            // Hash Password
            $password = password_hash($passwordRaw, PASSWORD_DEFAULT);

            // 1. Insert into 'users' table
            $userStmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'doctor')");
            $userStmt->bind_param("sss", $name, $email, $password);

            if ($userStmt->execute()) {

                // 2. Insert into 'doctors' table as well
                $docStmt = $conn->prepare("INSERT INTO doctors (name, email, specialization, available_time) VALUES (?, ?, ?, ?)");
                $docStmt->bind_param("ssss", $name, $email, $specialization, $available_time);
                $docStmt->execute();
                $docStmt->close();

                $success = "Doctor added successfully!";
            } else {
                $error = "Failed to add doctor. Please try again.";
            }
            $userStmt->close();
        }
    } else {
        $error = "Please fill all required fields!";
    }
}

/* DELETE DOCTOR */
if (isset($_GET["delete"])) {
    $id = intval($_GET["delete"]);

    // Fetch email to clear from 'doctors' table as well
    $getStmt = $conn->prepare("SELECT email FROM users WHERE id = ? AND role = 'doctor'");
    $getStmt->bind_param("i", $id);
    $getStmt->execute();
    $res = $getStmt->get_result();

    if ($res->num_rows === 1) {
        $doc = $res->fetch_assoc();
        $email = $doc['email'];

        // Delete from users table
        $delUser = $conn->prepare("DELETE FROM users WHERE id = ? AND role = 'doctor'");
        $delUser->bind_param("i", $id);
        $delUser->execute();
        $delUser->close();

        // Delete from doctors table
        $delDoc = $conn->prepare("DELETE FROM doctors WHERE email = ?");
        $delDoc->bind_param("s", $email);
        $delDoc->execute();
        $delDoc->close();

        header("Location: manage-doctors.php?msg=deleted");
        exit();
    }
    $getStmt->close();
}

/* FETCH DOCTORS */
$sql = "SELECT u.id, u.name, u.email, d.specialization, d.available_time 
        FROM users u 
        LEFT JOIN doctors d ON u.email = d.email 
        WHERE u.role = 'doctor' 
        ORDER BY u.id DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MediCare | Manage Doctors</title>

<!-- FontAwesome Icons & Google Fonts -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>
* { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
body { display: flex; background-color: #f4f7fe; color: #333; min-height: 100vh; }

/* Sidebar */
.sidebar { width: 260px; background: #0b78a6; color: #fff; padding: 20px; display: flex; flex-direction: column; justify-content: space-between; }
.sidebar .brand { font-size: 22px; font-weight: 700; display: flex; align-items: center; gap: 10px; margin-bottom: 30px; }
.sidebar-menu { list-style: none; }
.sidebar-menu li { margin-bottom: 10px; }
.sidebar-menu a { display: flex; align-items: center; gap: 12px; color: #e0f2fe; text-decoration: none; padding: 12px 15px; border-radius: 8px; font-weight: 500; transition: 0.3s; }
.sidebar-menu a:hover, .sidebar-menu a.active { background: rgba(255, 255, 255, 0.2); color: #fff; }

/* Main Content */
.main-content { flex: 1; padding: 30px; overflow-y: auto; }
.header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }

/* Content Layout */
.content-grid { display: grid; grid-template-columns: 1fr 2fr; gap: 25px; }

/* Cards */
.card { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.03); }
.card h3 { color: #1e293b; font-size: 18px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }

/* Form Elements */
input, select { width: 100%; padding: 10px 12px; margin-bottom: 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px; outline: none; }
input:focus, select:focus { border-color: #0b78a6; }
button { width: 100%; padding: 11px; background: #0b78a6; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 600; transition: 0.3s; }
button:hover { background: #085a7d; }

/* Table */
table { width: 100%; border-collapse: collapse; }
th, td { padding: 12px; border-bottom: 1px solid #f1f5f9; text-align: left; font-size: 13px; }
th { background: #f8fafc; color: #64748b; font-weight: 600; }
.btn-delete { background: #fee2e2; color: #ef4444; padding: 6px 12px; border-radius: 6px; text-decoration: none; font-size: 12px; font-weight: 600; transition: 0.2s; }
.btn-delete:hover { background: #ef4444; color: white; }

.alert { padding: 10px; border-radius: 6px; font-size: 13px; margin-bottom: 15px; font-weight: 500; }
.alert-danger { background: #fee2e2; color: #991b1b; }
.alert-success { background: #dcfce7; color: #166534; }
</style>
</head>
<body>

    <!-- Sidebar Navigation -->
    <div class="sidebar">
        <div>
            <div class="brand">
                <i class="fa-solid fa-user-doctor"></i> MediCare
            </div>
            <ul class="sidebar-menu">
                <li><a href="admin-dashboard.php"><i class="fa-solid fa-chart-pie"></i> Dashboard</a></li>
                <li><a href="manage-doctors.php" class="active"><i class="fa-solid fa-user-md"></i> Manage Doctors</a></li>
                <li><a href="patient-records.php"><i class="fa-solid fa-procedures"></i> Patient Records</a></li>
                <li><a href="view-appointments.php"><i class="fa-solid fa-calendar-check"></i> Appointments</a></li>
            </ul>
        </div>
        <div>
            <a href="logout.php" style="color: #f87171; text-decoration:none;"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="header">
            <h2>Manage Doctors</h2>
        </div>

        <div class="content-grid">
            <!-- Form Card -->
            <div class="card">
                <h3><i class="fa-solid fa-user-plus" style="color:#0b78a6;"></i> Add New Doctor</h3>

                <?php if($error): ?> <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div> <?php endif; ?>
                <?php if($success): ?> <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div> <?php endif; ?>

                <form method="POST" action="manage-doctors.php">
                    <input type="text" name="name" placeholder="Doctor Full Name" required>
                    <input type="email" name="email" placeholder="Email Address" required>
                    <input type="password" name="password" placeholder="Password" required>

                    <select name="specialization" required>
                        <option value="">-- Select Specialization --</option>
                        <option value="General Physician">General Physician</option>
                        <option value="Cardiologist">Cardiologist</option>
                        <option value="Dermatologist">Dermatologist</option>
                        <option value="Neurologist">Neurologist</option>
                        <option value="Pediatrician">Pediatrician</option>
                    </select>

                    <input type="text" name="available_time" placeholder="Available Time (e.g. 09:00 AM - 02:00 PM)" required>

                    <button type="submit" name="addDoctor"><i class="fa-solid fa-plus"></i> Add Doctor</button>
                </form>
            </div>

            <!-- Table Card -->
            <div class="card">
                <h3><i class="fa-solid fa-user-nurse" style="color:#0b78a6;"></i> Doctor List</h3>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Doctor Details</th>
                            <th>Specialization</th>
                            <th>Available Time</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td>#<?php echo $row["id"]; ?></td>
                                <td>
                                    <strong>Dr. <?php echo htmlspecialchars($row["name"]); ?></strong><br>
                                    <small style="color:#64748b;"><?php echo htmlspecialchars($row["email"]); ?></small>
                                </td>
                                <td><?php echo htmlspecialchars($row["specialization"] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($row["available_time"] ?? 'N/A'); ?></td>
                                <td>
                                    <a class="btn-delete" href="?delete=<?php echo $row["id"]; ?>" onclick="return confirm('Are you sure you want to delete this doctor?')"><i class="fa-solid fa-trash"></i> Delete</a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="5" style="text-align:center;">No doctors found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</body>
</html><?php
session_start();
include "db.php";

/* Admin Check */
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
    header("Location: login.php");
    exit();
}

$error = "";
$success = "";

/* ADD DOCTOR */
if (isset($_POST["addDoctor"])) {

    $name           = trim($_POST["name"]);
    $email          = trim($_POST["email"]);
    $passwordRaw    = $_POST["password"];
    $specialization = trim($_POST["specialization"]);
    $available_time = trim($_POST["available_time"]);

    if (!empty($name) && !empty($email) && !empty($passwordRaw)) {

        // Check if email already exists
        $checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $checkStmt->bind_param("s", $email);
        $checkStmt->execute();
        $checkStmt->store_result();

        if ($checkStmt->num_rows > 0) {
            $error = "Email address is already registered!";
        } else {
            $checkStmt->close();

            // Hash Password
            $password = password_hash($passwordRaw, PASSWORD_DEFAULT);

            // 1. Insert into 'users' table
            $userStmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'doctor')");
            $userStmt->bind_param("sss", $name, $email, $password);

            if ($userStmt->execute()) {

                // 2. Insert into 'doctors' table as well
                $docStmt = $conn->prepare("INSERT INTO doctors (name, email, specialization, available_time) VALUES (?, ?, ?, ?)");
                $docStmt->bind_param("ssss", $name, $email, $specialization, $available_time);
                $docStmt->execute();
                $docStmt->close();

                $success = "Doctor added successfully!";
            } else {
                $error = "Failed to add doctor. Please try again.";
            }
            $userStmt->close();
        }
    } else {
        $error = "Please fill all required fields!";
    }
}

/* DELETE DOCTOR */
if (isset($_GET["delete"])) {
    $id = intval($_GET["delete"]);

    // Fetch email to clear from 'doctors' table as well
    $getStmt = $conn->prepare("SELECT email FROM users WHERE id = ? AND role = 'doctor'");
    $getStmt->bind_param("i", $id);
    $getStmt->execute();
    $res = $getStmt->get_result();

    if ($res->num_rows === 1) {
        $doc = $res->fetch_assoc();
        $email = $doc['email'];

        // Delete from users table
        $delUser = $conn->prepare("DELETE FROM users WHERE id = ? AND role = 'doctor'");
        $delUser->bind_param("i", $id);
        $delUser->execute();
        $delUser->close();

        // Delete from doctors table
        $delDoc = $conn->prepare("DELETE FROM doctors WHERE email = ?");
        $delDoc->bind_param("s", $email);
        $delDoc->execute();
        $delDoc->close();

        header("Location: manage-doctors.php?msg=deleted");
        exit();
    }
    $getStmt->close();
}

/* FETCH DOCTORS */
$sql = "SELECT u.id, u.name, u.email, d.specialization, d.available_time 
        FROM users u 
        LEFT JOIN doctors d ON u.email = d.email 
        WHERE u.role = 'doctor' 
        ORDER BY u.id DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MediCare | Manage Doctors</title>

<!-- FontAwesome Icons & Google Fonts -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>
* { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
body { display: flex; background-color: #f4f7fe; color: #333; min-height: 100vh; }

/* Sidebar */
.sidebar { width: 260px; background: #0b78a6; color: #fff; padding: 20px; display: flex; flex-direction: column; justify-content: space-between; }
.sidebar .brand { font-size: 22px; font-weight: 700; display: flex; align-items: center; gap: 10px; margin-bottom: 30px; }
.sidebar-menu { list-style: none; }
.sidebar-menu li { margin-bottom: 10px; }
.sidebar-menu a { display: flex; align-items: center; gap: 12px; color: #e0f2fe; text-decoration: none; padding: 12px 15px; border-radius: 8px; font-weight: 500; transition: 0.3s; }
.sidebar-menu a:hover, .sidebar-menu a.active { background: rgba(255, 255, 255, 0.2); color: #fff; }

/* Main Content */
.main-content { flex: 1; padding: 30px; overflow-y: auto; }
.header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }

/* Content Layout */
.content-grid { display: grid; grid-template-columns: 1fr 2fr; gap: 25px; }

/* Cards */
.card { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.03); }
.card h3 { color: #1e293b; font-size: 18px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }

/* Form Elements */
input, select { width: 100%; padding: 10px 12px; margin-bottom: 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px; outline: none; }
input:focus, select:focus { border-color: #0b78a6; }
button { width: 100%; padding: 11px; background: #0b78a6; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 600; transition: 0.3s; }
button:hover { background: #085a7d; }

/* Table */
table { width: 100%; border-collapse: collapse; }
th, td { padding: 12px; border-bottom: 1px solid #f1f5f9; text-align: left; font-size: 13px; }
th { background: #f8fafc; color: #64748b; font-weight: 600; }
.btn-delete { background: #fee2e2; color: #ef4444; padding: 6px 12px; border-radius: 6px; text-decoration: none; font-size: 12px; font-weight: 600; transition: 0.2s; }
.btn-delete:hover { background: #ef4444; color: white; }

.alert { padding: 10px; border-radius: 6px; font-size: 13px; margin-bottom: 15px; font-weight: 500; }
.alert-danger { background: #fee2e2; color: #991b1b; }
.alert-success { background: #dcfce7; color: #166534; }
</style>
</head>
<body>

    <!-- Sidebar Navigation -->
    <div class="sidebar">
        <div>
            <div class="brand">
                <i class="fa-solid fa-user-doctor"></i> MediCare
            </div>
            <ul class="sidebar-menu">
                <li><a href="admin-dashboard.php"><i class="fa-solid fa-chart-pie"></i> Dashboard</a></li>
                <li><a href="manage-doctors.php" class="active"><i class="fa-solid fa-user-md"></i> Manage Doctors</a></li>
                <li><a href="patient-records.php"><i class="fa-solid fa-procedures"></i> Patient Records</a></li>
                <li><a href="view-appointments.php"><i class="fa-solid fa-calendar-check"></i> Appointments</a></li>
            </ul>
        </div>
        <div>
            <a href="logout.php" style="color: #f87171; text-decoration:none;"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="header">
            <h2>Manage Doctors</h2>
        </div>

        <div class="content-grid">
            <!-- Form Card -->
            <div class="card">
                <h3><i class="fa-solid fa-user-plus" style="color:#0b78a6;"></i> Add New Doctor</h3>

                <?php if($error): ?> <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div> <?php endif; ?>
                <?php if($success): ?> <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div> <?php endif; ?>

                <form method="POST" action="manage-doctors.php">
                    <input type="text" name="name" placeholder="Doctor Full Name" required>
                    <input type="email" name="email" placeholder="Email Address" required>
                    <input type="password" name="password" placeholder="Password" required>

                    <select name="specialization" required>
                        <option value="">-- Select Specialization --</option>
                        <option value="General Physician">General Physician</option>
                        <option value="Cardiologist">Cardiologist</option>
                        <option value="Dermatologist">Dermatologist</option>
                        <option value="Neurologist">Neurologist</option>
                        <option value="Pediatrician">Pediatrician</option>
                    </select>

                    <input type="text" name="available_time" placeholder="Available Time (e.g. 09:00 AM - 02:00 PM)" required>

                    <button type="submit" name="addDoctor"><i class="fa-solid fa-plus"></i> Add Doctor</button>
                </form>
            </div>

            <!-- Table Card -->
            <div class="card">
                <h3><i class="fa-solid fa-user-nurse" style="color:#0b78a6;"></i> Doctor List</h3>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Doctor Details</th>
                            <th>Specialization</th>
                            <th>Available Time</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td>#<?php echo $row["id"]; ?></td>
                                <td>
                                    <strong>Dr. <?php echo htmlspecialchars($row["name"]); ?></strong><br>
                                    <small style="color:#64748b;"><?php echo htmlspecialchars($row["email"]); ?></small>
                                </td>
                                <td><?php echo htmlspecialchars($row["specialization"] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($row["available_time"] ?? 'N/A'); ?></td>
                                <td>
                                    <a class="btn-delete" href="?delete=<?php echo $row["id"]; ?>" onclick="return confirm('Are you sure you want to delete this doctor?')"><i class="fa-solid fa-trash"></i> Delete</a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="5" style="text-align:center;">No doctors found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</body>
</html><?php
session_start();
include "db.php";

/* Admin Check */
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
    header("Location: login.php");
    exit();
}

$error = "";
$success = "";

/* ADD DOCTOR */
if (isset($_POST["addDoctor"])) {

    $name           = trim($_POST["name"]);
    $email          = trim($_POST["email"]);
    $passwordRaw    = $_POST["password"];
    $specialization = trim($_POST["specialization"]);
    $available_time = trim($_POST["available_time"]);

    if (!empty($name) && !empty($email) && !empty($passwordRaw)) {

        // Check if email already exists
        $checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $checkStmt->bind_param("s", $email);
        $checkStmt->execute();
        $checkStmt->store_result();

        if ($checkStmt->num_rows > 0) {
            $error = "Email address is already registered!";
        } else {
            $checkStmt->close();

            // Hash Password
            $password = password_hash($passwordRaw, PASSWORD_DEFAULT);

            // 1. Insert into 'users' table
            $userStmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'doctor')");
            $userStmt->bind_param("sss", $name, $email, $password);

            if ($userStmt->execute()) {

                // 2. Insert into 'doctors' table as well
                $docStmt = $conn->prepare("INSERT INTO doctors (name, email, specialization, available_time) VALUES (?, ?, ?, ?)");
                $docStmt->bind_param("ssss", $name, $email, $specialization, $available_time);
                $docStmt->execute();
                $docStmt->close();

                $success = "Doctor added successfully!";
            } else {
                $error = "Failed to add doctor. Please try again.";
            }
            $userStmt->close();
        }
    } else {
        $error = "Please fill all required fields!";
    }
}

/* DELETE DOCTOR */
if (isset($_GET["delete"])) {
    $id = intval($_GET["delete"]);

    // Fetch email to clear from 'doctors' table as well
    $getStmt = $conn->prepare("SELECT email FROM users WHERE id = ? AND role = 'doctor'");
    $getStmt->bind_param("i", $id);
    $getStmt->execute();
    $res = $getStmt->get_result();

    if ($res->num_rows === 1) {
        $doc = $res->fetch_assoc();
        $email = $doc['email'];

        // Delete from users table
        $delUser = $conn->prepare("DELETE FROM users WHERE id = ? AND role = 'doctor'");
        $delUser->bind_param("i", $id);
        $delUser->execute();
        $delUser->close();

        // Delete from doctors table
        $delDoc = $conn->prepare("DELETE FROM doctors WHERE email = ?");
        $delDoc->bind_param("s", $email);
        $delDoc->execute();
        $delDoc->close();

        header("Location: manage-doctors.php?msg=deleted");
        exit();
    }
    $getStmt->close();
}

/* FETCH DOCTORS */
$sql = "SELECT u.id, u.name, u.email, d.specialization, d.available_time 
        FROM users u 
        LEFT JOIN doctors d ON u.email = d.email 
        WHERE u.role = 'doctor' 
        ORDER BY u.id DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MediCare | Manage Doctors</title>

<!-- FontAwesome Icons & Google Fonts -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>
* { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
body { display: flex; background-color: #f4f7fe; color: #333; min-height: 100vh; }

/* Sidebar */
.sidebar { width: 260px; background: #0b78a6; color: #fff; padding: 20px; display: flex; flex-direction: column; justify-content: space-between; }
.sidebar .brand { font-size: 22px; font-weight: 700; display: flex; align-items: center; gap: 10px; margin-bottom: 30px; }
.sidebar-menu { list-style: none; }
.sidebar-menu li { margin-bottom: 10px; }
.sidebar-menu a { display: flex; align-items: center; gap: 12px; color: #e0f2fe; text-decoration: none; padding: 12px 15px; border-radius: 8px; font-weight: 500; transition: 0.3s; }
.sidebar-menu a:hover, .sidebar-menu a.active { background: rgba(255, 255, 255, 0.2); color: #fff; }

/* Main Content */
.main-content { flex: 1; padding: 30px; overflow-y: auto; }
.header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }

/* Content Layout */
.content-grid { display: grid; grid-template-columns: 1fr 2fr; gap: 25px; }

/* Cards */
.card { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.03); }
.card h3 { color: #1e293b; font-size: 18px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }

/* Form Elements */
input, select { width: 100%; padding: 10px 12px; margin-bottom: 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px; outline: none; }
input:focus, select:focus { border-color: #0b78a6; }
button { width: 100%; padding: 11px; background: #0b78a6; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 600; transition: 0.3s; }
button:hover { background: #085a7d; }

/* Table */
table { width: 100%; border-collapse: collapse; }
th, td { padding: 12px; border-bottom: 1px solid #f1f5f9; text-align: left; font-size: 13px; }
th { background: #f8fafc; color: #64748b; font-weight: 600; }
.btn-delete { background: #fee2e2; color: #ef4444; padding: 6px 12px; border-radius: 6px; text-decoration: none; font-size: 12px; font-weight: 600; transition: 0.2s; }
.btn-delete:hover { background: #ef4444; color: white; }

.alert { padding: 10px; border-radius: 6px; font-size: 13px; margin-bottom: 15px; font-weight: 500; }
.alert-danger { background: #fee2e2; color: #991b1b; }
.alert-success { background: #dcfce7; color: #166534; }
</style>
</head>
<body>

    <!-- Sidebar Navigation -->
    <div class="sidebar">
        <div>
            <div class="brand">
                <i class="fa-solid fa-user-doctor"></i> MediCare
            </div>
            <ul class="sidebar-menu">
                <li><a href="admin-dashboard.php"><i class="fa-solid fa-chart-pie"></i> Dashboard</a></li>
                <li><a href="manage-doctors.php" class="active"><i class="fa-solid fa-user-md"></i> Manage Doctors</a></li>
                <li><a href="patient-records.php"><i class="fa-solid fa-procedures"></i> Patient Records</a></li>
                <li><a href="view-appointments.php"><i class="fa-solid fa-calendar-check"></i> Appointments</a></li>
            </ul>
        </div>
        <div>
            <a href="logout.php" style="color: #f87171; text-decoration:none;"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="header">
            <h2>Manage Doctors</h2>
        </div>

        <div class="content-grid">
            <!-- Form Card -->
            <div class="card">
                <h3><i class="fa-solid fa-user-plus" style="color:#0b78a6;"></i> Add New Doctor</h3>

                <?php if($error): ?> <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div> <?php endif; ?>
                <?php if($success): ?> <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div> <?php endif; ?>

                <form method="POST" action="manage-doctors.php">
                    <input type="text" name="name" placeholder="Doctor Full Name" required>
                    <input type="email" name="email" placeholder="Email Address" required>
                    <input type="password" name="password" placeholder="Password" required>

                    <select name="specialization" required>
                        <option value="">-- Select Specialization --</option>
                        <option value="General Physician">General Physician</option>
                        <option value="Cardiologist">Cardiologist</option>
                        <option value="Dermatologist">Dermatologist</option>
                        <option value="Neurologist">Neurologist</option>
                        <option value="Pediatrician">Pediatrician</option>
                    </select>

                    <input type="text" name="available_time" placeholder="Available Time (e.g. 09:00 AM - 02:00 PM)" required>

                    <button type="submit" name="addDoctor"><i class="fa-solid fa-plus"></i> Add Doctor</button>
                </form>
            </div>

            <!-- Table Card -->
            <div class="card">
                <h3><i class="fa-solid fa-user-nurse" style="color:#0b78a6;"></i> Doctor List</h3>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Doctor Details</th>
                            <th>Specialization</th>
                            <th>Available Time</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td>#<?php echo $row["id"]; ?></td>
                                <td>
                                    <strong>Dr. <?php echo htmlspecialchars($row["name"]); ?></strong><br>
                                    <small style="color:#64748b;"><?php echo htmlspecialchars($row["email"]); ?></small>
                                </td>
                                <td><?php echo htmlspecialchars($row["specialization"] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($row["available_time"] ?? 'N/A'); ?></td>
                                <td>
                                    <a class="btn-delete" href="?delete=<?php echo $row["id"]; ?>" onclick="return confirm('Are you sure you want to delete this doctor?')"><i class="fa-solid fa-trash"></i> Delete</a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="5" style="text-align:center;">No doctors found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</body>
</html><?php
session_start();
include "db.php";

/* Admin Check */
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
    header("Location: login.php");
    exit();
}

$error = "";
$success = "";

/* ADD DOCTOR */
if (isset($_POST["addDoctor"])) {

    $name           = trim($_POST["name"]);
    $email          = trim($_POST["email"]);
    $passwordRaw    = $_POST["password"];
    $specialization = trim($_POST["specialization"]);
    $available_time = trim($_POST["available_time"]);

    if (!empty($name) && !empty($email) && !empty($passwordRaw)) {

        // Check if email already exists
        $checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $checkStmt->bind_param("s", $email);
        $checkStmt->execute();
        $checkStmt->store_result();

        if ($checkStmt->num_rows > 0) {
            $error = "Email address is already registered!";
        } else {
            $checkStmt->close();

            // Hash Password
            $password = password_hash($passwordRaw, PASSWORD_DEFAULT);

            // 1. Insert into 'users' table
            $userStmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'doctor')");
            $userStmt->bind_param("sss", $name, $email, $password);

            if ($userStmt->execute()) {

                // 2. Insert into 'doctors' table as well
                $docStmt = $conn->prepare("INSERT INTO doctors (name, email, specialization, available_time) VALUES (?, ?, ?, ?)");
                $docStmt->bind_param("ssss", $name, $email, $specialization, $available_time);
                $docStmt->execute();
                $docStmt->close();

                $success = "Doctor added successfully!";
            } else {
                $error = "Failed to add doctor. Please try again.";
            }
            $userStmt->close();
        }
    } else {
        $error = "Please fill all required fields!";
    }
}

/* DELETE DOCTOR */
if (isset($_GET["delete"])) {
    $id = intval($_GET["delete"]);

    // Fetch email to clear from 'doctors' table as well
    $getStmt = $conn->prepare("SELECT email FROM users WHERE id = ? AND role = 'doctor'");
    $getStmt->bind_param("i", $id);
    $getStmt->execute();
    $res = $getStmt->get_result();

    if ($res->num_rows === 1) {
        $doc = $res->fetch_assoc();
        $email = $doc['email'];

        // Delete from users table
        $delUser = $conn->prepare("DELETE FROM users WHERE id = ? AND role = 'doctor'");
        $delUser->bind_param("i", $id);
        $delUser->execute();
        $delUser->close();

        // Delete from doctors table
        $delDoc = $conn->prepare("DELETE FROM doctors WHERE email = ?");
        $delDoc->bind_param("s", $email);
        $delDoc->execute();
        $delDoc->close();

        header("Location: manage-doctors.php?msg=deleted");
        exit();
    }
    $getStmt->close();
}

/* FETCH DOCTORS */
$sql = "SELECT u.id, u.name, u.email, d.specialization, d.available_time 
        FROM users u 
        LEFT JOIN doctors d ON u.email = d.email 
        WHERE u.role = 'doctor' 
        ORDER BY u.id DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MediCare | Manage Doctors</title>

<!-- FontAwesome Icons & Google Fonts -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>
* { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
body { display: flex; background-color: #f4f7fe; color: #333; min-height: 100vh; }

/* Sidebar */
.sidebar { width: 260px; background: #0b78a6; color: #fff; padding: 20px; display: flex; flex-direction: column; justify-content: space-between; }
.sidebar .brand { font-size: 22px; font-weight: 700; display: flex; align-items: center; gap: 10px; margin-bottom: 30px; }
.sidebar-menu { list-style: none; }
.sidebar-menu li { margin-bottom: 10px; }
.sidebar-menu a { display: flex; align-items: center; gap: 12px; color: #e0f2fe; text-decoration: none; padding: 12px 15px; border-radius: 8px; font-weight: 500; transition: 0.3s; }
.sidebar-menu a:hover, .sidebar-menu a.active { background: rgba(255, 255, 255, 0.2); color: #fff; }

/* Main Content */
.main-content { flex: 1; padding: 30px; overflow-y: auto; }
.header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }

/* Content Layout */
.content-grid { display: grid; grid-template-columns: 1fr 2fr; gap: 25px; }

/* Cards */
.card { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.03); }
.card h3 { color: #1e293b; font-size: 18px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }

/* Form Elements */
input, select { width: 100%; padding: 10px 12px; margin-bottom: 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px; outline: none; }
input:focus, select:focus { border-color: #0b78a6; }
button { width: 100%; padding: 11px; background: #0b78a6; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 600; transition: 0.3s; }
button:hover { background: #085a7d; }

/* Table */
table { width: 100%; border-collapse: collapse; }
th, td { padding: 12px; border-bottom: 1px solid #f1f5f9; text-align: left; font-size: 13px; }
th { background: #f8fafc; color: #64748b; font-weight: 600; }
.btn-delete { background: #fee2e2; color: #ef4444; padding: 6px 12px; border-radius: 6px; text-decoration: none; font-size: 12px; font-weight: 600; transition: 0.2s; }
.btn-delete:hover { background: #ef4444; color: white; }

.alert { padding: 10px; border-radius: 6px; font-size: 13px; margin-bottom: 15px; font-weight: 500; }
.alert-danger { background: #fee2e2; color: #991b1b; }
.alert-success { background: #dcfce7; color: #166534; }
</style>
</head>
<body>

    <!-- Sidebar Navigation -->
    <div class="sidebar">
        <div>
            <div class="brand">
                <i class="fa-solid fa-user-doctor"></i> MediCare
            </div>
            <ul class="sidebar-menu">
                <li><a href="admin-dashboard.php"><i class="fa-solid fa-chart-pie"></i> Dashboard</a></li>
                <li><a href="manage-doctors.php" class="active"><i class="fa-solid fa-user-md"></i> Manage Doctors</a></li>
                <li><a href="patient-records.php"><i class="fa-solid fa-procedures"></i> Patient Records</a></li>
                <li><a href="view-appointments.php"><i class="fa-solid fa-calendar-check"></i> Appointments</a></li>
            </ul>
        </div>
        <div>
            <a href="logout.php" style="color: #f87171; text-decoration:none;"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="header">
            <h2>Manage Doctors</h2>
        </div>

        <div class="content-grid">
            <!-- Form Card -->
            <div class="card">
                <h3><i class="fa-solid fa-user-plus" style="color:#0b78a6;"></i> Add New Doctor</h3>

                <?php if($error): ?> <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div> <?php endif; ?>
                <?php if($success): ?> <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div> <?php endif; ?>

                <form method="POST" action="manage-doctors.php">
                    <input type="text" name="name" placeholder="Doctor Full Name" required>
                    <input type="email" name="email" placeholder="Email Address" required>
                    <input type="password" name="password" placeholder="Password" required>

                    <select name="specialization" required>
                        <option value="">-- Select Specialization --</option>
                        <option value="General Physician">General Physician</option>
                        <option value="Cardiologist">Cardiologist</option>
                        <option value="Dermatologist">Dermatologist</option>
                        <option value="Neurologist">Neurologist</option>
                        <option value="Pediatrician">Pediatrician</option>
                    </select>

                    <input type="text" name="available_time" placeholder="Available Time (e.g. 09:00 AM - 02:00 PM)" required>

                    <button type="submit" name="addDoctor"><i class="fa-solid fa-plus"></i> Add Doctor</button>
                </form>
            </div>

            <!-- Table Card -->
            <div class="card">
                <h3><i class="fa-solid fa-user-nurse" style="color:#0b78a6;"></i> Doctor List</h3>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Doctor Details</th>
                            <th>Specialization</th>
                            <th>Available Time</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td>#<?php echo $row["id"]; ?></td>
                                <td>
                                    <strong>Dr. <?php echo htmlspecialchars($row["name"]); ?></strong><br>
                                    <small style="color:#64748b;"><?php echo htmlspecialchars($row["email"]); ?></small>
                                </td>
                                <td><?php echo htmlspecialchars($row["specialization"] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($row["available_time"] ?? 'N/A'); ?></td>
                                <td>
                                    <a class="btn-delete" href="?delete=<?php echo $row["id"]; ?>" onclick="return confirm('Are you sure you want to delete this doctor?')"><i class="fa-solid fa-trash"></i> Delete</a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="5" style="text-align:center;">No doctors found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</body>
</html><?php
session_start();
include "db.php";

/* Admin Check */
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
    header("Location: login.php");
    exit();
}

$error = "";
$success = "";

/* ADD DOCTOR */
if (isset($_POST["addDoctor"])) {

    $name           = trim($_POST["name"]);
    $email          = trim($_POST["email"]);
    $passwordRaw    = $_POST["password"];
    $specialization = trim($_POST["specialization"]);
    $available_time = trim($_POST["available_time"]);

    if (!empty($name) && !empty($email) && !empty($passwordRaw)) {

        // Check if email already exists
        $checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $checkStmt->bind_param("s", $email);
        $checkStmt->execute();
        $checkStmt->store_result();

        if ($checkStmt->num_rows > 0) {
            $error = "Email address is already registered!";
        } else {
            $checkStmt->close();

            // Hash Password
            $password = password_hash($passwordRaw, PASSWORD_DEFAULT);

            // 1. Insert into 'users' table
            $userStmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'doctor')");
            $userStmt->bind_param("sss", $name, $email, $password);

            if ($userStmt->execute()) {

                // 2. Insert into 'doctors' table as well
                $docStmt = $conn->prepare("INSERT INTO doctors (name, email, specialization, available_time) VALUES (?, ?, ?, ?)");
                $docStmt->bind_param("ssss", $name, $email, $specialization, $available_time);
                $docStmt->execute();
                $docStmt->close();

                $success = "Doctor added successfully!";
            } else {
                $error = "Failed to add doctor. Please try again.";
            }
            $userStmt->close();
        }
    } else {
        $error = "Please fill all required fields!";
    }
}

/* DELETE DOCTOR */
if (isset($_GET["delete"])) {
    $id = intval($_GET["delete"]);

    // Fetch email to clear from 'doctors' table as well
    $getStmt = $conn->prepare("SELECT email FROM users WHERE id = ? AND role = 'doctor'");
    $getStmt->bind_param("i", $id);
    $getStmt->execute();
    $res = $getStmt->get_result();

    if ($res->num_rows === 1) {
        $doc = $res->fetch_assoc();
        $email = $doc['email'];

        // Delete from users table
        $delUser = $conn->prepare("DELETE FROM users WHERE id = ? AND role = 'doctor'");
        $delUser->bind_param("i", $id);
        $delUser->execute();
        $delUser->close();

        // Delete from doctors table
        $delDoc = $conn->prepare("DELETE FROM doctors WHERE email = ?");
        $delDoc->bind_param("s", $email);
        $delDoc->execute();
        $delDoc->close();

        header("Location: manage-doctors.php?msg=deleted");
        exit();
    }
    $getStmt->close();
}

/* FETCH DOCTORS */
$sql = "SELECT u.id, u.name, u.email, d.specialization, d.available_time 
        FROM users u 
        LEFT JOIN doctors d ON u.email = d.email 
        WHERE u.role = 'doctor' 
        ORDER BY u.id DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MediCare | Manage Doctors</title>

<!-- FontAwesome Icons & Google Fonts -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>
* { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
body { display: flex; background-color: #f4f7fe; color: #333; min-height: 100vh; }

/* Sidebar */
.sidebar { width: 260px; background: #0b78a6; color: #fff; padding: 20px; display: flex; flex-direction: column; justify-content: space-between; }
.sidebar .brand { font-size: 22px; font-weight: 700; display: flex; align-items: center; gap: 10px; margin-bottom: 30px; }
.sidebar-menu { list-style: none; }
.sidebar-menu li { margin-bottom: 10px; }
.sidebar-menu a { display: flex; align-items: center; gap: 12px; color: #e0f2fe; text-decoration: none; padding: 12px 15px; border-radius: 8px; font-weight: 500; transition: 0.3s; }
.sidebar-menu a:hover, .sidebar-menu a.active { background: rgba(255, 255, 255, 0.2); color: #fff; }

/* Main Content */
.main-content { flex: 1; padding: 30px; overflow-y: auto; }
.header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }

/* Content Layout */
.content-grid { display: grid; grid-template-columns: 1fr 2fr; gap: 25px; }

/* Cards */
.card { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.03); }
.card h3 { color: #1e293b; font-size: 18px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }

/* Form Elements */
input, select { width: 100%; padding: 10px 12px; margin-bottom: 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px; outline: none; }
input:focus, select:focus { border-color: #0b78a6; }
button { width: 100%; padding: 11px; background: #0b78a6; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 600; transition: 0.3s; }
button:hover { background: #085a7d; }

/* Table */
table { width: 100%; border-collapse: collapse; }
th, td { padding: 12px; border-bottom: 1px solid #f1f5f9; text-align: left; font-size: 13px; }
th { background: #f8fafc; color: #64748b; font-weight: 600; }
.btn-delete { background: #fee2e2; color: #ef4444; padding: 6px 12px; border-radius: 6px; text-decoration: none; font-size: 12px; font-weight: 600; transition: 0.2s; }
.btn-delete:hover { background: #ef4444; color: white; }

.alert { padding: 10px; border-radius: 6px; font-size: 13px; margin-bottom: 15px; font-weight: 500; }
.alert-danger { background: #fee2e2; color: #991b1b; }
.alert-success { background: #dcfce7; color: #166534; }
</style>
</head>
<body>

    <!-- Sidebar Navigation -->
    <div class="sidebar">
        <div>
            <div class="brand">
                <i class="fa-solid fa-user-doctor"></i> MediCare
            </div>
            <ul class="sidebar-menu">
                <li><a href="admin-dashboard.php"><i class="fa-solid fa-chart-pie"></i> Dashboard</a></li>
                <li><a href="manage-doctors.php" class="active"><i class="fa-solid fa-user-md"></i> Manage Doctors</a></li>
                <li><a href="patient-records.php"><i class="fa-solid fa-procedures"></i> Patient Records</a></li>
                <li><a href="view-appointments.php"><i class="fa-solid fa-calendar-check"></i> Appointments</a></li>
            </ul>
        </div>
        <div>
            <a href="logout.php" style="color: #f87171; text-decoration:none;"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="header">
            <h2>Manage Doctors</h2>
        </div>

        <div class="content-grid">
            <!-- Form Card -->
            <div class="card">
                <h3><i class="fa-solid fa-user-plus" style="color:#0b78a6;"></i> Add New Doctor</h3>

                <?php if($error): ?> <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div> <?php endif; ?>
                <?php if($success): ?> <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div> <?php endif; ?>

                <form method="POST" action="manage-doctors.php">
                    <input type="text" name="name" placeholder="Doctor Full Name" required>
                    <input type="email" name="email" placeholder="Email Address" required>
                    <input type="password" name="password" placeholder="Password" required>

                    <select name="specialization" required>
                        <option value="">-- Select Specialization --</option>
                        <option value="General Physician">General Physician</option>
                        <option value="Cardiologist">Cardiologist</option>
                        <option value="Dermatologist">Dermatologist</option>
                        <option value="Neurologist">Neurologist</option>
                        <option value="Pediatrician">Pediatrician</option>
                    </select>

                    <input type="text" name="available_time" placeholder="Available Time (e.g. 09:00 AM - 02:00 PM)" required>

                    <button type="submit" name="addDoctor"><i class="fa-solid fa-plus"></i> Add Doctor</button>
                </form>
            </div>

            <!-- Table Card -->
            <div class="card">
                <h3><i class="fa-solid fa-user-nurse" style="color:#0b78a6;"></i> Doctor List</h3>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Doctor Details</th>
                            <th>Specialization</th>
                            <th>Available Time</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td>#<?php echo $row["id"]; ?></td>
                                <td>
                                    <strong>Dr. <?php echo htmlspecialchars($row["name"]); ?></strong><br>
                                    <small style="color:#64748b;"><?php echo htmlspecialchars($row["email"]); ?></small>
                                </td>
                                <td><?php echo htmlspecialchars($row["specialization"] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($row["available_time"] ?? 'N/A'); ?></td>
                                <td>
                                    <a class="btn-delete" href="?delete=<?php echo $row["id"]; ?>" onclick="return confirm('Are you sure you want to delete this doctor?')"><i class="fa-solid fa-trash"></i> Delete</a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="5" style="text-align:center;">No doctors found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</body>
</html><?php
session_start();
include "db.php";

/* Admin Check */
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
    header("Location: login.php");
    exit();
}

$error = "";
$success = "";

/* ADD DOCTOR */
if (isset($_POST["addDoctor"])) {

    $name           = trim($_POST["name"]);
    $email          = trim($_POST["email"]);
    $passwordRaw    = $_POST["password"];
    $specialization = trim($_POST["specialization"]);
    $available_time = trim($_POST["available_time"]);

    if (!empty($name) && !empty($email) && !empty($passwordRaw)) {

        // Check if email already exists
        $checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $checkStmt->bind_param("s", $email);
        $checkStmt->execute();
        $checkStmt->store_result();

        if ($checkStmt->num_rows > 0) {
            $error = "Email address is already registered!";
        } else {
            $checkStmt->close();

            // Hash Password
            $password = password_hash($passwordRaw, PASSWORD_DEFAULT);

            // 1. Insert into 'users' table
            $userStmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'doctor')");
            $userStmt->bind_param("sss", $name, $email, $password);

            if ($userStmt->execute()) {

                // 2. Insert into 'doctors' table as well
                $docStmt = $conn->prepare("INSERT INTO doctors (name, email, specialization, available_time) VALUES (?, ?, ?, ?)");
                $docStmt->bind_param("ssss", $name, $email, $specialization, $available_time);
                $docStmt->execute();
                $docStmt->close();

                $success = "Doctor added successfully!";
            } else {
                $error = "Failed to add doctor. Please try again.";
            }
            $userStmt->close();
        }
    } else {
        $error = "Please fill all required fields!";
    }
}

/* DELETE DOCTOR */
if (isset($_GET["delete"])) {
    $id = intval($_GET["delete"]);

    // Fetch email to clear from 'doctors' table as well
    $getStmt = $conn->prepare("SELECT email FROM users WHERE id = ? AND role = 'doctor'");
    $getStmt->bind_param("i", $id);
    $getStmt->execute();
    $res = $getStmt->get_result();

    if ($res->num_rows === 1) {
        $doc = $res->fetch_assoc();
        $email = $doc['email'];

        // Delete from users table
        $delUser = $conn->prepare("DELETE FROM users WHERE id = ? AND role = 'doctor'");
        $delUser->bind_param("i", $id);
        $delUser->execute();
        $delUser->close();

        // Delete from doctors table
        $delDoc = $conn->prepare("DELETE FROM doctors WHERE email = ?");
        $delDoc->bind_param("s", $email);
        $delDoc->execute();
        $delDoc->close();

        header("Location: manage-doctors.php?msg=deleted");
        exit();
    }
    $getStmt->close();
}

/* FETCH DOCTORS */
$sql = "SELECT u.id, u.name, u.email, d.specialization, d.available_time 
        FROM users u 
        LEFT JOIN doctors d ON u.email = d.email 
        WHERE u.role = 'doctor' 
        ORDER BY u.id DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MediCare | Manage Doctors</title>

<!-- FontAwesome Icons & Google Fonts -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>
* { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
body { display: flex; background-color: #f4f7fe; color: #333; min-height: 100vh; }

/* Sidebar */
.sidebar { width: 260px; background: #0b78a6; color: #fff; padding: 20px; display: flex; flex-direction: column; justify-content: space-between; }
.sidebar .brand { font-size: 22px; font-weight: 700; display: flex; align-items: center; gap: 10px; margin-bottom: 30px; }
.sidebar-menu { list-style: none; }
.sidebar-menu li { margin-bottom: 10px; }
.sidebar-menu a { display: flex; align-items: center; gap: 12px; color: #e0f2fe; text-decoration: none; padding: 12px 15px; border-radius: 8px; font-weight: 500; transition: 0.3s; }
.sidebar-menu a:hover, .sidebar-menu a.active { background: rgba(255, 255, 255, 0.2); color: #fff; }

/* Main Content */
.main-content { flex: 1; padding: 30px; overflow-y: auto; }
.header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }

/* Content Layout */
.content-grid { display: grid; grid-template-columns: 1fr 2fr; gap: 25px; }

/* Cards */
.card { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.03); }
.card h3 { color: #1e293b; font-size: 18px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }

/* Form Elements */
input, select { width: 100%; padding: 10px 12px; margin-bottom: 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px; outline: none; }
input:focus, select:focus { border-color: #0b78a6; }
button { width: 100%; padding: 11px; background: #0b78a6; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 600; transition: 0.3s; }
button:hover { background: #085a7d; }

/* Table */
table { width: 100%; border-collapse: collapse; }
th, td { padding: 12px; border-bottom: 1px solid #f1f5f9; text-align: left; font-size: 13px; }
th { background: #f8fafc; color: #64748b; font-weight: 600; }
.btn-delete { background: #fee2e2; color: #ef4444; padding: 6px 12px; border-radius: 6px; text-decoration: none; font-size: 12px; font-weight: 600; transition: 0.2s; }
.btn-delete:hover { background: #ef4444; color: white; }

.alert { padding: 10px; border-radius: 6px; font-size: 13px; margin-bottom: 15px; font-weight: 500; }
.alert-danger { background: #fee2e2; color: #991b1b; }
.alert-success { background: #dcfce7; color: #166534; }
</style>
</head>
<body>

    <!-- Sidebar Navigation -->
    <div class="sidebar">
        <div>
            <div class="brand">
                <i class="fa-solid fa-user-doctor"></i> MediCare
            </div>
            <ul class="sidebar-menu">
                <li><a href="admin-dashboard.php"><i class="fa-solid fa-chart-pie"></i> Dashboard</a></li>
                <li><a href="manage-doctors.php" class="active"><i class="fa-solid fa-user-md"></i> Manage Doctors</a></li>
                <li><a href="patient-records.php"><i class="fa-solid fa-procedures"></i> Patient Records</a></li>
                <li><a href="view-appointments.php"><i class="fa-solid fa-calendar-check"></i> Appointments</a></li>
            </ul>
        </div>
        <div>
            <a href="logout.php" style="color: #f87171; text-decoration:none;"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="header">
            <h2>Manage Doctors</h2>
        </div>

        <div class="content-grid">
            <!-- Form Card -->
            <div class="card">
                <h3><i class="fa-solid fa-user-plus" style="color:#0b78a6;"></i> Add New Doctor</h3>

                <?php if($error): ?> <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div> <?php endif; ?>
                <?php if($success): ?> <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div> <?php endif; ?>

                <form method="POST" action="manage-doctors.php">
                    <input type="text" name="name" placeholder="Doctor Full Name" required>
                    <input type="email" name="email" placeholder="Email Address" required>
                    <input type="password" name="password" placeholder="Password" required>

                    <select name="specialization" required>
                        <option value="">-- Select Specialization --</option>
                        <option value="General Physician">General Physician</option>
                        <option value="Cardiologist">Cardiologist</option>
                        <option value="Dermatologist">Dermatologist</option>
                        <option value="Neurologist">Neurologist</option>
                        <option value="Pediatrician">Pediatrician</option>
                    </select>

                    <input type="text" name="available_time" placeholder="Available Time (e.g. 09:00 AM - 02:00 PM)" required>

                    <button type="submit" name="addDoctor"><i class="fa-solid fa-plus"></i> Add Doctor</button>
                </form>
            </div>

            <!-- Table Card -->
            <div class="card">
                <h3><i class="fa-solid fa-user-nurse" style="color:#0b78a6;"></i> Doctor List</h3>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Doctor Details</th>
                            <th>Specialization</th>
                            <th>Available Time</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td>#<?php echo $row["id"]; ?></td>
                                <td>
                                    <strong>Dr. <?php echo htmlspecialchars($row["name"]); ?></strong><br>
                                    <small style="color:#64748b;"><?php echo htmlspecialchars($row["email"]); ?></small>
                                </td>
                                <td><?php echo htmlspecialchars($row["specialization"] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($row["available_time"] ?? 'N/A'); ?></td>
                                <td>
                                    <a class="btn-delete" href="?delete=<?php echo $row["id"]; ?>" onclick="return confirm('Are you sure you want to delete this doctor?')"><i class="fa-solid fa-trash"></i> Delete</a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="5" style="text-align:center;">No doctors found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</body>
</html>
