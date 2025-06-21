<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}


$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT name, username, email, mobile_no, address FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Task Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/styles.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary px-3">
        <h4>GeoTrust Admin Console</h1>
        <a href="contact.php">CONTACT US</a>
        <div class="ms-auto">
            <div class="dropdown">
                <button class="btn btn-outline-light dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    Settings
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><h6 class="dropdown-header">Profile</h6></li>
                    <li><span class="dropdown-item-text"><strong>Name:</strong> <?= htmlspecialchars($user['name']) ?></span></li>
                    <li><span class="dropdown-item-text"><strong>Username:</strong> <?= htmlspecialchars($user['username']) ?></span></li>
                    <li><span class="dropdown-item-text"><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></span></li>
                    <li><span class="dropdown-item-text"><strong>Mobile:</strong> <?= htmlspecialchars($user['mobile_no']) ?></span></li>
                    <li><span class="dropdown-item-text"><strong>Address:</strong> <?= htmlspecialchars($user['address']) ?></span></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="text-center">
            <h1 class="display-5">Welcome, <?= htmlspecialchars($user['name']) ?> 👋</h1>
            <p class="lead">Here you can manage your existing memberships.</p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
