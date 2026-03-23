<?php
session_start();

/* Optional: Only patient can view */
/*
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "patient") {
    header("Location: login.php");
    exit();
}
*/

/* Demo Doctor Data (later DB connect karamu) */
$doctors = [
    [
        "id" => 1,
        "name" => "Dr. John Silva",
        "specialty" => "Cardiologist",
        "experience" => "12 Years Experience",
        "image" => "https://www.shutterstock.com/image-photo/portrait-handsome-hispanic-male-doctor-600nw-2608441611.jpg"
    ],
    [
        "id" => 2,
        "name" => "Dr. Maya Fernando",
        "specialty" => "Dermatologist",
        "experience" => "8 Years Experience",
        "image" => "https://www.shutterstock.com/image-photo/portrait-handsome-hispanic-male-doctor-600nw-2608441611.jpg"
    ],
    [
        "id" => 3,
        "name" => "Dr. Ruwan Perera",
        "specialty" => "Neurologist",
        "experience" => "15 Years Experience",
        "image" => "https://www.shutterstock.com/image-photo/portrait-handsome-hispanic-male-doctor-600nw-2608441611.jpg"
    ],
    [
        "id" => 4,
        "name" => "Dr. Nadeesha Karun",
        "specialty" => "Pediatrician",
        "experience" => "10 Years Experience",
        "image" => "https://www.shutterstock.com/image-photo/portrait-handsome-hispanic-male-doctor-600nw-2608441611.jpg"
    ]
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MediCare | Doctor List</title>

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: "Poppins", sans-serif;
}

body {
    background: #f0faff;
}

header {
    background: #0b78a6;
    padding: 18px;
    text-align: center;
    color: white;
    font-size: 22px;
}

.container {
    width: 90%;
    margin: 30px auto;
}

.card {
    background: white;
    padding: 20px;
    border-radius: 12px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 20px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.card img {
    width: 110px;
    height: 110px;
    border-radius: 50%;
    border: 3px solid #0b78a6;
    object-fit: cover;
}

.info {
    flex: 1;
}

.info h3 {
    color: #0b78a6;
}

.btn {
    background: #0b78a6;
    color: white;
    padding: 10px 14px;
    border-radius: 6px;
    text-decoration: none;
}

.btn:hover {
    background: #095c80;
}
</style>
</head>

<body>

<header>MediCare | Doctor List</header>

<div class="container">

<?php foreach ($doctors as $doc): ?>
    <div class="card">
        <img src="<?php echo htmlspecialchars($doc["image"]); ?>" alt="Doctor">

        <div class="info">
            <h3><?php echo htmlspecialchars($doc["name"]); ?></h3>
            <p><strong>Specialty:</strong> <?php echo htmlspecialchars($doc["specialty"]); ?></p>
            <p><strong>Experience:</strong> <?php echo htmlspecialchars($doc["experience"]); ?></p>
        </div>

        <!-- View Profile Button -->
        <a class="btn" href="doctor-profile.php?id=<?php echo $doc["id"]; ?>">
            View Profile
        </a>
    </div>
<?php endforeach; ?>

</div>

</body>
</html>
