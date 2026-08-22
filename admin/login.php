<?php

session_start();

require_once "../config/database.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    $stmt = $conn->prepare("
        SELECT id, name, email, password, role
        FROM users
        WHERE email = ?
    ");

    $stmt->bind_param("s", $email);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows === 1) {

        $user = $result->fetch_assoc();

        if (!password_verify($password, $user["password"])) {

            $message = "Incorrect password.";

        } elseif ($user["role"] !== "admin") {

            $message = "You do not have admin access.";

        } else {

            $_SESSION["admin_id"] = $user["id"];
            $_SESSION["admin_name"] = $user["name"];
            $_SESSION["admin_email"] = $user["email"];
            $_SESSION["admin_role"] = $user["role"];

            header("Location: dashboard.php");
            exit;
        }

    } else {

        $message = "Admin account not found.";
    }

    $stmt->close();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | BUNKO SPACE</title>
</head>
<body>

    <h1>Admin Login</h1>

    <?php if ($message !== ""): ?>
        <p><?= htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <form method="POST" action="">

        <div>
            <label for="email">Email</label>

            <input
                type="email"
                id="email"
                name="email"
                required
            >
        </div>

        <div>
            <label for="password">Password</label>

            <input
                type="password"
                id="password"
                name="password"
                required
            >
        </div>

        <button type="submit">
            Login as Admin
        </button>

    </form>

</body>
</html>