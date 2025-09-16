<?php
session_start();
include 'config.php';

$success = "";
$error = "";

// Form Submission Handling
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $confirm_password = mysqli_real_escape_string($conn, $_POST['confirm_password']);

    // Validation
    if (empty($fullname) || empty($username) || empty($email) || empty($phone) || empty($password) || empty($confirm_password)) {
        $error = "❌ All fields are required!";
    } elseif ($password !== $confirm_password) {
        $error = "❌ Passwords do not match!";
    } else {
        // Check if email or username already exists
        $check_user = mysqli_query($conn, "SELECT * FROM users WHERE email='$email' OR username='$username'");
        if (mysqli_num_rows($check_user) > 0) {
            $error = "❌ Email or username already registered!";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $insert = mysqli_query($conn, "INSERT INTO users (fullname, username, email, phone, password) VALUES('$fullname','$username','$email','$phone','$hashedPassword')");
            if ($insert) {
                $success = "✅ Registration successful! You can now log in.";
            } else {
                $error = "❌ Registration failed, try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register | Medical Chatbot System</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="container mt-5">
    <div class="card mx-auto" style="max-width: 450px;">
        <div class="card-body">
            <h3 class="card-title text-center">Create Account</h3>

            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php elseif ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label>Full Name:</label>
                    <input type="text" name="fullname" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Username:</label>
                    <input type="text" name="username" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Email Address:</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Phone Number:</label>
                    <input type="text" name="phone" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Password:</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Confirm Password:</label>
                    <input type="password" name="confirm_password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Create Account</button>
            </form>

            <p class="mt-3 text-center">Already have an account? <a href="login.php">Log In</a></p>
        </div>
    </div>
</div>

</body>
</html>