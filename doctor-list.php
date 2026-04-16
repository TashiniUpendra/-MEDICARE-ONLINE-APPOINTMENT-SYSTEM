<?php
session_start();

/* Sample doctors (DB use karanna puluwan later) */
$doctors = [
    ["name" => "Dr. John Silva", "specialty" => "Cardiologist"],
    ["name" => "Dr. Maya Fernando", "specialty" => "Dermatologist"],
    ["name" => "Dr. Ruwan Perera", "specialty" => "Neurologist"],
    ["name" => "Dr. Nadeesha Karun", "specialty" => "Pediatrician"]
];
?>

<!DOCTYPE html>
<html>
<head>
<title>Doctor List</title>

<style>
body{font-family:Arial;background:#f0faff}
.container{width:90%;margin:40px auto}
.card{
    background:white;
    padding:20px;
    margin-bottom:15px;
    border-radius:10px;
    box-shadow:0 4px 10px rgba(0,0,0,0.1);
}
.btn{
    background:#0b78a6;
    color:white;
    padding:8px 12px;
    text-decoration:none;
    border-radius:5px;
}
</style>
</head>

<body>

<div class="container">
<h2>Select Doctor</h2>

<?php foreach($doctors as $doc): ?>
<div class="card">
    <h3><?php echo $doc["name"]; ?></h3>
    <p><?php echo $doc["specialty"]; ?></p>

    <a href="appointment-booking.php?doctor=<?php echo urlencode($doc["name"]); ?>" class="btn">
        Book Appointment
    </a>
</div>
<?php endforeach; ?>

</div>

</body>
</html>
