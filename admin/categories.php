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
    SELECT id, name, description, created_at
    FROM categories
    ORDER BY id DESC
");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories | BUNKO SPACE</title>
</head>
<body>

    <h1>Book Categories</h1>

    <a href="dashboard.php">← Dashboard</a>

    <br><br>

    <a href="category_add.php">
        + Add Category
    </a>

    <hr>

    <?php if ($result->num_rows > 0): ?>

        <table border="1" cellpadding="10">

            <thead>
                <tr>
                    <th>ID</th>
                    <th>Category</th>
                    <th>Description</th>
                    <th>Created At</th>
                    <th>Action</th>
                </tr>
            </thead>

            <tbody>

                <?php while ($category = $result->fetch_assoc()): ?>

                    <tr>

                        <td>
                            <?= $category["id"]; ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($category["name"]); ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($category["description"] ?? "-"); ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($category["created_at"]); ?>
                        </td>

                        <td>

                            <a href="category_edit.php?id=<?= $category["id"]; ?>">
                                Edit
                            </a>

                            |

                            <a href="category_delete.php?id=<?= $category["id"]; ?>">
                                Delete
                            </a>

                        </td>

                    </tr>

                <?php endwhile; ?>

            </tbody>

        </table>

    <?php else: ?>

        <p>No categories found.</p>

    <?php endif; ?>

</body>
</html> 