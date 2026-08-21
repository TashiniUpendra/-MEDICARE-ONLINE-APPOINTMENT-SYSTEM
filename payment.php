<?php
session_start();
include "db.php";

if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "patient") {
    header("Location: login.php");
    exit();
}

$appointment_id = $_GET['appointment_id'] ?? 0;

// Fetch Appointment Details & Fee
$stmt = $conn->prepare("SELECT a.*, ds.doctor_fee, u.name as doctor_name, u.specialization 
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

$doctor_fee = $appointment['doctor_fee'] ?? 1500.00;

// Process Payment Submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['pay_now'])) {
    $card_name = $_POST['card_name'];
    $transaction_id = "TXN" . rand(100000, 999999);
    $patient_id = $_SESSION["user_id"] ?? $_SESSION["id"];

    // 1. Insert into Payments
    $pay_stmt = $conn->prepare("INSERT INTO payments (appointment_id, patient_id, amount, payment_method, payment_status, transaction_id) VALUES (?, ?, ?, 'Card Payment', 'Completed', ?)");
    $pay_stmt->bind_param("iids", $appointment_id, $patient_id, $doctor_fee, $transaction_id);
    
    if ($pay_stmt->execute()) {
        // 2. Update Appointment Payment Status & Status
        $conn->query("UPDATE appointments SET payment_status = 'Completed', status = 'Confirmed' WHERE id = '$appointment_id'");

        // 3. Add Notification
        $notif_msg = "Payment of LKR " . number_format($doctor_fee, 2) . " received for Appointment #$appointment_id. Status: Confirmed!";
        $conn->query("INSERT INTO notifications (user_id, message) VALUES ('$patient_id', '$notif_msg')");

        $success = $transaction_id;
    } else {
        $error = "Payment failed. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout & Payment | MediCare</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { background: #f0f4f9; display: flex; justify-content: center; align-items: center; min-height: 100vh; padding: 20px; }
        
        .checkout-card { background: white; width: 100%; max-width: 480px; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.06); padding: 32px; border: 1px solid #e2e8f0; }
        
        .card-header { text-align: center; margin-bottom: 25px; }
        .card-header .icon-wrap { width: 60px; height: 60px; background: #e0f2fe; color: #0b78a6; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 24px; margin: 0 auto 12px; }
        .card-header h2 { color: #1e293b; font-size: 22px; font-weight: 700; }
        .card-header p { color: #64748b; font-size: 13px; margin-top: 4px; }

        /* Receipt Box */
        .summary-box { background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 12px; padding: 18px; margin-bottom: 24px; }
        .summary-row { display: flex; justify-content: space-between; font-size: 13px; color: #475569; margin-bottom: 8px; }
        .summary-row:last-child { margin-bottom: 0; padding-top: 8px; border-top: 1px solid #e2e8f0; font-weight: 700; color: #0f172a; font-size: 15px; }
        
        /* Form Inputs */
        .form-group { margin-bottom: 16px; }
        label { display: block; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; color: #475569; margin-bottom: 6px; }
        .input-wrapper { position: relative; }
        .input-wrapper i { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 15px; }
        .input-wrapper input { width: 100%; padding: 12px 14px 12px 42px; border: 1px solid #cbd5e1; border-radius: 8px; outline: none; font-size: 14px; transition: 0.2s; }
        .input-wrapper input:focus { border-color: #0b78a6; box-shadow: 0 0 0 3px rgba(11, 120, 166, 0.12); }
        
        .card-brands { display: flex; gap: 8px; margin-top: 6px; justify-content: flex-end; color: #64748b; font-size: 20px; }
        
        .btn-pay { width: 100%; background: #0b78a6; color: white; border: none; padding: 14px; border-radius: 8px; font-weight: 600; font-size: 15px; cursor: pointer; transition: 0.3s; display: flex; align-items: center; justify-content: center; gap: 8px; }
        .btn-pay:hover { background: #085a7d; }

        /* Success State */
        .success-box { text-align: center; padding: 10px 0; }
        .success-icon { width: 70px; height: 70px; background: #dcfce7; color: #16a34a; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 32px; margin: 0 auto 16px; }
        .success-box h3 { color: #15803d; font-size: 20px; font-weight: 700; margin-bottom: 6px; }
        .txn-badge { display: inline-block; background: #f1f5f9; border: 1px solid #cbd5e1; padding: 6px 14px; border-radius: 20px; font-family: monospace; font-size: 13px; color: #334155; margin: 12px 0 24px; font-weight: 600; }
        .btn-secondary { display: block; background: #e2e8f0; color: #334155; text-decoration: none; padding: 12px; border-radius: 8px; font-weight: 600; font-size: 14px; transition: 0.2s; }
        .btn-secondary:hover { background: #cbd5e1; }
    </style>
</head>
<body>

<div class="checkout-card">

    <?php if(isset($success)): ?>
        
        <!-- Payment Success View -->
        <div class="success-box">
            <div class="success-icon"><i class="fa-solid fa-check"></i></div>
            <h3>Payment Successful!</h3>
            <p style="font-size: 13px; color: #64748b;">Your appointment has been confirmed.</p>
            <div class="txn-badge">Transaction ID: <?php echo $success; ?></div>
            <a href="patient-dashboard.php" class="btn-secondary"><i class="fa-solid fa-house"></i> Return to Dashboard</a>
        </div>

    <?php else: ?>

        <!-- Payment Form View -->
        <div class="card-header">
            <div class="icon-wrap"><i class="fa-solid fa-shield-halved"></i></div>
            <h2>Checkout & Payment</h2>
            <p>Complete your booking payment securely</p>
        </div>

        <div class="summary-box">
            <div class="summary-row">
                <span>Doctor Name:</span>
                <strong>Dr. <?php echo htmlspecialchars($appointment['doctor_name']); ?></strong>
            </div>
            <div class="summary-row">
                <span>Appointment Ref:</span>
                <span>#<?php echo $appointment_id; ?></span>
            </div>
            <div class="summary-row">
                <span>Total Amount:</span>
                <span>LKR <?php echo number_format($doctor_fee, 2); ?></span>
            </div>
        </div>

        <form method="POST">
            <div class="form-group">
                <label>Cardholder Name</label>
                <div class="input-wrapper">
                    <i class="fa-solid fa-user"></i>
                    <input type="text" name="card_name" placeholder="John Doe" required>
                </div>
            </div>

            <div class="form-group">
                <label>Card Number</label>
                <div class="input-wrapper">
                    <i class="fa-solid fa-credit-card"></i>
                    <input type="text" maxlength="16" placeholder="4111 2222 3333 4444" required>
                </div>
                <div class="card-brands">
                    <i class="fa-brands fa-cc-visa"></i>
                    <i class="fa-brands fa-cc-mastercard"></i>
                </div>
            </div>

            <div style="display: flex; gap: 12px;">
                <div class="form-group" style="flex:1;">
                    <label>Expiry Date</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-calendar"></i>
                        <input type="text" placeholder="MM/YY" maxlength="5" required>
                    </div>
                </div>
                <div class="form-group" style="flex:1;">
                    <label>CVV / CVC</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-lock"></i>
                        <input type="password" maxlength="3" placeholder="123" required>
                    </div>
                </div>
            </div>

            <button type="submit" name="pay_now" class="btn-pay">
                <i class="fa-solid fa-lock"></i> Pay LKR <?php echo number_format($doctor_fee, 2); ?>
            </button>
        </form>

    <?php endif; ?>

</div>

</body>
</html>
