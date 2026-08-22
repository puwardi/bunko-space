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
        messages.id,
        messages.subject,
        messages.is_read,
        messages.created_at,
        users.name AS sender_name,
        users.email AS sender_email
    FROM messages
    JOIN users
        ON messages.user_id = users.id
    ORDER BY messages.id DESC
");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages | BUNKO SPACE</title>
</head>
<body>

    <h1>Messages</h1>

    <a href="dashboard.php">← Dashboard</a>

    <hr>

    <?php if ($result->num_rows > 0): ?>

        <table border="1" cellpadding="10">

            <thead>
                <tr>
                    <th>ID</th>
                    <th>Sender</th>
                    <th>Subject</th>
                    <th>Status</th>
                    <th>Sent At</th>
                    <th>Action</th>
                </tr>
            </thead>

            <tbody>

                <?php while ($message = $result->fetch_assoc()): ?>

                    <tr>

                        <td>
                            <?= $message["id"]; ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($message["sender_name"]); ?>
                            <br>
                            <?= htmlspecialchars($message["sender_email"]); ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($message["subject"]); ?>
                        </td>

                        <td>
                            <?= $message["is_read"] ? "Read" : "Unread"; ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($message["created_at"]); ?>
                        </td>

                        <td>
                            <a href="message_detail.php?id=<?= $message["id"]; ?>">
                                View
                            </a>
                        </td>

                    </tr>

                <?php endwhile; ?>

            </tbody>

        </table>

    <?php else: ?>

        <p>No messages found.</p>

    <?php endif; ?>

</body>
</html>