<?php
session_start();
include "db.php";

// Patient Session Validation
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "patient") {
    header("Location: login.php");
    exit();
}

$patient_id = $_SESSION["user_id"] ?? $_SESSION["id"];
$selected_doctor_id = $_GET['doctor_id'] ?? '';

// Handle Appointment Booking Submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['book_appointment'])) {
    $doctor_id = $_POST['doctor_id'];
    $appointment_date = $_POST['appointment_date'];
    $schedule_id = $_POST['schedule_id'];
    $reason = mysqli_real_escape_string($conn, $_POST['reason']); // Reason Input

    // Fetch schedule details
    $sched_stmt = $conn->prepare("SELECT start_time, room_no FROM doctor_schedules WHERE id = ?");
    $sched_stmt->bind_param("i", $schedule_id);
    $sched_stmt->execute();
    $sched_res = $sched_stmt->get_result()->fetch_assoc();

    if ($sched_res) {
        $appointment_time = $sched_res['start_time'];
        $room_no = $sched_res['room_no'];
        $status = 'pending';
        $payment_status = 'pending';

        // Updated Query: Insert reason column into appointments table
        $stmt = $conn->prepare("INSERT INTO appointments (patient_id, doctor_id, appointment_date, appointment_time, room_no, reason, status, payment_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iissssss", $patient_id, $doctor_id, $appointment_date, $appointment_time, $room_no, $reason, $status, $payment_status);

        if ($stmt->execute()) {
            $success = "Appointment booked successfully! Pending confirmation.";
        } else {
            $error = "Error booking appointment: " . $conn->error;
        }
    } else {
        $error = "Invalid Schedule Selected!";
    }
}

// Fetch All Doctors
$doctors = $conn->query("SELECT id, name, specialization FROM users WHERE role = 'doctor'");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Appointment | MediCare</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { background: #f4f7fe; padding: 40px 20px; color: #333; }
        .container { max-width: 650px; margin: auto; background: white; padding: 35px; border-radius: 14px; box-shadow: 0 4px 20px rgba(0,0,0,0.06); }
        h2 { color: #0b78a6; font-weight: 700; margin-bottom: 25px; display: flex; align-items: center; gap: 10px; }
        .form-group { margin-bottom: 20px; }
        label { font-size: 14px; font-weight: 600; color: #475569; display: block; margin-bottom: 8px; }
        select, input, textarea { width: 100%; padding: 12px 15px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; outline: none; transition: 0.3s; }
        select:focus, input:focus, textarea:focus { border-color: #0b78a6; box-shadow: 0 0 0 3px rgba(11, 120, 166, 0.1); }
        
        .details-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 20px; margin-bottom: 25px; display: none; }
        .details-box p { font-size: 14px; color: #334155; margin-bottom: 8px; }
        .details-box p strong { color: #0b78a6; }

        .btn-submit { background: #0b78a6; color: white; border: none; padding: 14px; border-radius: 8px; font-weight: 600; font-size: 16px; cursor: pointer; width: 100%; transition: 0.3s; }
        .btn-submit:hover { background: #085a7d; }
        
        .alert-success { color: #15803d; background: #dcfce7; padding: 14px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; font-weight: 500; }
        .alert-danger { color: #b91c1c; background: #fee2e2; padding: 14px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; font-weight: 500; }
        .btn-back { display: inline-block; margin-bottom: 15px; color: #64748b; text-decoration: none; font-size: 14px; font-weight: 500; }
    </style>
</head>
<body>

<div class="container">
    <a href="patient-dashboard.php" class="btn-back"><i class="fa-solid fa-arrow-left"></i> Back to Dashboard</a>
    <h2><i class="fa-solid fa-calendar-check"></i> Book an Appointment</h2>

    <?php if(isset($success)) echo "<div class='alert-success'><i class='fa-solid fa-circle-check'></i> $success</div>"; ?>
    <?php if(isset($error)) echo "<div class='alert-danger'><i class='fa-solid fa-triangle-exclamation'></i> $error</div>"; ?>

    <form method="POST">
        <!-- Select Doctor -->
        <div class="form-group">
            <label>Select Doctor:</label>
            <select name="doctor_id" id="doctor_select" required onchange="fetchSchedules(this.value)">
                <option value="">-- Choose a Doctor --</option>
                <?php while($doc = $doctors->fetch_assoc()): ?>
                    <option value="<?php echo $doc['id']; ?>" <?php echo ($selected_doctor_id == $doc['id']) ? 'selected' : ''; ?>>
                        Dr. <?php echo htmlspecialchars($doc['name']); ?> <?php echo !empty($doc['specialization']) ? "(".htmlspecialchars($doc['specialization']).")" : ""; ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <!-- Select Schedule/Time Slot -->
        <div class="form-group">
            <label>Available Schedules & Time Slots:</label>
            <select name="schedule_id" id="schedule_select" required onchange="showScheduleDetails()" disabled>
                <option value="">-- First Select a Doctor --</option>
            </select>
        </div>

        <!-- Appointment Date -->
        <div class="form-group">
            <label>Preferred Appointment Date:</label>
            <input type="date" name="appointment_date" min="<?php echo date('Y-m-d'); ?>" required>
        </div>

        <!-- Reason / Symptoms Field -->
        <div class="form-group">
            <label>Reason for Appointment:</label>
            <textarea name="reason" rows="3" placeholder="e.g. Medical Advice, Regular Checkup, Fever, Headache..." required></textarea>
        </div>

        <!-- Auto Loaded Details Box -->
        <div class="details-box" id="details_box">
            <p><i class="fa-solid fa-door-open"></i> <strong>Room / Location:</strong> <span id="disp_room"></span></p>
            <p><i class="fa-solid fa-clock"></i> <strong>Time Slot:</strong> <span id="disp_time"></span></p>
            <p><i class="fa-solid fa-money-bill-wave"></i> <strong>Doctor Fee:</strong> LKR <span id="disp_fee"></span></p>
        </div>

        <button type="submit" name="book_appointment" class="btn-submit"><i class="fa-solid fa-paper-plane"></i> Confirm Booking</button>
    </form>
</div>

<script>
let scheduleData = [];

function fetchSchedules(doctorId) {
    const schedSelect = document.getElementById('schedule_select');
    const detailsBox = document.getElementById('details_box');
    
    schedSelect.innerHTML = '<option value="">Loading schedules...</option>';
    schedSelect.disabled = true;
    detailsBox.style.display = 'none';

    if (!doctorId) {
        schedSelect.innerHTML = '<option value="">-- First Select a Doctor --</option>';
        return;
    }

    fetch('get-doctor-schedules.php?doctor_id=' + doctorId)
        .then(response => response.json())
        .then(data => {
            scheduleData = data;
            schedSelect.innerHTML = '<option value="">-- Select Time Slot & Day --</option>';
            
            if (data.length === 0) {
                schedSelect.innerHTML = '<option value="">No available schedules for this doctor</option>';
                return;
            }

            data.forEach((item, index) => {
                const opt = document.createElement('option');
                opt.value = item.id;
                opt.textContent = `${item.available_day} (${item.start_time} - ${item.end_time})`;
                opt.setAttribute('data-index', index);
                schedSelect.appendChild(opt);
            });
            schedSelect.disabled = false;
        })
        .catch(err => {
            schedSelect.innerHTML = '<option value="">Error loading schedules</option>';
        });
}

function showScheduleDetails() {
    const schedSelect = document.getElementById('schedule_select');
    const selectedOpt = schedSelect.options[schedSelect.selectedIndex];
    const detailsBox = document.getElementById('details_box');

    if (!schedSelect.value) {
        detailsBox.style.display = 'none';
        return;
    }

    const index = selectedOpt.getAttribute('data-index');
    const item = scheduleData[index];

    if (item) {
        document.getElementById('disp_room').innerText = item.room_no;
        document.getElementById('disp_time').innerText = item.start_time + ' - ' + item.end_time;
        document.getElementById('disp_fee').innerText = parseFloat(item.doctor_fee).toFixed(2);
        detailsBox.style.display = 'block';
    }
}

window.onload = function() {
    const doctorSelect = document.getElementById('doctor_select');
    if (doctorSelect.value) {
        fetchSchedules(doctorSelect.value);
    }
};
</script>

</body>
</html>
