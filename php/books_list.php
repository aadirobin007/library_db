<?php
session_start();
require '../db/config.php';
require 'validation.php';

if (!isset($_SESSION['username'])) {
    header("Location: ../sdashboard.php");
    exit();
}

$username = $_SESSION['username'];
$message = "";

/* SEARCH AND FILTER */
$search = isset($_GET['search']) ? sanitize_string($_GET['search']) : '';
$category_filter = isset($_GET['category']) ? sanitize_string($_GET['category']) : '';
$available_only = isset($_GET['available']) && $_GET['available'] === 'yes';

// Build query with filters
$query = "SELECT * FROM books WHERE 1=1";
$params = [];
$types = '';

if (!empty($search)) {
    $query .= " AND (title LIKE ? OR author LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'ss';
}

if (!empty($category_filter)) {
    $query .= " AND category = ?";
    $params[] = $category_filter;
    $types .= 's';
}

if ($available_only) {
    $query .= " AND quantity > 0";
}

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

/* BORROW BOOK */
if(isset($_POST['borrow'])){
    // Validate and sanitize book ID
    $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);

    if (!$id || $id <= 0) {
        $message = "Invalid book ID.";
    } else {
        $stmt = $conn->prepare("SELECT * FROM books WHERE id=?");
        $stmt->bind_param("i",$id);
        $stmt->execute();
        $result = $stmt->get_result();
        $book = $result->fetch_assoc();

        if($book && $book['quantity'] > 0){
            $title = sanitize_string($book['title']);
            $issue_date = date("Y-m-d");

            $stmt2 = $conn->prepare("INSERT INTO borrowed_books (book_id, book_title, borrower_username, borrower_role, issued_by, issue_date, status) VALUES (?, ?, ?, 'Student', 'Self Borrow', ?, 'borrowed')");
            $stmt2->bind_param("isss",$id,$title,$username,$issue_date);
            $stmt2->execute();

            /* Reduce quantity */
            $newqty = $book['quantity'] - 1;

            $stmt3 = $conn->prepare("UPDATE books SET quantity=? WHERE id=?");
            $stmt3->bind_param("ii",$newqty,$id);
            $stmt3->execute();

            $message = "Book borrowed successfully!";
        } else {
            $message = "Book not available.";
        }
    }
}

/* RESERVE BOOK */
if(isset($_POST['reserve'])){
    $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);

    if (!$id || $id <= 0) {
        $message = "Invalid book ID.";
    } else {
        $stmt = $conn->prepare("SELECT * FROM books WHERE id=? AND quantity <= 0");
        $stmt->bind_param("i",$id);
        $stmt->execute();
        $result = $stmt->get_result();
        $book = $result->fetch_assoc();

        if($book){
            // Check if already reserved
            $stmt_check = $conn->prepare("SELECT * FROM book_reservations WHERE book_id=? AND username=? AND status='pending'");
            $stmt_check->bind_param("is",$id,$username);
            $stmt_check->execute();
            $result_check = $stmt_check->get_result();

            if($result_check->num_rows > 0){
                $message = "You have already reserved this book.";
            } else {
                $stmt_insert = $conn->prepare("INSERT INTO book_reservations (book_id, book_title, username, reserve_date, status) VALUES (?, ?, ?, ?, 'pending')");
                $reserve_date = date("Y-m-d");
                $stmt_insert->bind_param("isss",$id,$book['title'],$username,$reserve_date);
                $stmt_insert->execute();

                $message = "Book reserved successfully! You will be notified when available.";
            }
        } else {
            $message = "Book is available for borrowing.";
        }
    }
}

$result = $conn->query("SELECT * FROM books");
?>

<!DOCTYPE html>
<html>
<head>

<title>Library Books</title>

<link rel="stylesheet" href="../css/style.css">

</head>

<body>

<header class="top-header">

<h1>Library Books</h1>

<div>
<a href="sdashboard.php" class="nav-btn">Dashboard</a>
<a href="student_borrowed_book.php" class="nav-btn">My Books</a>
<a href="../php/logout.php" class="nav-btn">Logout</a>
</div>

</header>

<div class="content">

<h2 class="center-text">Available Books</h2>

<?php
if(isset($message)){
    echo "<p class='center-text'>$message</p>";
}
?>

<!-- Search and Filter Form -->
<form method="GET" style="margin:20px auto; max-width:600px; display:flex; gap:10px; flex-wrap:wrap; justify-content:center;">
    <input type="text" name="search" placeholder="Search by title or author..." value="<?php echo htmlspecialchars($search); ?>" style="flex:1; min-width:200px; padding:10px; border:1px solid #ccc; border-radius:6px;">

    <select name="category" style="padding:10px; border:1px solid #ccc; border-radius:6px;">
        <option value="">All Categories</option>
        <option value="Fiction" <?php echo $category_filter === 'Fiction' ? 'selected' : ''; ?>>Fiction</option>
        <option value="Non-Fiction" <?php echo $category_filter === 'Non-Fiction' ? 'selected' : ''; ?>>Non-Fiction</option>
        <option value="Science" <?php echo $category_filter === 'Science' ? 'selected' : ''; ?>>Science</option>
        <option value="Technology" <?php echo $category_filter === 'Technology' ? 'selected' : ''; ?>>Technology</option>
        <option value="History" <?php echo $category_filter === 'History' ? 'selected' : ''; ?>>History</option>
        <option value="Biography" <?php echo $category_filter === 'Biography' ? 'selected' : ''; ?>>Biography</option>
        <option value="Other" <?php echo $category_filter === 'Other' ? 'selected' : ''; ?>>Other</option>
    </select>

    <label style="display:flex; align-items:center; gap:5px;">
        <input type="checkbox" name="available" value="yes" <?php echo $available_only ? 'checked' : ''; ?>>
        Available only
    </label>

    <button type="submit" style="width:auto; padding:10px 20px;">Search</button>
    <a href="books_list.php" style="padding:10px 20px; background:#666; color:white; border-radius:6px; text-decoration:none;">Clear</a>
</form>

<table>

<tr>
<th>Book ID</th>
<th>Title</th>
<th>Author</th>
<th>Category</th>
<th>Quantity</th>
<th>Borrow</th>
</tr>

<?php

if($result->num_rows > 0){
    while($row = $result->fetch_assoc()){
        echo "<tr>";
        echo "<td>".htmlspecialchars($row['id'])."</td>";
        echo "<td>".htmlspecialchars($row['title'])."</td>";
        echo "<td>".htmlspecialchars($row['author'])."</td>";
        echo "<td>".htmlspecialchars($row['category'])."</td>";
        $qty = htmlspecialchars($row['quantity']);
        $available = $row['quantity'] > 0;
        echo "<td>".($available ? $qty : '<span style="color:red;">'.$qty.' (Out of stock)</span>')."</td>";
        echo "<td>
        <form method='POST'>
        <input type='hidden' name='id' value='".htmlspecialchars($row['id'])."'>
        ".($available ? "<button type='submit' name='borrow'>Borrow</button>" : "<button type='submit' name='reserve' onclick='return confirm(\"Reserve this book? You will be notified when available.\")'>Reserve</button>")."
        </form>
        </td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='6'>No books available</td></tr>";
}

?>

</table>

</div>

</body>
</html>