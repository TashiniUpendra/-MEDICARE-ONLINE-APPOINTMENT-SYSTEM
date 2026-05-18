<?php
session_start();
include "db.php";

/* Doctor only */
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "doctor") {
    header("Location: login.php");
    exit();
}

$email = $_SESSION["email"] ?? "";

/* Get doctor details */
$sql = "SELECT * FROM users WHERE email='$email'";
$result = mysqli_query($conn, $sql);
$user = mysqli_fetch_assoc($result);

/* Safe fallback */
if (!$user) {
    die("Doctor not found!");
}

/* Doctor Image */
$image = "uploads/" . $user["image"];

if (empty($user["image"]) || !file_exists($image)) {
    $image = "https://cdn-icons-png.flaticon.com/512/387/387561.png";
}

/* Values */
$name = $user["name"] ?? "Doctor";
$email = $user["email"] ?? "No Email";
$specialization = $user["specialization"] ?? "Not Added";
$address = $user["address"] ?? "Not Added";
$phone = $user["phone"] ?? "Not Added";
$gender = $user["gender"] ?? "Not Added";
$experience = $user["experience"] ?? "0";
$hospital = $user["hospital"] ?? "Not Added";
$qualification = $user["qualification"] ?? "Not Added";
$role = strtoupper($user["role"]);
$status = "ACTIVE";
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Doctor Profile</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>

*{
    font-family:"Poppins",sans-serif;
}

body{
    background:#eef6fb;
}

/* TOPBAR */
.topbar{
    background:linear-gradient(135deg,#0b78a6,#6dd5fa);
    padding:18px 35px;
    color:white;
    display:flex;
    justify-content:space-between;
    align-items:center;
}

.back-btn{
    text-decoration:none;
    background:white;
    color:#0b78a6;
    padding:10px 18px;
    border-radius:10px;
    font-weight:600;
}

.main{
    padding:40px;
}

/* HEADER */
.profile-header{
    background:linear-gradient(135deg,#0f172a,#1e293b);
    border-radius:20px;
    padding:40px;
    color:white;
    margin-bottom:30px;
}

.avatar{
    width:130px;
    height:130px;
    border-radius:50%;
    object-fit:cover;
    border:4px solid rgba(255,255,255,0.4);
}

/* BADGES */
.badge-custom{
    display:inline-block;
    padding:8px 16px;
    border-radius:30px;
    margin-top:10px;
    margin-right:8px;
    font-size:14px;
    font-weight:500;
}

.badge-role{
    background:#2563eb;
}

.badge-status{
    background:#10b981;
}

/* CARDS */
.info-card{
    background:white;
    border-radius:18px;
    padding:30px;
    box-shadow:0 4px 15px rgba(0,0,0,0.08);
    height:100%;
}

.info-title{
    font-size:22px;
    font-weight:600;
    margin-bottom:25px;
    color:#0b78a6;
}

.info-box{
    background:#f8fbff;
    padding:18px;
    border-radius:14px;
    margin-bottom:18px;
}

.info-box small{
    display:block;
    color:#777;
    margin-bottom:5px;
}

.info-box h6{
    margin:0;
    font-weight:600;
    color:#222;
}

</style>
</head>

<body>

<!-- TOPBAR -->
<div class="topbar">

    <h3>
        <i class="bi bi-heart-pulse-fill"></i>
        MediCare Doctor Panel
    </h3>

    <a href="doctor-dashboard.php" class="back-btn">
        <i class="bi bi-arrow-left"></i>
        Back Dashboard
    </a>

</div>

<div class="main">

<!-- PROFILE HEADER -->
<div class="profile-header">

    <div class="d-flex flex-column flex-md-row align-items-center gap-4">

        <img src="<?php echo $image; ?>" class="avatar">

        <div>
            <h2 class="fw-bold mb-1">
                Dr. <?php echo htmlspecialchars($name); ?>
            </h2>

            <p style="color:#dbeafe;font-size:17px;">
                <i class="bi bi-envelope-fill"></i>
                <?php echo htmlspecialchars($email); ?>
            </p>

            <span class="badge-custom badge-role">
                <i class="bi bi-person-badge"></i>
                <?php echo htmlspecialchars($role); ?>
            </span>

            <span class="badge-custom badge-status">
                <i class="bi bi-check-circle-fill"></i>
                <?php echo htmlspecialchars($status); ?>
            </span>
        </div>

    </div>

</div>

<!-- CONTENT -->
<div class="row g-4">

    <!-- LEFT SIDE -->
    <div class="col-lg-6">

        <div class="info-card">

            <div class="info-title">
                <i class="bi bi-person-lines-fill"></i>
                Doctor Information
            </div>

            <div class="info-box">
                <small>Doctor Name</small>
                <h6><?php echo htmlspecialchars($name); ?></h6>
            </div>

            <div class="info-box">
                <small>Email Address</small>
                <h6><?php echo htmlspecialchars($email); ?></h6>
            </div>

            <div class="info-box">
                <small>Phone Number</small>
                <h6><?php echo htmlspecialchars($phone); ?></h6>
            </div>

            <div class="info-box">
                <small>Gender</small>
                <h6><?php echo htmlspecialchars($gender); ?></h6>
            </div>

            <div class="info-box">
                <small>Address</small>
                <h6><?php echo htmlspecialchars($address); ?></h6>
            </div>

        </div>

    </div>

    <!-- RIGHT SIDE -->
    <div class="col-lg-6">

        <div class="info-card">

            <div class="info-title">
                <i class="bi bi-hospital"></i>
                Professional Details
            </div>

            <div class="info-box">
                <small>Specialization</small>
                <h6><?php echo htmlspecialchars($specialization); ?></h6>
            </div>

            <div class="info-box">
                <small>Qualification</small>
                <h6><?php echo htmlspecialchars($qualification); ?></h6>
            </div>

            <div class="info-box">
                <small>Experience</small>
                <h6><?php echo htmlspecialchars($experience); ?> Years</h6>
            </div>

            <div class="info-box">
                <small>Hospital / Clinic</small>
                <h6><?php echo htmlspecialchars($hospital); ?></h6>
            </div>

            <div class="info-box">
                <small>Profile Status</small>
                <h6 style="color:#10b981;">
                    ACTIVE
                </h6>
            </div>

        </div>

    </div>

</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
