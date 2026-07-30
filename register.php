<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include "db.php";

$error = "";
$success = "";

$uploadDir = "uploads/";
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name  = trim($_POST["name"] ?? '');
    $email = trim($_POST["email"] ?? '');
    $password = password_hash($_POST["password"] ?? '', PASSWORD_DEFAULT);
    $role  = $_POST["role"] ?? '';
    $specialization = $_POST["specialization"] ?? '';

    $imageName = "";

    /* Doctor image upload */
    if ($role === "doctor" && !empty($_FILES["image"]["name"])) {
        $fileName = time() . "_" . basename($_FILES["image"]["name"]);
        $targetFile = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
            $imageName = $fileName;
        }
    }

    if ($name && $email && $_POST["password"] && $role) {

        // Use Prepared Statement to check email existence
        $checkStmt = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ?");
        mysqli_stmt_bind_param($checkStmt, "s", $email);
        mysqli_stmt_execute($checkStmt);
        mysqli_stmt_store_result($checkStmt);

        if (mysqli_stmt_num_rows($checkStmt) > 0) {
            $error = "Email already exists!";
        } else {
            mysqli_stmt_close($checkStmt);

            // Use Prepared Statement to insert secure data
            $insertStmt = mysqli_prepare(
                $conn, 
                "INSERT INTO users (name, email, password, role, image, specialization) VALUES (?, ?, ?, ?, ?, ?)"
            );
            mysqli_stmt_bind_param($insertStmt, "ssssss", $name, $email, $password, $role, $imageName, $specialization);

            if (mysqli_stmt_execute($insertStmt)) {
                $success = "Registration successful!";
            } else {
                $error = "Error: " . mysqli_stmt_error($insertStmt);
            }
            mysqli_stmt_close($insertStmt);
        }

    } else {
        $error = "Please fill in all required fields!";
    }
}
?>
