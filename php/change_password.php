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
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];

    // Verify current password
    $stmt = $conn->prepare(
        "SELECT password FROM users WHERE username = ?"
    );
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();

        if ($row['password'] !== $current_password) {
            $message = "Current password is incorrect.";
        } else {

            // Update password
            $update = $conn->prepare(
                "UPDATE users SET password = ? WHERE username = ?"
            );
            $update->bind_param("ss", $new_password, $username);

            if ($update->execute()) {
                $message = "Password changed successfully.";
            } else {
                $message = "Failed to update password.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Change Password</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<header>
    <h2>Change Password</h2>
    <a href="tdashboard.php" class="nav-btn">Back</a>
</header>

<div class="card">
    <form method="post">

        <input type="password"
               class="pwd-field"
               name="current_password"
               placeholder="Current Password"
               required>
        <label class="show-pwd">
            <input type="checkbox" class="pwd-toggle">
            Show Password
        </label>

        <input type="password"
               class="pwd-field"
               name="new_password"
               placeholder="New Password"
               required>
        <label class="show-pwd">
            <input type="checkbox" class="pwd-toggle">
            Show Password
        </label>

        <button type="submit">Update Password</button>
    </form>

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