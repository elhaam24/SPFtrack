<?php
session_start();
error_reporting(0);
include("includes/config.php");

if (isset($_POST['register'])) {
    $name = mysqli_real_escape_string($conn, trim($_POST['student_name']));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $confirm_password = mysqli_real_escape_string($conn, $_POST['confirm_password']);

    if (!$name || !$email || !$password || !$confirm_password) {
        $_SESSION['errmsg'] = "All fields are required.";
    } elseif ($password !== $confirm_password) {
        $_SESSION['errmsg'] = "Passwords do not match.";
    } else {
        $email_check = mysqli_query($conn, "SELECT student_id FROM student WHERE student_email='$email'");
        if ($email_check && mysqli_num_rows($email_check) > 0) {
            $_SESSION['errmsg'] = "An account with that email already exists.";
        } else {
            $insert = mysqli_query($conn, "INSERT INTO student (student_name, student_email, student_password) VALUES ('$name', '$email', '$password')");
            if ($insert) {
                $_SESSION['successmsg'] = "Registration successful. Please log in.";
                header("location:index.php");
                exit();
            } else {
                $_SESSION['errmsg'] = "Unable to create account. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Register | SPF Track</title>
    <link rel="stylesheet" href="assets/css/login.css">
    <link rel="shortcut icon" href="images/logo.png" type="image/png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>

<body class="login-page">
    <div class="login-container">
        <div class="login-header">
            <a href="../index.php"><img src="images/logo.png" alt="Logo" class="logo"></a>
            <h2>Create Student Account</h2>
            <p>Register to access student progress tracking and course materials.</p>
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
                <?php 
                if (isset($_SESSION['errmsg'])) {
                    echo htmlentities($_SESSION['errmsg']);
                    $_SESSION['errmsg'] = "";
                }
                ?>
            </span>

            <div class="form-group">
                <label for="student_name">Full Name</label>
                <div class="input-icon">
                    <input type="text" name="student_name" id="student_name" placeholder="Enter your full name" required>
                    <i class="icon-user"></i>
                </div>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <div class="input-icon">
                    <input type="email" name="email" id="email" placeholder="Enter your email" required>
                    <i class="icon-mail"></i>
                </div>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-icon">
                    <input type="password" name="password" id="password" placeholder="Create a password" required>
                    <i class="icon-lock"></i>
                </div>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <div class="input-icon">
                    <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm your password" required>
                    <i class="icon-lock"></i>
                </div>
            </div>

            <button type="submit" name="register" class="btn-login">Register</button>
        </form>

        <div class="login-footer">
            <p>Already have an account? <a href="index.php">Login here</a></p>
            <p>&copy; <?php echo date('Y'); ?> SPF Track. All rights reserved.</p>
        </div>
    </div>
</body>

</html>
