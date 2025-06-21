<?php
session_start();
include 'config.php';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $role = $_POST['role']; 

    if ($role === 'user') {
        $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ?");
    } else {
        $stmt = $conn->prepare("SELECT id, username, password FROM admins WHERE username = ?");
    }

    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];

        if ($role === 'user') {
            header("Location: dashboard.php");
        } else {
            header("Location: admin-console.php");
        }
        exit();
    } else {
        $error = "Invalid credentials.";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login - GeoTrust</title>
  <link rel="stylesheet" href="css/styles.css">
</head>
<body>
  <div class="login-container">
    <div class="login-left">
      <a href="index.php" class="logo">GeoTrust</a>
    </div>
    <div class="login-right">
      <div class="login-card">
        <h2>Login</h2>
        <?php if ($error): ?>
          <p class="error-msg"><?= $error ?></p>
        <?php endif; ?>
        <form method="POST" action="">
          <div class="login-tabs">
            <label><input type="radio" name="role" value="user" checked> User Login</label>
            <label><input type="radio" name="role" value="admin"> Admin Login</label>
          </div>
          <input type="text" name="username" placeholder="Username" required>
          <input type="password" name="password" placeholder="Password" required>
          <button type="submit" class="btn">Login</button>
        </form>
        <p class="register-link">New user? <a href="register.php">Register here</a></p>
      </div>
    </div>
  </div>
</body>
</html>
