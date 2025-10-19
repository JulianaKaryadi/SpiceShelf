<?php
// adminviews/recipes.php

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php?action=adminLogin');
    exit;
}

// Set default values for pagination variables
$current_page = isset($page) ? $page : 1;
$total_pages = isset($total_pages) ? $total_pages : 1;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Recipes - SpiceShelf Admin</title>
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
        
        /* Recipe Management Specific Styles */
        .success-message {
            background-color: #d4edda;
            color: #155724;
            padding: 12px 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            border-left: 4px solid #28a745;
        }
        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 12px 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            border-left: 4px solid #dc3545;
        }
        .recipes-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .recipes-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .recipe-report-btn {
            display: inline-flex;
            align-items: center;
            padding: 8px 15px;
            background-color: #17a2b8;
            color: white;
            border-radius: 4px;
            text-decoration: none;
            font-size: 14px;
            transition: background-color 0.3s;
        }
        .recipe-report-btn:hover {
            background-color: #138496;
            color: white;
            text-decoration: none;
        }
        .recipe-report-btn i {
            margin-right: 8px;
        }
        .search-filter-container {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .search-form {
            flex: 1;
            min-width: 300px;
            display: flex;
        }
        .search-form input {
            flex: 1;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 4px 0 0 4px;
            font-size: 14px;
        }
        .search-form button {
            padding: 10px 15px;
            background-color: #F29E52;
            color: white;
            border: none;
            border-radius: 0 4px 4px 0;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .search-form button:hover {
            background-color: #e48a3c;
        }
        .filter-dropdown {
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            min-width: 150px;
        }
        .recipes-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .recipe-card {
            background-color: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: transform 0.3s, box-shadow 0.3s;
            display: flex;
            flex-direction: column;
        }
        .recipe-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .recipe-card-image {
            height: 180px;
            background-color: #f8f9fa;
            overflow: hidden;
            position: relative;
        }
        .recipe-card-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s;
        }
        .recipe-card:hover .recipe-card-image img {
            transform: scale(1.05);
        }
        .recipe-visibility {
            position: absolute;
            top: 10px;
            right: 10px;
            padding: 3px 8px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            z-index: 2;
        }
        .public-badge {
            background-color: #d4edda;
            color: #155724;
        }
        .private-badge {
            background-color: #f8d7da;
            color: #721c24;
        }
        .recipe-card-content {
            padding: 15px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        .recipe-card-title {
            font-size: 18px;
            font-weight: 600;
            color: #333;
            margin: 0 0 10px;
            line-height: 1.3;
        }
        .recipe-card-author {
            font-size: 13px;
            color: #777;
            margin-bottom: 10px;
        }
        .recipe-card-author a {
            color: #F29E52;
            text-decoration: none;
        }
        .recipe-card-author a:hover {
            text-decoration: underline;
        }
        .recipe-card-meta {
            display: flex;
            gap: 10px;
            margin-bottom: 10px;
            font-size: 13px;
            color: #666;
        }
        .recipe-card-meta span {
            display: flex;
            align-items: center;
        }
        .recipe-card-meta i {
            margin-right: 5px;
            color: #F29E52;
        }
        .recipe-card-description {
            font-size: 14px;
            color: #555;
            margin-bottom: 15px;
            line-height: 1.5;
            flex: 1;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .recipe-card-actions {
            display: flex;
            justify-content: space-between;
            margin-top: auto;
        }
        .recipe-card-btn {
            padding: 8px 12px;
            background-color: #F29E52;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 13px;
            text-decoration: none;
            transition: background-color 0.3s;
            display: inline-flex;
            align-items: center;
        }
        .recipe-card-btn i {
            margin-right: 5px;
        }
        .recipe-card-btn:hover {
            background-color: #e48a3c;
            color: white;
            text-decoration: none;
        }
        .recipe-card-btn.delete {
            background-color: #dc3545;
        }
        .recipe-card-btn.delete:hover {
            background-color: #c82333;
        }
        .no-results {
            grid-column: 1 / -1;
            background-color: #fff;
            padding: 30px;
            text-align: center;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .no-results i {
            font-size: 48px;
            color: #6c757d;
            margin-bottom: 15px;
        }
        .no-results h3 {
            font-size: 20px;
            color: #333;
            margin-top: 0;
        }
        .no-results p {
            color: #6c757d;
            margin-bottom: 20px;
        }
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 20px;
            gap: 5px;
        }
        .pagination-link {
            display: inline-block;
            padding: 8px 12px;
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 4px;
            color: #333;
            text-decoration: none;
            transition: all 0.3s;
        }
        .pagination-link:hover {
            background-color: #f8f9fa;
            border-color: #F29E52;
        }
        .pagination-link.active {
            background-color: #F29E52;
            border-color: #F29E52;
            color: #fff;
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
                <h1 class="admin-title">Recipe Management</h1>
                <div class="admin-user-info">
                    <span>Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                    <a href="index.php?action=adminLogout" class="admin-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </header>
            
            <!-- Success/Error Messages -->
            <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle"></i> Recipe deleted successfully.
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['error']) && $_GET['error'] == 1): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i> Error deleting recipe. Please try again.
                </div>
            <?php endif; ?>
            
            <!-- Recipes Header -->
            <div class="recipes-header">
                <h2><i class="fas fa-utensils"></i> All Recipes</h2>
                <div class="recipes-actions">
                    <a href="index.php?action=adminRecipeReports" class="recipe-report-btn">
                        <i class="fas fa-chart-pie"></i> View Recipe Reports
                    </a>
                </div>
            </div>
            
            <!-- Search & Filter -->
            <div class="search-filter-container">
                <form class="search-form" action="index.php" method="GET">
                    <input type="hidden" name="action" value="adminRecipes">
                    <input type="text" name="search" placeholder="Search recipes by name or description" 
                           value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                    <button type="submit"><i class="fas fa-search"></i></button>
                </form>
            </div>
            
            <!-- Recipes Grid -->
            <div class="recipes-grid">
                <?php if (empty($recipes)): ?>
                    <div class="no-results">
                        <i class="fas fa-search"></i>
                        <h3>No recipes found</h3>
                        <p>
                            <?php if (isset($_GET['search'])): ?>
                                No recipes match your search criteria. Try different keywords.
                            <?php else: ?>
                                There are no recipes in the database.
                            <?php endif; ?>
                        </p>
                    </div>
                <?php else: ?>
                    <?php foreach ($recipes as $recipe): ?>
                        <div class="recipe-card">
                        <div class="recipe-card-image">
                        <img src="<?php echo htmlspecialchars($recipe['image'] ?? 'assets/images/default_recipe.jpg'); ?>" 
                            alt="<?php echo htmlspecialchars($recipe['recipe_name'] ?? 'Recipe Image'); ?>">
                            
                            <?php if (isset($recipe['public']) && $recipe['public']): ?>
                                <span class="recipe-visibility public-badge">Public</span>
                            <?php else: ?>
                                <span class="recipe-visibility private-badge">Private</span>
                            <?php endif; ?>
                        </div>
                            
                            <div class="recipe-card-content">
                                <h3 class="recipe-card-title"><?php echo htmlspecialchars($recipe['recipe_name']); ?></h3>
                                
                                <div class="recipe-card-author">
                                    By <a href="index.php?action=adminViewUser&id=<?php echo isset($recipe['user_id']) ? $recipe['user_id'] : '0'; ?>" class="author-link"><?php echo isset($recipe['username']) ? htmlspecialchars($recipe['username']) : 'Unknown User'; ?></a>
                                </div>
                                
                                <div class="recipe-card-meta">
                                    <span>
                                        <i class="fas fa-calendar"></i> 
                                        <?php echo date('M d, Y', strtotime($recipe['created_at'])); ?>
                                    </span>
                                </div>
                                
                                <p class="recipe-card-description">
                                    <?php 
                                        $description = isset($recipe['description']) ? $recipe['description'] : '';
                                        echo htmlspecialchars(substr($description, 0, 100));
                                        if (strlen($description) > 100) echo '...';
                                    ?>
                                </p>
                                
                                <div class="recipe-card-actions">
                                    <a href="index.php?action=adminViewRecipe&id=<?php echo $recipe['recipe_id']; ?>" class="recipe-card-btn">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                    <a href="#" class="recipe-card-btn delete" onclick="openConfirmationModal(<?php echo $recipe['recipe_id']; ?>, '<?php echo htmlspecialchars(addslashes($recipe['recipe_name'])); ?>')">
                                        <i class="fas fa-trash"></i> Delete
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <!-- Pagination -->
            <?php if (!empty($recipes)): ?>
                <div class="pagination">
                    <?php 
                        // Build the base URL for pagination links
                        $params = $_GET;
                        unset($params['page']); // Remove existing page parameter
                        $query_string = http_build_query($params);
                        $base_url = 'index.php?' . ($query_string ? $query_string . '&' : '') . 'page=';
                        
                        // Previous page link
                        if ($current_page > 1): 
                    ?>
                        <a href="<?php echo $base_url . ($current_page - 1); ?>" class="pagination-link">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    <?php endif; ?>
                    
                    <?php
                        // Determine pagination range
                        $start_page = max(1, $current_page - 2);
                        $end_page = min($total_pages, $current_page + 2);
                        
                        // Show first page if not in range
                        if ($start_page > 1): 
                    ?>
                        <a href="<?php echo $base_url . '1'; ?>" class="pagination-link">1</a>
                        <?php if ($start_page > 2): ?>
                            <span class="pagination-link" style="background:none;border:none;">...</span>
                        <?php endif; ?>
                    <?php endif; ?>
                    
                    <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                        <a href="<?php echo $base_url . $i; ?>" 
                           class="pagination-link <?php echo ($i == $current_page) ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                    
                    <?php
                        // Show last page if not in range
                        if ($end_page < $total_pages): 
                    ?>
                        <?php if ($end_page < $total_pages - 1): ?>
                            <span class="pagination-link" style="background:none;border:none;">...</span>
                        <?php endif; ?>
                        <a href="<?php echo $base_url . $total_pages; ?>" class="pagination-link">
                            <?php echo $total_pages; ?>
                        </a>
                    <?php endif; ?>
                    
                    <?php if ($current_page < $total_pages): ?>
                        <a href="<?php echo $base_url . ($current_page + 1); ?>" class="pagination-link">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>
    
    <!-- Confirmation Modal for Recipe Deletion -->
    <div id="confirmationModal" class="confirmation-modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeConfirmationModal()">&times;</span>
            <h2 class="modal-title">Confirm Recipe Deletion</h2>
            <p class="modal-text">
                Are you sure you want to delete the recipe <strong id="recipeNameToDelete"></strong>?<br>
                This action cannot be undone.
            </p>
            <div class="modal-actions">
                <button class="btn-cancel" onclick="closeConfirmationModal()">Cancel</button>
                <form id="deleteRecipeForm" action="index.php?action=adminDeleteRecipe" method="POST" style="display:inline;">
                    <input type="hidden" id="recipeIdToDelete" name="recipe_id" value="">
                    <button type="submit" class="btn-confirm">Delete Recipe</button>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        // Modal functions
        function openConfirmationModal(recipeId, recipeName) {
            document.getElementById('recipeIdToDelete').value = recipeId;
            document.getElementById('recipeNameToDelete').innerText = recipeName;
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
        
        // Preserve filters when sorting or changing page
        function updateQueryParam(param, value) {
            // Get current URL parts
            let url = new URL(window.location.href);
            let params = new URLSearchParams(url.search);
            
            // Update the specific parameter
            params.set(param, value);
            
            // Rebuild URL and navigate
            url.search = params.toString();
            window.location.href = url.toString();
            return false;
        }
    </script>
</body>
</html>