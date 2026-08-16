<?php
session_start();
include "db.php";

// Admin validation
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
    header("Location: login.php");
    exit();
}

// Handle Form Submission to Add Doctor Schedule & Room
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_schedule'])) {
    $doctor_id = $_POST['doctor_id'];
    $available_day = $_POST['available_day'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $room_no = $_POST['room_no'];
    $doctor_fee = $_POST['doctor_fee'];

    $stmt = $conn->prepare("INSERT INTO doctor_schedules (doctor_id, available_day, start_time, end_time, room_no, doctor_fee) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssd", $doctor_id, $available_day, $start_time, $end_time, $room_no, $doctor_fee);
    
    if ($stmt->execute()) {
        $success = "Schedule added successfully!";
    } else {
        $error = "Error adding schedule: " . $conn->error;
    }
}

// Handle Delete Schedule
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM doctor_schedules WHERE id = $id");
    header("Location: manage-schedules.php");
    exit();
}

// Fetch Doctors list from users table
$doctors = $conn->query("SELECT id, name FROM users WHERE role = 'doctor'");

// Fetch existing Schedules
$schedules = $conn->query("
    SELECT s.*, u.name AS doctor_name 
    FROM doctor_schedules s 
    JOIN users u ON s.doctor_id = u.id 
    ORDER BY s.id DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Schedules & Rooms | MediCare Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { background: #f4f7fe; padding: 30px; color: #333; }
        .container { max-width: 950px; margin: auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        h2 { color: #0b78a6; margin-bottom: 20px; font-weight: 700; }
        .btn-back { display: inline-flex; align-items: center; gap: 8px; color: #0b78a6; text-decoration: none; font-weight: 600; margin-bottom: 20px; font-size: 14px; }
        .btn-back:hover { text-decoration: underline; }
        .form-group { margin-bottom: 18px; }
        label { font-size: 14px; font-weight: 600; color: #475569; display: block; margin-bottom: 6px; }
        select, input { width: 100%; padding: 11px 14px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; outline: none; transition: 0.3s; }
        select:focus, input:focus { border-color: #0b78a6; box-shadow: 0 0 0 3px rgba(11, 120, 166, 0.1); }
        .form-row { display: flex; gap: 15px; }
        .form-row .form-group { flex: 1; }
        .btn-submit { background: #0b78a6; color: white; border: none; padding: 12px 20px; border-radius: 8px; font-weight: 600; font-size: 15px; cursor: pointer; width: 100%; transition: 0.3s; }
        .btn-submit:hover { background: #085a7d; }
        .alert-success { color: #15803d; background: #dcfce7; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; font-weight: 500; }
        .alert-danger { color: #b91c1c; background: #fee2e2; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; font-weight: 500; }
        
        table { width: 100%; margin-top: 35px; border-collapse: collapse; }
        th, td { padding: 14px 15px; border-bottom: 1px solid #f1f5f9; text-align: left; font-size: 14px; }
        th { background: #f8fafc; color: #64748b; font-weight: 600; text-transform: uppercase; font-size: 12px; }
        .room-badge { background: #e0f2fe; color: #0284c7; padding: 4px 10px; border-radius: 6px; font-weight: 600; font-size: 13px; }
        .btn-delete { color: #ef4444; text-decoration: none; font-weight: 600; font-size: 13px; }
        .btn-delete:hover { text-decoration: underline; }
    </style>
</head>
<body>

<div class="container">
    <a href="admin-dashboard.php" class="btn-back"><i class="fa-solid fa-arrow-left"></i> Back to Admin Dashboard</a>
    <h2><i class="fa-solid fa-calendar-plus"></i> Manage Doctor Schedule & Room Numbers</h2>

    <?php if(isset($success)) echo "<div class='alert-success'><i class='fa-solid fa-circle-check'></i> $success</div>"; ?>
    <?php if(isset($error)) echo "<div class='alert-danger'><i class='fa-solid fa-triangle-exclamation'></i> $error</div>"; ?>

    <form method="POST">
        <div class="form-group">
            <label>Select Doctor:</label>
            <select name="doctor_id" required>
                <option value="">-- Choose Doctor --</option>
                <?php while($doc = $doctors->fetch_assoc()): ?>
                    <option value="<?php echo $doc['id']; ?>"><?php echo htmlspecialchars($doc['name']); ?></option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Available Day:</label>
                <select name="available_day" required>
                    <option value="Monday">Monday</option>
                    <option value="Tuesday">Tuesday</option>
                    <option value="Wednesday">Wednesday</option>
                    <option value="Thursday">Thursday</option>
                    <option value="Friday">Friday</option>
                    <option value="Saturday">Saturday</option>
                    <option value="Sunday">Sunday</option>
                </select>
            </div>
            <div class="form-group">
                <label>Room No / Location:</label>
                <input type="text" name="room_no" placeholder="e.g. Room 04 / Consultation Room A" required>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Start Time:</label>
                <input type="time" name="start_time" required>
            </div>
            <div class="form-group">
                <label>End Time:</label>
                <input type="time" name="end_time" required>
            </div>
            <div class="form-group">
                <label>Doctor Fee (LKR):</label>
                <input type="number" step="0.01" name="doctor_fee" placeholder="2500.00" required>
            </div>
        </div>

        <button type="submit" name="add_schedule" class="btn-submit"><i class="fa-solid fa-floppy-disk"></i> Save Doctor Schedule</button>
    </form>

    <h3 style="margin-top: 40px; color: #1e293b; font-size: 18px;">Active Doctor Schedules</h3>
    <table>
        <thead>
            <tr>
                <th>Doctor Name</th>
                <th>Day</th>
                <th>Time Slot</th>
                <th>Room No</th>
                <th>Doctor Fee</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if($schedules && $schedules->num_rows > 0): ?>
                <?php while($row = $schedules->fetch_assoc()): ?>
                    <tr>
                        <td><b><?php echo htmlspecialchars($row['doctor_name']); ?></b></td>
                        <td><?php echo htmlspecialchars($row['available_day']); ?></td>
                        <td><?php echo date("g:i A", strtotime($row['start_time'])) . " - " . date("g:i A", strtotime($row['end_time'])); ?></td>
                        <td><span class="room-badge"><?php echo htmlspecialchars($row['room_no']); ?></span></td>
                        <td>LKR <?php echo number_format($row['doctor_fee'], 2); ?></td>
                        <td><a href="manage-schedules.php?delete=<?php echo $row['id']; ?>" class="btn-delete" onclick="return confirm('Are you sure you want to delete this schedule?')"><i class="fa-solid fa-trash"></i> Delete</a></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="6" style="text-align:center; padding: 25px; color: #94a3b8;">No schedules added yet. Add a new schedule above.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>
