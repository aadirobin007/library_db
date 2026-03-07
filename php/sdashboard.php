<?php
session_start();

if(!isset($_SESSION['username'])||$_SESSION['category'] !== 'Student'){
    header("Location: /library_management/login.html");
    exit();
}

?>
<html>
    <head>
        <link rel="stylesheet" href="../css/style.css">
    </head>
    <body>
        <header>
            <h2>WELCOME <?php echo htmlspecialchars($_SESSION['username']); ?> </h2>
            <a href="logout.php" class="nav-btn">Logout</a>
        </header>
           <div class="content">
    <div class="dashboard-box">
        <h2>This is the Student dashboard</h2>
        <p>Welcome to your dashboard. More features coming soon.</p>
    </div>
</div>
<nav>
        
    </nav>

    </body>
</html>