<?php
session_start();
include "db.php";

if (!isset($_SESSION["user_id"]) && !isset($_SESSION["id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"] ?? $_SESSION["id"];
<?php
session_start();
include "db.php";

if (!isset($_SESSION["user_id"]) && !isset($_SESSION["id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"] ?? $_SESSION["id"];

// Notifications Unread සියල්ල Read (1) ලෙස Update කිරීම
$conn->query("UPDATE notifications SET is_read = 1 WHERE user_id = '$user_id'");

// Current User ගේ Notifications ලබා ගැනීම
$notifications = $conn->query("SELECT * FROM notifications WHERE user_id = '$user_id' ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications | MediCare</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { background: #f4f7fe; padding: 40px 20px; color: #333; }
        .container { max-width: 650px; margin: auto; background: white; padding: 30px; border-radius: 14px; box-shadow: 0 4px 20px rgba(0,0,0,0.06); }
        h2 { color: #0b78a6; margin-bottom: 25px; display: flex; align-items: center; gap: 10px; font-weight: 700; }
        .notif-card { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px 20px; margin-bottom: 12px; display: flex; align-items: flex-start; gap: 15px; transition: 0.3s; }
        .notif-card:hover { border-color: #0b78a6; background: #fff; box-shadow: 0 2px 10px rgba(0,0,0,0.04); }
        .notif-icon { background: #e0f2fe; color: #0b78a6; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0; }
        .notif-content p { font-size: 14px; color: #334155; line-height: 1.5; margin-bottom: 4px; }
        .notif-time { font-size: 12px; color: #94a3b8; font-weight: 500; }
        .btn-back { display: inline-block; margin-bottom: 15px; color: #64748b; text-decoration: none; font-size: 14px; font-weight: 500; }
        .empty-box { text-align: center; padding: 40px 0; color: #64748b; }
        .empty-box i { font-size: 45px; color: #cbd5e1; margin-bottom: 10px; display: block; }
    </style>
</head>
<body>

<div class="container">
    <a href="patient-dashboard.php" class="btn-back"><i class="fa-solid fa-arrow-left"></i> Back to Dashboard</a>
    <h2><i class="fa-solid fa-bell"></i> Notifications</h2>

    <?php if ($notifications && $notifications->num_rows > 0): ?>
        <?php while($row = $notifications->fetch_assoc()): ?>
            <div class="notif-card">
                <div class="notif-icon"><i class="fa-solid fa-envelope-open-text"></i></div>
                <div class="notif-content">
                    <p><?php echo htmlspecialchars($row['message']); ?></p>
                    <span class="notif-time"><i class="fa-regular fa-clock"></i> <?php echo date('M d, Y - h:i A', strtotime($row['created_at'])); ?></span>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="empty-box">
            <i class="fa-solid fa-bell-slash"></i>
            <p>No notifications found yet.</p>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
// Notifications Unread සියල්ල Read (1) ලෙස Update කිරීම
$conn->query("UPDATE notifications SET is_read = 1 WHERE user_id = '$user_id'");

// Current User ගේ Notifications ලබා ගැනීම
$notifications = $conn->query("SELECT * FROM notifications WHERE user_id = '$user_id' ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications | MediCare</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { background: #f4f7fe; padding: 40px 20px; color: #333; }
        .container { max-width: 650px; margin: auto; background: white; padding: 30px; border-radius: 14px; box-shadow: 0 4px 20px rgba(0,0,0,0.06); }
        h2 { color: #0b78a6; margin-bottom: 25px; display: flex; align-items: center; gap: 10px; font-weight: 700; }
        .notif-card { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px 20px; margin-bottom: 12px; display: flex; align-items: flex-start; gap: 15px; transition: 0.3s; }
        .notif-card:hover { border-color: #0b78a6; background: #fff; box-shadow: 0 2px 10px rgba(0,0,0,0.04); }
        .notif-icon { background: #e0f2fe; color: #0b78a6; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0; }
        .notif-content p { font-size: 14px; color: #334155; line-height: 1.5; margin-bottom: 4px; }
        .notif-time { font-size: 12px; color: #94a3b8; font-weight: 500; }
        .btn-back { display: inline-block; margin-bottom: 15px; color: #64748b; text-decoration: none; font-size: 14px; font-weight: 500; }
        .empty-box { text-align: center; padding: 40px 0; color: #64748b; }
        .empty-box i { font-size: 45px; color: #cbd5e1; margin-bottom: 10px; display: block; }
    </style>
</head>
<body>

<div class="container">
    <a href="patient-dashboard.php" class="btn-back"><i class="fa-solid fa-arrow-left"></i> Back to Dashboard</a>
    <h2><i class="fa-solid fa-bell"></i> Notifications</h2>

    <?php if ($notifications && $notifications->num_rows > 0): ?>
        <?php while($row = $notifications->fetch_assoc()): ?>
            <div class="notif-card">
                <div class="notif-icon"><i class="fa-solid fa-envelope-open-text"></i></div>
                <div class="notif-content">
                    <p><?php echo htmlspecialchars($row['message']); ?></p>
                    <span class="notif-time"><i class="fa-regular fa-clock"></i> <?php echo date('M d, Y - h:i A', strtotime($row['created_at'])); ?></span>
                </div>
            </div>
        <?php meendwhile; ?>
    <?php else: ?>
        <div class="empty-box">
            <i class="fa-solid fa-bell-slash"></i>
            <p>No notifications found yet.</p>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
