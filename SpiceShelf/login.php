<?php
// adminviews/login.php

// Initialize variables
$username = isset($_POST['username']) ? $_POST['username'] : '';
$errors = isset($errors) ? $errors : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - SpiceShelf</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
        }
        .admin-login-container {
            max-width: 400px;
            margin: 100px auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        .admin-logo {
            text-align: center;
            margin-bottom: 20px;
        }
        .admin-logo h1 {
            color: #F29E52;
            margin: 0;
            font-size: 28px;
        }
        .admin-logo p {
            color: #6c757d;
            margin: 5px 0 0;
        }
        .admin-login-title {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
            font-size: 22px;
        }
        .admin-login-form .form-group {
            margin-bottom: 20px;
        }
        .admin-login-form label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #333;
        }
        .admin-login-form input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 15px;
            transition: border-color 0.3s;
        }
        .admin-login-form input:focus {
            border-color: #F29E52;
            outline: none;
            box-shadow: 0 0 0 2px rgba(242, 158, 82, 0.2);
        }
        .admin-login-form button {
            width: 100%;
            padding: 14px;
            background-color: #F29E52;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: background-color 0.3s;
        }
        .admin-login-form button:hover {
            background-color: #e48a3c;
        }
        .errors {
            background-color: #f8d7da;
            color: #721c24;
            padding: 12px 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            font-size: 14px;
            border-left: 4px solid #f5c6cb;
        }
        .errors ul {
            margin: 0;
            padding-left: 20px;
        }
        .back-to-site {
            text-align: center;
            margin-top: 20px;
        }
        .back-to-site a {
            color: #6c757d;
            text-decoration: none;
            font-size: 14px;
        }
        .back-to-site a:hover {
            color: #F29E52;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="admin-login-container">
        <div class="admin-logo">
            <h1>SpiceShelf</h1>
            <p>Admin Portal</p>
        </div>
        
        <h2 class="admin-login-title">Administrator Login</h2>
        
        <?php if (!empty($errors)): ?>
            <div class="errors">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <form class="admin-login-form" action="index.php?action=adminLogin" method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" required autofocus>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <div class="form-group">
                <button type="submit">Login</button>
            </div>
        </form>
        
        <div class="back-to-site">
            <a href="index.php?action=login">Return to Main Site</a>
        </div>
    </div>
</body>
</html>