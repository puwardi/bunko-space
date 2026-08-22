<?php

session_start();

require_once "../config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

$message_status = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $subject = trim($_POST["subject"]);
    $message = trim($_POST["message"]);

    if ($subject === "" || $message === "") {

        $message_status = "Subject and message are required.";

    } else {

        $stmt = $conn->prepare("
            INSERT INTO messages
            (user_id, subject, message)
            VALUES (?, ?, ?)
        ");

        $stmt->bind_param(
            "iss",
            $_SESSION["user_id"],
            $subject,
            $message
        );

        if ($stmt->execute()) {
            $message_status = "Message sent successfully.";
        } else {
            $message_status = "Failed to send message.";
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

    <title>Contact Us | BUNKO SPACE</title>
</head>

<body>

    <h1>Contact Us</h1>

    <p>
        Have a question or need help?
        Send a message to BUNKO SPACE.
    </p>

    <?php if ($message_status !== ""): ?>

        <p>
            <?= htmlspecialchars($message_status); ?>
        </p>

    <?php endif; ?>

    <form method="POST" action="">

        <div>

            <label>
                Name
            </label>

            <input
                type="text"
                value="<?= htmlspecialchars($_SESSION["user_name"]); ?>"
                disabled
            >

        </div>

        <div>

            <label>
                Email
            </label>

            <input
                type="email"
                value="<?= htmlspecialchars($_SESSION["user_email"]); ?>"
                disabled
            >

        </div>

        <div>

            <label for="subject">
                Subject
            </label>

            <input
                type="text"
                id="subject"
                name="subject"
                required
            >

        </div>

        <div>

            <label for="message">
                Message
            </label>

            <textarea
                id="message"
                name="message"
                rows="6"
                required
            ></textarea>

        </div>

        <button type="submit">
            Send Message
        </button>

    </form>

</body>
</html> 