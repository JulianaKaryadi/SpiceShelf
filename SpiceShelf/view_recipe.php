<?php
// adminviews/view_recipe.php

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php?action=adminLogin');
    exit;
}

// Check if recipe data is available
if (!isset($recipe) || !$recipe) {
    echo "<div style='text-align:center; margin-top:50px;'>";
    echo "<h2>Recipe not found</h2>";
    echo "<p>The requested recipe does not exist or has been deleted.</p>";
    echo "<a href='index.php?action=adminRecipes' class='btn btn-primary'>Back to Recipes List</a>";
    echo "</div>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Recipe - SpiceShelf Admin</title>
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
        
        /* Recipe View Specific Styles */
        .recipe-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
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
        .delete-recipe-btn {
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
        .delete-recipe-btn:hover {
            background-color: #c82333;
            color: white;
        }
        .delete-recipe-btn i {
            margin-right: 8px;
        }
        .recipe-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }
        .recipe-image-container {
            background-color: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .recipe-image {
            width: 100%;
            height: 300px;
            object-fit: cover;
            display: block;
        }
        .recipe-details {
            background-color: #fff;
            border-radius: 8px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .recipe-name {
            font-size: 24px;
            color: #333;
            margin: 0 0 10px;
        }
        .recipe-meta {
            margin-bottom: 20px;
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
        }
        .recipe-meta-item {
            display: flex;
            align-items: center;
            font-size: 14px;
            color: #666;
        }
        .recipe-meta-item i {
            margin-right: 5px;
            color: #F29E52;
        }
        .recipe-description {
            color: #555;
            margin-bottom: 20px;
            line-height: 1.6;
        }
        .recipe-author {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }
        .author-avatar {
            width: 40px;
            height: 40px;
            background-color: #F29E52;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            color: #fff;
            margin-right: 10px;
        }
        .author-info {
            font-size: 14px;
        }
        .author-name {
            font-weight: 600;
            color: #333;
            display: block;
        }
        .author-link {
            color: #F29E52;
            text-decoration: none;
        }
        .author-link:hover {
            text-decoration: underline;
        }
        .visibility-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            margin-bottom: 5px;
        }
        .public-badge {
            background-color: #d4edda;
            color: #155724;
        }
        .private-badge {
            background-color: #f8d7da;
            color: #721c24;
        }
        .recipe-categories {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 15px;
        }
        .category-tag {
            display: inline-block;
            padding: 5px 10px;
            background-color: #f0f0f0;
            border-radius: 20px;
            font-size: 12px;
            color: #333;
            border: 1px solid #ddd;
        }
        .recipe-sections {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }
        .recipe-section {
            background-color: #fff;
            border-radius: 8px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
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
        .ingredients-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .ingredients-list li {
            display: flex;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px dashed #eee;
        }
        .ingredients-list li:last-child {
            border-bottom: none;
        }
        .ingredients-list li::before {
            content: "\f00c";
            font-family: "Font Awesome 5 Free";
            font-weight: 900;
            color: #F29E52;
            margin-right: 10px;
            font-size: 12px;
        }
        .instructions-list {
            list-style: none;
            padding: 0;
            margin: 0;
            counter-reset: steps;
        }
        .instructions-list li {
            position: relative;
            padding: 15px 0 15px 45px;
            border-bottom: 1px dashed #eee;
            line-height: 1.6;
        }
        .instructions-list li:last-child {
            border-bottom: none;
        }
        .instructions-list li::before {
            counter-increment: steps;
            content: counter(steps);
            position: absolute;
            left: 0;
            top: 15px;
            width: 30px;
            height: 30px;
            background-color: #F29E52;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
        .instructions-text {
            white-space: pre-line;
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
        
        /* Responsive adjustments */
        @media (max-width: 992px) {
            .recipe-container, .recipe-sections {
                grid-template-columns: 1fr;
            }
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
                <li><a href="index.php?action=adminUsers"><i class="fas fa-users"></i> View Users</a></li>
                
                <li class="admin-menu-category">Recipe Management</li>
                <li><a href="index.php?action=adminRecipes" class="active"><i class="fas fa-utensils"></i> View Recipes</a></li>
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
                <h1 class="admin-title">Recipe Details</h1>
                <div class="admin-user-info">
                    <span>Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                    <a href="index.php?action=adminLogout" class="admin-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </header>
            
            <!-- Recipe Header with Back Button -->
            <div class="recipe-header">
                <a href="index.php?action=adminRecipes" class="back-button">
                    <i class="fas fa-arrow-left"></i> Back to Recipes
                </a>
                <button class="delete-recipe-btn" onclick="openConfirmationModal()">
                    <i class="fas fa-trash"></i> Delete Recipe
                </button>
            </div>
            
            <!-- Recipe Overview -->
            <div class="recipe-container">
                <!-- Recipe Image -->
                <div class="recipe-image-container">
                    <img src="<?php echo htmlspecialchars($recipe['image'] ?? 'assets/images/default_recipe.jpg'); ?>" 
                        class="recipe-image" alt="<?php echo htmlspecialchars($recipe['recipe_name']); ?>">
                </div>
                
                <!-- Recipe Details -->
                <div class="recipe-details">
                    <!-- Visibility Badge -->
                    <?php if ($recipe['public']): ?>
                        <span class="visibility-badge public-badge">Public Recipe</span>
                    <?php else: ?>
                        <span class="visibility-badge private-badge">Private Recipe</span>
                    <?php endif; ?>
                    
                    <h2 class="recipe-name"><?php echo htmlspecialchars($recipe['recipe_name']); ?></h2>
                    
                    <!-- Recipe Meta Info -->
                    <div class="recipe-meta">
                        <div class="recipe-meta-item">
                            <i class="fas fa-clock"></i> Prep: <?php echo $recipe['prep_time']; ?> mins
                        </div>
                        <div class="recipe-meta-item">
                            <i class="fas fa-fire"></i> Cook: <?php echo $recipe['cook_time']; ?> mins
                        </div>
                        <div class="recipe-meta-item">
                            <i class="fas fa-users"></i> Serves: <?php echo $recipe['serving_size']; ?>
                        </div>
                        <div class="recipe-meta-item">
                            <i class="fas fa-calendar"></i> Added: <?php echo date('M d, Y', strtotime($recipe['created_at'])); ?>
                        </div>
                    </div>
                    
                    <!-- Recipe Description -->
                    <p class="recipe-description"><?php echo nl2br(htmlspecialchars($recipe['description'])); ?></p>
                    
                    <!-- Recipe Author -->
                    <div class="recipe-author">
                        <div class="author-avatar">
                            <?php echo substr($recipe['username'], 0, 1); ?>
                        </div>
                        <div class="author-info">
                            <span>Created by</span>
                            <span class="author-name">
                                <a href="index.php?action=adminViewUser&id=<?php echo $recipe['user_id']; ?>" class="author-link">
                                    <?php echo htmlspecialchars($recipe['username']); ?>
                                </a>
                            </span>
                        </div>
                    </div>
                    
                    <!-- Recipe Categories -->
                    <?php if (!empty($recipe_categories)): ?>
                        <div class="recipe-categories">
                            <?php foreach ($recipe_categories as $category): ?>
                                <span class="category-tag"><?php echo htmlspecialchars($category); ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Recipe Content Sections -->
            <div class="recipe-sections">
                <!-- Ingredients Section -->
                <div class="recipe-section">
                    <h3 class="section-title">
                        <i class="fas fa-list"></i> Ingredients
                    </h3>
                    
                    <?php if (empty($recipe_ingredients)): ?>
                        <p>No ingredients listed for this recipe.</p>
                    <?php else: ?>
                        <ul class="ingredients-list">
                            <?php foreach ($recipe_ingredients as $ingredient): ?>
                                <li>
                                    <?php 
                                        echo htmlspecialchars($ingredient['quantity']) . ' ';
                                        echo htmlspecialchars($ingredient['measurement']) . ' ';
                                        echo htmlspecialchars($ingredient['ingredient']);
                                    ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
                
                <!-- Instructions Section -->
                <div class="recipe-section">
                    <h3 class="section-title">
                        <i class="fas fa-tasks"></i> Instructions
                    </h3>
                    
                    <?php if (empty($recipe['steps'])): ?>
                        <p>No instructions provided for this recipe.</p>
                    <?php else: ?>
                        <?php
                            // Convert numbered list into array of steps
                            $steps = preg_split('/\r\n|\r|\n/', $recipe['steps']);
                            // Filter out empty lines and lines that are just numbers
                            $steps = array_filter($steps, function($step) {
                                $step = trim($step);
                                return !empty($step) && !preg_match('/^\d+\.?\s*$/', $step);
                            });
                            
                            // Clean up step numbers if present
                            $steps = array_map(function($step) {
                                return preg_replace('/^\d+\.?\s*/', '', $step);
                            }, $steps);
                        ?>
                        
                        <ol class="instructions-list">
                            <?php foreach ($steps as $step): ?>
                                <li><?php echo htmlspecialchars(trim($step)); ?></li>
                            <?php endforeach; ?>
                        </ol>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
    
    <!-- Confirmation Modal for Recipe Deletion -->
    <div id="confirmationModal" class="confirmation-modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeConfirmationModal()">&times;</span>
            <h2 class="modal-title">Confirm Recipe Deletion</h2>
            <p class="modal-text">
                Are you sure you want to delete the recipe <strong><?php echo htmlspecialchars($recipe['recipe_name']); ?></strong>?<br>
                This action cannot be undone.
            </p>
            <div class="modal-actions">
                <button class="btn-cancel" onclick="closeConfirmationModal()">Cancel</button>
                <form action="index.php?action=adminDeleteRecipe" method="POST" style="display:inline;">
                    <input type="hidden" name="recipe_id" value="<?php echo $recipe['recipe_id']; ?>">
                    <button type="submit" class="btn-confirm">Delete Recipe</button>
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
        
        // Close modal when clicking outside of it
        window.onclick = function(event) {
            if (event.target == document.getElementById('confirmationModal')) {
                closeConfirmationModal();
            }
        }
    </script>
</body>
</html>