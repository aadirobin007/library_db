<?php
session_start();
require '../db/config.php';

/* Only allow Admin */
if (!isset($_SESSION['username']) || $_SESSION['category'] !== 'admin') {
    header("Location: ../admin_login.html");
    exit();
}

/* Fetch users */
$result = $conn->query("SELECT id, username, category FROM users");

/* Dashboard Statistics */
$total_books = $conn->query("SELECT SUM(quantity) as total FROM books")->fetch_assoc()['total'] ?? 0;
$total_users = $conn->query("SELECT COUNT(*) as total FROM users")->fetch_assoc()['total'] ?? 0;
$active_borrows = $conn->query("SELECT COUNT(*) as total FROM borrowed_books WHERE status='borrowed'")->fetch_assoc()['total'] ?? 0;
$overdue_books = $conn->query("SELECT COUNT(*) as total FROM borrowed_books WHERE status='borrowed' AND due_date < CURDATE()")->fetch_assoc()['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<header class="top-header">
    <h1>Admin Dashboard</h1>
    <div>
        <a href="../home.html" class="nav-btn">Back Home</a>

        <a href="role_requests.php" class="nav-btn">Role Request</a>

        <a href="logout.php" class="nav-btn">Logout</a>
    </div>
</header>

<!-- Dashboard Statistics Cards -->
<div class="card-container" style="margin:20px auto; max-width:1000px;">
    <div class="card">
        <h3>Total Books</h3>
        <p style="font-size:32px; font-weight:bold; color:#1e3c72;"><?php echo $total_books; ?></p>
    </div>
    <div class="card">
        <h3>Total Users</h3>
        <p style="font-size:32px; font-weight:bold; color:#1e3c72;"><?php echo $total_users; ?></p>
    </div>
    <div class="card">
        <h3>Active Borrows</h3>
        <p style="font-size:32px; font-weight:bold; color:#28a745;"><?php echo $active_borrows; ?></p>
    </div>
    <div class="card">
        <h3>Overdue Books</h3>
        <p style="font-size:32px; font-weight:bold; color:#dc3545;"><?php echo $overdue_books; ?></p>
    </div>
</div>

<h2 style="text-align:center;">Manage Users</h2>

<!-- Messages -->
<?php
if (isset($_SESSION['error'])) {
    echo "<p style='color:red; text-align:center'>" . $_SESSION['error'] . "</p>";
    unset($_SESSION['error']);
}
if (isset($_SESSION['success'])) {
    echo "<p style='color:green; text-align:center'>" . $_SESSION['success'] . "</p>";
    unset($_SESSION['success']);
}
?>

<!-- Add User -->
<div class="reg">
    <form method="post" action="admin_actions.php">
        <input type="text" name="username" placeholder="Username" required>

        <input type="password" id="new_password" name="password" placeholder="Password" required>

        <label>
            <input type="checkbox"
                onclick="document.getElementById('new_password').type =
                this.checked ? 'text' : 'password'">
            Show Password
        </label>

        <select name="category" required>
    <option value="Student">Student</option>
    <option value="Teacher">Teacher</option>
    <option value="Teacher_Admin">Teacher_Admin</option>
    <option value="Librarian">Librarian</option>
    <option value="Library_Assistant">Library Assistant</option>
</select>

        <button type="submit" name="action" value="add">Add User</button>
    </form>
</div>

<!-- Users Table -->
<table border="1" cellpadding="8" cellspacing="0" style="margin:auto;">
    <tr>
        <th>Username</th>
        <th>Category</th>
        <th>Actions</th>
    </tr>

<?php while ($row = $result->fetch_assoc()): ?>
    <tr>
        <td><?= htmlspecialchars($row['username']) ?></td>
        <td><?= htmlspecialchars($row['category']) ?></td>
        <td>

            <!-- Edit Role -->
            <form method="post" action="admin_actions.php" style="display:inline;">
                <input type="hidden" name="user_id" value="<?= $row['id'] ?>">
                <select name="category">
                    <option value="Student" <?= $row['category']=='Student'?'selected':'' ?>>Student</option>
                    <option value="Teacher" <?= $row['category']=='Teacher'?'selected':'' ?>>Teacher</option>
                    <option value="Teacher_Admin" <?= $row['category']=='Teacher_Admin'?'selected':'' ?>>Teacher_Admin</option>
                    <option value="Admin" <?= $row['category']=='Admin'?'selected':'' ?>>Admin</option>
                </select>
                <button type="submit" name="action" value="edit">Update</button>
            </form>

            <!-- Delete User -->
            <?php if ($row['username'] !== $_SESSION['username']): ?>
            <form method="post" action="admin_actions.php" style="display:inline;"
                  onsubmit="return confirm('Delete this user?');">
                <input type="hidden" name="user_id" value="<?= $row['id'] ?>">
                <button type="submit" name="action" value="delete">Delete</button>
            </form>
            <?php else: ?>
                <span style="color:gray;">(You)</span>
            <?php endif; ?>

        </td>
    </tr>
<?php endwhile; ?>
</table>

</body>
</html>