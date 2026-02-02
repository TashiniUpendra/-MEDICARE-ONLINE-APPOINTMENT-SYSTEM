<?php
session_start();
include "db.php";

// Only admin
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin'){
    header("Location: login.php");
    exit();
}

// Fetch statistics
$total_users = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM users"))['total'];
$total_customers = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM users WHERE role='customer'"))['total'];
$total_employees = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM users WHERE role='employee'"))['total'];
$total_orders = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM orders"))['total'];
$total_menu = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM menu"))['total'];
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Admin Dashboard — SnapEats</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<style>
/* BODY & BACKGROUND */
body {
    font-family: 'Poppins', sans-serif;
    background: linear-gradient(135deg, #ff9800, #ff5722);
    min-height: 100vh;
}

/* NAVBAR */
.navbar-brand { font-weight:700; color:#fff !important; }
.navbar { background:#ff5722 !important; }

/* DASHBOARD HEADINGS */
h2, h4 { color:#fff; font-weight:700; }

/* STAT CARDS */
.stat-card {
    background:#ff9800;
    color:#fff;
    border-radius:15px;
    box-shadow:0 8px 25px rgba(0,0,0,0.2);
    transition:0.3s;
    padding:30px 20px;
    text-align:center;
}
.stat-card:hover { background:#ff6b00; }
.stat-card h2 { font-size:2rem; margin:10px 0 0 0; }
.stat-card h5 { font-weight:600; }

/* QUICK ACTION CARDS */
.action-card {
    background:#ff5722;
    color:#fff;
    border-radius:15px;
    box-shadow:0 8px 25px rgba(0,0,0,0.2);
    padding:30px 0;
    text-align:center;
    transition:0.3s;
}
.action-card:hover { background:#ff794d; text-decoration:none; }
.action-card i { font-size:2.5rem; }
.action-card p { margin-top:15px; font-weight:600; }

/* CONTAINER */
.container { margin-top:50px; }

/* LOGOUT BUTTON */
.btn-logout {
    background-color: #ff5722;
    color: #fff;
    font-weight: 600;
    border-radius: 8px;
    padding: 6px 16px;
    transition: 0.3s;
}
.btn-logout:hover {
    background-color: #ff6b33;
    color: #fff;
    transform: translateY(-2px);
}
</style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark px-4">
  <a class="navbar-brand" href="#">SnapEats Admin</a>
  <div class="collapse navbar-collapse">
    <ul class="navbar-nav ms-auto">
      <li class="nav-item ms-2">
        <a class="btn btn-logout" href="logout.php">Logout</a>
      </li>
    </ul>
  </div>
</nav>

<div class="container">

    <h2 class="mb-4">Dashboard</h2>

    <!-- Statistics -->
    <div class="row g-4">
        <div class="col-md-3 col-sm-6">
            <div class="stat-card">
                <h5>Total Users</h5>
                <h2><?php echo $total_users; ?></h2>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="stat-card">
                <h5>Customers</h5>
                <h2><?php echo $total_customers; ?></h2>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="stat-card">
                <h5>Employees</h5>
                <h2><?php echo $total_employees; ?></h2>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="stat-card">
                <h5>Total Orders</h5>
                <h2><?php echo $total_orders; ?></h2>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="stat-card">
                <h5>Menu Items</h5>
                <h2><?php echo $total_menu; ?></h2>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <h4 class="mt-5 mb-4">Quick Actions</h4>
    <div class="row g-4">
        <div class="col-md-3 col-sm-6">
            <a href="manage_menu.php" class="action-card d-block">
                <i class="bi bi-card-list"></i>
                <p>Manage Menu</p>
            </a>
        </div>
        <div class="col-md-3 col-sm-6">
            <a href="manage_employees.php" class="action-card d-block">
                <i class="bi bi-people"></i>
                <p>Manage Employees</p>
            </a>
        </div>
        <div class="col-md-3 col-sm-6">
            <a href="add_admin.php" class="action-card d-block">
                <i class="bi bi-person-plus"></i>
                <p>Add Admin</p>
            </a>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
