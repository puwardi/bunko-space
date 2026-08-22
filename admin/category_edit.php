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
    SELECT id, name, description
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

    $name = trim($_POST["name"]);
    $description = trim($_POST["description"]);

    if ($name === "") {
        $message = "Category name is required.";
    } else {

        $stmt_update = $conn->prepare("
            UPDATE categories
            SET name = ?, description = ?
            WHERE id = ?
        ");

        $stmt_update->bind_param(
            "ssi",
            $name,
            $description,
            $category_id
        );

        if ($stmt_update->execute()) {
            header("Location: categories.php");
            exit;
        } else {
            if ($conn->errno === 1062) {
                $message = "Category name already exists.";
            } else {
                $message = "Failed to update category.";
            }
        }

        $stmt_update->close();
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Category | BUNKO SPACE</title>
</head>
<body>

    <h1>Edit Category</h1>

    <a href="categories.php">← Back</a>

    <?php if ($message !== ""): ?>
        <p><?= htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <form method="POST" action="">

        <div>
            <label for="name">Category Name</label>

            <input
                type="text"
                id="name"
                name="name"
                value="<?= htmlspecialchars($category["name"]); ?>"
                required
            >
        </div>

        <div>
            <label for="description">Description</label>

            <textarea
                id="description"
                name="description"
                rows="5"
            ><?= htmlspecialchars($category["description"] ?? ""); ?></textarea>
        </div>

        <button type="submit">
            Update Category
        </button>

    </form>

</body>
</html>