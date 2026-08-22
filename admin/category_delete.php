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
    die("Invalid category ID.");
}

$category_id = (int) $_GET["id"];

$stmt = $conn->prepare("
    SELECT id, name
    FROM categories
    WHERE id = ?
");

$stmt->bind_param("i", $category_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    die("Category not found.");
}

$category = $result->fetch_assoc();

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $check = $conn->prepare("
        SELECT COUNT(*) AS total
        FROM books
        WHERE category_id = ?
    ");

    $check->bind_param("i", $category_id);
    $check->execute();

    $book_count = $check
        ->get_result()
        ->fetch_assoc()["total"];

    if ($book_count > 0) {

        $message =
            "This category cannot be deleted because it is still used by books.";

    } else {

        $delete = $conn->prepare("
            DELETE FROM categories
            WHERE id = ?
        ");

        $delete->bind_param("i", $category_id);

        if ($delete->execute()) {
            header("Location: categories.php");
            exit;
        } else {
            $message = "Failed to delete category.";
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
    <title>Delete Category | BUNKO SPACE</title>
</head>
<body>

    <h1>Delete Category</h1>

    <?php if ($message !== ""): ?>
        <p><?= htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <p>
        Are you sure you want to delete
        <strong><?= htmlspecialchars($category["name"]); ?></strong>?
    </p>

    <form method="POST" action="">
        <button type="submit">
            Yes, Delete
        </button>
    </form>

    <a href="categories.php">
        Cancel
    </a>

</body>
</html>