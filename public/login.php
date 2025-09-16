<?php
session_start();
include 'config.php';

$error = "";

// Handle login form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    // Check if username exists
    $query = "SELECT * FROM users WHERE username='$username' LIMIT 1";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        if (password_verify($password, $user['password'])) {
            // Set session and redirect to chatbot
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['fullname'] = $user['fullname'];
            $_SESSION['username'] = $user['username'];
            header("Location: chat.php");
            exit();
        } else {
            $error = "❌ Incorrect password.";
        }
    } else {
        $error = "❌ Username not found.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login | Medical Chatbot System</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="container mt-5">
    <div class="card mx-auto" style="max-width: 450px;">
        <div class="card-body">
            <h3 class="card-title text-center">User Login</h3>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label>Username:</label>
                    <input type="text" name="username" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Password:</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-success btn-block">Login</button>
            </form>

            <p class="mt-3 text-center">Don't have an account? <a href="register.php">Create one</a></p>
        </div>
    </div>
</div>

</body>
</html>