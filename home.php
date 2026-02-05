<?php
session_start();

/*
  Assume after login you set:
  $_SESSION["role"] = "patient" / "admin" / "doctor";
  $_SESSION["name"] = "User Name";
*/

// If you want to allow everyone to see Home, keep this.
// If you want only logged users, uncomment the block below.
/*
if (!isset($_SESSION["name"])) {
    header("Location: login.php");
    exit();
}
*/

$name = $_SESSION["name"] ?? null; // if not logged in -> null
$role = $_SESSION["role"] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>MediCare | Home</title>
  <link rel="stylesheet" href="home.css">
</head>
<body>

  <!-- Navigation Bar -->
  <nav class="navbar">
    <div class="logo">MediCare</div>

    <ul class="nav-links">
      <li><a href="home.php" class="active">Home</a></li>
      <li><a href="doctor-list.php">Doctors</a></li>

      <?php if ($name): ?>
        <!-- If logged in -->
        <li><a href="patient-dashboard.php">
          <?php echo htmlspecialchars($name); ?>
        </a></li>
        <li><a href="logout.php">Logout</a></li>
      <?php else: ?>
        <!-- If not logged in -->
        <li><a href="login.php">Login</a></li>
      <?php endif; ?>
    </ul>
  </nav>

  <!-- Hero Section -->
  <section class="hero">
    <div class="hero-text">

      <?php if ($name): ?>
        <h1>Welcome, <?php echo htmlspecialchars($name); ?> 👋</h1>
        <p>Book your doctor appointments online with ease and convenience.</p>
      <?php else: ?>
        <h1>Your Health, Our Priority</h1>
        <p>Book your doctor appointments online with ease and convenience.</p>
      <?php endif; ?>

      <a href="doctor-list.php" class="btn">Book Appointment</a>
    </div>

    <div class="hero-img">
      <img src="https://cdn-icons-png.flaticon.com/512/387/387561.png" alt="Doctor">
    </div>
  </section>

  <!-- Services Section -->
  <section class="services">
    <h2>Our Services</h2>

    <div class="service-boxes">
      <div class="box">
        <img src="https://cdn-icons-png.flaticon.com/512/3209/3209265.png" alt="Doctors">
        <h3>Qualified Doctors</h3>
        <p>Meet our team of experienced and certified healthcare professionals.</p>
      </div>

      <div class="box">
        <img src="https://cdn-icons-png.flaticon.com/512/3209/3209280.png" alt="Appointments">
        <h3>Easy Appointments</h3>
        <p>Book your appointment online without waiting in queues.</p>
      </div>

      <div class="box">
        <img src="https://cdn-icons-png.flaticon.com/512/2922/2922510.png" alt="Care">
        <h3>24/7 Support</h3>
        <p>We are always here to provide support whenever you need it.</p>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer>
    MediCare Online Appointment System © <?php echo date("Y"); ?>
  </footer>

  <script src="home.js"></script>
</body>
</html>
