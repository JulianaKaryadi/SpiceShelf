<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - SpiceShelf</title>
    <link rel="stylesheet" href="assets/css/register.css">
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
            <form action="index.php?action=register" method="POST" class="registration-form">
                <h2>Register</h2>
                <div class="form-row">
                    <div class="form-group">
                        <label for="username">Username:</label>
                        <input type="text" id="username" name="username" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="password">Password:</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password:</label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Dietary Preferences:</label>
                        <div class="checkbox-group">
                            <label><input type="checkbox" name="dietary_preferences[]" value="7">None</label>
                            <?php foreach ($preferences as $preference): ?>
                                <label><input type="checkbox" name="dietary_preferences[]" value="<?= $preference['preference_id']; ?>"><?= htmlspecialchars($preference['preference_name']); ?></label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Allergies:</label>
                        <div class="checkbox-group">
                            <label><input type="checkbox" name="allergies[]" value="9">None</label>
                            <?php foreach ($allergies as $allergy): ?>
                                <label><input type="checkbox" name="allergies[]" value="<?= $allergy['allergy_id']; ?>"><?= htmlspecialchars($allergy['allergy_name']); ?></label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <button type="submit" name="register">Register</button>
            </form>
        </div>
    </div>
</body>
</html>