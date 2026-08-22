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
    SELECT *
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

$categories = $conn->query("
    SELECT id, name
    FROM categories
    ORDER BY name ASC
");

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $category_id = (int) $_POST["category_id"];
    $title = trim($_POST["title"]);
    $author = trim($_POST["author"]);
    $publisher = trim($_POST["publisher"]);
    $isbn = trim($_POST["isbn"]);
    $publish_year = trim($_POST["publish_year"]);
    $price = (float) $_POST["price"];
    $stock = (int) $_POST["stock"];
    $description = trim($_POST["description"]);
    $status = $_POST["status"];

    $cover = $book["cover"];

    if (
        isset($_FILES["cover"]) &&
        $_FILES["cover"]["error"] === UPLOAD_ERR_OK
    ) {
        $allowed_extensions = ["jpg", "jpeg", "png", "webp"];

        $original_name = $_FILES["cover"]["name"];
        $tmp_name = $_FILES["cover"]["tmp_name"];

        $extension = strtolower(
            pathinfo($original_name, PATHINFO_EXTENSION)
        );

        if (!in_array($extension, $allowed_extensions)) {

            $message = "Invalid cover file type.";

        } else {

            $new_cover =
                uniqid("book_", true) . "." . $extension;

            $upload_path =
                "../assets/uploads/" . $new_cover;

            if (move_uploaded_file($tmp_name, $upload_path)) {

                if (
                    $book["cover"] !== null &&
                    $book["cover"] !== ""
                ) {
                    $old_cover_path =
                        "../assets/uploads/" . $book["cover"];

                    if (file_exists($old_cover_path)) {
                        unlink($old_cover_path);
                    }
                }

                $cover = $new_cover;

            } else {

                $message = "Failed to upload new cover.";
            }
        }
    }

    if ($message === "") {

        $stmt_update = $conn->prepare("
            UPDATE books
            SET
                category_id = ?,
                title = ?,
                author = ?,
                publisher = ?,
                isbn = ?,
                publish_year = ?,
                price = ?,
                stock = ?,
                cover = ?,
                description = ?,
                status = ?
            WHERE id = ?
        ");

        $stmt_update->bind_param(
            "isssssdisssi",
            $category_id,
            $title,
            $author,
            $publisher,
            $isbn,
            $publish_year,
            $price,
            $stock,
            $cover,
            $description,
            $status,
            $book_id
        );

        if ($stmt_update->execute()) {
            header("Location: books.php");
            exit;
        } else {
            $message = "Failed to update book.";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Book | BUNKO SPACE</title>
</head>
<body>

    <h1>Edit Book</h1>

    <a href="books.php">← Back</a>

    <?php if ($message !== ""): ?>
        <p><?= htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <form
        method="POST"
        action=""
        enctype="multipart/form-data"
    >

        <div>
            <label for="category_id">Category</label>

            <select
                id="category_id"
                name="category_id"
                required
            >

                <?php while ($category = $categories->fetch_assoc()): ?>

                    <option
                        value="<?= $category["id"]; ?>"
                        <?= $category["id"] == $book["category_id"]
                            ? "selected"
                            : ""; ?>
                    >
                        <?= htmlspecialchars($category["name"]); ?>
                    </option>

                <?php endwhile; ?>

            </select>
        </div>

                <div>
            <label for="title">Title</label>

            <input
                type="text"
                id="title"
                name="title"
                value="<?= htmlspecialchars($book["title"]); ?>"
                required
            >
        </div>

        <div>
            <label for="author">Author</label>

            <input
                type="text"
                id="author"
                name="author"
                value="<?= htmlspecialchars($book["author"]); ?>"
                required
            >
        </div>

        <div>
            <label for="publisher">Publisher</label>

            <input
                type="text"
                id="publisher"
                name="publisher"
                value="<?= htmlspecialchars($book["publisher"] ?? ""); ?>"
            >
        </div>

        <div>
            <label for="isbn">ISBN</label>

            <input
                type="text"
                id="isbn"
                name="isbn"
                value="<?= htmlspecialchars($book["isbn"] ?? ""); ?>"
            >
        </div>

                <div>
            <label for="publish_year">Publish Year</label>

            <input
                type="number"
                id="publish_year"
                name="publish_year"
                value="<?= htmlspecialchars($book["publish_year"] ?? ""); ?>"
            >
        </div>

        <div>
            <label for="price">Price</label>

            <input
                type="number"
                id="price"
                name="price"
                value="<?= $book["price"]; ?>"
                min="0"
                step="0.01"
                required
            >
        </div>

        <div>
            <label for="stock">Stock</label>

            <input
                type="number"
                id="stock"
                name="stock"
                value="<?= $book["stock"]; ?>"
                min="0"
                required
            >
        </div>

                <div>
            <label for="description">Description</label>

            <textarea
                id="description"
                name="description"
                rows="5"
            ><?= htmlspecialchars($book["description"] ?? ""); ?></textarea>
        </div>

        <div>
            <label for="status">Status</label>

            <select
                id="status"
                name="status"
                required
            >
                <option
                    value="active"
                    <?= $book["status"] === "active"
                        ? "selected"
                        : ""; ?>
                >
                    Active
                </option>

                <option
                    value="inactive"
                    <?= $book["status"] === "inactive"
                        ? "selected"
                        : ""; ?>
                >
                    Inactive
                </option>
            </select>
        </div>

        <div>
            <label for="cover">
                Replace Cover
            </label>

            <input
                type="file"
                id="cover"
                name="cover"
                accept=".jpg,.jpeg,.png,.webp"
            >
        </div>

        <button type="submit">
            Update Book
        </button>

    </form>

</body>
</html>