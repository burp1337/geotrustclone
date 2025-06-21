<?php
session_start();
include 'config.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';
require 'PHPMailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$error = '';
$success = '';
$show_otp_form = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_POST['otp'])) {
    $captcha_response = $_POST['g-recaptcha-response'];
    $secret_key = '6Ldi5FkrAAAAANNoMNHAzDAbHMPeuHUb4RzPMuzd';
    $verify_url = 'https://www.google.com/recaptcha/api/siteverify';
    $response = file_get_contents($verify_url . '?secret=' . $secret_key . '&response=' . $captcha_response);
    $response_data = json_decode($response);

    if (!$response_data->success) {
        $error = "CAPTCHA verification failed. Please try again.";
    } else {
        $name = trim($_POST['name']);
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $mobile_no = trim($_POST['mobile_no']);
        $address = trim($_POST['address']);
        $password = $_POST['password'];

        if (empty($name) || empty($username) || empty($email) || empty($mobile_no) || empty($address) || empty($password)) {
            $error = "All fields are required.";
        } elseif (!preg_match('/^[A-Za-z\s]{2,100}$/', $name)) {
            $error = "Name must be 2–100 letters and spaces only.";
        } elseif (!preg_match('/^[A-Za-z0-9]{3,50}$/', $username)) {
            $error = "Username must be 3–50 alphanumeric characters.";
        } elseif (!preg_match('/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.(com|org|net|edu|gov|in)$/', $email)) {
            $error = "Email must be valid and end with .com, .org, etc.";
        } elseif (!preg_match('/^[0-9]{10}$/', $mobile_no)) {
            $error = "Mobile number must be exactly 10 digits.";
        } elseif (strlen($address) < 5 || strlen($address) > 255) {
            $error = "Address must be 5–255 characters.";
        } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', $password)) {
            $error = "Password must be at least 8 characters long and include uppercase, lowercase, number, and special character.";
        } else {
            $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmt->bind_param("ss", $username, $email);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $error = "Username or email already exists.";
            } else {
                $otp = rand(100000, 999999);
                $_SESSION['otp'] = $otp;
                $_SESSION['temp_user'] = [
                    'name' => $name,
                    'username' => $username,
                    'email' => $email,
                    'mobile_no' => $mobile_no,
                    'address' => $address,
                    'password' => password_hash($password, PASSWORD_DEFAULT)
                ];

                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'sudhishbisht600@gmail.com';
                    $mail->Password = 'tvzgowwyretircwj';
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port = 587;

                    $mail->setFrom('your_gmail@gmail.com', 'Task Manager Verification');
                    $mail->addAddress($email);
                    $mail->isHTML(true);
                    $mail->Subject = 'Your OTP for Registration';
                    $mail->Body = "Your OTP is: <b>$otp</b>. It is valid for 5 minutes.";
                    $mail->send();
                    $show_otp_form = true;
                } catch (Exception $e) {
                    $error = "Failed to send OTP. Error: {$mail->ErrorInfo}";
                }
            }
            $stmt->close();
        }
    }
} elseif ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['otp'])) {
    $entered_otp = trim($_POST['otp']);
    if ($entered_otp == $_SESSION['otp']) {
        $temp_user = $_SESSION['temp_user'];
        $stmt = $conn->prepare("INSERT INTO users (name, username, email, mobile_no, address, password) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $temp_user['name'], $temp_user['username'], $temp_user['email'], $temp_user['mobile_no'], $temp_user['address'], $temp_user['password']);
        if ($stmt->execute()) {
            $success = "Registration successful! Redirecting to login...";
            header("refresh:2;url=login.php");
            unset($_SESSION['otp']);
            unset($_SESSION['temp_user']);
        } else {
            $error = "Registration failed. Please try again.";
            $show_otp_form = true;
        }
        $stmt->close();
    } else {
        $error = "Invalid OTP. Please try again.";
        $show_otp_form = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - GeoTrust</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
<div class="login-container">
    <div class="login-left">
      <a href="index.php" class="logo">GeoTrust</a>
    </div>
    <div class="login-right">
      <div class="login-card">
        <h2>User Registration</h2>
        <?php if ($error): ?>
          <p class="error-msg"><?= $error ?></p>
        <?php endif; ?>
        <?php if ($success): ?>
          <p class="success-msg"><?= $success ?></p>
        <?php endif; ?>

        <?php if ($show_otp_form): ?>
          <form method="POST" action="">
            <input type="text" name="otp" placeholder="Enter OTP" required>
            <button type="submit" class="btn">Verify OTP</button>
          </form>
        <?php else: ?>
          <form method="POST" action="">
            <input type="text" name="name" placeholder="Full Name"
                    pattern="^[A-Za-z\s]{2,100}$"
                    title="Only letters and spaces (2–100 characters)"
                    value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '' ?>" required>

            <input type="text" name="username" placeholder="Username"
                    pattern="^[A-Za-z0-9]{3,50}$"
                    title="3–50 alphanumeric characters only"
                    value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>" required>

            <input type="email" name="email" placeholder="Email"
                    pattern="^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.(com|org|net|edu|gov|in)$"
                    title="Enter a valid email like user@example.com"
                    value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>" required>

            <input type="text" name="mobile_no" placeholder="Mobile Number"
                    pattern="^[0-9]{10}$"
                    title="Mobile number must be exactly 10 digits"
                    value="<?= isset($_POST['mobile_no']) ? htmlspecialchars($_POST['mobile_no']) : '' ?>" required>

            <input type="text" name="address" placeholder="Address"
                    minlength="5" maxlength="255"
                    title="Address must be 5–255 characters"
                    value="<?= isset($_POST['address']) ? htmlspecialchars($_POST['address']) : '' ?>" required>

            <input type="password" name="password" placeholder="Password" 
                    pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$" 
                    title="At least 8 characters, including uppercase, lowercase, number, and special character" required>

            <div class="g-recaptcha" data-sitekey="6Ldi5FkrAAAAALHLekG7NQbnadu-YJUlt9U9NmmA"></div>

            <button type="submit" class="btn">Register</button>
          </form>

        <?php endif; ?>
        <p class="register-link">Already a user? <a href="login.php">Login here</a></p>
      </div>
    </div>
</div>
</body>
</html>
