<?php
session_start();

$name = $_SESSION["name"] ?? null;
$role = $_SESSION["role"] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MediCare | Home</title>

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: Arial, sans-serif;
}

/* Navbar */
.navbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #0b78a6;
    padding: 15px 30px;
}

.logo {
    color: white;
    font-size: 22px;
    font-weight: bold;
}

.nav-links {
    list-style: none;
    display: flex;
    gap: 20px;
}

.nav-links li a {
    color: white;
    text-decoration: none;
    font-weight: bold;
}

.nav-links li a:hover {
    text-decoration: underline;
}

/* Hero */
.hero {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 50px;
}

.hero-text {
    max-width: 50%;
}

.hero-text h1 {
    font-size: 40px;
    margin-bottom: 15px;
}

.hero-text p {
    margin-bottom: 20px;
    font-size: 18px;
}

.btn {
    background: #0b78a6;
    color: white;
    padding: 10px 18px;
    text-decoration: none;
    border-radius: 6px;
}

.btn:hover {
    background: #095c80;
}

.hero-img img {
    width: 280px;
}

/* Services */
.services {
    text-align: center;
    padding: 40px;
}

.service-boxes {
    display: flex;
    justify-content: center;
    gap: 20px;
    margin-top: 25px;
}

.box {
    background: #f4f9fc;
    padding: 20px;
    border-radius: 10px;
    width: 250px;
}

.box img {
    width: 60px;
    margin-bottom: 10px;
}

/* Footer */
footer {
    text-align: center;
    background: #0b78a6;
    color: white;
    padding: 12px;
    margin-top: 40px;
}
</style>

</head>
<body>

<!-- Navbar -->
<nav class="navbar">
    <div class="logo">MediCare</div>

    <ul class="nav-links">
        <li><a href="home.php">Home</a></li>
        <li><a href="doctor-list.php">Doctors</a></li>

        <?php if ($name): ?>
            <li><a href="patient-dashboard.php">
                <?php echo htmlspecialchars($name); ?>
            </a></li>
            <li><a href="logout.php">Logout</a></li>
        <?php else: ?>
            <li><a href="login.php">Login</a></li>
        <?php endif; ?>
    </ul>
</nav>

<!-- Hero Section -->
<section class="hero">

    <div class="hero-text">
        <?php if ($name): ?>
            <h1>Welcome, <?php echo htmlspecialchars($name); ?> 👋</h1>
        <?php else: ?>
            <h1>Your Health, Our Priority</h1>
        <?php endif; ?>

        <p>Book your doctor appointments online with ease and convenience.</p>

        <a href="doctor-list.php" class="btn">Book Appointment</a>
    </div>

    <div class="hero-img">
        <img src="https://cdn-icons-png.flaticon.com/512/387/387561.png" alt="Doctor">
    </div>

</section>

<!-- Services -->
<section class="services">
    <h2>Our Services</h2>

    <div class="service-boxes">

        <div class="box">
            <img src="https://cdn-icons-png.flaticon.com/512/3209/3209265.png">
            <h3>Qualified Doctors</h3>
            <p>Experienced and certified doctors available.</p>
        </div>

        <div class="box">
            <img src="https://cdn-icons-png.flaticon.com/512/3209/3209280.png">
            <h3>Easy Appointments</h3>
            <p>Book appointments without waiting in queues.</p>
        </div>

        <div class="box">
            <img src="https://cdn-icons-png.flaticon.com/512/2922/2922510.png">
            <h3>24/7 Support</h3>
            <p>Always ready to help you anytime.</p>
        </div>

    </div>
</section>

<!-- Footer -->
<footer>
    MediCare Online Appointment System © <?php echo date("Y"); ?>
</footer>

</body>
</html>
