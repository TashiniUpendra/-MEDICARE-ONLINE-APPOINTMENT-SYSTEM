<?php
session_start();
include "db.php";

/* Only patient can access */
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "patient") {
    header("Location: login.php");
    exit();
}

/* Search functionality */
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

/* Fetch Doctors from Database */
if (!empty($search)) {
    $stmt = $conn->prepare("SELECT id, name, email FROM users WHERE role = 'doctor' AND name LIKE ?");
    $searchTerm = "%" . $search . "%";
    $stmt->bind_param("s", $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query("SELECT id, name, email FROM users WHERE role = 'doctor'");
}

/* Doctors list */
$doctors = [];
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()){
        $doctors[] = [
            "id" => $row["id"],
            "name" => $row["name"],
            "specialty" => "General Physician" // Default specialty
        ];
    }
} else if (empty($search)) {
    // Fallback demo list if no doctors in database
    $doctors = [
        ["id" => 1, "name" => "Dr. John Silva", "specialty" => "Cardiologist"],
        ["id" => 2, "name" => "Dr. Maya Fernando", "specialty" => "Dermatologist"],
        ["id" => 3, "name" => "Dr. Ruwan Perera", "specialty" => "Neurologist"],
        ["id" => 4, "name" => "Dr. Nadeesha Karun", "specialty" => "Pediatrician"]
    ];
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
.doctors-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; }
.doctor-card { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.03); text-align: center; transition: 0.3s; display: flex; flex-direction: column; align-items: center; }
.doctor-card:hover { transform: translateY(-5px); box-shadow: 0 8px 20px rgba(0,0,0,0.06); }

.doctor-avatar { width: 70px; height: 70px; background: #e0f2fe; color: #0b78a6; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 28px; margin-bottom: 15px; }
.doctor-name { font-size: 18px; font-weight: 600; color: #1e293b; margin-bottom: 5px; }
.doctor-specialty { font-size: 13px; color: #0b78a6; background: #e0f2fe; padding: 4px 12px; border-radius: 20px; font-weight: 500; margin-bottom: 20px; display: inline-block; }

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
                <li><a href="patient-dashboard.php"><i class="fa-solid fa-chart-pie"></i> Dashboard</a></li>
                <li><a href="doctor-list.php" class="active"><i class="fa-solid fa-user-doctor"></i> Book Appointment</a></li>
                <li><a href="appointment-history.php"><i class="fa-solid fa-calendar-check"></i> My Appointments</a></li>
                <li><a href="profile.php"><i class="fa-solid fa-user-gear"></i> Profile</a></li>
            </ul>
        </div>
        <div>
            <a href="logout.php" style="color: #f87171; text-decoration:none;"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
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
                    <div class="doctor-avatar">
                        <i class="fa-solid fa-user-md"></i>
                    </div>
                    <div class="doctor-name"><?php echo htmlspecialchars($doc["name"]); ?></div>
                    <span class="doctor-specialty"><i class="fa-solid fa-stethoscope"></i> <?php echo htmlspecialchars($doc["specialty"]); ?></span>
                    
                    <a href="appointment-booking.php?doctor_id=<?php echo $doc['id'] ?? 0; ?>&doctor=<?php echo urlencode($doc["name"]); ?>" class="btn-book">
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
