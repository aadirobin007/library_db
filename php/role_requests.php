<?php
session_start();
require '../db/config.php';

/* 🔐 ADMIN ACCESS ONLY */
if (!isset($_SESSION['username']) || $_SESSION['category'] !== 'admin') {
    http_response_code(403);
    echo "Access denied";
    exit();
}

$message = "";

/* HANDLE ACCEPT / DENY ACTIONS */
if (isset($_POST['username'], $_POST['action'])) {

    $username = $_POST['username'];
    $action = $_POST['action'];

    if ($action === 'accept') {

        // Get requested role
        $stmt = $conn->prepare(
            "SELECT requested_role 
             FROM role_requests 
             WHERE username=? AND status='Pending' 
             LIMIT 1"
        );
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $resultRole = $stmt->get_result();

        if ($resultRole->num_rows > 0) {

            $row = $resultRole->fetch_assoc();
            $role = $row['requested_role'];

            // Update user category
            $stmt = $conn->prepare(
                "UPDATE users SET category=? WHERE username=?"
            );
            $stmt->bind_param("ss", $role, $username);
            $stmt->execute();

            // Update request status
            $stmt = $conn->prepare(
                "UPDATE role_requests 
                 SET status='Accepted' 
                 WHERE username=? AND status='Pending'"
            );
            $stmt->bind_param("s", $username);
            $stmt->execute();

            $message = "Accepted $username as $role";

        } else {

            $message = "Request not found.";

        }

    } elseif ($action === 'deny') {

        $stmt = $conn->prepare(
            "UPDATE role_requests 
             SET status='Denied' 
             WHERE username=? AND status='Pending'"
        );
        $stmt->bind_param("s", $username);
        $stmt->execute();

        $message = "Denied request from $username";

    }
}


/* FETCH PENDING REQUESTS */
$stmt = $conn->prepare(
    "SELECT username, requested_role 
     FROM role_requests 
     WHERE status='Pending'"
);
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

<?php
if (!empty($message)) {
    echo "<div class='message center-text'>$message</div>";
}
?>

<table class="role-requests-table">

<tr>
<th>Username</th>
<th>Requested Role</th>
<th>Actions</th>
</tr>

<?php if ($result->num_rows > 0): ?>

<?php while ($row = $result->fetch_assoc()): ?>

<tr>

<td><?php echo htmlspecialchars($row['username']); ?></td>

<td><?php echo htmlspecialchars($row['requested_role']); ?></td>

<td>

<form method="POST" style="display:inline;">
<input type="hidden" name="username"
value="<?php echo htmlspecialchars($row['username']); ?>">
<button type="submit" name="action" value="accept">
Accept
</button>
</form>

<form method="POST" style="display:inline;">
<input type="hidden" name="username"
value="<?php echo htmlspecialchars($row['username']); ?>">
<button type="submit" name="action" value="deny">
Deny
</button>
</form>

</td>

</tr>

<?php endwhile; ?>

<?php else: ?>

<tr>
<td colspan="3">No pending requests.</td>
</tr>

<?php endif; ?>

</table>

</body>
</html>