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

$total_books = $conn->query("
    SELECT COUNT(*) AS total
    FROM books
")->fetch_assoc()["total"];

$total_categories = $conn->query("
    SELECT COUNT(*) AS total
    FROM categories
")->fetch_assoc()["total"];

$total_users = $conn->query("
    SELECT COUNT(*) AS total
    FROM users
    WHERE role = 'user'
")->fetch_assoc()["total"];

$total_orders = $conn->query("
    SELECT COUNT(*) AS total
    FROM orders
")->fetch_assoc()["total"];

$unread_messages = $conn->query("
    SELECT COUNT(*) AS total
    FROM messages
    WHERE is_read = 0
")->fetch_assoc()["total"];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | BUNKO SPACE</title>
</head>
<body>

    <h1>
        Welcome Admin,
        <?= htmlspecialchars($_SESSION["admin_name"]); ?>!
    </h1>

    <p>BUNKO SPACE Admin Dashboard</p>

    <nav>
        <a href="categories.php">Categories</a>
        |
        <a href="books.php">Books</a>
        |
        <a href="users.php">Users</a>
        |
        <a href="orders.php">Orders</a>
        |
        <a href="messages.php">Messages</a>
    </nav>

    <hr>

    <h2>Dashboard Summary</h2>

    <div>
        <p>
            Total Books:
            <strong><?= $total_books; ?></strong>
        </p>

        <p>
            Categories:
            <strong><?= $total_categories; ?></strong>
        </p>

        <p>
            Registered Users:
            <strong><?= $total_users; ?></strong>
        </p>

        <p>
            Orders:
            <strong><?= $total_orders; ?></strong>
        </p>

        <p>
            Unread Messages:
            <strong><?= $unread_messages; ?></strong>
        </p>
    </div>

    <hr>

    <a href="logout.php">Logout</a>

</body>
</html>