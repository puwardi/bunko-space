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
        id,
        name,
        email,
        created_at
    FROM users
    WHERE role = 'user'
    ORDER BY id DESC
");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registered Users | BUNKO SPACE</title>
</head>
<body>

    <h1>Registered Users</h1>

    <a href="dashboard.php">← Dashboard</a>

    <hr>

    <?php if ($result->num_rows > 0): ?>

        <table border="1" cellpadding="10">

            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Registered At</th>
                </tr>
            </thead>

            <tbody>

                <?php while ($user = $result->fetch_assoc()): ?>

                    <tr>

                        <td>
                            <?= $user["id"]; ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($user["name"]); ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($user["email"]); ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($user["created_at"]); ?>
                        </td>

                    </tr>

                <?php endwhile; ?>

            </tbody>

        </table>

    <?php else: ?>

        <p>No registered users found.</p>

    <?php endif; ?>

</body>
</html>