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
    die("Invalid book ID.");
}

$book_id = (int) $_GET["id"];

$stmt = $conn->prepare("
    SELECT id, title, cover
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

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $check = $conn->prepare("
        SELECT COUNT(*) AS total
        FROM order_details
        WHERE book_id = ?
    ");

    $check->bind_param("i", $book_id);
    $check->execute();

    $order_count = $check
        ->get_result()
        ->fetch_assoc()["total"];

    if ($order_count > 0) {

        $message =
            "This book cannot be deleted because it already exists in order history.";

    } else {

        $delete = $conn->prepare("
            DELETE FROM books
            WHERE id = ?
        ");

        $delete->bind_param("i", $book_id);

        if ($delete->execute()) {

            if (
                $book["cover"] !== null &&
                $book["cover"] !== ""
            ) {
                $cover_path =
                    "../assets/uploads/" . $book["cover"];

                if (file_exists($cover_path)) {
                    unlink($cover_path);
                }
            }

            header("Location: books.php");
            exit;

        } else {

            $message = "Failed to delete book.";
        }

        $delete->close();
    }

    $check->close();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Book | BUNKO SPACE</title>
</head>
<body>

    <h1>Delete Book</h1>

    <?php if ($message !== ""): ?>
        <p><?= htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <p>
        Are you sure you want to delete
        <strong><?= htmlspecialchars($book["title"]); ?></strong>?
    </p>

    <form method="POST" action="">
        <button type="submit">
            Yes, Delete
        </button>
    </form>

    <a href="books.php">
        Cancel
    </a>

</body>
</html>