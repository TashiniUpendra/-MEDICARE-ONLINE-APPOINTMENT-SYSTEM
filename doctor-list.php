<?php
session_start();
include "db.php";

// Get doctors from database
$sql = "SELECT * FROM doctors";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>MediCare | Doctor List</title>

<style>
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:"Poppins", sans-serif;
}
body{
    background:#f0faff;
}
header{
    background:#0b78a6;
    color:white;
    text-align:center;
    padding:18px;
    font-size:22px;
}
.container{
    width:90%;
    margin:40px auto;
}
.card{
    background:white;
    padding:20px;
    border-radius:12px;
    box-shadow:0 4px 18px rgba(0,0,0,0.1);
    margin-bottom:20px;
    display:flex;
    align-items:center;
    gap:20px;
}
.card img{
    width:100px;
    height:100px;
    border-radius:50%;
}
.info{
    flex:1;
}
.btn{
    background:#0b78a6;
    color:white;
    padding:10px 14px;
    text-decoration:none;
    border-radius:6px;
}
</style>

</head>
<body>

<header>MediCare | Doctor List</header>

<div class="container">

<?php if ($result->num_rows > 0): ?>
    
    <?php while($row = $result->fetch_assoc()): ?>
        
        <div class="card">
            <img src="https://via.placeholder.com/100" alt="Doctor">

            <div class="info">
                <h3><?php echo htmlspecialchars($row["name"]); ?></h3>
                <p><strong>Specialty:</strong> <?php echo htmlspecialchars($row["specialization"]); ?></p>
                <p><strong>Experience:</strong> <?php echo htmlspecialchars($row["experience"]); ?></p>
            </div>

            <a class="btn" href="doctor-profile.php?id=<?php echo $row['id']; ?>">
                View Profile
            </a>
        </div>

    <?php endwhile; ?>

<?php else: ?>
    <p>No doctors found.</p>
<?php endif; ?>

</div>

</body>
</html>
