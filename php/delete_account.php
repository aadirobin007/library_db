<?php
session_start();
require '../db/config.php';

if (!isset($_SESSION['username'])) {
    header("Location: /library_management/login.html");
    exit();
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = $_SESSION['username'];
    $password = $_POST['password'];

    // Verify password
    $stmt = $conn->prepare(
        "SELECT password FROM users WHERE username = ?"
    );
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();

        if ($row['password'] !== $password) {
            $message = "Incorrect password.";
        } else {

            // Delete account
            $delete = $conn->prepare(
                "DELETE FROM users WHERE username = ?"
            );
            $delete->bind_param("s", $username);

            if ($delete->execute()) {
                session_destroy();
                header("Location: /library_management/login.html");
                exit();
            } else {
                $message = "Failed to delete account.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Delete Account</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<header>
    <h2>Delete Account</h2>
    <a href="tdashboard.php" class="nav-btn">Back</a>
</header>

<div class="card">
    <form method="post">

        <input type="password"
               class="pwd-field"
               name="password"
               placeholder="Confirm Password"
               required>

        <label class="show-pwd">
            <input type="checkbox" class="pwd-toggle">
            Show Password
        </label>

        <button type="submit" style="background-color:#c0392b;">
            Delete My Account
        </button>

    </form>
</div>
    <?php if ($message): ?>
        <p><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>
</div>
<script>
document.querySelectorAll('.pwd-toggle').forEach(toggle => {
    toggle.addEventListener('change', function () {
        const pwdInput = this.parentElement.previousElementSibling;
        if (pwdInput && pwdInput.classList.contains('pwd-field')) {
            pwdInput.type = this.checked ? 'text' : 'password';
        }
    });
});
</script>
</body>
</html>