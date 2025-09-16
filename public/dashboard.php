<?php

session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard | Chatbot System</title>
    <link rel="stylesheet" href="css/style.css">

</head>
<body>

<h2>Welcome, <?php echo $_SESSION['fullname']; ?>!</h2>
<p>You are logged in successfully.</p>
<a href="logout.php">Logout</a>

</body>
</html>
