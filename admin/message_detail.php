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
    die("Invalid message ID.");
}

$message_id = (int) $_GET["id"];

$stmt = $conn->prepare("
    SELECT
        messages.id,
        messages.subject,
        messages.message,
        messages.is_read,
        messages.created_at,
        users.name AS sender_name,
        users.email AS sender_email
    FROM messages
    JOIN users
        ON messages.user_id = users.id
    WHERE messages.id = ?
");

$stmt->bind_param("i", $message_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    die("Message not found.");
}

$message = $result->fetch_assoc();

if (!$message["is_read"]) {

    $update = $conn->prepare("
        UPDATE messages
        SET is_read = 1
        WHERE id = ?
    ");

    $update->bind_param("i", $message_id);
    $update->execute();
    $update->close();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Message Detail | BUNKO SPACE</title>
</head>
<body>

    <h1>Message Detail</h1>

    <a href="messages.php">← Back to Messages</a>

    <hr>

    <p>
        <strong>From:</strong>
        <?= htmlspecialchars($message["sender_name"]); ?>
    </p>

    <p>
        <strong>Email:</strong>
        <?= htmlspecialchars($message["sender_email"]); ?>
    </p>

    <p>
        <strong>Subject:</strong>
        <?= htmlspecialchars($message["subject"]); ?>
    </p>

    <p>
        <strong>Sent At:</strong>
        <?= htmlspecialchars($message["created_at"]); ?>
    </p>

    <hr>

    <p>
        <?= nl2br(htmlspecialchars($message["message"])); ?>
    </p>

</body>
</html>