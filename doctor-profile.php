<?php
session_start();
include "db.php";

/* Doctor session check */
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "doctor") {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["id"] ?? $_SESSION["user_id"] ?? 0;
$email   = $_SESSION["email"] ?? "";
$msg     = "";

/* Get Doctor Details with Doctor Fee */
$stmt = $conn->prepare("SELECT u.*, ds.doctor_fee 
                        FROM users u 
                        LEFT JOIN doctor_schedules ds ON u.id = ds.doctor_id 
                        WHERE u.id = ? AND u.role = 'doctor'");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    die("<div style='text-align:center; padding:50px; font-family:sans-serif;'>
            <h2>Doctor details not found!</h2>
            <p>Please logout and log in again.</p>
            <a href='login.php' style='padding:10px 20px; background:#0284c7; color:white; text-decoration:none; border-radius:5px;'>Go to Login</a>
         </div>");
}

$user_id = $user['id'];

/* Handle Profile Update Form */
if (isset($_POST['update_profile'])) {
    $phone          = trim($_POST['phone']);
    $specialization = trim($_POST['specialization']);
    $qualification  = trim($_POST['qualification']);
    $experience     = intval($_POST['experience']);
    $hospital       = trim($_POST['hospital']);
    $address        = trim($_POST['address']);
    $doctor_fee     = floatval($_POST['doctor_fee']);
    
    $image_name = $user['image']; // Default existing image

    /* Profile Image Upload Processing */
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $file_ext = strtolower(pathinfo($_FILES["profile_image"]["name"], PATHINFO_EXTENSION));
        $allowed = array("jpg", "jpeg", "png", "webp");

        if (in_array($file_ext, $allowed)) {
            $new_filename = "doc_" . $user_id . "_" . time() . "." . $file_ext;
            $target_file = $target_dir . $new_filename;

            if (move_uploaded_file($_FILES["profile_image"]["tmp_name"], $target_file)) {
                $image_name = $new_filename;
            }
        }
    }

    /* Update Users Table */
    $update_sql = "UPDATE users SET phone=?, specialization=?, qualification=?, experience=?, hospital=?, address=?, image=? WHERE id=?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("sssisssi", $phone, $specialization, $qualification, $experience, $hospital, $address, $image_name, $user_id);
    
    if ($update_stmt->execute()) {
        /* Update or Insert Doctor Fee into doctor_schedules */
        $check_fee = $conn->query("SELECT * FROM doctor_schedules WHERE doctor_id = '$user_id'");
        if ($check_fee->num_rows > 0) {
            $conn->query("UPDATE doctor_schedules SET doctor_fee = '$doctor_fee' WHERE doctor_id = '$user_id'");
        } else {
            $conn->query("INSERT INTO doctor_schedules (doctor_id, doctor_fee) VALUES ('$user_id', '$doctor_fee')");
        }

        $msg = "Profile updated successfully!";
        
        /* Refresh Local Data */
        $user['phone']          = $phone;
        $user['specialization'] = $specialization;
        $user['qualification']  = $qualification;
        $user['experience']     = $experience;
        $user['hospital']       = $hospital;
        $user['address']        = $address;
        $user['image']          = $image_name;
        $user['doctor_fee']     = $doctor_fee;
    } else {
        $msg = "Failed to update profile.";
    }
}

/* Doctor Image Path Setup */
$image = (!empty($user["image"]) && file_exists("uploads/" . $user["image"])) 
            ? "uploads/" . $user["image"] 
            : "https://cdn-icons-png.flaticon.com/512/387/387561.png";

$name           = $user["name"] ?? "Doctor";
$email          = $user["email"] ?? "No Email";
$specialization = $user["specialization"] ?? "Not Specified";
$address        = $user["address"] ?? "Not Added";
$phone          = $user["phone"] ?? "Not Added";
$gender         = $user["gender"] ?? "Not Specified";
$experience     = $user["experience"] ?? "0";
$hospital       = $user["hospital"] ?? "Not Specified";
$qualification  = $user["qualification"] ?? "Not Specified";
$doctor_fee     = $user["doctor_fee"] ?? "1500.00";
$role           = strtoupper($user["role"]);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MediCare | Doctor Profile</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>
* { font-family: 'Plus Jakarta Sans', sans-serif; }
body { background: #f8fafc; color: #1e293b; min-height: 100vh; }

.topbar {
    background: #0f172a;
    padding: 16px 35px;
    color: white;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 3px solid #0284c7;
}

.back-btn {
    text-decoration: none;
    background: rgba(255,255,255,0.1);
    color: white;
    padding: 8px 18px;
    border-radius: 10px;
    font-weight: 600;
    font-size: 14px;
    transition: 0.3s;
}
.back-btn:hover { background: #0284c7; color: white; }

.main { padding: 35px 40px; }

.profile-header {
    background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
    border-radius: 20px;
    padding: 35px;
    color: white;
    margin-bottom: 30px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.05);
}

.avatar {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid #0284c7;
}

.badge-custom {
    display: inline-block;
    padding: 6px 14px;
    border-radius: 30px;
    margin-top: 10px;
    margin-right: 8px;
    font-size: 13px;
    font-weight: 600;
}
.badge-role { background: #0284c7; }
.badge-status { background: #16a34a; }

.info-card {
    background: white;
    border-radius: 18px;
    padding: 28px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.02);
    border: 1px solid #f1f5f9;
    height: 100%;
}

.info-title {
    font-size: 18px;
    font-weight: 700;
    margin-bottom: 20px;
    color: #0f172a;
    display: flex;
    align-items: center;
    gap: 10px;
}

.info-box {
    background: #f8fafc;
    padding: 14px 18px;
    border-radius: 12px;
    margin-bottom: 14px;
    border: 1px solid #f1f5f9;
}

.info-box small { display: block; color: #64748b; font-size: 12px; font-weight: 500; margin-bottom: 3px; }
.info-box h6 { margin: 0; font-weight: 600; color: #0f172a; font-size: 15px; }

.btn-edit {
    background: #0284c7;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 10px;
    font-weight: 600;
    font-size: 14px;
    transition: 0.3s;
}
.btn-edit:hover { background: #0369a1; color: white; }
</style>
</head>

<body>

<div class="topbar">
    <h4 class="m-0 fw-bold">
        <i class="bi bi-heart-pulse-fill text-info me-2"></i>MediCare Doctor Panel
    </h4>
    <a href="doctor-dashboard.php" class="back-btn">
        <i class="bi bi-arrow-left me-1"></i> Dashboard
    </a>
</div>

<div class="main">

    <?php if(!empty($msg)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> <?php echo $msg; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="profile-header">
        <div class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-4">
            <div class="d-flex flex-column flex-md-row align-items-center gap-4">
                <img src="<?php echo $image; ?>" class="avatar" alt="Doctor Photo">
                <div class="text-center text-md-start">
                    <h2 class="fw-bold mb-1">Dr. <?php echo htmlspecialchars($name); ?></h2>
                    <p style="color:#94a3b8; font-size:15px;" class="mb-2">
                        <i class="bi bi-envelope-fill me-1"></i> <?php echo htmlspecialchars($email); ?>
                    </p>
                    <span class="badge-custom badge-role">
                        <i class="bi bi-person-badge me-1"></i> <?php echo htmlspecialchars($role); ?>
                    </span>
                    <span class="badge-custom badge-status">
                        <i class="bi bi-check-circle-fill me-1"></i> ACTIVE
                    </span>
                </div>
            </div>
            
            <button class="btn btn-edit" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                <i class="bi bi-pencil-square me-1"></i> Edit Profile
            </button>
        </div>
    </div>

    <div class="row g-4">
        <!-- Personal Info -->
        <div class="col-lg-6">
            <div class="info-card">
                <div class="info-title">
                    <i class="bi bi-person-lines-fill text-primary"></i> Personal Details
                </div>
                <div class="info-box">
                    <small>Full Name</small>
                    <h6>Dr. <?php echo htmlspecialchars($name); ?></h6>
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

        <!-- Professional Info -->
        <div class="col-lg-6">
            <div class="info-card">
                <div class="info-title">
                    <i class="bi bi-hospital text-primary"></i> Professional Details
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
                    <small>Consultation Fee</small>
                    <h6 class="text-primary fw-bold">LKR <?php echo number_format((float)$doctor_fee, 2); ?></h6>
                </div>
                <div class="info-box">
                    <small>Experience</small>
                    <h6><?php echo htmlspecialchars($experience); ?> Years</h6>
                </div>
                <div class="info-box">
                    <small>Hospital / Clinic</small>
                    <h6><?php echo htmlspecialchars($hospital); ?></h6>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- EDIT PROFILE MODAL -->
<div class="modal fade" id="editProfileModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg" style="border-radius:18px;">
      <div class="modal-header" style="background:#0f172a; color:white; border-radius:18px 18px 0 0;">
        <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square me-2"></i>Edit Doctor Profile</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      
      <!-- Added enctype for image uploads -->
      <form action="doctor-profile.php" method="POST" enctype="multipart/form-data">
        <div class="modal-body p-4">
            
            <div class="mb-3">
                <label class="form-label text-muted fw-semibold">Profile Picture</label>
                <input type="file" name="profile_image" class="form-control" accept="image/*">
            </div>
            
            <div class="mb-3">
                <label class="form-label text-muted fw-semibold">Phone Number</label>
                <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($phone); ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label text-muted fw-semibold">Specialization</label>
                <input type="text" name="specialization" class="form-control" value="<?php echo htmlspecialchars($specialization); ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label text-muted fw-semibold">Qualification</label>
                <input type="text" name="qualification" class="form-control" value="<?php echo htmlspecialchars($qualification); ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label text-muted fw-semibold">Consultation Fee (LKR)</label>
                <input type="number" step="0.01" name="doctor_fee" class="form-control" value="<?php echo htmlspecialchars($doctor_fee); ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label text-muted fw-semibold">Experience (Years)</label>
                <input type="number" name="experience" class="form-control" value="<?php echo htmlspecialchars($experience); ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label text-muted fw-semibold">Hospital / Clinic</label>
                <input type="text" name="hospital" class="form-control" value="<?php echo htmlspecialchars($hospital); ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label text-muted fw-semibold">Address</label>
                <textarea name="address" class="form-control" rows="2"><?php echo htmlspecialchars($address); ?></textarea>
            </div>
        </div>
        <div class="modal-footer bg-light" style="border-radius:0 0 18px 18px;">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" name="update_profile" class="btn btn-primary" style="background:#0284c7; border:none;">Save Changes</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
