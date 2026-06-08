<?php
session_start();
error_reporting(0);
include("includes/config.php");

if (isset($_POST['login'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password']; // Removed md5 to match other files (plain text)

    $query = mysqli_query($conn, "SELECT * FROM student WHERE student_email='$email' AND student_password='$password'");
    $num = mysqli_fetch_array($query);

    if ($num > 0) {
        $_SESSION['student_login'] = $_POST['email'];
        $_SESSION['student_id'] = $num['student_id'];
        $_SESSION['student_name'] = $num['student_name'];
        header("location:dashboard.php");
        exit();
    } else {
        $_SESSION['errmsg'] = "Invalid email or password";
        header("location:index.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Login | SPF Track</title>
    <link rel="stylesheet" href="assets/css/login.css">
    <link rel="shortcut icon" href="images/logo.png" type="image/png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
</head>

<body class="login-page">
    <div class="login-container">
        <div class="login-header">
            <a href="../index.php"><img src="images/logo.png" alt="Logo" class="logo"></a>
            <h2>Student Login</h2>
            <p>Welcome back! Please login to your account.</p>
        </div>

        <form method="post" action="">
            <span style="color:green;">
                <?php 
                if (isset($_SESSION['successmsg'])) {
                    echo htmlentities($_SESSION['successmsg']);
                    $_SESSION['successmsg'] = "";
                }
                ?>
            </span>
            <span style="color:red;">
                <?php echo htmlentities($_SESSION['errmsg']); ?>
                <?php echo htmlentities($_SESSION['errmsg'] = ""); ?>
            </span>

            <div class="form-group">
                <label for="email">Email</label>
                <div class="input-icon">
                    <input type="email" name="email" id="email" placeholder="Enter your email" required>
                    <i class="icon-user"></i>
                </div>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-icon">
                    <input type="password" name="password" id="password" placeholder="Enter your password" required>
                    <i class="icon-lock"></i>
                </div>
            </div>

            <div class="form-footer">
                <a href="#" class="forgot-password">Forgot Password?</a>
            </div>

            <button type="submit" name="login" class="btn-login">Login</button>
        </form>

        <div class="login-footer">
            <p>Don't have an account? <a href="register.php">Register here</a></p>
            <p>&copy;
                <?php echo date('Y'); ?> SPF Track. All rights reserved.
            </p>
        </div>
    </div>
</body>

</html>