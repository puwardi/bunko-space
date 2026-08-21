<?php

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    die("Invalid order ID.");
}

$order_id = (int) $_GET["id"];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Success | BUNKO SPACE</title>
</head>
<body>

    <h1>Order Successful!</h1>

    <p>
        Your order has been created successfully.
    </p>

    <p>
        Order ID:
        <strong>#<?= $order_id; ?></strong>
    </p>

    <p>
        Payment:
        <strong>Cash on Delivery (COD)</strong>
    </p>

    <a href="books.php">
        Continue Shopping
    </a>

</body>
</html>