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

$result = $conn->query("
    SELECT
        books.id,
        books.title,
        books.author,
        books.price,
        books.stock,
        books.status,
        books.created_at,
        categories.name AS category_name
    FROM books
    JOIN categories
        ON books.category_id = categories.id
    ORDER BY books.id DESC
");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Books Management | BUNKO SPACE</title>
</head>
<body>

    <h1>Books Management</h1>

    <a href="dashboard.php">← Dashboard</a>

    <br><br>

    <a href="book_add.php">
        + Add Book
    </a>

    <hr>

    <?php if ($result->num_rows > 0): ?>

        <table border="1" cellpadding="10">

            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Author</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Status</th>
                    <th>Created At</th>
                    <th>Action</th>
                </tr>
            </thead>

            <tbody>

                <?php while ($book = $result->fetch_assoc()): ?>

                    <tr>

                        <td>
                            <?= $book["id"]; ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($book["title"]); ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($book["category_name"]); ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($book["author"]); ?>
                        </td>

                        <td>
                            Rp<?= number_format(
                                $book["price"],
                                0,
                                ",",
                                "."
                            ); ?>
                        </td>

                        <td>
                            <?= $book["stock"]; ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($book["status"]); ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($book["created_at"]); ?>
                        </td>

                        <td>

                            <a href="book_edit.php?id=<?= $book["id"]; ?>">
                                Edit
                            </a>

                            |

                            <a href="book_delete.php?id=<?= $book["id"]; ?>">
                                Delete
                            </a>

                        </td>

                    </tr>

                <?php endwhile; ?>

            </tbody>

        </table>

    <?php else: ?>

        <p>No books found.</p>

    <?php endif; ?>

</body>
</html>