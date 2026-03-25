<?php
session_start();
require '../db/config.php';
require 'validation.php';

/* =========================
   ACCESS CONTROL
========================= */
if (!isset($_SESSION['username']) ||
    ($_SESSION['category'] !== 'Teacher' && $_SESSION['category'] !== 'Teacher_Admin')) {
    header("Location: ../login.html");
    exit();
}

$username = $_SESSION['username'];
$message = "";

// Borrow limits by role
$limits = [
    'Student' => 3,
    'Teacher' => 5,
    'Teacher_Admin' => 5,
    'Librarian' => 10,
    'Library_Assistant' => 5,
    'admin' => 10
];

$max_books = $limits[$_SESSION['category']] ?? 3;

/* =========================
   SHOW SESSION MESSAGE
========================= */
if(isset($_SESSION['request_message'])){
    $message = $_SESSION['request_message'];
    unset($_SESSION['request_message']);
}

/* =========================
   BORROW BOOK
========================= */
if (isset($_POST['borrow_book'])) {
    // Validate book ID
    $book_id = filter_var($_POST['book_id'], FILTER_VALIDATE_INT);

    if (!$book_id || $book_id <= 0) {
        $message = "Invalid book ID.";
    } else {
        $stmt_check = $conn->prepare(
            "SELECT COUNT(*) as total FROM borrowed_books WHERE borrower_username=? AND status='borrowed'"
        );
        $stmt_check->bind_param("s",$username);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
        $row_check = $result_check->fetch_assoc();
        $current_books = $row_check['total'];

        if ($current_books >= $max_books) {
            $message = "Borrow limit reached. Maximum $max_books books allowed.";
        } else {
            $stmt = $conn->prepare("SELECT * FROM books WHERE id=?");
            $stmt->bind_param("i",$book_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $book = $result->fetch_assoc();

            if ($book && $book['quantity'] > 0) {
                $title = sanitize_string($book['title']);
                $issue_date = date("Y-m-d");
                $due_date = date("Y-m-d", strtotime("+7 days"));

                $stmt2 = $conn->prepare(
                    "INSERT INTO borrowed_books
                    (book_id, book_title, borrower_username, borrower_role, issued_by, issue_date, due_date, status)
                    VALUES (?,?,?,'Teacher','Self Borrow',?,?, 'borrowed')"
                );

                $stmt2->bind_param("issss",$book_id,$title,$username,$issue_date,$due_date);
                $stmt2->execute();

                $stmt3 = $conn->prepare("UPDATE books SET quantity = quantity - 1 WHERE id=?");
                $stmt3->bind_param("i",$book_id);
                $stmt3->execute();

                $message = "Book borrowed successfully.";
            } else {
                $message = "Book not available.";
            }
        }
    }
}

/* =========================
   RETURN BOOK
========================= */
if (isset($_POST['return_book'])) {
    // Validate borrow ID
    $borrow_id = filter_var($_POST['borrow_id'], FILTER_VALIDATE_INT);

    if (!$borrow_id || $borrow_id <= 0) {
        $message = "Invalid borrow ID.";
    } else {
        $stmt = $conn->prepare("SELECT * FROM borrowed_books WHERE id=? AND borrower_username=?");
        $stmt->bind_param("is",$borrow_id,$username);
        $stmt->execute();
        $result = $stmt->get_result();
        $borrow = $result->fetch_assoc();

        if ($borrow) {
            $book_id = $borrow['book_id'];
            $return_date = date("Y-m-d");

            $stmt2 = $conn->prepare(
                "UPDATE borrowed_books SET status='returned', return_date=? WHERE id=? AND borrower_username=?"
            );

            $stmt2->bind_param("sis",$return_date,$borrow_id,$username);
            $stmt2->execute();

            $stmt3 = $conn->prepare("UPDATE books SET quantity = quantity + 1 WHERE id=?");
            $stmt3->bind_param("i",$book_id);
            $stmt3->execute();

            $message = "Book returned successfully.";
        } else {
            $message = "Borrow record not found.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>

<title>Teacher Dashboard</title>

<link rel="stylesheet" href="../css/style.css">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>
.alert {
background:#f0f4c3;
border-left:5px solid #cddc39;
padding:10px;
margin:10px 0;
}
.profile-panel form select{
margin-top:5px;
padding:5px;
}
.profile-panel form button{
margin-top:5px;
padding:5px 10px;
}
</style>

</head>

<body>

<header class="top-header">

<h2>WELCOME<?php echo htmlspecialchars($username); ?></h2>

<div class="profile-menu">

<button class="profile-btn" onclick="toggleProfileMenu()">
<i class="fa-solid fa-user"></i> Profile
</button>

<div class="profile-panel" id="profilePanel">

<?php if ($_SESSION['category'] === 'Teacher_Admin'): ?>
<a href="addf.php">Add Faculty</a>
<?php endif; ?>

<?php if ($_SESSION['category'] === 'Teacher'): ?>

<form method="post" action="submit_admin_request.php">

<label>Request Role:</label>

<select name="requested_role" required>
<option value="">--Select Role--</option>
<option value="Teacher_Admin">Teacher Admin</option>
<option value="Librarian">Librarian</option>
<option value="Library_Assistant">Library Assistant</option>
</select>

<button type="submit" class="nav-btn">
Submit Request
</button>

</form>

<?php endif; ?>

<a href="change_password.php">Change Password</a>
<a href="borrowed_book.php">Borrowed Books</a>
<a href="logout.php">Logout</a>

</div>
</div>
</header>

<div class="content">

<?php if(!empty($message)) echo "<div class='alert'>".$message."</div>"; ?>

<div class="card">

<h2>Teacher Dashboard</h2>

<h3>Books Due Today</h3>

<table>

<tr>
<th>Title</th>
<th>Due Date</th>
<th>Return</th>
</tr>

<?php

$today = date("Y-m-d");

$stmt = $conn->prepare(
"SELECT * FROM borrowed_books WHERE borrower_username=? AND due_date=? AND status='borrowed'"
);

$stmt->bind_param("ss",$username,$today);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {

    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>".htmlspecialchars($row['book_title'])."</td>";
        echo "<td>".htmlspecialchars($row['due_date'])."</td>";
        echo "<td>
        <form method='POST'>
        <input type='hidden' name='borrow_id' value='".$row['id']."'>
        <button type='submit' name='return_book'>Return</button>
        </form>
        </td>";
        echo "</tr>";
    }

} else {

    echo "<tr><td colspan='3'>No books due today</td></tr>";

}

?>

</table>

<h3>Overdue Books</h3>

<table>

<tr>
<th>Title</th>
<th>Due Date</th>
<th>Days Overdue</th>
<th>Fine</th>
<th>Return</th>
</tr>

<?php

// Fine rate per day (adjust as needed)
$fine_per_day = 5.00; // 5 currency units per day

$stmt = $conn->prepare(
"SELECT * FROM borrowed_books WHERE borrower_username=? AND due_date < ? AND status='borrowed'"
);

$stmt->bind_param("ss",$username,$today);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {

    while ($row = $result->fetch_assoc()) {
        $days_overdue = floor((strtotime($today) - strtotime($row['due_date'])) / 86400);
        $fine = $days_overdue * $fine_per_day;
        echo "<tr style='background:#ffebee;'>";
        echo "<td>".htmlspecialchars($row['book_title'])."</td>";
        echo "<td>".htmlspecialchars($row['due_date'])."</td>";
        echo "<td><strong>".$days_overdue." days</strong></td>";
        echo "<td><strong>".number_format($fine, 2)."</strong></td>";
        echo "<td>
        <form method='POST'>
        <input type='hidden' name='borrow_id' value='".$row['id']."'>
        <button type='submit' name='return_book'>Return</button>
        </form>
        </td>";
        echo "</tr>";
    }

} else {

    echo "<tr><td colspan='5'>No overdue books</td></tr>";

}

?>

</table>


<h3>Library Books</h3>

<table>

<tr>
<th>ID</th>
<th>Title</th>
<th>Author</th>
<th>Category</th>
<th>Quantity</th>
<th>Borrow</th>
</tr>

<?php

$result = $conn->query("SELECT * FROM books");

while ($row = $result->fetch_assoc()) {

echo "<tr>";

echo "<td>".$row['id']."</td>";
echo "<td>".$row['title']."</td>";
echo "<td>".$row['author']."</td>";
echo "<td>".$row['category']."</td>";
echo "<td>".$row['quantity']."</td>";

echo "<td>
<form method='POST'>
<input type='hidden' name='book_id' value='".$row['id']."'>
<button type='submit' name='borrow_book'>Borrow</button>
</form>
</td>";

echo "</tr>";

}

?>

</table>

</div>
</div>

<script>

function toggleProfileMenu(){

const panel = document.getElementById("profilePanel");

panel.style.display =
panel.style.display === "block" ? "none" : "block";

}

document.addEventListener("click", function(event){

const panel = document.getElementById("profilePanel");
const button = document.querySelector(".profile-btn");

if (!panel.contains(event.target) && !button.contains(event.target)) {
panel.style.display = "none";
}

});

</script>

</body>
</html>