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

$categories = $conn->query("
    SELECT id, name
    FROM categories
    ORDER BY name ASC
");

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

    $cover = null;

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

        $cover = uniqid("book_", true) . "." . $extension;

        $upload_path =
            "../assets/uploads/" . $cover;

        if (!move_uploaded_file($tmp_name, $upload_path)) {
            $message = "Failed to upload book cover.";
        }
    }
}

    if (
        $category_id <= 0 ||
        $title === "" ||
        $author === "" ||
        $price < 0 ||
        $stock < 0
    ) {
        $message = "Please fill in all required fields correctly.";
    } else {

        $stmt = $conn->prepare("
    INSERT INTO books
    (
        category_id,
        title,
        author,
        publisher,
        isbn,
        publish_year,
        price,
        stock,
        cover,
        description,
        status
    )
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");                     


        $stmt->bind_param(
    "isssssdisss",
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
    $status
);


        if ($stmt->execute()) {
            header("Location: books.php");
            exit;
        } else {
            $message = "Failed to add book.";
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
    <title>Add Book | BUNKO SPACE</title>
</head>
<body>

    <h1>Add Book</h1>

    <a href="books.php">← Back</a>

    <?php if ($message !== ""): ?>
        <p><?= htmlspecialchars($message); ?></p>
    <?php endif; ?>

<form method="POST" action="" enctype="multipart/form-data">

        <div>
            <label for="category_id">Category</label>

            <select
                id="category_id"
                name="category_id"
                required
            >
                <option value="">
                    -- Select Category --
                </option>

                <?php while ($category = $categories->fetch_assoc()): ?>
                    <option value="<?= $category["id"]; ?>">
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
                required
            >
        </div>

        <div>
            <label for="author">Author</label>

            <input
                type="text"
                id="author"
                name="author"
                required
            >
        </div>

        <div>
            <label for="publisher">Publisher</label>

            <input
                type="text"
                id="publisher"
                name="publisher"
            >
        </div>

        <div>
            <label for="isbn">ISBN</label>

            <input
                type="text"
                id="isbn"
                name="isbn"
            >
        </div>

        <div>
            <label for="publish_year">Publish Year</label>

            <input
                type="number"
                id="publish_year"
                name="publish_year"
                min="1000"
                max="9999"
            >
        </div>

        <div>
            <label for="price">Price</label>

            <input
                type="number"
                id="price"
                name="price"
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
            ></textarea>
        </div>

        <div>
            <label for="status">Status</label>

            <select
                id="status"
                name="status"
                required
            >
                <option value="active">
                    Active
                </option>

                <option value="inactive">
                    Inactive
                </option>
            </select>
        </div>

        <div>
            <label for="cover">Cover</label>

            <input
            type="file"
            id="cover"
            name="cover"
            accept=".jpg,.jpeg,.png,.webp"
            >

        </div>

        <button type="submit">
            Add Book
        </button>

    </form>

</body>
</html>