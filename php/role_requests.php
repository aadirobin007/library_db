<?php
session_start();
require '../db/config.php';

// Only admin can access
if (!isset($_SESSION['username']) || $_SESSION['category'] != 'admin') {
    http_response_code(403);
    echo "Access denied";
    exit();
}

// Handle Accept / Deny actions
if (isset($_POST['username'], $_POST['action'])) {
    $username = $_POST['username'];
    $action = $_POST['action'];

    if ($action === 'accept') {
        $stmt = $conn->prepare("UPDATE users SET category='Teacher_Admin' WHERE username=?");
        $stmt->bind_param("s", $username);
        $stmt->execute();

        $stmt = $conn->prepare("UPDATE category_requests SET status='accepted' WHERE username=?");
        $stmt->bind_param("s", $username);
        $stmt->execute();

        $message = "Accepted $username";
    } elseif ($action === 'deny') {
        $stmt = $conn->prepare("UPDATE category_requests SET status='denied' WHERE username=?");
        $stmt->bind_param("s", $username);
        $stmt->execute();

        $message = "Denied $username";
    }
}

// Fetch pending requests
$stmt = $conn->prepare("SELECT username, requested_role FROM role_requests WHERE status='pending'");
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Role Requests</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<h2 class="center-text">Pending Role Requests</h2>

<?php if (!empty($message)) echo "<div class='message center-text'>$message</div>"; ?>

<table class="role-requests-table">
    <tr>
        <th>Username</th>
        <th>Requested Role</th>
        <th>Actions</th>
    </tr>
    <?php if($result->num_rows > 0): ?>
        <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['username']); ?></td>
                <td><?php echo htmlspecialchars($row['requested_role']); ?></td>
                <td>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="username" value="<?php echo $row['username']; ?>">
                        <button type="submit" name="action" value="accept">Accept</button>
                    </form>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="username" value="<?php echo $row['username']; ?>">
                        <button type="submit" name="action" value="deny">Deny</button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr><td colspan="3">No pending requests.</td></tr>
    <?php endif; ?>
</table>

</body>
</html>