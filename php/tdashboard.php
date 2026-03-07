<?php
session_start();
require '../db/config.php';

/* 🔐 ACCESS CONTROL */
if (
    !isset($_SESSION['username']) ||
    ($_SESSION['category'] !== 'Teacher' && $_SESSION['category'] !== 'Teacher_Admin')
) {
    header("Location: /library_management/login.html");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Teacher Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<?php
/* 🔔 PROMOTION MESSAGE */
if (!empty($_SESSION['promotion_message'])) {

    $bg    = $_SESSION['promotion_status'] === 'approved' ? '#e6ffea' : '#ffecec';
    $color = $_SESSION['promotion_status'] === 'approved' ? '#006400' : '#900';

    echo "<div style='background:$bg;color:$color;padding:10px;margin:10px;text-align:center;'>
            {$_SESSION['promotion_message']}
          </div>";

    // Clear message from DB (show once)
    $stmt = $conn->prepare(
        "UPDATE users 
         SET promotion_message=NULL, promotion_status=NULL 
         WHERE username=?"
    );
    $stmt->bind_param("s", $_SESSION['username']);
    $stmt->execute();

    unset($_SESSION['promotion_message'], $_SESSION['promotion_status']);
}
?>


<header class="top-header">
    <h2>WELCOME <?php echo htmlspecialchars($_SESSION['username']); ?></h2>

    <div class="profile-menu">
        <button class="profile-btn" onclick="toggleProfileMenu()">
    <i class="fa-solid fa-user"></i>
    <span>Profile</span>
</button>

       <div class="profile-panel" id="profilePanel">

    <?php if ($_SESSION['category'] === 'Teacher_Admin'): ?>
        <a href="addf.php">Add Faculty</a>
    <?php elseif ($_SESSION['category'] === 'Teacher'): ?>
        <a href="submit_admin_request.php">Request Admin Privileges</a>
    <?php endif; ?>

    <a href="change_password.php">Change Password</a>

    <a href="delete_account.php"
       onclick="return confirm('Are you sure you want to delete your account?');">
       Delete Account
    </a>

    <a href="logout.php">Logout</a>
</div>

    </div>
</header>

<div class="card">
    <h2>Teacher Dashboard</h2>
    <p>The books to be returned today are:</p>

    <!-- Add Faculty Button -->
    
</div>

<script>
function toggleProfileMenu() {
    const panel = document.getElementById("profilePanel");
    panel.style.display = panel.style.display === "block" ? "none" : "block";
}

// Close panel when clicking outside
document.addEventListener("click", function (event) {
    const panel = document.getElementById("profilePanel");
    const button = document.querySelector(".profile-btn");

    if (!panel.contains(event.target) && !button.contains(event.target)) {
        panel.style.display = "none";
    }
});
</script>
</body>
</html>