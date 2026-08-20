<?php

session_start();

require_once "../config/database.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: cart.php");
    exit;
}

$book_id = isset($_POST["book_id"])
    ? (int) $_POST["book_id"]
    : 0;

$quantity = isset($_POST["quantity"])
    ? (int) $_POST["quantity"]
    : 0;

if (
    $book_id <= 0 ||
    $quantity <= 0 ||
    !isset($_SESSION["cart"][$book_id])
) {
    die("Invalid cart update.");
}

$stmt = $conn->prepare("
    SELECT stock, status
    FROM books
    WHERE id = ?
");

$stmt->bind_param("i", $book_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    die("Book not found.");
}

$book = $result->fetch_assoc();

if ($book["status"] !== "active") {
    die("Book is unavailable.");
}

if ($quantity > $book["stock"]) {
    die("Requested quantity exceeds available stock.");
}

$_SESSION["cart"][$book_id]["quantity"] = $quantity;

header("Location: cart.php");
exit;