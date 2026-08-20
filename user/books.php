<?php

session_start();

require_once "../config/database.php";

$search = isset($_GET["search"]) ? trim($_GET["search"]) : "";

if ($search !== "") {
    $keyword = "%" . $search . "%";

    $stmt = $conn->prepare("
        SELECT
            books.id,
            books.title,
            books.author,
            books.price,
            books.stock,
            books.cover,
            books.status,
            categories.name AS category_name
        FROM books
        JOIN categories
            ON books.category_id = categories.id
        WHERE books.status = 'active'
        AND (
            books.title LIKE ?
            OR books.author LIKE ?
            OR categories.name LIKE ?
        )
        ORDER BY books.created_at DESC
    ");

    $stmt->bind_param("sss", $keyword, $keyword, $keyword);
    $stmt->execute();

    $result = $stmt->get_result();

} else {

    $sql = "
        SELECT
            books.id,
            books.title,
            books.author,
            books.price,
            books.stock,
            books.cover,
            books.status,
            categories.name AS category_name
        FROM books
        JOIN categories
            ON books.category_id = categories.id
        WHERE books.status = 'active'
        ORDER BY books.created_at DESC
    ";

    $result = $conn->query($sql);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Books | BUNKO SPACE</title>
</head>
<body>

    <h1>Books</h1>

    <form method="GET" action="">
    <input
        type="text"
        name="search"
        placeholder="Search books..."
        value="<?= htmlspecialchars($search); ?>"
    >

    <button type="submit">Search</button>
</form>

    <?php if ($search !== ""): ?>

        <p>
        Search results for:
        <strong><?= htmlspecialchars($search); ?></strong>
        </p>

        <a href="books.php">Show all books</a>
        
    <?php endif; ?>

    <?php if ($result->num_rows > 0): ?>

        <?php while ($book = $result->fetch_assoc()): ?>

            <div>
                <h2><?= htmlspecialchars($book["title"]); ?></h2>

                <p>
                    Category:
                    <?= htmlspecialchars($book["category_name"]); ?>
                </p>

                <p>
                    Author:
                    <?= htmlspecialchars($book["author"]); ?>
                </p>

                <p>
                    Price:
                    Rp<?= number_format($book["price"], 0, ",", "."); ?>
                </p>

                <p>
                    Stock:
                    <?= $book["stock"]; ?>
                </p>

                <a href="detail.php?id=<?= $book["id"]; ?>">
                    View Detail
                
                </a>

                <hr>
            </div>

        <?php endwhile; ?>

    <?php else: ?>

        <p>No books available.</p>

    <?php endif; ?>

</body>
</html>