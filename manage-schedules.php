<?php
session_start();
include "db.php";

// Schedule එකක් එකතු කිරීම
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
        $error = "Error adding schedule.";
    }
}

// Schedule එකක් Delete කිරීම
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM doctor_schedules WHERE id = $id");
    header("Location: manage-schedules.php");
    exit();
}

// Doctors ලාගේ ලැයිස්තුව ගෙන ඒම
$doctors = $conn->query("SELECT id, name FROM users WHERE role = 'doctor'");

// දැනට තියෙන Schedules ගෙන ඒම
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
    <title>Manage Doctor Schedules | MediCare</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Poppins', sans-serif; }
        body { background:#f4f7fe; padding:30px; }
        .container { max-width:900px; margin:auto; background:white; padding:25px; border-radius:12px; box-shadow:0 4px 12px rgba(0,0,0,0.05); }
        h2 { color:#0b78a6; margin-bottom:20px; }
        .form-group { margin-bottom:15px; }
        label { font-size:14px; font-weight:500; color:#333; display:block; margin-bottom:5px; }
        select, input { width:100%; padding:10px; border:1px solid #ccc; border-radius:6px; font-size:14px; }
        .form-row { display:flex; gap:15px; }
        .form-row .form-group { flex:1; }
        .btn-submit { background:#0b78a6; color:white; border:none; padding:12px 20px; border-radius:6px; font-weight:600; cursor:pointer; width:100%; }
        .btn-submit:hover { background:#085a7d; }
        table { width:100%; margin-top:30px; border-collapse:collapse; }
        th, td { padding:12px; border-bottom:1px solid #eee; text-align:left; font-size:14px; }
        th { background:#f8fafc; color:#64748b; }
        .btn-delete { color:#ef4444; text-decoration:none; font-weight:600; }
        .alert-success { color:#16a34a; background:#dcfce7; padding:10px; border-radius:6px; margin-bottom:15px; }
    </style>
</head>
<body>

<div class="container">
    <h2><i class="fa-solid fa-calendar-plus"></i> Add Doctor Schedule & Room</h2>

    <?php if(isset($success)) echo "<div class='alert-success'>$success</div>"; ?>

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
                <input type="text" name="room_no" placeholder="e.g. Room 04" required>
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

        <button type="submit" name="add_schedule" class="btn-submit">Save Schedule</button>
    </form>

    <h3 style="margin-top:40px; color:#333;">Existing Schedules</h3>
    <table>
        <thead>
            <tr>
                <th>Doctor</th>
                <th>Day</th>
                <th>Time</th>
                <th>Room</th>
                <th>Fee</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if($schedules->num_rows > 0): ?>
                <?php while($row = $schedules->fetch_assoc()): ?>
                    <tr>
                        <td><b><?php echo htmlspecialchars($row['doctor_name']); ?></b></td>
                        <td><?php echo $row['available_day']; ?></td>
                        <td><?php echo date("g:i A", strtotime($row['start_time'])) . " - " . date("g:i A", strtotime($row['end_time'])); ?></td>
                        <td><span style="background:#e0f2fe; color:#0b78a6; padding:3px 8px; border-radius:4px; font-weight:500;"><?php echo $row['room_no']; ?></span></td>
                        <td>LKR <?php echo number_format($row['doctor_fee'], 2); ?></td>
                        <td><a href="manage-schedules.php?delete=<?php echo $row['id']; ?>" class="btn-delete" onclick="return confirm('Delete this schedule?')">Delete</a></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="6" style="text-align:center;">No schedules added yet.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>