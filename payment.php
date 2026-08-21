<?php
session_start();
include "db.php";

if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "patient") {
    header("Location: login.php");
    exit();
}

$appointment_id = $_GET['appointment_id'] ?? 0;

// Fetch Appointment Details & Fee
$stmt = $conn->prepare("SELECT a.*, ds.doctor_fee, u.name as doctor_name 
                        FROM appointments a 
                        JOIN users u ON a.doctor_id = u.id 
                        LEFT JOIN doctor_schedules ds ON a.doctor_id = ds.doctor_id 
                        WHERE a.id = ?");
$stmt->bind_param("i", $appointment_id);
$stmt->execute();
$appointment = $stmt->get_result()->fetch_assoc();

if (!$appointment) {
    die("Invalid Appointment!");
}

$doctor_fee = $appointment['doctor_fee'] ?? 1500.00; // Default Fee if schedule fee not found

// Process Payment Submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['pay_now'])) {
    $card_name = $_POST['card_name'];
    $transaction_id = "TXN" . rand(100000, 999999);
    $patient_id = $_SESSION["user_id"] ?? $_SESSION["id"];

    // 1. Insert into Payments Table
    $pay_stmt = $conn->prepare("INSERT INTO payments (appointment_id, patient_id, amount, payment_method, payment_status, transaction_id) VALUES (?, ?, ?, 'Card Payment', 'Completed', ?)");
    $pay_stmt->bind_param("iids", $appointment_id, $patient_id, $doctor_fee, $transaction_id);
    
    if ($pay_stmt->execute()) {
        // 2. Update Appointment Payment Status & Status
        $conn->query("UPDATE appointments SET payment_status = 'Completed', status = 'Confirmed' WHERE id = '$appointment_id'");

        // 3. Add Notification
        $notif_msg = "Payment of LKR " . number_format($doctor_fee, 2) . " received for Appointment #$appointment_id. Status: Confirmed!";
        $conn->query("INSERT INTO notifications (user_id, message) VALUES ('$patient_id', '$notif_msg')");

        $success = "Payment Successful! Transaction ID: " . $transaction_id;
    } else {
        $error = "Payment failed. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Checkout & Payment | MediCare</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { background: #f4f7fe; padding: 40px 20px; }
        .pay-box { max-width: 500px; margin: auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        h2 { color: #0b78a6; margin-bottom: 20px; }
        .fee-summary { background: #e0f2fe; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: 600; color: #0369a1; display: flex; justify-content: space-between; }
        .form-group { margin-bottom: 15px; }
        label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 5px; color: #475569; }
        input { width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; outline: none; }
        .btn-pay { width: 100%; background: #16a34a; color: white; border: none; padding: 12px; border-radius: 6px; font-weight: 600; font-size: 16px; cursor: pointer; }
        .btn-pay:hover { background: #15803d; }
        .alert-success { background: #dcfce7; color: #15803d; padding: 12px; border-radius: 6px; margin-bottom: 15px; text-align: center; }
        .btn-dash { display: block; text-align: center; margin-top: 15px; color: #0b78a6; text-decoration: none; font-size: 14px; }
    </style>
</head>
<body>

<div class="pay-box">
    <h2><i class="fa-solid fa-credit-card"></i> Payment Checkout</h2>

    <?php if(isset($success)): ?>
        <div class="alert-success">
            <i class="fa-solid fa-circle-check"></i> <?php echo $success; ?>
        </div>
        <a href="patient-dashboard.php" class="btn-dash"><i class="fa-solid fa-arrow-left"></i> Return to Dashboard</a>
    <?php else: ?>

        <div class="fee-summary">
            <span>Doctor Fee (Dr. <?php echo htmlspecialchars($appointment['doctor_name']); ?>):</span>
            <span>LKR <?php echo number_format($doctor_fee, 2); ?></span>
        </div>

        <form method="POST">
            <div class="form-group">
                <label>Cardholder Name</label>
                <input type="text" name="card_name" placeholder="John Doe" required>
            </div>
            <div class="form-group">
                <label>Card Number</label>
                <input type="text" maxlength="16" placeholder="4111 2222 3333 4444" required>
            </div>
            <div style="display: flex; gap: 10px;">
                <div class="form-group" style="flex:1;">
                    <label>Expiry Date</label>
                    <input type="text" placeholder="MM/YY" required>
                </div>
                <div class="form-group" style="flex:1;">
                    <label>CVV</label>
                    <input type="password" maxlength="3" placeholder="123" required>
                </div>
            </div>

            <button type="submit" name="pay_now" class="btn-pay"><i class="fa-solid fa-lock"></i> Pay LKR <?php echo number_format($doctor_fee, 2); ?></button>
        </form>

    <?php endif; ?>
</div>

</body>
</html>