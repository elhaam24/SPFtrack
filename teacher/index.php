<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
include("includes/config.php");

if (isset($_POST['login'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    $query = mysqli_query($conn, "SELECT teacher_id, teacher_name FROM teacher WHERE teacher_email='$email' AND teacher_password='$password'");

    if (!$query) {
        die("Query failed: " . mysqli_error($conn));
    }

    $num = mysqli_fetch_array($query);

    if ($num) {
        $_SESSION['teacher_login'] = $_POST['email'];
        $_SESSION['teacher_id'] = $num['teacher_id'];
        $_SESSION['teacher_name'] = $num['teacher_name'];
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
    <title>Teacher Login | SPF Track</title>
    <link rel="stylesheet" href="assets/CSS/login.css">
    <link rel="shortcut icon" href="images/logo.png" type="image/png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
</head>

<body class="login-page">
    <div class="login-container">
        <div class="login-header">
            <img src="images/logo.png" alt="Logo" class="logo">
            <h2>Teacher Login</h2>
            <p>Welcome back! Please login to your account.</p>
        </div>

        <form method="post" action="">
            <span style="color:red;">
                <?php 
                if (isset($_SESSION['errmsg'])) {
                    echo htmlentities($_SESSION['errmsg']);
                    $_SESSION['errmsg'] = "";
                }
                ?>
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
            <p>&copy;
                <?php echo date('Y'); ?> SPF Track. All rights reserved.
            </p>
        </div>
    </div>
</body>

</html>