<?php
session_start();
include "db.php";

/* Login check */
if (!isset($_SESSION["role"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["id"] ?? 0;
$role    = $_SESSION["role"] ?? "patient";

/* Update Details */
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $new_name  = trim($_POST["name"]);
    $new_email = trim($_POST["email"]);

    if (!empty($new_name) && !empty($new_email)) {
        /* Database Update */
        $stmt = $conn->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
        $stmt->bind_param("ssi", $new_name, $new_email, $user_id);

        if ($stmt->execute()) {
            // Update Session Variables
            $_SESSION["name"]  = $new_name;
            $_SESSION["email"] = $new_email;
            
            $msg = "Profile updated successfully!";
            $msg_type = "success";
        } else {
            $msg = "Failed to update profile. Email might already exist.";
            $msg_type = "error";
        }
    } else {
        $msg = "Please fill in all fields.";
        $msg_type = "error";
    }
}

/* Safe display values */
$name  = $_SESSION["name"] ?? "No Name";
$email = $_SESSION["email"] ?? "No Email";
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MediCare | My Profile</title>

<!-- FontAwesome Icons & Google Fonts -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>
* { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
body { display: flex; background-color: #f4f7fe; color: #333; min-height: 100vh; }

/* Sidebar Navigation */
.sidebar { width: 260px; background: #0b78a6; color: #fff; padding: 20px; display: flex; flex-direction: column; justify-content: space-between; }
.sidebar .brand { font-size: 22px; font-weight: 700; display: flex; align-items: center; gap: 10px; margin-bottom: 30px; }
.sidebar-menu { list-style: none; }
.sidebar-menu li { margin-bottom: 10px; }
.sidebar-menu a { display: flex; align-items: center; gap: 12px; color: #e0f2fe; text-decoration: none; padding: 12px 15px; border-radius: 8px; font-weight: 500; transition: 0.3s; }
.sidebar-menu a:hover, .sidebar-menu a.active { background: rgba(255, 255, 255, 0.2); color: #fff; }

/* Main Content Area */
.main-content { flex: 1; padding: 30px; overflow-y: auto; }
.header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }

/* Profile Card Box */
.profile-card { background: white; max-width: 600px; padding: 30px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.03); }

/* Avatar Section */
.profile-header { display: flex; align-items: center; gap: 20px; margin-bottom: 25px; padding-bottom: 20px; border-bottom: 1px solid #f1f5f9; }
.avatar-circle { width: 80px; height: 80px; background: #e0f2fe; color: #0b78a6; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 32px; }
.user-title h3 { font-size: 20px; color: #1e293b; }
.user-title p { font-size: 13px; color: #64748b; text-transform: capitalize; }

/* Form Elements */
.form-group { margin-bottom: 18px; }
.form-group label { display: block; font-size: 13px; font-weight: 500; color: #475569; margin-bottom: 6px; }
.form-control { width: 100%; padding: 12px 15px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; outline: none; transition: 0.3s; }
.form-control:focus { border-color: #0b78a6; box-shadow: 0 0 0 3px rgba(11, 120, 166, 0.1); }
.form-control[disabled] { background-color: #f8fafc; color: #94a3b8; cursor: not-allowed; }

/* Alert Messages */
.alert { padding: 12px 15px; border-radius: 8px; font-size: 13px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
.alert-success { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
.alert-error { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }

/* Submit Button */
.btn-submit { background: #0b78a6; color: white; border: none; padding: 12px 20px; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; transition: 0.3s; width: 100%; display: flex; justify-content: center; align-items: center; gap: 8px; }
.btn-submit:hover { background: #085a7d; }
</style>
</head>
<body>

    <!-- Sidebar Navigation -->
    <div class="sidebar">
        <div>
            <div class="brand">
                <i class="fa-solid fa-heart-pulse"></i> MediCare
            </div>
            <ul class="sidebar-menu">
                <li><a href="patient-dashboard.php"><i class="fa-solid fa-chart-pie"></i> Dashboard</a></li>
                <li><a href="doctor-list.php"><i class="fa-solid fa-user-doctor"></i> Book Appointment</a></li>
                <li><a href="appointment-history.php"><i class="fa-solid fa-calendar-check"></i> My Appointments</a></li>
                <li><a href="profile.php" class="active"><i class="fa-solid fa-user-gear"></i> Profile</a></li>
            </ul>
        </div>
        <div>
            <a href="logout.php" style="color: #f87171; text-decoration:none;"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        
        <div class="header">
            <h2>Account Settings</h2>
        </div>

        <div class="profile-card">
            
            <div class="profile-header">
                <div class="avatar-circle">
                    <i class="fa-solid fa-user"></i>
                </div>
                <div class="user-title">
                    <h3><?php echo htmlspecialchars($name); ?></h3>
                    <p><i class="fa-solid fa-shield-halved"></i> <?php echo htmlspecialchars($role); ?></p>
                </div>
            </div>

            <?php if(isset($msg)): ?>
                <div class="alert alert-<?php echo $msg_type; ?>">
                    <i class="fa-solid <?php echo $msg_type === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation'; ?>"></i>
                    <?php echo $msg; ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                
                <div class="form-group">
                    <label><i class="fa-solid fa-user-pen"></i> Full Name</label>
                    <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($name); ?>" required>
                </div>

                <div class="form-group">
                    <label><i class="fa-solid fa-envelope"></i> Email Address</label>
                    <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($email); ?>" required>
                </div>

                <div class="form-group">
                    <label><i class="fa-solid fa-user-tag"></i> Role</label>
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($role); ?>" disabled>
                </div>

                <button type="submit" class="btn-submit">
                    <i class="fa-solid fa-floppy-disk"></i> Save Changes
                </button>

            </form>

        </div>

    </div>

</body>
</html>
