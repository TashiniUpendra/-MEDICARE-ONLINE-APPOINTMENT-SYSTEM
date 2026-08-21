<?php
session_start();
include "db.php";

if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "patient") {
    header("Location: login.php");
    exit();
}

$appointment_id = $_GET['appointment_id'] ?? 0;

// Fetch Appointment Details & Correct Doctor Name
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

$doctor_fee = $appointment['doctor_fee'] ?? 1500.00;
$is_success = false;

// Process Payment
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $pay_method = $_POST['payment_method'] ?? 'Card';
    $patient_id = $_SESSION["user_id"] ?? $_SESSION["id"];

    if ($pay_method === 'Cash') {
        // Cash Payment - Status is still Pending for Doctor approval
        $conn->query("UPDATE appointments SET payment_status = 'Pay at Hospital', status = 'Pending' WHERE id = '$appointment_id'");
        $is_success = true;
        $msg = "Cash payment option selected. Status remains Pending until Doctor confirms.";
    } else {
        // Card Payment - Payment status Completed, but Appointment status remains Pending
        $transaction_id = "TXN" . rand(100000, 999999);
        $pay_stmt = $conn->prepare("INSERT INTO payments (appointment_id, patient_id, amount, payment_method, payment_status, transaction_id) VALUES (?, ?, ?, 'Card Payment', 'Completed', ?)");
        $pay_stmt->bind_param("iids", $appointment_id, $patient_id, $doctor_fee, $transaction_id);
        
        if ($pay_stmt->execute()) {
            // ONLY UPDATE payment_status to Paid. Status remains 'Pending'
            $conn->query("UPDATE appointments SET payment_status = 'Completed', status = 'Pending' WHERE id = '$appointment_id'");
            $is_success = true;
            $msg = "Payment successful! Waiting for doctor confirmation.";
        }
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { background: #f0f4f9; display: flex; justify-content: center; align-items: center; min-height: 100vh; padding: 20px; }
        .checkout-card { background: white; width: 100%; max-width: 500px; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.06); padding: 32px; border: 1px solid #e2e8f0; }
        .card-header { text-align: center; margin-bottom: 20px; }
        .card-header .icon-wrap { width: 55px; height: 55px; background: #e0f2fe; color: #0b78a6; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 22px; margin: 0 auto 10px; }
        .card-header h2 { color: #1e293b; font-size: 20px; font-weight: 700; }
        
        .summary-box { background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 12px; padding: 16px; margin-bottom: 20px; }
        .summary-row { display: flex; justify-content: space-between; font-size: 13px; color: #475569; margin-bottom: 8px; }
        .summary-row:last-child { margin-bottom: 0; padding-top: 8px; border-top: 1px solid #e2e8f0; font-weight: 700; color: #0f172a; font-size: 15px; }

        .method-tabs { display: flex; gap: 10px; margin-bottom: 20px; }
        .tab-btn { flex: 1; padding: 10px; border: 1px solid #cbd5e1; background: #fff; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; text-align: center; color: #64748b; }
        .tab-btn.active { border-color: #0b78a6; background: #e0f2fe; color: #0b78a6; }

        .form-group { margin-bottom: 14px; }
        label { display: block; font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 6px; }
        .input-wrapper input { width: 100%; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 8px; outline: none; font-size: 14px; }
        
        .btn-pay { width: 100%; background: #0b78a6; color: white; border: none; padding: 12px; border-radius: 8px; font-weight: 600; font-size: 15px; cursor: pointer; margin-top: 10px; }
        .btn-pay:hover { background: #085a7d; }
    </style>
</head>
<body>

<div class="checkout-card">
    <div class="card-header">
        <div class="icon-wrap"><i class="fa-solid fa-credit-card"></i></div>
        <h2>Payment Details</h2>
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

    <!-- Select Payment Method -->
    <div class="method-tabs">
        <div class="tab-btn active" id="cardTab" onclick="switchMethod('Card')"><i class="fa-solid fa-credit-card"></i> Pay Online</div>
        <div class="tab-btn" id="cashTab" onclick="switchMethod('Cash')"><i class="fa-solid fa-money-bill-wave"></i> Pay at Hospital</div>
    </div>

    <form method="POST" id="paymentForm">
        <input type="hidden" name="payment_method" id="payment_method" value="Card">

        <!-- Card Section -->
        <div id="cardSection">
            <div class="form-group">
                <label>Cardholder Name</label>
                <div class="input-wrapper"><input type="text" name="card_name" placeholder="John Doe"></div>
            </div>
            <div class="form-group">
                <label>Card Number</label>
                <div class="input-wrapper"><input type="text" maxlength="16" placeholder="4111 2222 3333 4444"></div>
            </div>
            <div style="display: flex; gap: 10px;">
                <div class="form-group" style="flex:1;">
                    <label>Expiry Date</label>
                    <div class="input-wrapper"><input type="text" placeholder="MM/YY"></div>
                </div>
                <div class="form-group" style="flex:1;">
                    <label>CVV</label>
                    <div class="input-wrapper"><input type="password" maxlength="3" placeholder="123"></div>
                </div>
            </div>
        </div>

        <!-- Cash Section -->
        <div id="cashSection" style="display: none; text-align: center; padding: 15px 0; color: #475569; font-size: 13px;">
            <i class="fa-solid fa-hospital-user" style="font-size: 30px; color: #0b78a6; margin-bottom: 8px;"></i>
            <p>You can pay <strong>LKR <?php echo number_format($doctor_fee, 2); ?></strong> directly at the hospital counter on your appointment date.</p>
        </div>

        <button type="submit" class="btn-pay" id="payBtn"><i class="fa-solid fa-lock"></i> Pay LKR <?php echo number_format($doctor_fee, 2); ?></button>
    </form>
</div>

<script>
function switchMethod(type) {
    document.getElementById('payment_method').value = type;
    if(type === 'Card') {
        document.getElementById('cardTab').classList.add('active');
        document.getElementById('cashTab').classList.remove('active');
        document.getElementById('cardSection').style.display = 'block';
        document.getElementById('cashSection').style.display = 'none';
        document.getElementById('payBtn').innerHTML = '<i class="fa-solid fa-lock"></i> Pay LKR <?php echo number_format($doctor_fee, 2); ?>';
    } else {
        document.getElementById('cashTab').classList.add('active');
        document.getElementById('cardTab').classList.remove('active');
        document.getElementById('cardSection').style.display = 'none';
        document.getElementById('cashSection').style.display = 'block';
        document.getElementById('payBtn').innerHTML = '<i class="fa-solid fa-check"></i> Confirm Cash Option';
    }
}
</script>

<?php if ($is_success): ?>
<script>
    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'success',
        title: 'Booking Saved!',
        text: '<?php echo $msg; ?>',
        showConfirmButton: false,
        timer: 2500
    }).then(() => {
        window.location.href = 'appointment-history.php';
    });
</script>
<?php endif; ?>

</body>
</html>
