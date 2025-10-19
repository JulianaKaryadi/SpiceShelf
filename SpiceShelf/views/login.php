<!-- login.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="assets/css/login.css">
</head>
<body>
    <?php include('includes/header.php'); ?>
    <div class="background-animation">
        <div class="shape shape1"></div>
        <div class="shape shape2"></div>
        <div class="shape shape3"></div>
        <div class="shape shape4"></div>
    </div>
    <div class="container">
        <div class="form-wrapper">
            <div class="logo">
                <img src="assets/image/logo.png" alt="SpiceShelf Logo">
            </div>
            <form action="index.php?action=login" method="POST" class="login-form">
                <h2>Login</h2>
                <?php if (isset($error)): ?>
                    <p class="error-message"><?= htmlspecialchars($error); ?></p>
                <?php endif; ?>
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" name="email" id="email" required>
                </div>
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" name="password" id="password" required>
                </div>
                <button type="submit">Login</button>
            </form>
        </div>
    </div>
</body>
</html>