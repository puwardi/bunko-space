<?php

session_start();

if (
    !isset($_SESSION["admin_id"]) ||
    !isset($_SESSION["admin_role"]) ||
    $_SESSION["admin_role"] !== "admin"
) {
    header("Location: login.php");
    exit;
}

require_once "../config/database.php";

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    die("Invalid order ID.");
}

$order_id = (int) $_GET["id"];

$stmt = $conn->prepare("
    SELECT
        orders.*,
        users.name AS customer_name,
        users.email AS customer_email
    FROM orders
    JOIN users
        ON orders.user_id = users.id
    WHERE orders.id = ?
");

$stmt->bind_param("i", $order_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    die("Order not found.");
}

$order = $result->fetch_assoc();

$stmt_items = $conn->prepare("
    SELECT
        order_details.quantity,
        order_details.price,
        order_details.subtotal,
        books.title
    FROM order_details
    JOIN books
        ON order_details.book_id = books.id
    WHERE order_details.order_id = ?
");

$stmt_items->bind_param("i", $order_id);
$stmt_items->execute();

$items = $stmt_items->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Detail | BUNKO SPACE</title>
</head>
<body>

    <h1>Order Detail #<?= $order["id"]; ?></h1>

    <a href="orders.php">← Back to Orders</a>

    <hr>

    <h2>Customer</h2>

    <p>
        Name:
        <?= htmlspecialchars($order["customer_name"]); ?>
    </p>

    <p>
        Email:
        <?= htmlspecialchars($order["customer_email"]); ?>
    </p>

    <h2>Shipping</h2>

    <p>
        Receiver:
        <?= htmlspecialchars($order["receiver_name"]); ?>
    </p>

    <p>
        Phone:
        <?= htmlspecialchars($order["receiver_phone"]); ?>
    </p>

    <p>
        Address:
        <?= nl2br(htmlspecialchars($order["shipping_address"])); ?>
    </p>

    <h2>Items</h2>

    <?php while ($item = $items->fetch_assoc()): ?>

        <p>
            <?= htmlspecialchars($item["title"]); ?>
            × <?= $item["quantity"]; ?>
            —
            Rp<?= number_format($item["subtotal"], 0, ",", "."); ?>
        </p>

    <?php endwhile; ?>

    <hr>

    <p>
        <strong>
            Total:
            Rp<?= number_format($order["total_price"], 0, ",", "."); ?>
        </strong>
    </p>

    <p>
        Payment:
        <?= htmlspecialchars($order["payment_method"]); ?>
    </p>

    <p>
        Status:
        <?= htmlspecialchars($order["status"]); ?>
    </p>

</body>
</html>