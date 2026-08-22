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

$result = $conn->query("
    SELECT
        orders.id,
        orders.receiver_name,
        orders.receiver_phone,
        orders.shipping_address,
        orders.total_price,
        orders.payment_method,
        orders.status,
        orders.created_at,
        users.name AS customer_name,
        users.email AS customer_email
    FROM orders
    JOIN users
        ON orders.user_id = users.id
    ORDER BY orders.id DESC
");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders | BUNKO SPACE</title>
</head>
<body>

    <h1>Orders</h1>

    <a href="dashboard.php">← Dashboard</a>

    <hr>

    <?php if ($result->num_rows > 0): ?>

        <table border="1" cellpadding="10">

            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Customer</th>
                    <th>Receiver</th>
                    <th>Total</th>
                    <th>Payment</th>
                    <th>Status</th>
                    <th>Order Date</th>
                    <th>Action</th>
                </tr>
            </thead>

            <tbody>

                <?php while ($order = $result->fetch_assoc()): ?>

                    <tr>

                        <td>
                            #<?= $order["id"]; ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($order["customer_name"]); ?>
                            <br>
                            <?= htmlspecialchars($order["customer_email"]); ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($order["receiver_name"]); ?>
                        </td>

                        <td>
                            Rp<?= number_format(
                                $order["total_price"],
                                0,
                                ",",
                                "."
                            ); ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($order["payment_method"]); ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($order["status"]); ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($order["created_at"]); ?>
                        </td>

                        <td>
                            <a href="order_detail.php?id=<?= $order["id"]; ?>">
                                View Detail
                            </a>
                        </td>

                    </tr>

                <?php endwhile; ?>

            </tbody>

        </table>

    <?php else: ?>

        <p>No orders found.</p>

    <?php endif; ?>

</body>
</html>