<?php

session_start();

require_once "../config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

$cart = $_SESSION["cart"] ?? [];

if (empty($cart)) {
    header("Location: cart.php");
    exit;
}

$total = 0;

foreach ($cart as $item) {
    $total += $item["price"] * $item["quantity"];
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $receiver_name = trim($_POST["receiver_name"]);
    $receiver_phone = trim($_POST["receiver_phone"]);
    $shipping_address = trim($_POST["shipping_address"]);

    if (
        $receiver_name === "" ||
        $receiver_phone === "" ||
        $shipping_address === ""
    ) {
        $message = "All fields are required.";
    } else {

        $conn->begin_transaction();

        try {

            $stmt_order = $conn->prepare("
                INSERT INTO orders
                (
                    user_id,
                    receiver_name,
                    receiver_phone,
                    shipping_address,
                    total_price,
                    payment_method,
                    status
                )
                VALUES (?, ?, ?, ?, ?, 'COD', 'pending')
            ");

            $stmt_order->bind_param(
                "isssd",
                $_SESSION["user_id"],
                $receiver_name,
                $receiver_phone,
                $shipping_address,
                $total
            );

            $stmt_order->execute();

            $order_id = $conn->insert_id;

            foreach ($cart as $item) {

                $book_id = $item["book_id"];
                $quantity = $item["quantity"];
                $price = $item["price"];
                $subtotal = $price * $quantity;

                $stmt_stock = $conn->prepare("
                    SELECT stock, status
                    FROM books
                    WHERE id = ?
                    FOR UPDATE
                ");

                $stmt_stock->bind_param("i", $book_id);
                $stmt_stock->execute();

                $stock_result = $stmt_stock->get_result();

                if ($stock_result->num_rows !== 1) {
                    throw new Exception("Book not found.");
                }

                $book = $stock_result->fetch_assoc();

                if ($book["status"] !== "active") {
                    throw new Exception("One of the books is unavailable.");
                }

                if ($quantity > $book["stock"]) {
                    throw new Exception("Not enough stock for one of the books.");
                }

                $stmt_detail = $conn->prepare("
                    INSERT INTO order_details
                    (
                        order_id,
                        book_id,
                        quantity,
                        price,
                        subtotal
                    )
                    VALUES (?, ?, ?, ?, ?)
                ");

                $stmt_detail->bind_param(
                    "iiidd",
                    $order_id,
                    $book_id,
                    $quantity,
                    $price,
                    $subtotal
                );

                $stmt_detail->execute();

                $stmt_update_stock = $conn->prepare("
                    UPDATE books
                    SET stock = stock - ?
                    WHERE id = ?
                ");

                $stmt_update_stock->bind_param(
                    "ii",
                    $quantity,
                    $book_id
                );

                $stmt_update_stock->execute();
            }

            $conn->commit();

            unset($_SESSION["cart"]);

            header("Location: order_success.php?id=" . $order_id);
            exit;

        } catch (Exception $e) {

            $conn->rollback();

            $message = $e->getMessage();
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout | BUNKO SPACE</title>
</head>
<body>

    <h1>Checkout</h1>

    <?php if ($message !== ""): ?>
        <p><?= htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <h2>Order Summary</h2>

    <?php foreach ($cart as $item): ?>

        <p>
            <?= htmlspecialchars($item["title"]); ?>
            × <?= $item["quantity"]; ?>
            =
            Rp<?= number_format(
                $item["price"] * $item["quantity"],
                0,
                ",",
                "."
            ); ?>
        </p>

    <?php endforeach; ?>

    <h3>
        Total:
        Rp<?= number_format($total, 0, ",", "."); ?>
    </h3>

    <p>
        Payment Method:
        <strong>Cash on Delivery (COD)</strong>
    </p>

    <form method="POST" action="">

        <div>
            <label for="receiver_name">
                Receiver Name
            </label>

            <input
                type="text"
                id="receiver_name"
                name="receiver_name"
                required
            >
        </div>

        <div>
            <label for="receiver_phone">
                Phone Number
            </label>

            <input
                type="text"
                id="receiver_phone"
                name="receiver_phone"
                required
            >
        </div>

        <div>
            <label for="shipping_address">
                Shipping Address
            </label>

            <textarea
                id="shipping_address"
                name="shipping_address"
                required
            ></textarea>
        </div>

        <button type="submit">
            Place Order
        </button>

    </form>

</body>
</html>