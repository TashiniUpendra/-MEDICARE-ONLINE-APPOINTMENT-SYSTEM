<?php
session_start();
include "db.php";

$error = "";
$success = "";

// Image Uploads Folder එක නැත්නම් automatic Create වෙනවා
$uploadDir = "uploads/";
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name           = trim($_POST["name"] ?? '');
    $email          = trim($_POST["email"] ?? '');
    $passwordRaw    = $_POST["password"] ?? '';
    $role           = $_POST["role"] ?? '';
    $specialization = $_POST["specialization"] ?? '';

    $imageName = "";

    /* Doctor Image Upload Logic */
    if ($role === "doctor" && !empty($_FILES["image"]["name"])) {
        $fileName = time() . "_" . basename($_FILES["image"]["name"]);
        $targetFile = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
            $imageName = $fileName;
        }
    }

    if (!empty($name) && !empty($email) && !empty($passwordRaw) && !empty($role)) {

        // Hash Password Securely
        $password = password_hash($passwordRaw, PASSWORD_DEFAULT);

        // Check if Email already exists
        $checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $checkStmt->bind_param("s", $email);
        $checkStmt->execute();
        $checkStmt->store_result();

        if ($checkStmt->num_rows > 0) {
            $error = "Email address is already registered!";
        } else {
            $checkStmt->close();

            // Insert into 'users' table
            $insertStmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
            $insertStmt->bind_param("ssss", $name, $email, $password, $role);

            if ($insertStmt->execute()) {
                
                // If Doctor, Insert details into 'doctors' table as well
                if ($role === "doctor") {
                    $docStmt = $conn->prepare("INSERT INTO doctors (name, email, specialization, image) VALUES (?, ?, ?, ?)");
                    $docStmt->bind_param("ssss", $name, $email, $specialization, $imageName);
                    $docStmt->execute();
                    $docStmt->close();
                }

                $success = "Registration successful! You can now login.";
            } else {
                $error = "Registration failed. Please try again.";
            }
            $insertStmt->close();
        }
    } else {
        $error = "Please fill all required fields!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MediCare | Register</title>
<!-- Bootstrap 5 & Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<style>
    body {
        margin: 0;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: linear-gradient(135deg, #0b78a6 0%, #6dd5fa 100%);
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 40px 15px;
        position: relative;
    }

    /* Top Home Button */
    .btn-home {
        position: absolute;
        top: 20px;
        left: 20px;
        background: rgba(255, 255, 255, 0.2);
        color: white;
        border: 1px solid rgba(255, 255, 255, 0.4);
        padding: 8px 18px;
        border-radius: 30px;
        text-decoration: none;
        font-weight: 500;
        backdrop-filter: blur(5px);
        transition: all 0.3s ease;
    }
    .btn-home:hover {
        background: white;
        color: #0b78a6;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }

    /* Card Styling */
    .register-card {
        background: #ffffff;
        padding: 35px;
        border-radius: 16px;
        width: 100%;
        max-width: 450px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
    }

    .brand-title {
        color: #0b78a6;
        font-weight: 700;
        margin-bottom: 5px;
    }

    .form-control, .form-select {
        padding: 11px 14px;
        border-radius: 8px;
        border: 1px solid #ced4da;
        font-size: 0.95rem;
    }

    .form-control:focus, .form-select:focus {
        border-color: #0b78a6;
        box-shadow: 0 0 0 0.25rem rgba(11, 120, 166, 0.25);
    }

    .btn-submit {
        width: 100%;
        padding: 12px;
        background: #0b78a6;
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        font-size: 1rem;
        transition: background 0.3s ease;
    }

    .btn-submit:hover {
        background: #085a7d;
    }

    .login-link {
        color: #0b78a6;
        text-decoration: none;
        font-weight: 600;
    }
    .login-link:hover {
        text-decoration: underline;
    }
</style>

<script>
function showDoctorFields() {
    let role = document.getElementById("role").value;
    document.getElementById("specField").style.display = (role === "doctor") ? "block" : "none";
    document.getElementById("imageField").style.display = (role === "doctor") ? "block" : "none";
}
</script>
</head>

<body>

<!-- Back to Home Button -->
<a href="home.php" class="btn-home">
    <i class="bi bi-house-door-fill me-1"></i> Back to Home
</a>

<div class="register-card">
    <div class="text-center mb-4">
        <h2 class="brand-title"><i class="bi bi-heart-pulse-fill me-2"></i>MediCare</h2>
        <p class="text-muted mb-0">Create your new account</p>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger py-2 text-center" role="alert">
            <small><i class="bi bi-exclamation-triangle-fill me-1"></i> <?php echo htmlspecialchars($error); ?></small>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success py-2 text-center" role="alert">
            <small><i class="bi bi-check-circle-fill me-1"></i> <?php echo htmlspecialchars($success); ?></small>
        </div>
    <?php endif; ?>

    <form method="POST" action="register.php" enctype="multipart/form-data">

        <div class="mb-3">
            <label class="form-label text-secondary fw-semibold">Full Name</label>
            <input type="text" name="name" class="form-control" placeholder="John Doe" required>
        </div>

        <div class="mb-3">
            <label class="form-label text-secondary fw-semibold">Email Address</label>
            <input type="email" name="email" class="form-control" placeholder="name@example.com" required>
        </div>

        <div class="mb-3">
            <label class="form-label text-secondary fw-semibold">Password</label>
            <input type="password" name="password" class="form-control" placeholder="••••••••" required>
        </div>

        <div class="mb-3">
            <label class="form-label text-secondary fw-semibold">Select Role</label>
            <select name="role" id="role" class="form-select" onchange="showDoctorFields()" required>
                <option value="">-- Select Role --</option>
                <option value="patient">Patient</option>
                <option value="doctor">Doctor</option>
                <option value="admin">Admin</option>
            </select>
        </div>

        <!-- Specialization (Visible for Doctor only) -->
        <div class="mb-3" id="specField" style="display:none;">
            <label class="form-label text-secondary fw-semibold">Specialization</label>
            <select name="specialization" class="form-select">
                <option value="">-- Select Specialization --</option>
                <option value="General Physician">General Physician</option>
                <option value="Cardiologist">Cardiologist</option>
                <option value="Dermatologist">Dermatologist</option>
                <option value="Neurologist">Neurologist</option>
                <option value="Pediatrician">Pediatrician</option>
                <option value="Orthopedic Surgeon">Orthopedic Surgeon</option>
                <option value="Gynecologist">Gynecologist & Obstetrician</option>
                <option value="Psychiatrist">Psychiatrist</option>
                <option value="ENT Specialist">ENT Specialist</option>
                <option value="Ophthalmologist">Ophthalmologist (Eye Specialist)</option>
                <option value="Radiologist">Radiologist</option>
                <option value="Oncologist">Oncologist</option>
                <option value="Urologist">Urologist</option>
                <option value="Gastroenterologist">Gastroenterologist</option>
                <option value="Dentist">Dental Surgeon / Dentist</option>
            </select>
        </div>

        <!-- Profile Image (Visible for Doctor only) -->
        <div class="mb-3" id="imageField" style="display:none;">
            <label class="form-label text-secondary fw-semibold">Profile Picture</label>
            <input type="file" name="image" class="form-control" accept="image/*">
        </div>

        <button type="submit" class="btn-submit mt-2 mb-3">
            Register Account <i class="bi bi-person-plus-fill ms-1"></i>
        </button>

    </form>

    <div class="text-center mt-2">
        <p class="mb-0 text-muted">Already have an account? <a href="login.php" class="login-link">Login Here</a></p>
    </div>

</div>

</body>
</html>
