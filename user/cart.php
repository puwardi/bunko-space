<?php

session_start();

$cart = $_SESSION["cart"] ?? [];

$total = 0;

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart | BUNKO SPACE</title>
</head>
<body>

    <h1>Shopping Cart</h1>

    <a href="books.php">← Continue Shopping</a>

    <?php if (empty($cart)): ?>

        <p>Your cart is empty.</p>

    <?php else: ?>

<?php foreach ($cart as $item): ?>

    <?php
    $subtotal = $item["price"] * $item["quantity"];
    $total += $subtotal;
    ?>

    <div>

        <h2>
            <?= htmlspecialchars($item["title"]); ?>
        </h2>

        <p>
            Price:
            Rp<?= number_format($item["price"], 0, ",", "."); ?>
        </p>

        <form method="POST" action="update_cart.php">

            <input
                type="hidden"
                name="book_id"
                value="<?= $item["book_id"]; ?>"
            >

            <label for="quantity_<?= $item["book_id"]; ?>">
                Quantity
            </label>

            <input
                type="number"
                id="quantity_<?= $item["book_id"]; ?>"
                name="quantity"
                value="<?= $item["quantity"]; ?>"
                min="1"
                required
            >

            <button type="submit">
                Update
            </button>

        </form>

        <form method="POST" action="remove_from_cart.php">

            <input
                type="hidden"
                name="book_id"
                value="<?= $item["book_id"]; ?>"
            >

            <button type="submit">
                Remove
            </button>

        </form>

        <p>
            Subtotal:
            Rp<?= number_format($subtotal, 0, ",", "."); ?>
        </p>

        <hr>

    </div>

<?php endforeach; ?>

        <h2>
            Total:
            Rp<?= number_format($total, 0, ",", "."); ?>
        </h2>

        <a href="checkout.php">
            Checkout
        </a>
    <?php endif; ?>

</body>
</html>