<?php

session_start();

require_once "../config/database.php";

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    die("Invalid book ID.");
}

$book_id = (int) $_GET["id"];

$stmt = $conn->prepare("
    SELECT
        books.id,
        books.title,
        books.author,
        books.publisher,
        books.isbn,
        books.publish_year,
        books.price,
        books.stock,
        books.cover,
        books.description,
        books.status,
        categories.name AS category_name
    FROM books
    JOIN categories
        ON books.category_id = categories.id
    WHERE books.id = ?
    AND books.status = 'active'
");

$stmt->bind_param("i", $book_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    die("Book not found.");
}

$book = $result->fetch_assoc();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($book["title"]); ?> | BUNKO SPACE</title>
</head>
<body>

    <a href="books.php">← Back to Books</a>

    <h1><?= htmlspecialchars($book["title"]); ?></h1>

    <p>
        <strong>Category:</strong>
        <?= htmlspecialchars($book["category_name"]); ?>
    </p>

    <p>
        <strong>Author:</strong>
        <?= htmlspecialchars($book["author"]); ?>
    </p>

    <p>
        <strong>Publisher:</strong>
        <?= htmlspecialchars($book["publisher"] ?? "-"); ?>
    </p>

    <p>
        <strong>ISBN:</strong>
        <?= htmlspecialchars($book["isbn"] ?? "-"); ?>
    </p>

    <p>
        <strong>Publish Year:</strong>
        <?= htmlspecialchars($book["publish_year"] ?? "-"); ?>
    </p>

    <p>
        <strong>Price:</strong>
        Rp<?= number_format($book["price"], 0, ",", "."); ?>
    </p>

    <p>
        <strong>Stock:</strong>
        <?= $book["stock"]; ?>
    </p>

    <p>
        <strong>Description:</strong><br>
        <?= nl2br(htmlspecialchars($book["description"] ?? "-")); ?>
    </p>

</body>
</html>

<?php if ($book["stock"] > 0): ?>
    <form method="POST" action="add_to_cart.php">
        <input
            type="hidden"
            name="book_id"
            value="<?= $book["id"]; ?>"
        >

        <label for="quantity">Quantity</label>

        <input
            type="number"
            id="quantity"
            name="quantity"
            min="1"
            max="<?= $book["stock"]; ?>"
            value="1"
            required
        >

        <button type="submit">
            Add to Cart
        </button>
    </form>
<?php else: ?>
    <p>Out of stock.</p>
<?php endif; ?>