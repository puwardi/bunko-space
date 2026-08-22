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

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"]);
    $description = trim($_POST["description"]);

    if ($name === "") {

        $message = "Category name is required.";

    } else {

        $stmt = $conn->prepare("
            INSERT INTO categories
            (name, description)
            VALUES (?, ?)
        ");

        $stmt->bind_param(
            "ss",
            $name,
            $description
        );

        if ($stmt->execute()) {

            header("Location: categories.php");
            exit;

        } else {

            if ($conn->errno === 1062) {
                $message = "Category already exists.";
            } else {
                $message = "Failed to add category.";
            }
        }

        $stmt->close();
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Category | BUNKO SPACE</title>
</head>
<body>

    <h1>Add Category</h1>

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
                required
            >
        </div>

        <div>
            <label for="description">Description</label>

            <textarea
                id="description"
                name="description"
                rows="5"
            ></textarea>
        </div>

        <button type="submit">
            Add Category
        </button>

    </form>

</body>
</html>