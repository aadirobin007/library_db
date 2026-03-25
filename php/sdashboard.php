<?php
session_start();

if (!isset($_SESSION['username']) || $_SESSION['category'] !== 'Student') {
    header("Location: ../login.html");
    exit();
}

$username = $_SESSION['username'];
?>

<!DOCTYPE html>
<html>
<head>
<title>Student Dashboard</title>

<link rel="stylesheet" href="../css/style.css">

</head>

<body>

<header class="top-header">

<h1>WELCOME <?php echo $username; ?></h1>

<div>
<a href="student_borrowed_book.php" class="nav-btn">My Borrowed Books</a>
<a href="../php/logout.php" class="nav-btn">Logout</a>
</div>

</header>


<div class="content">

<h2>This is the Student Dashboard</h2>
<p>Welcome to your dashboard</p>

</div>


<div class="card-container">

<div class="card">
<h3>My Borrowed Books</h3>
<p>View books you have borrowed.</p>
<a href="student_borrowed_book.php" class="nav-btn">View</a>
</div>

<div class="card">
<h3>Browse Books</h3>
<p>Search available books in the library.</p>
<a href="books_list.php" class="nav-btn">Browse</a>
</div>


</div>

</div>

</body>
</html>