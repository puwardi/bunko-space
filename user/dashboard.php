<?php

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard | BUNKO SPACE</title>
</head>
<body>

    <h1>
        Welcome, <?= htmlspecialchars($_SESSION["user_name"]); ?>!
    </h1>

    <p>You have successfully logged in to BUNKO SPACE.</p>

    <a href="../auth/logout.php">Logout</a>
</body>
</html>