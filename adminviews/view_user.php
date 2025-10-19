<?php
// adminviews/view_user.php

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php?action=adminLogin');
    exit;
}

// Check if user data is available
if (!isset($user) || !$user) {
    echo "<div style='text-align:center; margin-top:50px;'>";
    echo "<h2>User not found</h2>";
    echo "<p>The requested user does not exist or has been deleted.</p>";
    echo "<a href='index.php?action=adminUsers' class='btn btn-primary'>Back to Users List</a>";
    echo "</div>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View User - SpiceShelf Admin</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <!-- Add Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Admin Dashboard Styles */
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
        }
        .admin-container {
            display: flex;
            min-height: 100vh;
        }
        .admin-sidebar {
            width: 260px;
            background-color: #333;
            color: #fff;
            padding: 0;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
            position: fixed;
            height: 100%;
            overflow-y: auto;
        }
        .admin-logo {
            padding: 20px;
            text-align: center;
            margin-bottom: 10px;
            border-bottom: 1px solid #444;
        }
        .admin-logo h2 {
            color: #F29E52;
            margin: 0;
            font-size: 24px;
        }
        .admin-logo p {
            color: #aaa;
            margin: 5px 0 0;
            font-size: 14px;
        }
        .admin-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .admin-menu-category {
            font-size: 12px;
            text-transform: uppercase;
            color: #999;
            padding: 15px 20px 5px;
            letter-spacing: 0.5px;
        }
        .admin-menu li a {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: #fff;
            text-decoration: none;
            transition: all 0.3s;
            border-left: 3px solid transparent;
        }
        .admin-menu li a i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        .admin-menu li a:hover, 
        .admin-menu li a.active {
            background-color: #444;
            border-left-color: #F29E52;
        }
        .admin-menu li a.active {
            background-color: #F29E52;
            color: #fff;
        }
        .admin-content {
            flex: 1;
            padding: 20px;
            margin-left: 260px;
        }
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid #ddd;
        }
        .admin-title {
            margin: 0;
            color: #333;
            font-size: 24px;
        }
        .admin-user-info {
            display: flex;
            align-items: center;
        }
        .admin-user-info span {
            margin-right: 15px;
            color: #555;
        }
        .admin-logout {
            padding: 8px 15px;
            background-color: #dc3545;
            color: white;
            border: none;
            border-radius: 4px;
            text-decoration: none;
            font-size: 14px;
            transition: background-color 0.3s;
        }
        .admin-logout:hover {
            background-color: #c82333;
        }
        
        /* User Profile Specific Styles */
        .profile-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .back-button {
            display: inline-flex;
            align-items: center;
            padding: 8px 15px;
            background-color: #6c757d;
            color: white;
            border-radius: 4px;
            text-decoration: none;
            font-size: 14px;
            transition: background-color 0.3s;
        }
        .back-button:hover {
            background-color: #5a6268;
            color: white;
            text-decoration: none;
        }
        .back-button i {
            margin-right: 8px;
        }
        .delete-user-btn {
            display: inline-flex;
            align-items: center;
            padding: 8px 15px;
            background-color: #dc3545;
            color: white;
            border: none;
            border-radius: 4px;
            text-decoration: none;
            font-size: 14px;
            transition: background-color 0.3s;
            cursor: pointer;
        }
        .delete-user-btn:hover {
            background-color: #c82333;
            color: white;
        }
        .delete-user-btn i {
            margin-right: 8px;
        }
        .profile-container {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            overflow: hidden;
        }
        .profile-header-banner {
            background-color: #F29E52;
            color: white;
            padding: 30px;
            position: relative;
        }
        .profile-avatar {
            width: 100px;
            height: 100px;
            background-color: #fff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            color: #F29E52;
            margin-bottom: 15px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .profile-username {
            font-size: 24px;
            font-weight: bold;
            margin: 0;
        }
        .profile-email {
            font-size: 16px;
            opacity: 0.9;
            margin: 5px 0 0;
        }
        .profile-joined {
            font-size: 14px;
            opacity: 0.8;
            margin: 5px 0 0;
        }
        .inactive-user-alert {
            margin-top: 15px;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .inactive-badge {
            background-color: #f8d7da;
            color: #721c24;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
        }
        .notify-user-btn {
            display: inline-flex;
            align-items: center;
            padding: 8px 15px;
            background-color: #17a2b8;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 14px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .notify-user-btn:hover {
            background-color: #138496;
        }
        .notify-user-btn i {
            margin-right: 8px;
        }
        .profile-stats {
            padding: 20px 30px;
            display: flex;
            border-bottom: 1px solid #eee;
        }
        .stat-item {
            flex: 1;
            text-align: center;
            padding: 10px;
        }
        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: #333;
            margin: 0;
        }
        .stat-label {
            font-size: 14px;
            color: #777;
            margin: 5px 0 0;
        }
        .profile-sections {
            padding: 30px;
        }
        .profile-section {
            margin-bottom: 30px;
        }
        .section-title {
            font-size: 18px;
            color: #333;
            margin: 0 0 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: center;
        }
        .section-title i {
            margin-right: 10px;
            color: #F29E52;
        }
        .preference-tags, .allergy-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
        }
        .tag {
            display: inline-block;
            padding: 5px 10px;
            background-color: #f8f9fa;
            border-radius: 20px;
            font-size: 14px;
            color: #333;
            border: 1px solid #ddd;
        }
        .preference-tag {
            background-color: #e8f4f8;
            border-color: #bee5eb;
            color: #0c5460;
        }
        .allergy-tag {
            background-color: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
        }
        .recipe-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .recipe-table th, 
        .recipe-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        .recipe-table th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #333;
        }
        .recipe-table tr:hover {
            background-color: #f8f9fa;
        }
        .recipe-link {
            color: #F29E52;
            text-decoration: none;
        }
        .recipe-link:hover {
            text-decoration: underline;
        }
        .no-items {
            text-align: center;
            padding: 20px;
            color: #777;
            font-style: italic;
            background-color: #f8f9fa;
            border-radius: 4px;
        }
        .public-badge, .private-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }
        .public-badge {
            background-color: #d4edda;
            color: #155724;
        }
        .private-badge {
            background-color: #f8d7da;
            color: #721c24;
        }
        .confirmation-modal {
            display: none;
            position: fixed;
            z-index: 1000;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        .modal-content {
            background-color: #fff;
            margin: 15% auto;
            padding: 30px;
            border-radius: 8px;
            max-width: 500px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            position: relative;
        }
        .close-modal {
            position: absolute;
            right: 15px;
            top: 10px;
            font-size: 24px;
            color: #aaa;
            cursor: pointer;
        }
        .close-modal:hover {
            color: #333;
        }
        .modal-title {
            color: #333;
            margin-top: 0;
        }
        .modal-text {
            color: #555;
            margin-bottom: 20px;
        }
        .modal-actions {
            display: flex;
            justify-content: center;
            gap: 15px;
        }
        .btn-cancel {
            padding: 10px 20px;
            background-color: #6c757d;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .btn-cancel:hover {
            background-color: #5a6268;
        }
        .btn-confirm {
            padding: 10px 20px;
            background-color: #dc3545;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .btn-confirm:hover {
            background-color: #c82333;
        }
        .btn-notify {
            padding: 10px 20px;
            background-color: #17a2b8;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .btn-notify:hover {
            background-color: #138496;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Admin Sidebar -->
        <aside class="admin-sidebar">
            <div class="admin-logo">
                <h2>SpiceShelf</h2>
                <p>Admin Portal</p>
            </div>
            
            <ul class="admin-menu">
                <li class="admin-menu-category">Dashboard</li>
                <li><a href="index.php?action=adminDashboard"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                
                <li class="admin-menu-category">User Management</li>
                <li><a href="index.php?action=adminUsers" class="active"><i class="fas fa-users"></i> View Users</a></li>
                
                <li class="admin-menu-category">Recipe Management</li>
                <li><a href="index.php?action=adminRecipes"><i class="fas fa-utensils"></i> View Recipes</a></li>
                <li><a href="index.php?action=adminIngredients"><i class="fas fa-apple-alt"></i> Ingredients</a></li>
                <li><a href="index.php?action=adminCategories"><i class="fas fa-tags"></i> Categories</a></li>
                
                <li class="admin-menu-category">Reports</li>
                <li><a href="index.php?action=adminUserReports"><i class="fas fa-chart-line"></i> User Engagement</a></li> 
                <li><a href="index.php?action=adminRecipeReports"><i class="fas fa-chart-pie"></i> Recipe Favorites</a></li>
                <li><a href="index.php?action=adminAdReports"><i class="fas fa-chart-bar"></i> Ad Performance</a></li>
                
                <li class="admin-menu-category">Advertisements</li>
                <li><a href="index.php?action=adminAds"><i class="fas fa-ad"></i> Manage Ads</a></li>
            </ul>
        </aside>
        
        <!-- Admin Content -->
        <main class="admin-content">
            <header class="admin-header">
                <h1 class="admin-title">User Details</h1>
                <div class="admin-user-info">
                    <span>Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                    <a href="index.php?action=adminLogout" class="admin-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </header>
            
            <!-- Profile Header with Back Button -->
            <div class="profile-header">
                <a href="index.php?action=adminUsers" class="back-button">
                    <i class="fas fa-arrow-left"></i> Back to Users
                </a>
                <button class="delete-user-btn" onclick="openConfirmationModal()">
                    <i class="fas fa-trash"></i> Delete User
                </button>
            </div>
            
            <!-- User Profile Container -->
            <div class="profile-container">
                <!-- Profile Header Banner -->
                <div class="profile-header-banner">
                    <div class="profile-avatar">
                        <?php echo substr($user['username'], 0, 1); ?>
                    </div>
                    <h2 class="profile-username"><?php echo htmlspecialchars($user['username']); ?></h2>
                    <p class="profile-email"><?php echo htmlspecialchars($user['email']); ?></p>
                    <p class="profile-joined">Member since <?php echo date('F j, Y', strtotime($user['created_at'])); ?></p>
                    
                    <!-- Add inactive user notification badge and button -->
                    <?php if (isset($user['status']) && $user['status'] == 'inactive'): ?>
                        <div class="inactive-user-alert">
                            <span class="inactive-badge">Inactive Account</span>
                            <button class="notify-user-btn" onclick="openNotificationModal()">
                                <i class="fas fa-envelope"></i> Send Inactivity Notice
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Profile Stats -->
                <div class="profile-stats">
                    <div class="stat-item">
                        <p class="stat-value"><?php echo count($user_recipes); ?></p>
                        <p class="stat-label">Recipes Created</p>
                    </div>
                    
                    <div class="stat-item">
                        <?php
                            // Count user's favorites - assuming we have this data
                            $favorites_count = isset($user_favorites) ? count($user_favorites) : 0;
                        ?>
                        <p class="stat-value"><?php echo $favorites_count; ?></p>
                        <p class="stat-label">Favorite Recipes</p>
                    </div>
                    
                    <div class="stat-item">
                        <?php
                            // Calculate user's account age in days
                            $created_date = new DateTime($user['created_at']);
                            $current_date = new DateTime();
                            $account_age = $created_date->diff($current_date)->days;
                        ?>
                        <p class="stat-value"><?php echo $account_age; ?></p>
                        <p class="stat-label">Days Active</p>
                    </div>
                </div>
                
                <!-- Profile Details Sections -->
                <div class="profile-sections">
                    <!-- Dietary Preferences Section -->
                    <div class="profile-section">
                        <h3 class="section-title">
                            <i class="fas fa-utensils"></i> Dietary Preferences
                        </h3>
                        
                        <?php if (empty($user_preferences)): ?>
                            <div class="no-items">No dietary preferences specified</div>
                        <?php else: ?>
                            <div class="preference-tags">
                                <?php foreach ($user_preferences as $preference): ?>
                                    <span class="tag preference-tag"><?php echo htmlspecialchars($preference); ?></span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Allergies Section -->
                    <div class="profile-section">
                        <h3 class="section-title">
                            <i class="fas fa-exclamation-triangle"></i> Food Allergies
                        </h3>
                        
                        <?php if (empty($user_allergies)): ?>
                            <div class="no-items">No food allergies specified</div>
                        <?php else: ?>
                            <div class="allergy-tags">
                                <?php foreach ($user_allergies as $allergy): ?>
                                    <span class="tag allergy-tag"><?php echo htmlspecialchars($allergy); ?></span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- User's Recipes Section -->
                    <div class="profile-section">
                        <h3 class="section-title">
                            <i class="fas fa-book-open"></i> User's Recipes
                        </h3>
                        
                        <?php if (empty($user_recipes)): ?>
                            <div class="no-items">This user has not created any recipes yet</div>
                        <?php else: ?>
                            <table class="recipe-table">
                                <thead>
                                    <tr>
                                        <th>Recipe Name</th>
                                        <th>Created</th>
                                        <th>Visibility</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($user_recipes as $recipe): ?>
                                        <tr>
                                            <td>
                                                <a href="index.php?action=adminViewRecipe&id=<?php echo $recipe['recipe_id']; ?>" class="recipe-link">
                                                    <?php echo htmlspecialchars($recipe['recipe_name']); ?>
                                                </a>
                                            </td>
                                            <td><?php echo date('M d, Y', strtotime($recipe['created_at'])); ?></td>
                                            <td>
                                                <?php if ($recipe['public']): ?>
                                                    <span class="public-badge">Public</span>
                                                <?php else: ?>
                                                    <span class="private-badge">Private</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="index.php?action=adminViewRecipe&id=<?php echo $recipe['recipe_id']; ?>" class="recipe-link">
                                                    <i class="fas fa-eye"></i> View
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <!-- Email Notification Modal -->
    <div id="notificationModal" class="confirmation-modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeNotificationModal()">&times;</span>
            <h2 class="modal-title">Send Inactivity Notice</h2>
            <p class="modal-text">
                Send an email to notify <strong><?php echo htmlspecialchars($user['username']); ?></strong> that their account 
                is inactive. If they don't log in within 3 days, their account may be deleted.
            </p>
            
            <form action="index.php?action=adminSendInactivityNotice" method="POST" id="notificationForm">
                <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                <input type="hidden" name="email" value="<?php echo $user['email']; ?>">
                
                <div class="form-group" style="margin-bottom: 20px; text-align: left;">
                    <label for="email_subject" style="display: block; margin-bottom: 5px; font-weight: bold;">Email Subject:</label>
                    <input type="text" id="email_subject" name="email_subject" 
                        value="Important: Your SpiceShelf Account Status" 
                        style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                </div>
                
                <div class="form-group" style="margin-bottom: 20px; text-align: left;">
                    <label for="email_message" style="display: block; margin-bottom: 5px; font-weight: bold;">Email Message:</label>
                    <textarea id="email_message" name="email_message" rows="6" 
                        style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; resize: vertical;"
                    >Dear <?php echo htmlspecialchars($user['username']); ?>,

We noticed that you haven't logged into your SpiceShelf account recently. Per our inactive account policy, accounts that remain inactive for extended periods may be deleted.

Please log in to your account within the next 3 days to keep it active. If you do not log in, your account may be deleted along with all your recipes and data.

Thank you,
The SpiceShelf Team</textarea>
                </div>
                
                <div class="modal-actions">
                    <button type="button" class="btn-cancel" onclick="closeNotificationModal()">Cancel</button>
                    <button type="submit" class="btn-notify">Send Email</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Confirmation Modal for User Deletion -->
    <div id="confirmationModal" class="confirmation-modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeConfirmationModal()">&times;</span>
            <h2 class="modal-title">Confirm User Deletion</h2>
            <p class="modal-text">
                Are you sure you want to delete the user <strong><?php echo htmlspecialchars($user['username']); ?></strong>?<br>
                This action cannot be undone and will remove all of the user's recipes, favorites, and other data.
            </p>
            <div class="modal-actions">
                <button class="btn-cancel" onclick="closeConfirmationModal()">Cancel</button>
                <form action="index.php?action=adminDeleteUser" method="POST" style="display:inline;">
                    <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                    <button type="submit" class="btn-confirm">Delete User</button>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        // Modal functions
        function openConfirmationModal() {
            document.getElementById('confirmationModal').style.display = 'block';
        }
        
        function closeConfirmationModal() {
            document.getElementById('confirmationModal').style.display = 'none';
        }
        
        function openNotificationModal() {
            document.getElementById('notificationModal').style.display = 'block';
        }
        
        function closeNotificationModal() {
            document.getElementById('notificationModal').style.display = 'none';
        }
        
        // Close modal when clicking outside of it
        window.onclick = function(event) {
            if (event.target == document.getElementById('confirmationModal')) {
                closeConfirmationModal();
            }
            if (event.target == document.getElementById('notificationModal')) {
                closeNotificationModal();
            }
        }
    </script>
</body>
</html>