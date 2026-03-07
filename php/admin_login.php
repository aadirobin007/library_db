<?php
session_start();
include('../db/config.php'); // Your DB connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Get form values safely
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    // Query only for admin category
    $sql = "SELECT * FROM users 
            WHERE username = '$username' 
            AND password = '$password' 
            AND category = 'admin'"; // Only admins

    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) == 1) {
        $row = mysqli_fetch_assoc($result);

        // Store session
        $_SESSION['username'] = $row['username'];
        $_SESSION['category'] = $row['category'];

        // Redirect to admin dashboard
        header("Location: admin_dashboard.php");
        exit();
    } else {
        $error = "Invalid Admin Login. Please check your username and password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="login-box">
    <h2>Admin Login</h2>
    <?php if(!empty($error)) { echo "<div class='error'>$error</div>"; } ?>
    <form method="POST">
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" class="pwd-field" name="password" placeholder="Password" required>
        <label class="show-pwd">
        <input type="checkbox" class="pwd-toggle">
        Show Password
        </label>
        <button type="submit">Login</button>
    </form>
</div>
<script>
document.querySelectorAll('.pwd-toggle').forEach(toggle => {
    toggle.addEventListener('change', function () {
        const pwdInput = this.closest('form').querySelector('.pwd-field');
        if (pwdInput) {
            pwdInput.type = this.checked ? 'text' : 'password';
        }
    });
});
</script>
</body>
</html>