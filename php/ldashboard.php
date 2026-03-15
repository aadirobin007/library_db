<?php
session_start();
require '../db/config.php';

/* ACCESS CONTROL */
if (!isset($_SESSION['username']) ||
   ($_SESSION['category'] !== 'Librarian' &&
    $_SESSION['category'] !== 'Library_Assistant')) {
    header("Location: ../login.html");
    exit();
}

/* FETCH BOOKS */
$result = $conn->query("SELECT * FROM books ORDER BY id DESC");
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Librarian Dashboard</title>
<link rel="stylesheet" href="../css/style.css">
</head>

<body>
<div>
<header class="top-header">
<h2>Library Dashboard,</h2>
<span>Welcome <?php echo htmlspecialchars($_SESSION['username']); ?></span></h2>
<a href="issue_book.php" class="nav-btn">Issue Book</a>
<a href="borrowed_book.php" class="nav-btn">Borrowed Books</a>

<a href="logout.php" class="nav-btn">Logout</a>
</div>
</header>

<h2 class="center-text">Manage Books</h2>

<!-- ADD BOOK FORM -->

<div class="card">

<h3>Add New Book</h3>

<form method="POST" action="book_action.php">

<input type="text" name="title" placeholder="Book Title" required>

<input type="text" name="author" placeholder="Author" required>

<input type="text" name="category" placeholder="Category">

<input type="number" name="quantity" placeholder="Quantity" required>

<button type="submit" name="action" value="add">
Add Book
</button>

</form>

</div>


<!-- BOOK TABLE -->

<table class="role-requests-table">

<tr>
<th>ID</th>
<th>Title</th>
<th>Author</th>
<th>Category</th>
<th>Quantity</th>
<th>Actions</th>
</tr>

<?php while($row = $result->fetch_assoc()): ?>

<tr>

<td><?php echo $row['id']; ?></td>

<td><?php echo htmlspecialchars($row['title']); ?></td>

<td><?php echo htmlspecialchars($row['author']); ?></td>

<td><?php echo htmlspecialchars($row['category']); ?></td>

<td><?php echo $row['quantity']; ?></td>

<td>

<form method="POST" action="book_action.php" style="display:inline;">

<input type="hidden" name="id" value="<?php echo $row['id']; ?>">

<input type="text" name="title"
value="<?php echo htmlspecialchars($row['title']); ?>">

<input type="text" name="author"
value="<?php echo htmlspecialchars($row['author']); ?>">

<input type="text" name="category"
value="<?php echo htmlspecialchars($row['category']); ?>">

<input type="number" name="quantity"
value="<?php echo $row['quantity']; ?>">

<button type="submit" name="action" value="edit">
Update
</button>

</form>

</td>

</tr>

<?php endwhile; ?>

</table>

</body>
</html>