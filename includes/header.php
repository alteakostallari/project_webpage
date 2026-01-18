<?php
$isLoggedIn = isset($_SESSION['user_id']);
$role = $_SESSION['role'] ?? null;
?>
<!DOCTYPE html>
<html>

<head>
    <title>MintPlayground</title>
    <link rel="stylesheet" href="/WebPage_Project/css/style.css">
</head>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="js/main.js"></script>

<body class="<?php echo isset($page_class) ? $page_class : ''; ?>">

    <nav class="navbar-transparent">
        <div class="logo">
            <img src="/WebPage_Project/images/Logo-Img.png" alt="logo-img" />
        </div>

        <ul class="menu">

            <?php if (!$isLoggedIn): ?>
                <li><a href="login.php">Login</a></li>
                <li><a href="register.php">Register</a></li>

            <?php elseif ($role === 'user'): ?>
                <li><a href="index.php">Home</a></li>
                <li><a href="fields.php">Fields</a></li>
                <li><a href="my-bookings.php">My Bookings</a></li>
                <li><a href="profile.php">Profile</a></li>
                <li><a href="logout.php">Logout</a></li>

            <?php elseif ($role === 'admin'): ?>
                <li><a href="../admin/manage-users.php">Users</a></li>
                <li><a href="../admin/manage-fields.php">Fields</a></li>
                <li><a href="../admin/bookings.php">Bookings</a></li>
                <li><a href="../logout.php">Logout</a></li>
            <?php endif; ?>

        </ul>
    </nav>