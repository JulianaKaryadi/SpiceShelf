<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - SpiceShelf</title>
    <link rel="stylesheet" href="assets/css/profile.css">
</head>
<body>
    <?php include('includes/header.php'); ?>

    <div class="container">
        <h1>Edit Profile</h1>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="status-message success">
                <?= htmlspecialchars($_SESSION['success']); ?>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="status-message error">
                <?= htmlspecialchars($_SESSION['error']); ?>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        
        <form action="index.php?action=profile" method="POST">
            <!-- Left Column - Basic Info and Current Settings -->
            <div class="profile-left-column">
                <h2>Current Information</h2>
                
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" value="<?= htmlspecialchars($user['username']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']); ?>" required>
                </div>

                <div class="form-group">
                    <label>Current Dietary Preferences:</label>
                    <div class="current-info">
                        <p><?= !empty($user_dietary_preferences) 
                            ? implode(', ', array_column($user_dietary_preferences, 'preference_name')) 
                            : "No dietary preferences selected."; ?></p>
                    </div>
                </div>

                <div class="form-group">
                    <label>Current Allergies:</label>
                    <div class="current-info">
                        <p><?= !empty($user_allergies) 
                            ? implode(', ', array_column($user_allergies, 'allergy_name')) 
                            : "No allergies selected."; ?></p>
                    </div>
                </div>
            </div>

            <!-- Right Column - Updates -->
            <div class="profile-right-column">
                <h2>Update Information</h2>

                <div class="form-group">
                    <label for="password">New Password:</label>
                    <input type="password" id="password" name="password" placeholder="Leave blank to keep current password">
                    <p class="form-hint">Must be at least 8 characters</p>
                </div>

                <div class="form-group">
                    <label>Update Dietary Preferences:</label>
                    <div class="search-box">
                        <input type="text" id="preferences-search" placeholder="Search preferences..." class="search-input">
                        <button type="button" id="preferences-search-clear" class="clear-search-btn">✕</button>
                    </div>
                    <div class="checkbox-container" id="preferences-container">
                        <?php foreach ($all_preferences as $preference): ?>
                            <div class="checkbox-option">
                                <input type="checkbox" 
                                    id="pref_<?= $preference['preference_id']; ?>"
                                    name="dietary_preferences[]" 
                                    value="<?= $preference['preference_id']; ?>"
                                    <?= in_array($preference['preference_id'], array_column($user_dietary_preferences, 'preference_id')) ? 'checked' : ''; ?>>
                                <label for="pref_<?= $preference['preference_id']; ?>">
                                    <?= htmlspecialchars($preference['preference_name']); ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div id="preferences-container-count" class="selection-count"></div>
                </div>

                <div class="form-group">
                    <label>Update Allergies:</label>
                    <div class="search-box">
                        <input type="text" id="allergies-search" placeholder="Search allergies..." class="search-input">
                        <button type="button" id="allergies-search-clear" class="clear-search-btn">✕</button>
                    </div>
                    <div class="checkbox-container" id="allergies-container">
                        <?php foreach ($all_allergies as $allergy): ?>
                            <div class="checkbox-option">
                                <input type="checkbox" 
                                    id="allergy_<?= $allergy['allergy_id']; ?>"
                                    name="allergies[]" 
                                    value="<?= $allergy['allergy_id']; ?>"
                                    <?= in_array($allergy['allergy_id'], array_column($user_allergies, 'allergy_id')) ? 'checked' : ''; ?>>
                                <label for="allergy_<?= $allergy['allergy_id']; ?>">
                                    <?= htmlspecialchars($allergy['allergy_name']); ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div id="allergies-container-count" class="selection-count"></div>
                </div>

                <button type="submit">Save Changes</button>
            </div>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="assets/js/profile.js"></script>
</body>
</html>