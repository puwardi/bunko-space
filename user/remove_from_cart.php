<?php

session_start();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: cart.php");
    exit;
}

$book_id = isset($_POST["book_id"])
    ? (int) $_POST["book_id"]
    : 0;

if (
    $book_id > 0 &&
    isset($_SESSION["cart"][$book_id])
) {
    unset($_SESSION["cart"][$book_id]);
}

header("Location: cart.php");
exit;