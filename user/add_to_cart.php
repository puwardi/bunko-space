<?php

session_start();

require_once "../config/database.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: books.php");
    exit;
}

if (!isset($_POST["book_id"], $_POST["quantity"])) {
    die("Invalid cart request.");
}

$book_id = (int) $_POST["book_id"];
$quantity = (int) $_POST["quantity"];

if ($book_id <= 0 || $quantity <= 0) {
    die("Invalid book or quantity.");
}

$stmt = $conn->prepare("
    SELECT id, title, price, stock, status
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

if (!isset($_SESSION["cart"])) {
    $_SESSION["cart"] = [];
}

if (isset($_SESSION["cart"][$book_id])) {

    $new_quantity =
        $_SESSION["cart"][$book_id]["quantity"] + $quantity;

    if ($new_quantity > $book["stock"]) {
        die("Total quantity exceeds available stock.");
    }

    $_SESSION["cart"][$book_id]["quantity"] = $new_quantity;

} else {

    $_SESSION["cart"][$book_id] = [
        "book_id" => $book["id"],
        "title" => $book["title"],
        "price" => $book["price"],
        "quantity" => $quantity
    ];
}

header("Location: cart.php");
exit;