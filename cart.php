<?php
session_start();
include "db.php";

// Initialize cart session
if(!isset($_SESSION['cart'])){
    $_SESSION['cart'] = [];
}

// -------------------
// Add item to cart from confirm_order.php (POST)
// -------------------
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['menu_id'], $_POST['quantity'])){
    $menu_id = intval($_POST['menu_id']);
    $quantity = intval($_POST['quantity']);
    if($quantity < 1) $quantity = 1;

    if(isset($_SESSION['cart'][$menu_id])){
        $_SESSION['cart'][$menu_id] += $quantity;
    } else {
        $_SESSION['cart'][$menu_id] = $quantity;
    }

    // Redirect to prevent form resubmission
    header("Location: cart.php");
    exit();
}

// -------------------
// Remove item from cart
// -------------------
if(isset($_GET['remove'])){
    $remove_id = intval($_GET['remove']);
    if(isset($_SESSION['cart'][$remove_id])){
        unset($_SESSION['cart'][$remove_id]);
    }
    header("Location: cart.php");
    exit();
}

// -------------------
// Update quantities
// -------------------
$success = "";
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])){
    foreach($_POST['quantities'] as $id => $qty){
        $id = intval($id);
        $qty = intval($qty);
        if($qty > 0){
            $_SESSION['cart'][$id] = $qty;
        } else {
            unset($_SESSION['cart'][$id]);
        }
    }
    $success = "Cart updated!";
}
?>

<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Cart — SnapEats</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { font-family: 'Poppins', sans-serif; background: #fff8f6; }
.card { border-radius:16px; box-shadow:0 10px 25px rgba(0,0,0,0.1); margin-top:30px; }
.btn-remove { background:#ff4d4d; color:#fff; border:none; padding:5px 10px; border-radius:5px; }
.btn-remove:hover { background:#ff1a1a; }
.btn-update { background:#ff5733; color:#fff; border:none; padding:6px 12px; border-radius:5px; }
.btn-update:hover { background:#ff6b6b; }
</style>
</head>
<body>

<div class="container">
    <h3 class="mt-4 mb-3" style="color:#ff5733;">Your Cart</h3>

    <?php if($success){ ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php } ?>

    <form method="post">
    <table class="table table-bordered bg-white">
        <thead class="table-light">
            <tr>
                <th>Item</th>
                <th>Price (LKR)</th>
                <th>Quantity</th>
                <th>Subtotal</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php 
        $total = 0;
        if(!empty($_SESSION['cart'])){
            foreach($_SESSION['cart'] as $menu_id => $qty){
                $menu_id = intval($menu_id);
                $result = mysqli_query($conn, "SELECT * FROM menu WHERE id='$menu_id'");
                if(mysqli_num_rows($result)){
                    $item = mysqli_fetch_assoc($result);
                    $subtotal = $item['price'] * $qty;
                    $total += $subtotal;
        ?>
            <tr>
                <td><?php echo htmlspecialchars($item['name']); ?></td>
                <td><?php echo number_format($item['price'],2); ?></td>
                <td>
                    <input type="number" name="quantities[<?php echo $menu_id; ?>]" value="<?php echo $qty; ?>" min="1" style="width:60px;">
                </td>
                <td><?php echo number_format($subtotal,2); ?></td>
                <td>
                    <a href="cart.php?remove=<?php echo $menu_id; ?>" class="btn-remove">Remove</a>
                </td>
            </tr>
        <?php 
                }
            }
        } else { ?>
            <tr>
                <td colspan="5" class="text-center">Your cart is empty.</td>
            </tr>
        <?php } ?>
        </tbody>
    </table>

    <?php if(!empty($_SESSION['cart'])){ ?>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <strong>Total: LKR <?php echo number_format($total,2); ?></strong>
            <div>
                <button type="submit" name="update" class="btn-update">Update Cart</button>
                <a href="checkout.php" class="btn btn-success">Proceed to Checkout</a>
            </div>
        </div>
    <?php } ?>
    </form>
</div>

</body>
</html>
