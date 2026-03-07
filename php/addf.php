<?php
session_start();
require '../db/config.php';

/* ---------- ACCESS CONTROL ---------- */
if (!isset($_SESSION['username']) || $_SESSION['category'] !== 'Teacher_Admin') {
    die("Access denied");
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $faculty_username = trim($_POST['username']);
    $added_by = $_SESSION['username'];

    if ($faculty_username === "") {
        $message = "Username cannot be empty";
    } else {

        // check if already in faculties
        $check = $conn->prepare(
            "SELECT id FROM faculties WHERE username = ?"
        );
        $check->bind_param("s", $faculty_username);
        $check->execute();
        $res = $check->get_result();

        if ($res->num_rows > 0) {
            $message = "This username is already allowed as faculty";
        } else {

            // add to allowed faculty list
            $insert = $conn->prepare(
                "INSERT INTO faculties (username, added_by) VALUES (?, ?)"
            );
            $insert->bind_param("ss", $faculty_username, $added_by);

            if ($insert->execute()) {
                $message = "Faculty username added successfully";
            } else {
                $message = "Database error";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Faculty</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<header>
    <h2>Add Faculty (Allowed Teachers)</h2>
    <a href="tdashboard.php" class="nav-btn">Back</a>
</header>

<div class="card">
    <form method="post">
        <input type="text" name="username" placeholder="Faculty Username" required>
        <button type="submit">Add</button>
    </form>

    <?php if ($message): ?>
        <p><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>
</div>

</body>
</html>