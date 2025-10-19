<?php
// adminviews/categories.php

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
    <title>Categories Management - SpiceShelf Admin</title>
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
                <li><a href="index.php?action=adminIngredients"><i class="fas fa-apple-alt"></i> Ingredients</a></li>
                <li><a href="index.php?action=adminCategories" class="active"><i class="fas fa-tags"></i> Categories</a></li>
                
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
                <h1 class="admin-title">Categories Management</h1>
                <div class="admin-user-info">
                    <span>Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                    <a href="index.php?action=adminLogout" class="admin-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </header>
            
            <!-- Success/Error Messages -->
            <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle"></i> Category added successfully.
                </div>
            <?php elseif (isset($_GET['success']) && $_GET['success'] == 2): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle"></i> Category updated successfully.
                </div>
            <?php elseif (isset($_GET['success']) && $_GET['success'] == 3): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle"></i> Category deleted successfully.
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['error']) && $_GET['error'] == 1): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i> Error processing category. It may be in use in recipes.
                </div>
            <?php endif; ?>
            
            <!-- Categories Header -->
            <div class="admin-panel-header">
                <h2><i class="fas fa-tags"></i> Manage Recipe Categories</h2>
                <a href="#" class="add-ingredient-btn" onclick="openAddCategoryModal()">
                    <i class="fas fa-plus"></i> Add New Category
                </a>
            </div>
            
            <!-- Search Form -->
            <form class="search-form" action="index.php" method="GET">
                <input type="hidden" name="action" value="adminCategories">
                <input type="text" name="search" placeholder="Search categories" 
                       value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                <button type="submit"><i class="fas fa-search"></i> Search</button>
            </form>
            
            <?php if (empty($categories)): ?>
                <!-- No categories found -->
                <div class="no-results">
                    <i class="fas fa-tags"></i>
                    <h3>No categories found</h3>
                    <p>
                        <?php if (isset($_GET['search'])): ?>
                            No categories match your search criteria. Try different keywords.
                        <?php else: ?>
                            No recipe categories have been added yet. Add your first category to get started.
                        <?php endif; ?>
                    </p>
                    <button class="btn btn-submit" onclick="openAddCategoryModal()">Add Category</button>
                </div>
            <?php else: ?>
                <!-- Categories Cards -->
                <div class="ingredient-cards">
                    <?php foreach ($categories as $category): ?>
                    <div class="ingredient-card">
                        <h3 class="ingredient-name">
                            <i class="fas fa-tag"></i> 
                            <?php echo htmlspecialchars($category['category_name'], ENT_QUOTES, 'UTF-8'); ?>
                        </h3>
                        
                        <div class="ingredient-info">
                            <p>
                                <i class="fas fa-utensils"></i> 
                                Used in: <?php echo (int) ($category['recipe_count'] ?? 0); ?> recipes
                            </p>
                            
                            <?php if (!empty($category['preferences'])): ?>
                            <p>
                                <i class="fas fa-leaf"></i> 
                                Dietary Preferences: 
                                <?php 
                                    $preferenceNames = array_map(function($preference) {
                                        return htmlspecialchars($preference['preference_name'], ENT_QUOTES, 'UTF-8');
                                    }, $category['preferences']);
                                    echo implode(', ', $preferenceNames);
                                ?>
                            </p>
                            <?php endif; ?>
                            
                            <p>
                                <i class="fas fa-hashtag"></i> 
                                Category ID: #<?php echo (int) $category['category_id']; ?>
                            </p>
                        </div>
                        
                        <div class="ingredient-actions">
                            <a href="#" class="ingredient-btn" onclick="openEditCategoryModal(<?php echo (int)$category['category_id']; ?>, '<?php echo addslashes(htmlspecialchars($category['category_name'], ENT_QUOTES, 'UTF-8')); ?>', [<?php echo implode(',', array_map(function($preference) { return (int)$preference['preference_id']; }, $category['preferences'] ?? [])); ?>])">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <a href="index.php?action=adminDeleteCategory&id=<?php echo (int)$category['category_id']; ?>" 
                            class="ingredient-btn delete"
                            onclick="return confirm('Are you sure you want to delete this category? It will be removed from all recipes.');">
                                <i class="fas fa-trash"></i> Delete
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Pagination -->
                <div class="pagination">
                    <?php 
                        // Calculate total pages - this should be provided in the controller
                        $total_pages = isset($total_pages) ? $total_pages : 1;
                        $current_page = isset($_GET['page']) ? intval($_GET['page']) : 1;
                        
                        // Previous page link
                        if ($current_page > 1): 
                    ?>
                        <a href="index.php?action=adminCategories&page=<?php echo $current_page - 1; ?><?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?>" class="pagination-link">
                            <i class="fas fa-chevron-left"></i> Previous
                        </a>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="index.php?action=adminCategories&page=<?php echo $i; ?><?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?>" 
                           class="pagination-link <?php echo ($i == $current_page) ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                    
                    <?php if ($current_page < $total_pages): ?>
                        <a href="index.php?action=adminCategories&page=<?php echo $current_page + 1; ?><?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?>" class="pagination-link">
                            Next <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>
    
    <!-- Add Category Modal -->
    <div id="addCategoryModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeAddCategoryModal()">&times;</span>
            <h2 class="modal-title"><i class="fas fa-plus"></i> Add New Category</h2>
            
            <form class="modal-form" action="index.php?action=adminAddCategory" method="POST">
                <div class="form-group">
                    <label for="category_name">Category Name</label>
                    <input type="text" id="category_name" name="category_name" required placeholder="Enter category name">
                </div>
                
                <div class="form-group">
                    <label>Associated Dietary Preferences (if applicable)</label>
                    <div class="checkbox-group">
                        <?php foreach ($dietary_preferences as $preference): ?>
                            <div class="checkbox-item">
                                <input type="checkbox" id="preference_<?php echo $preference['preference_id']; ?>" 
                                    name="preferences[]" value="<?php echo $preference['preference_id']; ?>">
                                <label for="preference_<?php echo $preference['preference_id']; ?>">
                                    <?php echo htmlspecialchars($preference['preference_name']); ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-cancel" onclick="closeAddCategoryModal()">Cancel</button>
                    <button type="submit" class="btn btn-submit">Add Category</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Edit Category Modal -->
    <div id="editCategoryModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeEditCategoryModal()">&times;</span>
            <h2 class="modal-title"><i class="fas fa-edit"></i> Edit Category</h2>
            
            <form class="modal-form" action="index.php?action=adminEditCategory" method="POST">
                <input type="hidden" id="edit_category_id" name="category_id">
                
                <div class="form-group">
                    <label for="edit_category_name">Category Name</label>
                    <input type="text" id="edit_category_name" name="category_name" required placeholder="Enter category name">
                </div>
                
                <div class="form-group">
                    <label>Associated Dietary Preferences (if applicable)</label>
                    <div class="checkbox-group">
                        <?php foreach ($dietary_preferences as $preference): ?>
                            <div class="checkbox-item">
                                <input type="checkbox" id="edit_preference_<?php echo $preference['preference_id']; ?>" 
                                    name="preferences[]" value="<?php echo $preference['preference_id']; ?>"
                                    class="edit-preference-checkbox">
                                <label for="edit_preference_<?php echo $preference['preference_id']; ?>">
                                    <?php echo htmlspecialchars($preference['preference_name']); ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-cancel" onclick="closeEditCategoryModal()">Cancel</button>
                    <button type="submit" class="btn btn-submit">Update Category</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        // Modal functions
        function openAddCategoryModal() {
            document.getElementById('addCategoryModal').style.display = 'block';
            document.getElementById('category_name').focus();
        }
        
        function closeAddCategoryModal() {
            document.getElementById('addCategoryModal').style.display = 'none';
            document.getElementById('category_name').value = '';
        }
        
        function openEditCategoryModal(id, name, preferences) {
            document.getElementById('edit_category_id').value = id;
            document.getElementById('edit_category_name').value = name;
            
            // Reset all checkboxes first
            document.querySelectorAll('.edit-preference-checkbox').forEach(checkbox => {
                checkbox.checked = false;
            });
            
            // Check the appropriate checkboxes
            preferences.forEach(preferenceId => {
                const checkbox = document.getElementById(`edit_preference_${preferenceId}`);
                if (checkbox) checkbox.checked = true;
            });
            
            document.getElementById('editCategoryModal').style.display = 'block';
            document.getElementById('edit_category_name').focus();
        }
        
        function closeEditCategoryModal() {
            document.getElementById('editCategoryModal').style.display = 'none';
            document.getElementById('edit_category_id').value = '';
            document.getElementById('edit_category_name').value = '';
        }
        
        // Close modal when clicking outside of it
        window.onclick = function(event) {
            const addModal = document.getElementById('addCategoryModal');
            const editModal = document.getElementById('editCategoryModal');
            
            if (event.target == addModal) {
                closeAddCategoryModal();
            }
            if (event.target == editModal) {
                closeEditCategoryModal();
            }
        }
        
        // Handle Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeAddCategoryModal();
                closeEditCategoryModal();
            }
        });
        
        // Add any other JavaScript functionality here
        document.addEventListener('DOMContentLoaded', function() {
            // Example: Highlight active menu item
            const currentPath = window.location.search;
            const menuLinks = document.querySelectorAll('.admin-menu a');
            
            menuLinks.forEach(link => {
                if (link.getAttribute('href').includes('adminCategories')) {
                    link.classList.add('active');
                } else {
                    link.classList.remove('active');
                }
            });
        });
    </script>
</body>
</html>