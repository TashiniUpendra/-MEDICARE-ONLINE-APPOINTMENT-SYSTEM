<?php
session_start();
include "db.php";

/* Login Check */
if (!isset($_SESSION["role"])) {
    header("Location: login.php");
    exit();
}

$user_id   = $_SESSION["id"] ?? $_SESSION["user_id"] ?? 0;
$user_role = $_SESSION["role"];

$name           = "";
$email          = "";
$phone          = "";
$specialization = "";
$available_time = "";
$experience     = "";
$image          = "";

$message  = "";
$msg_type = "";

/* Fetch Existing Data */
if ($user_role === 'doctor') {
    $stmt = $conn->prepare("SELECT * FROM doctors WHERE email = ?");
    $stmt->bind_param("s", $_SESSION['email']);
    $stmt->execute();
    $doctor = $stmt->get_result()->fetch_assoc();

    if ($doctor) {
        $name           = $doctor['name'];
        $email          = $doctor['email'];
        $phone          = $doctor['phone'] ?? '';
        $specialization = $doctor['specialization'] ?? '';
        $available_time = $doctor['available_time'] ?? '';
        $experience     = $doctor['experience'] ?? '';
        $image          = $doctor['image'] ?? '';
    }
} else {
    $stmt = $conn->prepare("SELECT name, email, phone FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $patient = $stmt->get_result()->fetch_assoc();
    
    $name  = $patient['name'] ?? '';
    $email = $patient['email'] ?? '';
    $phone = $patient['phone'] ?? '';
}

/* Handle Updates */
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["update_profile"])) {
    $up_name  = trim($_POST["name"]);
    $up_email = trim($_POST["email"]);
    $up_phone = trim($_POST["phone"]);

    if ($user_role === 'doctor') {
        $up_spec = trim($_POST["specialization"] ?? '');
        $up_time = trim($_POST["available_time"] ?? '');
        $up_exp  = trim($_POST["experience"] ?? '');
        
        $image_name = $image;

        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === 0) {
            $img_file = $_FILES['profile_image']['name'];
            $tmp_name = $_FILES['profile_image']['tmp_name'];
            
            $image_name = time() . "_" . preg_replace("/[^a-zA-Z0-9.]/", "_", $img_file);
            $upload_path = "uploads/" . $image_name;

            if (!is_dir('uploads')) {
                mkdir('uploads', 0777, true);
            }
            move_uploaded_file($tmp_name, $upload_path);
        }

        $update_stmt = $conn->prepare("UPDATE doctors SET name = ?, email = ?, phone = ?, specialization = ?, available_time = ?, experience = ?, image = ? WHERE email = ?");
        $update_stmt->bind_param("ssssssss", $up_name, $up_email, $up_phone, $up_spec, $up_time, $up_exp, $image_name, $_SESSION['email']);
        
        if ($update_stmt->execute()) {
            $u_stmt = $conn->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
            $u_stmt->bind_param("ssi", $up_name, $up_email, $user_id);
            $u_stmt->execute();

            $_SESSION['email'] = $up_email;
            $name = $up_name; $email = $up_email; $phone = $up_phone;
            $specialization = $up_spec; $available_time = $up_time; $experience = $up_exp; $image = $image_name;

            $message = "Profile details updated successfully!";
            $msg_type = "success";
        } else {
            $message = "Failed to update profile details.";
            $msg_type = "danger";
        }
    } else {
        $u_stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, phone = ? WHERE id = ?");
        $u_stmt->bind_param("sssi", $up_name, $up_email, $up_phone, $user_id);
        if ($u_stmt->execute()) {
            $name = $up_name; $email = $up_email; $phone = $up_phone;
            $message = "Profile updated successfully!";
            $msg_type = "success";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediCare | Profile Settings</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        * { font-family: 'Plus Jakarta Sans', sans-serif; box-sizing: border-box; }
        body { background-color: #f8fafc; margin: 0; min-height: 100vh; }

        .wrapper { display: flex; min-height: 100vh; }

        .sidebar {
            width: 270px;
            background: linear-gradient(180deg, #0284c7 0%, #0369a1 100%);
            color: #ffffff;
            display: flex;
            flex-direction: column;
            padding: 32px 24px;
            flex-shrink: 0;
            box-shadow: 4px 0 24px rgba(0,0,0,0.02);
        }

        .brand-title { font-size: 26px; font-weight: 800; margin-bottom: 40px; display: flex; align-items: center; gap: 10px; }
        .nav-menu { display: flex; flex-direction: column; gap: 10px; flex-grow: 1; }

        .nav-item-link {
            display: flex; align-items: center; gap: 14px;
            color: rgba(255, 255, 255, 0.82); text-decoration: none;
            padding: 13px 18px; border-radius: 12px; font-weight: 600; font-size: 14px;
            transition: all 0.2s ease;
        }
        .nav-item-link:hover { background: rgba(255, 255, 255, 0.15); color: #fff; }
        .nav-item-link.active { background: rgba(255, 255, 255, 0.22); color: #fff; box-shadow: 0 4px 12px rgba(0,0,0,0.08); }

        .logout-link { margin-top: auto; color: #fff; }

        .main-content { flex-grow: 1; padding: 40px 50px; overflow-y: auto; }
        .page-header { margin-bottom: 30px; }
        .page-title { font-size: 28px; font-weight: 800; color: #0f172a; margin: 0; }
        .page-subtitle { font-size: 14px; color: #64748b; font-weight: 500; margin-top: 4px; }

        .profile-card {
            background: #ffffff;
            border-radius: 20px;
            padding: 32px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 10px 30px rgba(0,0,0,0.02);
        }

        .avatar-preview-container {
            position: relative;
            width: 110px;
            height: 110px;
            margin-right: 25px;
        }

        .profile-img {
            width: 110px; height: 110px;
            border-radius: 50%; object-fit: cover;
            border: 4px solid #e0f2fe;
            box-shadow: 0 4px 14px rgba(0,0,0,0.08);
        }

        .avatar-box {
            width: 110px; height: 110px;
            background: #e0f2fe; color: #0284c7;
            border-radius: 50%; display: flex;
            align-items: center; justify-content: center;
            font-size: 40px; font-weight: 800;
            border: 4px solid #f0f9ff;
        }

        .upload-badge {
            position: absolute;
            bottom: 2px; right: 2px;
            background: #0284c7; color: white;
            width: 32px; height: 32px;
            border-radius: 50%; display: flex;
            align-items: center; justify-content: center;
            cursor: pointer; border: 2px solid white;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
            transition: 0.2s;
        }
        .upload-badge:hover { background: #0369a1; transform: scale(1.05); }

        .user-meta-info h3 { font-size: 22px; font-weight: 800; color: #0f172a; margin: 0 0 6px 0; }
        .role-pill {
            display: inline-flex; align-items: center; gap: 6px;
            background: #e0f2fe; color: #0284c7; font-weight: 700;
            font-size: 12px; padding: 5px 14px; border-radius: 20px;
        }

        .section-header {
            font-size: 15px; font-weight: 700; color: #0f172a;
            display: flex; align-items: center; gap: 8px;
            margin-bottom: 20px; padding-bottom: 10px; border-bottom: 1px dashed #e2e8f0;
        }

        .form-label { font-size: 13px; font-weight: 700; color: #475569; margin-bottom: 8px; }
        .input-group-custom {
            position: relative;
            display: flex;
            align-items: center;
        }
        .input-group-custom i {
            position: absolute; left: 16px; color: #94a3b8; font-size: 16px;
        }
        .input-group-custom .form-control {
            padding-left: 44px;
        }
        .form-control {
            border-radius: 12px; padding: 12px 16px;
            border: 1px solid #cbd5e1; font-weight: 500; font-size: 14px;
            background-color: #f8fafc; transition: all 0.2s;
        }
        .form-control:focus {
            background-color: #ffffff;
            border-color: #0284c7;
            box-shadow: 0 0 0 4px rgba(2, 132, 199, 0.12);
        }

        .btn-theme {
            background-color: #0284c7; color: #ffffff;
            font-weight: 700; padding: 12px 32px;
            border-radius: 12px; border: none; transition: 0.2s;
            box-shadow: 0 4px 12px rgba(2, 132, 199, 0.25);
        }
        .btn-theme:hover { background-color: #0369a1; color: #fff; transform: translateY(-1px); }
    </style>
</head>

<body>

<div class="wrapper">
    <div class="sidebar">
        <div class="brand-title">
            <i class="bi bi-hospital-fill"></i> MediCare
        </div>
        <div class="nav-menu">
            <a href="<?php echo ($user_role === 'doctor') ? 'doctor-dashboard.php' : 'patient-dashboard.php'; ?>" class="nav-item-link">
                <i class="bi bi-grid-1x2-fill"></i> Dashboard
            </a>
            <?php if ($user_role === 'patient'): ?>
                <a href="book-appointment.php" class="nav-item-link">
                    <i class="bi bi-calendar-plus"></i> Book Appointment
                </a>
            <?php endif; ?>
            <a href="<?php echo ($user_role === 'doctor') ? 'appointments.php' : 'my-appointments.php'; ?>" class="nav-item-link">
                <i class="bi bi-calendar2-check-fill"></i> Appointments
            </a>
            <a href="profile.php" class="nav-item-link active">
                <i class="bi bi-person-badge-fill"></i> Profile
            </a>
        </div>
        <a href="logout.php" class="nav-item-link logout-link">
            <i class="bi bi-box-arrow-left"></i> Logout
        </a>
    </div>

    <div class="main-content">
        <div class="page-header">
            <h1 class="page-title">Account Settings</h1>
            <p class="page-subtitle">Manage your personal profile details and doctor preferences.</p>
        </div>

        <div class="profile-card">
            <?php if (!empty($message)): ?>
                <div class="alert alert-<?php echo $msg_type; ?> alert-dismissible fade show rounded-3 mb-4" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i><?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <form action="profile.php" method="POST" enctype="multipart/form-data">
                
                <div class="d-flex align-items-center mb-4 pb-3 border-bottom">
                    <div class="avatar-preview-container">
                        <?php if (!empty($image) && file_exists("uploads/" . $image)): ?>
                            <img src="uploads/<?php echo htmlspecialchars($image); ?>" id="avatar-preview" class="profile-img" alt="Profile Image">
                        <?php else: ?>
                            <div class="avatar-box" id="avatar-fallback">
                                <?php echo strtoupper(substr($name ?: 'D', 0, 1)); ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($user_role === 'doctor'): ?>
                            <label for="profile_image_input" class="upload-badge" title="Change Image">
                                <i class="bi bi-camera-fill"></i>
                            </label>
                            <input type="file" id="profile_image_input" name="profile_image" accept="image/*" class="d-none" onchange="previewImage(this)">
                        <?php endif; ?>
                    </div>

                    <div class="user-meta-info">
                        <h3><?php echo htmlspecialchars($name); ?></h3>
                        <div class="role-pill">
                            <i class="bi bi-patch-check-fill"></i> <?php echo ucfirst(htmlspecialchars($user_role)); ?> Account
                        </div>
                    </div>
                </div>

                <div class="section-header">
                    <i class="bi bi-person-lines-fill text-primary"></i> Personal Details
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label">Full Name</label>
                        <div class="input-group-custom">
                            <i class="bi bi-person"></i>
                            <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($name); ?>" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email Address</label>
                        <div class="input-group-custom">
                            <i class="bi bi-envelope"></i>
                            <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($email); ?>" required>
                        </div>
                    </div>
                </div>

                <div class="section-header">
                    <i class="bi bi-briefcase-fill text-primary"></i> Professional Details
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label">Phone Number</label>
                        <div class="input-group-custom">
                            <i class="bi bi-telephone"></i>
                            <input type="text" name="phone" class="form-control" placeholder="e.g. 0771234567" value="<?php echo htmlspecialchars($phone); ?>">
                        </div>
                    </div>
                    
                    <?php if ($user_role === 'doctor'): ?>
                        <div class="col-md-6">
                            <label class="form-label">Specialization</label>
                            <div class="input-group-custom">
                                <i class="bi bi-heart-pulse"></i>
                                <input type="text" name="specialization" class="form-control" placeholder="e.g. Neurologist" value="<?php echo htmlspecialchars($specialization); ?>">
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if ($user_role === 'doctor'): ?>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Available Time</label>
                            <div class="input-group-custom">
                                <i class="bi bi-clock"></i>
                                <input type="text" name="available_time" class="form-control" placeholder="e.g. 09:00 AM - 05:00 PM" value="<?php echo htmlspecialchars($available_time); ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Experience</label>
                            <div class="input-group-custom">
                                <i class="bi bi-award"></i>
                                <input type="text" name="experience" class="form-control" placeholder="e.g. 5 Years" value="<?php echo htmlspecialchars($experience); ?>">
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="text-end pt-3">
                    <button type="submit" name="update_profile" class="btn-theme">
                        <i class="bi bi-floppy-fill me-2"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function previewImage(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                var img = document.getElementById('avatar-preview');
                var fallback = document.getElementById('avatar-fallback');
                if (img) {
                    img.src = e.target.result;
                } else if(fallback) {
                    fallback.outerHTML = '<img src="' + e.target.result + '" id="avatar-preview" class="profile-img" alt="Profile Image">';
                }
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
</body>
</html>
