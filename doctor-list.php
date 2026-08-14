<?php
session_start();
include "db.php";

/* Search functionality */
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

/* Fetch Doctors and their Specialization using LEFT JOIN */
if (!empty($search)) {
    $stmt = $conn->prepare("
        SELECT u.id, u.name, u.email, d.specialization, d.image 
        FROM users u 
        LEFT JOIN doctors d ON u.email = d.email 
        WHERE u.role = 'doctor' AND u.name LIKE ?
    ");
    $searchTerm = "%" . $search . "%";
    $stmt->bind_param("s", $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query("
        SELECT u.id, u.name, u.email, d.specialization, d.image 
        FROM users u 
        LEFT JOIN doctors d ON u.email = d.email 
        WHERE u.role = 'doctor'
    ");
}

/* Doctors list */
$doctors = [];
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()){
        $doctors[] = [
            "id" => $row["id"],
            "name" => $row["name"],
            // Specialization එක Database එකෙන් ගනී (නැත්නම් General Physician ලෙස වැටේ)
            "specialty" => !empty($row["specialization"]) ? $row["specialization"] : "General Physician",
            "image" => !empty($row["image"]) ? $row["image"] : null
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MediCare | Select Doctor</title>

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

/* Main Content */
.main-content { flex: 1; padding: 30px; overflow-y: auto; }
.header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }

/* Search Bar */
.search-container { background: white; padding: 15px 20px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.03); margin-bottom: 25px; }
.search-form { display: flex; gap: 10px; }
.search-input { flex: 1; padding: 10px 15px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 14px; outline: none; }
.search-input:focus { border-color: #0b78a6; }
.search-btn { background: #0b78a6; color: white; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 500; cursor: pointer; transition: 0.3s; }
.search-btn:hover { background: #085a7d; }

/* Doctors Grid */
.doctors-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px; }
.doctor-card { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.03); text-align: center; transition: 0.3s; display: flex; flex-direction: column; align-items: center; }
.doctor-card:hover { transform: translateY(-5px); box-shadow: 0 8px 20px rgba(0,0,0,0.06); }

/* Profile Photo Styling */
.doctor-avatar { width: 85px; height: 85px; border-radius: 50%; object-fit: cover; border: 3px solid #e0f2fe; margin-bottom: 15px; }
.doctor-avatar-icon { width: 85px; height: 85px; background: #e0f2fe; color: #0b78a6; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 32px; margin-bottom: 15px; }

.doctor-name { font-size: 17px; font-weight: 600; color: #1e293b; margin-bottom: 5px; }
.doctor-specialty { font-size: 12px; color: #0b78a6; background: #e0f2fe; padding: 4px 12px; border-radius: 20px; font-weight: 500; margin-bottom: 20px; display: inline-block; }

.btn-book { display: inline-block; width: 100%; background: #0b78a6; color: white; padding: 10px; border-radius: 8px; text-decoration: none; font-size: 13px; font-weight: 600; transition: 0.3s; }
.btn-book:hover { background: #085a7d; }

.no-doctors { grid-column: 1 / -1; background: white; padding: 30px; text-align: center; border-radius: 12px; color: #64748b; }
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
                <li><a href="home.php"><i class="fa-solid fa-house"></i> Home</a></li>
                <?php if (isset($_SESSION["role"]) && $_SESSION["role"] === "patient"): ?>
                    <li><a href="patient-dashboard.php"><i class="fa-solid fa-chart-pie"></i> Dashboard</a></li>
                <?php endif; ?>
                <li><a href="doctor-list.php" class="active"><i class="fa-solid fa-user-doctor"></i> Book Appointment</a></li>
                <?php if (isset($_SESSION["role"]) && $_SESSION["role"] === "patient"): ?>
                    <li><a href="appointment-history.php"><i class="fa-solid fa-calendar-check"></i> My Appointments</a></li>
                    <li><a href="profile.php"><i class="fa-solid fa-user-gear"></i> Profile</a></li>
                <?php endif; ?>
            </ul>
        </div>
        <div>
            <?php if (isset($_SESSION["role"])): ?>
                <a href="logout.php" style="color: #f87171; text-decoration:none;"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
            <?php else: ?>
                <a href="login.php" style="color: #6dd5fa; text-decoration:none;"><i class="fa-solid fa-right-to-bracket"></i> Login</a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        
        <div class="header">
            <h2>Select a Doctor</h2>
        </div>

        <!-- Search Bar -->
        <div class="search-container">
            <form class="search-form" method="GET">
                <input type="text" name="search" class="search-input" placeholder="Search doctor by name..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="search-btn"><i class="fa-solid fa-magnifying-glass"></i> Search</button>
                <?php if(!empty($search)): ?>
                    <a href="doctor-list.php" class="search-btn" style="background:#64748b; text-decoration:none; display:flex; align-items:center;">Clear</a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Doctors Grid -->
        <div class="doctors-grid">
            <?php if (!empty($doctors)): ?>
                <?php foreach($doctors as $doc): ?>
                <div class="doctor-card">
                    
                    <!-- Check if photo exists in uploads folder -->
                    <?php if (!empty($doc['image']) && file_exists('uploads/' . $doc['image'])): ?>
                        <img src="uploads/<?php echo htmlspecialchars($doc['image']); ?>" alt="Doctor Photo" class="doctor-avatar">
                    <?php else: ?>
                        <div class="doctor-avatar-icon">
                            <i class="fa-solid fa-user-md"></i>
                        </div>
                    <?php endif; ?>

                    <div class="doctor-name"><?php echo htmlspecialchars($doc["name"]); ?></div>
                    <span class="doctor-specialty"><i class="fa-solid fa-stethoscope"></i> <?php echo htmlspecialchars($doc["specialty"]); ?></span>
                    
                    <a href="appointment-booking.php?doctor_id=<?php echo $doc['id']; ?>&doctor=<?php echo urlencode($doc["name"]); ?>" class="btn-book">
                        <i class="fa-solid fa-calendar-plus"></i> Book Appointment
                    </a>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-doctors">
                    <i class="fa-solid fa-user-slash" style="font-size:40px; margin-bottom:10px;"></i>
                    <p>No doctors found matching your search query.</p>
                </div>
            <?php endif; ?>
        </div>

    </div>

</body>
</html>
