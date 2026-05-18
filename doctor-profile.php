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

/* Success message */
$success = "";

if (isset($_GET["msg"]) && $_GET["msg"] == "success") {
    $success = "Profile Updated Successfully!";
}

/* Doctor Image */
$image = "uploads/" . $user["image"];

if (empty($user["image"]) || !file_exists($image)) {
    $image = "https://cdn-icons-png.flaticon.com/512/387/387561.png";
}

/* Values */
$name = $user["name"] ?? "Doctor";
$email = $user["email"] ?? "No Email";
$specialization = $user["specialization"] ?? "";
$address = $user["address"] ?? "";
$role = strtoupper($user["role"]);
$status = "ACTIVE";
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Doctor Profile</title>

<!-- Bootstrap -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Bootstrap Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

<!-- Google Font -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>

*{
    font-family:"Poppins",sans-serif;
}

body{
    background:#eef6fb;
    margin:0;
}

/* TOP BAR */
.topbar{
    background:linear-gradient(135deg,#0b78a6,#6dd5fa);
    padding:18px 35px;
    color:white;
    display:flex;
    justify-content:space-between;
    align-items:center;
    box-shadow:0 4px 15px rgba(0,0,0,0.1);
}

.topbar h3{
    margin:0;
    font-weight:600;
}

.back-btn{
    text-decoration:none;
    background:white;
    color:#0b78a6;
    padding:10px 18px;
    border-radius:10px;
    font-weight:600;
    transition:0.3s;
}

.back-btn:hover{
    background:#dff4ff;
}

/* MAIN */
.main{
    padding:40px;
}

/* PROFILE HEADER */
.profile-header{
    background:linear-gradient(135deg,#0f172a,#1e293b);
    border-radius:20px;
    padding:40px;
    color:white;
    position:relative;
    overflow:hidden;
    margin-bottom:30px;
    box-shadow:0 10px 30px rgba(0,0,0,0.1);
}

.profile-header::after{
    content:'';
    position:absolute;
    top:-60px;
    right:-60px;
    width:260px;
    height:260px;
    background:rgba(255,255,255,0.08);
    border-radius:50%;
}

.avatar{
    width:120px;
    height:120px;
    border-radius:50%;
    object-fit:cover;
    border:4px solid rgba(255,255,255,0.4);
    box-shadow:0 8px 20px rgba(0,0,0,0.2);
}

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
    transition:0.3s;
}

.info-card:hover{
    transform:translateY(-5px);
}

.info-title{
    font-size:20px;
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

/* FORM */
.form-control{
    border-radius:12px;
    padding:12px;
}

.form-label{
    font-weight:500;
    margin-bottom:6px;
}

/* BUTTON */
.edit-btn{
    width:100%;
    padding:14px;
    border:none;
    background:linear-gradient(135deg,#0b78a6,#118ac0);
    color:white;
    border-radius:12px;
    font-weight:600;
    font-size:16px;
    transition:0.3s;
}

.edit-btn:hover{
    background:linear-gradient(135deg,#095c80,#0b78a6);
}

/* SUCCESS */
.success{
    background:#d1fae5;
    color:#065f46;
    padding:15px;
    border-radius:12px;
    margin-bottom:20px;
    font-weight:500;
}

/* ANIMATION */
.fade-in{
    animation:fadeIn 0.8s ease;
}

@keyframes fadeIn{
    from{
        opacity:0;
        transform:translateY(20px);
    }
    to{
        opacity:1;
        transform:translateY(0);
    }
}

</style>
</head>

<body>

<!-- TOPBAR -->
<div class="topbar">
    <h3><i class="bi bi-heart-pulse-fill"></i> MediCare Doctor Panel</h3>

    <a href="doctor-dashboard.php" class="back-btn">
        <i class="bi bi-arrow-left"></i> Back Dashboard
    </a>
</div>

<div class="main">

<?php if($success): ?>
<div class="success">
    <i class="bi bi-check-circle-fill"></i>
    <?php echo $success; ?>
</div>
<?php endif; ?>

<!-- PROFILE HEADER -->
<div class="profile-header fade-in">

    <div class="d-flex flex-column flex-md-row align-items-center gap-4">

        <img src="<?php echo $image; ?>" class="avatar">

        <div style="z-index:2;">
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

    <!-- LEFT -->
    <div class="col-lg-5">

        <div class="info-card fade-in">

            <div class="info-title">
                <i class="bi bi-person-lines-fill"></i>
                Doctor Information
            </div>

            <div class="info-box">
                <small>Full Name</small>
                <h6><?php echo htmlspecialchars($name); ?></h6>
            </div>

            <div class="info-box">
                <small>Email Address</small>
                <h6><?php echo htmlspecialchars($email); ?></h6>
            </div>

            <div class="info-box">
                <small>Specialization</small>
                <h6><?php echo htmlspecialchars($specialization); ?></h6>
            </div>

            <div class="info-box">
                <small>Address</small>
                <h6><?php echo htmlspecialchars($address); ?></h6>
            </div>

        </div>

    </div>

    <!-- RIGHT -->
    <div class="col-lg-7">

        <div class="info-card fade-in">

            <div class="info-title">
                <i class="bi bi-pencil-square"></i>
                Update Profile
            </div>

            <form action="update_profile.php" method="POST" enctype="multipart/form-data">

                <div class="mb-3">
                    <label class="form-label">Doctor Name</label>

                    <input type="text"
                    name="name"
                    class="form-control"
                    value="<?php echo htmlspecialchars($name); ?>"
                    required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Email</label>

                    <input type="email"
                    name="email"
                    class="form-control"
                    value="<?php echo htmlspecialchars($email); ?>"
                    required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Specialization</label>

                    <select name="specialization"
                    class="form-control"
                    required>

<option value="Cardiologist" <?php if($specialization=="Cardiologist") echo "selected"; ?>>Cardiologist</option>

<option value="Neurologist" <?php if($specialization=="Neurologist") echo "selected"; ?>>Neurologist</option>

<option value="Dermatologist" <?php if($specialization=="Dermatologist") echo "selected"; ?>>Dermatologist</option>

<option value="Pediatrician" <?php if($specialization=="Pediatrician") echo "selected"; ?>>Pediatrician</option>

<option value="Orthopedic" <?php if($specialization=="Orthopedic") echo "selected"; ?>>Orthopedic</option>

<option value="ENT Specialist" <?php if($specialization=="ENT Specialist") echo "selected"; ?>>ENT Specialist</option>

<option value="Psychiatrist" <?php if($specialization=="Psychiatrist") echo "selected"; ?>>Psychiatrist</option>

<option value="Gynecologist" <?php if($specialization=="Gynecologist") echo "selected"; ?>>Gynecologist</option>

<option value="Oncologist" <?php if($specialization=="Oncologist") echo "selected"; ?>>Oncologist</option>

<option value="General Physician" <?php if($specialization=="General Physician") echo "selected"; ?>>General Physician</option>

                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Address</label>

                    <textarea
                    name="address"
                    class="form-control"
                    rows="3"><?php echo htmlspecialchars($address); ?></textarea>
                </div>

                <div class="mb-4">
                    <label class="form-label">Change Profile Image</label>

                    <input type="file"
                    name="image"
                    class="form-control">
                </div>

                <button type="submit" class="edit-btn">
                    <i class="bi bi-save"></i>
                    Update Profile
                </button>

            </form>

        </div>

    </div>

</div>

</div>

<!-- Bootstrap -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
