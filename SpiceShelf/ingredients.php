<?php
// adminviews/ingredients.php

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php?action=adminLogin');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ingredients Management - SpiceShelf Admin</title>
    <!-- Add Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/adminIngredients.css">
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
                <li><a href="index.php?action=adminRecipes"><i class="fas fa-utensils"></i> View Recipes</a></li>
                <li><a href="index.php?action=adminIngredients" class="active"><i class="fas fa-apple-alt"></i> Ingredients</a></li>
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
                <h1 class="admin-title">Ingredients Management</h1>
                <div class="admin-user-info">
                    <span>Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                    <a href="index.php?action=adminLogout" class="admin-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </header>
            
            <!-- Success/Error Messages -->
            <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle"></i> Ingredient added successfully.
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['error']) && $_GET['error'] == 1): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i> Error deleting ingredient. It may be in use in recipes.
                </div>
            <?php endif; ?>
            
            <!-- Ingredients Header -->
            <div class="admin-panel-header">
                <h2><i class="fas fa-apple-alt"></i> Manage Ingredients</h2>
                <a href="#" class="add-ingredient-btn" onclick="openAddIngredientModal()">
                    <i class="fas fa-plus"></i> Add New Ingredient
                </a>
            </div>
            
            <!-- Search Form -->
            <form class="search-form" action="index.php" method="GET">
                <input type="hidden" name="action" value="adminIngredients">
                <input type="text" name="search" placeholder="Search ingredients" 
                       value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                <button type="submit"><i class="fas fa-search"></i> Search</button>
            </form>
            
            <?php if (empty($ingredients)): ?>
                <!-- No ingredients found -->
                <div class="no-results">
                    <i class="fas fa-seedling"></i>
                    <h3>No ingredients found</h3>
                    <p>
                        <?php if (isset($_GET['search'])): ?>
                            No ingredients match your search criteria. Try different keywords.
                        <?php else: ?>
                            No ingredients have been added yet. Add your first ingredient to get started.
                        <?php endif; ?>
                    </p>
                    <button class="btn btn-submit" onclick="openAddIngredientModal()">Add Ingredient</button>
                </div>
            <?php else: ?>
                <!-- Ingredients Cards -->
                <?php foreach ($ingredients as $ingredient): ?>
                <div class="ingredient-card">
                    <h3 class="ingredient-name">
                        <i class="fas fa-leaf"></i> 
                        <?php echo htmlspecialchars($ingredient['name'], ENT_QUOTES, 'UTF-8'); ?>
                    </h3>
                    
                    <div class="ingredient-info">
                        <p>
                            <i class="fas fa-utensils"></i> 
                            Used in: <?php echo (int) $ingredient['recipe_count']; ?> recipes
                        </p>
                        
                        <?php if (!empty($ingredient['allergies'])): ?>
                        <p>
                            <i class="fas fa-exclamation-triangle"></i> 
                            Allergens: 
                            <?php 
                                $allergyNames = array_map(function($allergy) {
                                    return htmlspecialchars($allergy['allergy_name'], ENT_QUOTES, 'UTF-8');
                                }, $ingredient['allergies']);
                                echo implode(', ', $allergyNames);
                            ?>
                        </p>
                        <?php endif; ?>
                    </div>
                    
                    <div class="ingredient-actions">
                        <a href="#" class="ingredient-btn" onclick="openEditIngredientModal(<?php echo (int)$ingredient['ingredient_id']; ?>, '<?php echo addslashes(htmlspecialchars($ingredient['name'], ENT_QUOTES, 'UTF-8')); ?>', [<?php echo implode(',', array_map(function($allergy) { return (int)$allergy['allergy_id']; }, $ingredient['allergies'])); ?>])">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <a href="index.php?action=adminDeleteIngredient&id=<?php echo (int)$ingredient['ingredient_id']; ?>" 
                        class="ingredient-btn delete"
                        onclick="return confirm('Are you sure you want to delete this ingredient? It will be removed from all recipe filters.');">
                            <i class="fas fa-trash"></i> Delete
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
                
                <!-- Pagination -->
                <div class="pagination">
                    <?php 
                        // Calculate total pages - this should be provided in the controller
                        $total_pages = isset($total_pages) ? $total_pages : 1;
                        $current_page = isset($_GET['page']) ? intval($_GET['page']) : 1;
                        
                        // Previous page link
                        if ($current_page > 1): 
                    ?>
                        <a href="index.php?action=adminIngredients&page=<?php echo $current_page - 1; ?>" class="pagination-link">
                            <i class="fas fa-chevron-left"></i> Previous
                        </a>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="index.php?action=adminIngredients&page=<?php echo $i; ?>" 
                           class="pagination-link <?php echo ($i == $current_page) ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                    
                    <?php if ($current_page < $total_pages): ?>
                        <a href="index.php?action=adminIngredients&page=<?php echo $current_page + 1; ?>" class="pagination-link">
                            Next <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>
    
    <!-- Add Ingredient Modal -->
    <div id="addIngredientModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeAddIngredientModal()">&times;</span>
            <h2 class="modal-title"><i class="fas fa-plus"></i> Add New Ingredient</h2>
            
            <form class="modal-form" action="index.php?action=adminAddIngredient" method="POST">
                <div class="form-group">
                    <label for="name">Ingredient Name</label>
                    <input type="text" id="name" name="name" required>
                </div>
                
                <div class="form-group">
                    <label>Associated Allergies (if applicable)</label>
                    <div class="checkbox-group">
                        <?php foreach ($allergies as $allergy): ?>
                            <div class="checkbox-item">
                                <input type="checkbox" id="allergy_<?php echo $allergy['allergy_id']; ?>" 
                                    name="allergies[]" value="<?php echo $allergy['allergy_id']; ?>">
                                <label for="allergy_<?php echo $allergy['allergy_id']; ?>">
                                    <?php echo htmlspecialchars($allergy['allergy_name']); ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-cancel" onclick="closeAddIngredientModal()">Cancel</button>
                    <button type="submit" class="btn btn-submit">Add Ingredient</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Edit Ingredient Modal -->
    <div id="editIngredientModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeEditIngredientModal()">&times;</span>
            <h2 class="modal-title"><i class="fas fa-edit"></i> Edit Ingredient</h2>
            
            <form class="modal-form" action="index.php?action=adminEditIngredient" method="POST">
                <input type="hidden" id="edit_ingredient_id" name="ingredient_id">
                
                <div class="form-group">
                    <label for="edit_name">Ingredient Name</label>
                    <input type="text" id="edit_name" name="name" required>
                </div>
                
                <div class="form-group">
                    <label>Associated Allergies (if applicable)</label>
                    <div class="checkbox-group">
                        <?php foreach ($allergies as $allergy): ?>
                            <div class="checkbox-item">
                                <input type="checkbox" id="edit_allergy_<?php echo $allergy['allergy_id']; ?>" 
                                    name="allergies[]" value="<?php echo $allergy['allergy_id']; ?>"
                                    class="edit-allergy-checkbox">
                                <label for="edit_allergy_<?php echo $allergy['allergy_id']; ?>">
                                    <?php echo htmlspecialchars($allergy['allergy_name']); ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-cancel" onclick="closeEditIngredientModal()">Cancel</button>
                    <button type="submit" class="btn btn-submit">Update Ingredient</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        // Modal functions
        function openAddIngredientModal() {
            document.getElementById('addIngredientModal').style.display = 'block';
        }
        
        function closeAddIngredientModal() {
            document.getElementById('addIngredientModal').style.display = 'none';
        }
        
        function openEditIngredientModal(id, name, allergies) {
            document.getElementById('edit_ingredient_id').value = id;
            document.getElementById('edit_name').value = name;
            
            // Reset all checkboxes first
            document.querySelectorAll('.edit-allergy-checkbox').forEach(checkbox => {
                checkbox.checked = false;
            });
            
            // Check the appropriate checkboxes
            allergies.forEach(allergyId => {
                const checkbox = document.getElementById(`edit_allergy_${allergyId}`);
                if (checkbox) checkbox.checked = true;
            });
            
            document.getElementById('editIngredientModal').style.display = 'block';
        }
        
        function closeEditIngredientModal() {
            document.getElementById('editIngredientModal').style.display = 'none';
        }
        
        // Close modal when clicking outside of it
        window.onclick = function(event) {
            if (event.target == document.getElementById('addIngredientModal')) {
                closeAddIngredientModal();
            }
            if (event.target == document.getElementById('editIngredientModal')) {
                closeEditIngredientModal();
            }
        }
        
        // Add any other JavaScript functionality here
        document.addEventListener('DOMContentLoaded', function() {
            // Example: Highlight active menu item
            const currentPath = window.location.search;
            const menuLinks = document.querySelectorAll('.admin-menu a');
            
            menuLinks.forEach(link => {
                if (link.getAttribute('href').includes('adminIngredients')) {
                    link.classList.add('active');
                } else {
                    link.classList.remove('active');
                }
            });
        });
    </script>
</body>
</html>