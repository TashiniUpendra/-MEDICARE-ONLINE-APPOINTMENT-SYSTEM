<?php
session_start();
include "db.php";

/* Admin check */
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
    header("Location: login.php");
    exit();
}

$msg = "";

/* DELETE PATIENT */
if (isset($_GET["delete"])) {
    $id = intval($_GET["delete"]);
    
    $delStmt = $conn->prepare("DELETE FROM users WHERE id = ? AND role = 'patient'");
    $delStmt->bind_param("i", $id);
    if ($delStmt->execute()) {
        $msg = "Patient record deleted successfully!";
    }
    $delStmt->close();
}

/* SEARCH & FETCH PATIENTS */
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

if (!empty($search)) {
    $stmt = $conn->prepare("SELECT * FROM users WHERE role='patient' AND (name LIKE ? OR email LIKE ?) ORDER BY id DESC");
    $searchTerm = "%" . $search . "%";
    $stmt->bind_param("ss", $searchTerm, $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query("SELECT * FROM users WHERE role='patient' ORDER BY id DESC");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MediCare | Patient Records</title>

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

/* Card */
.card { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.03); }

/* Search Bar */
.toolbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
.search-box { display: flex; gap: 10px; }
.search-box input { padding: 8px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px; outline: none; width: 250px; }
.search-box button { padding: 8px 15px; background: #0b78a6; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 13px; }

/* Table */
table { width: 100%; border-collapse: collapse; }
th, td { padding: 12px; border-bottom: 1px solid #f1f5f9; text-align: left; font-size: 13px; }
th { background: #f8fafc; color: #64748b; font-weight: 600; }
.btn-delete { background: #fee2e2; color: #ef4444; padding: 6px 12px; border-radius: 6px; text-decoration: none; font-size: 12px; font-weight: 600; transition: 0.2s; }
.btn-delete:hover { background: #ef4444; color: white; }

.alert { padding: 10px; border-radius: 6px; font-size: 13px; margin-bottom: 15px; background: #dcfce7; color: #166534; font-weight: 500; }
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
                <li><a href="manage-doctors.php"><i class="fa-solid fa-user-md"></i> Manage Doctors</a></li>
                <li><a href="patient-records.php" class="active"><i class="fa-solid fa-procedures"></i> Patient Records</a></li>
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
            <h2>Patient Records</h2>
        </div>

        <div class="card">
            <?php if(!empty($msg)): ?>
                <div class="alert"><?php echo $msg; ?></div>
            <?php endif; ?>

            <div class="toolbar">
                <h3><i class="fa-solid fa-users" style="color:#0b78a6;"></i> Registered Patients</h3>
                <form class="search-box" method="GET" action="patient-records.php">
                    <input type="text" name="search" placeholder="Search by name or email..." value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit"><i class="fa-solid fa-search"></i> Search</button>
                </form>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Patient Name</th>
                        <th>Email Address</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td>#<?php echo $row["id"]; ?></td>
                            <td><strong><?php echo htmlspecialchars($row["name"]); ?></strong></td>
                            <td><?php echo htmlspecialchars($row["email"]); ?></td>
                            <td>
                                <a class="btn-delete" href="patient-records.php?delete=<?php echo $row["id"]; ?>" onclick="return confirm('Are you sure you want to delete this patient record?')"><i class="fa-solid fa-trash"></i> Delete</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="4" style="text-align:center;">No patients found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html><?php
session_start();
include "db.php";

/* Admin check */
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
    header("Location: login.php");
    exit();
}

$msg = "";

/* DELETE PATIENT */
if (isset($_GET["delete"])) {
    $id = intval($_GET["delete"]);
    
    $delStmt = $conn->prepare("DELETE FROM users WHERE id = ? AND role = 'patient'");
    $delStmt->bind_param("i", $id);
    if ($delStmt->execute()) {
        $msg = "Patient record deleted successfully!";
    }
    $delStmt->close();
}

/* SEARCH & FETCH PATIENTS */
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

if (!empty($search)) {
    $stmt = $conn->prepare("SELECT * FROM users WHERE role='patient' AND (name LIKE ? OR email LIKE ?) ORDER BY id DESC");
    $searchTerm = "%" . $search . "%";
    $stmt->bind_param("ss", $searchTerm, $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query("SELECT * FROM users WHERE role='patient' ORDER BY id DESC");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MediCare | Patient Records</title>

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

/* Card */
.card { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.03); }

/* Search Bar */
.toolbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
.search-box { display: flex; gap: 10px; }
.search-box input { padding: 8px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px; outline: none; width: 250px; }
.search-box button { padding: 8px 15px; background: #0b78a6; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 13px; }

/* Table */
table { width: 100%; border-collapse: collapse; }
th, td { padding: 12px; border-bottom: 1px solid #f1f5f9; text-align: left; font-size: 13px; }
th { background: #f8fafc; color: #64748b; font-weight: 600; }
.btn-delete { background: #fee2e2; color: #ef4444; padding: 6px 12px; border-radius: 6px; text-decoration: none; font-size: 12px; font-weight: 600; transition: 0.2s; }
.btn-delete:hover { background: #ef4444; color: white; }

.alert { padding: 10px; border-radius: 6px; font-size: 13px; margin-bottom: 15px; background: #dcfce7; color: #166534; font-weight: 500; }
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
                <li><a href="manage-doctors.php"><i class="fa-solid fa-user-md"></i> Manage Doctors</a></li>
                <li><a href="patient-records.php" class="active"><i class="fa-solid fa-procedures"></i> Patient Records</a></li>
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
            <h2>Patient Records</h2>
        </div>

        <div class="card">
            <?php if(!empty($msg)): ?>
                <div class="alert"><?php echo $msg; ?></div>
            <?php endif; ?>

            <div class="toolbar">
                <h3><i class="fa-solid fa-users" style="color:#0b78a6;"></i> Registered Patients</h3>
                <form class="search-box" method="GET" action="patient-records.php">
                    <input type="text" name="search" placeholder="Search by name or email..." value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit"><i class="fa-solid fa-search"></i> Search</button>
                </form>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Patient Name</th>
                        <th>Email Address</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td>#<?php echo $row["id"]; ?></td>
                            <td><strong><?php echo htmlspecialchars($row["name"]); ?></strong></td>
                            <td><?php echo htmlspecialchars($row["email"]); ?></td>
                            <td>
                                <a class="btn-delete" href="patient-records.php?delete=<?php echo $row["id"]; ?>" onclick="return confirm('Are you sure you want to delete this patient record?')"><i class="fa-solid fa-trash"></i> Delete</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="4" style="text-align:center;">No patients found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>
