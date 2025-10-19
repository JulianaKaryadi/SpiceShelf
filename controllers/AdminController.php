<?php
// controllers/AdminController.php

require_once 'models/Admin.php';

class AdminController {
    private $conn;
    private $adminModel;

    public function __construct($conn) {
        $this->conn = $conn;
        $this->adminModel = new Admin($conn);

        // DEBUG ONLY
        error_log("AdminController constructed. Current action: " . ($_GET['action'] ?? 'none'));
        error_log("Admin session ID: " . (isset($_SESSION['admin_id']) ? $_SESSION['admin_id'] : 'not set'));
    }
    
    /**
     * Check if admin is authenticated for protected routes
     */
    private function checkAdminAuth() {
        // List of actions that don't require authentication
        $public_actions = ['adminLogin'];
        
        // Get the current action from router
        $action = isset($_GET['action']) ? $_GET['action'] : '';
        
        // If action requires authentication and admin is not logged in
        if (!in_array($action, $public_actions) && !isset($_SESSION['admin_id'])) {
            header('Location: index.php?action=adminLogin');
            exit;
        } else if ($action === 'adminLogin' && isset($_SESSION['admin_id'])) {
            // If already logged in and trying to access login page, redirect to dashboard
            header('Location: index.php?action=adminDashboard');
            exit;
        }
    }
    
    /**
     * Admin login functionality
     */
    public function adminLogin() {
        error_log("adminLogin method called");
        
        // Check if admin is already logged in
        if (isset($_SESSION['admin_id'])) {
            error_log("Admin already logged in, redirecting to dashboard");
            // Redirect to admin dashboard
            header('Location: index.php?action=adminDashboard');
            exit;
        }
        
        // Check if form is submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            error_log("Admin login form submitted");
            // Sanitize and validate inputs
            $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
            $password = $_POST['password'];
            
            // Validate inputs
            $errors = [];
            
            if (empty($username)) {
                $errors[] = "Username is required";
            }
            
            if (empty($password)) {
                $errors[] = "Password is required";
            }
            
            // If no errors, attempt login
            if (empty($errors)) {
                // Debug logging
                error_log("Attempting admin login for username: " . $username);
                
                $admin = $this->adminModel->login($username, $password);
                
                if ($admin) {
                    // Login successful
                    error_log("Admin login successful for: " . $username);
                    
                    // Start session if not already started
                    if (session_status() === PHP_SESSION_NONE) {
                        session_start();
                    }
                    
                    // Set admin session
                    $_SESSION['admin_id'] = $admin['id'];
                    $_SESSION['admin_username'] = $admin['username'];
                    $_SESSION['admin_email'] = $admin['email'];
                    $_SESSION['is_admin'] = true;
                    
                    // Redirect to admin dashboard
                    header('Location: index.php?action=adminDashboard');
                    exit;
                } else {
                    // Login failed
                    error_log("Admin login failed for: " . $username);
                    $errors[] = "Invalid username or password";
                }
            }
            
            // Load the login view with errors
            include 'adminviews/login.php';
        } else {
            error_log("Displaying admin login form");
            // Display the login form
            include 'adminviews/login.php';
        }
    }
    
    /**
     * Admin logout functionality
     */
    public function adminLogout() {
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Unset admin session variables
        unset($_SESSION['admin_id']);
        unset($_SESSION['admin_username']);
        unset($_SESSION['admin_email']);
        unset($_SESSION['is_admin']);
        
        // Redirect to admin login
        header('Location: index.php?action=adminLogin');
        exit;
    }
    
    /**
     * Admin dashboard
     */
    public function adminDashboard() {
        // Update user statuses first
        $this->updateUserActivityStatus();

        // Count total users
        $total_users = $this->adminModel->countUsers();
        
        // Count total recipes
        $total_recipes = $this->adminModel->countRecipes();
        
        // Count total ingredients
        $total_ingredients = $this->adminModel->countIngredients();
        
        // Count total categories
        $total_categories = $this->adminModel->countCategories();
        
        // Get data for dashboard charts
        $user_engagement_data = $this->adminModel->getUserEngagementData();
        $recipe_performance_data = $this->adminModel->getRecipePerformanceData();
        $category_distribution_data = $this->adminModel->getCategoryDistributionData();
        $ad_performance_data = $this->adminModel->getAdPerformanceData();
        
        // Include dashboard view
        include 'adminviews/dashboard.php';
    }
    
    /**
     * Manage users
     */
    public function adminUsers() {
        // Update user statuses first
        $this->updateUserActivityStatus();

        // Get search parameter
        $search = isset($_GET['search']) ? $_GET['search'] : '';
        
        // Get status filter
        $status = isset($_GET['status']) ? $_GET['status'] : '';
        
        // Get pagination parameters
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $items_per_page = 10; // Show 10 users per page
        $offset = ($page - 1) * $items_per_page;
        
        // Get total users count for pagination
        $total_users_count = $this->adminModel->countUsers($search, $status);
        $total_pages = ceil($total_users_count / $items_per_page);
        
        // Get users with recipe count, filtered by status if provided
        $users = $this->adminModel->getAllUsers($search, $items_per_page, $offset, $status);
        
        // Include users view
        include 'adminviews/users.php';
    }
    
    /**
     * View specific user details
     */
    public function adminViewUser() {
        if (!isset($_GET['id'])) {
            header('Location: index.php?action=adminUsers');
            exit;
        }
        
        $user_id = $_GET['id'];
        
        // Get user details
        $user = $this->adminModel->getUserById($user_id);
        
        // Get user's recipes
        $user_recipes = $this->adminModel->getUserRecipes($user_id);
        
        // Get user's dietary preferences
        $user_preferences = $this->adminModel->getUserDietaryPreferences($user_id);
        
        // Get user's allergies
        $user_allergies = $this->adminModel->getUserAllergies($user_id);

        // Get user's favorite recipes
        $user_favorites = $this->adminModel->getUserFavorites($user_id);
        
        // Include view user view
        include 'adminviews/view_user.php';
    }
    
    /**
     * Manage recipes
     */
    public function adminRecipes() {
        // Get search parameter
        $search = isset($_GET['search']) ? $_GET['search'] : '';
        
        // Get pagination parameters
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $items_per_page = 10; // Show 10 recipes per page
        $offset = ($page - 1) * $items_per_page;
        
        // Get total recipes count for pagination
        $total_recipes = $this->adminModel->countRecipes($search);
        $total_pages = ceil($total_recipes / $items_per_page);
        
        // Get recipes with user info
        $recipes = $this->adminModel->getAllRecipes($search, $items_per_page, $offset);
        
        // Include recipes view
        include 'adminviews/recipes.php';
    }
    
    /**
     * View specific recipe details
     */
    public function adminViewRecipe() {
        if (!isset($_GET['id'])) {
            header('Location: index.php?action=adminRecipes');
            exit;
        }
        
        $recipe_id = $_GET['id'];
        
        // Get recipe details
        $recipe = $this->adminModel->getRecipeById($recipe_id);
        
        // Get recipe ingredients
        $recipe_ingredients = $this->adminModel->getRecipeIngredients($recipe_id);
        
        // Get recipe categories
        $recipe_categories = $this->adminModel->getRecipeCategories($recipe_id);
        
        // Include view recipe view
        include 'adminviews/view_recipe.php';
    }
    
    /**
     * Manage ingredients
     */
    public function adminIngredients() {
        // Get pagination data
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 20; // Items per page
        $offset = ($page - 1) * $limit;
        
        // Get search term
        $search = isset($_GET['search']) ? $_GET['search'] : '';
        
        // Prepare the query
        if (!empty($search)) {
            $query = "SELECT i.*, COUNT(ri.id) as recipe_count 
                    FROM ingredients i 
                    LEFT JOIN recipe_ingredients ri ON i.ingredient_id = ri.ingredient_id 
                    WHERE i.name LIKE ? 
                    GROUP BY i.ingredient_id 
                    ORDER BY i.name 
                    LIMIT ?, ?";
            $stmt = $this->conn->prepare($query);
            $searchParam = "%" . $search . "%";
            $stmt->bind_param("sii", $searchParam, $offset, $limit);
        } else {
            $query = "SELECT i.*, COUNT(ri.id) as recipe_count 
                    FROM ingredients i 
                    LEFT JOIN recipe_ingredients ri ON i.ingredient_id = ri.ingredient_id 
                    GROUP BY i.ingredient_id 
                    ORDER BY i.name 
                    LIMIT ?, ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("ii", $offset, $limit);
        }
        
        $stmt->execute();
        $ingredients = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        // Get total ingredients for pagination
        if (!empty($search)) {
            $countQuery = "SELECT COUNT(*) as total FROM ingredients WHERE name LIKE ?";
            $stmt = $this->conn->prepare($countQuery);
            $stmt->bind_param("s", $searchParam);
        } else {
            $countQuery = "SELECT COUNT(*) as total FROM ingredients";
            $stmt = $this->conn->prepare($countQuery);
        }
        
        $stmt->execute();
        $totalIngredients = $stmt->get_result()->fetch_assoc()['total'];
        $total_pages = ceil($totalIngredients / $limit);
        
        // Get allergies for the form
        $allergiesQuery = "SELECT * FROM allergies WHERE allergy_name != 'None' ORDER BY allergy_name";
        $allergies = $this->conn->query($allergiesQuery)->fetch_all(MYSQLI_ASSOC);
        
        // Get allergens for each ingredient
        $recipeModel = new Recipe($this->conn);
        foreach ($ingredients as &$ingredient) {
            $ingredient['allergies'] = $recipeModel->getIngredientAllergies($ingredient['ingredient_id']);
        }
        
        include('adminviews/ingredients.php');
    }
    
    /**
     * Add new ingredient
     */
    public function adminAddIngredient() {
        $name = $_POST['name'];
        $allergies = isset($_POST['allergies']) ? $_POST['allergies'] : [];
        
        // Insert the ingredient
        $query = "INSERT INTO ingredients (name) VALUES (?)";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $name);
        
        if ($stmt->execute()) {
            $ingredient_id = $stmt->insert_id;
            
            // Insert allergen associations
            $recipeModel = new Recipe($this->conn);
            foreach ($allergies as $allergy_id) {
                $recipeModel->addAllergyIngredientMapping($allergy_id, $ingredient_id);
            }
            
            header("Location: index.php?action=adminIngredients&success=1");
        } else {
            header("Location: index.php?action=adminIngredients&error=2");
        }
        exit;
    }

    public function adminEditIngredient() {
        $ingredient_id = $_POST['ingredient_id'];
        $name = $_POST['name'];
        $allergies = isset($_POST['allergies']) ? $_POST['allergies'] : [];
        
        // Update the ingredient name
        $query = "UPDATE ingredients SET name = ? WHERE ingredient_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("si", $name, $ingredient_id);
        
        if ($stmt->execute()) {
            // Delete existing allergen associations
            $query = "DELETE FROM allergy_ingredient_mapping WHERE ingredient_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $ingredient_id);
            $stmt->execute();
            
            // Insert new allergen associations
            $recipeModel = new Recipe($this->conn);
            foreach ($allergies as $allergy_id) {
                $recipeModel->addAllergyIngredientMapping($allergy_id, $ingredient_id);
            }
            
            header("Location: index.php?action=adminIngredients&success=2");
        } else {
            header("Location: index.php?action=adminIngredients&error=3");
        }
        exit;
    }
    
    /**
     * Delete the specified ingredient
     */
    public function adminDeleteIngredient() {
        if (isset($_GET['id'])) {
            $ingredient_id = $_GET['id'];
            
            if ($this->adminModel->deleteIngredient($ingredient_id)) {
                header('Location: index.php?action=adminIngredients&success=3');
                exit;
            } else {
                header('Location: index.php?action=adminIngredients&error=1');
                exit;
            }
        } else {
            header('Location: index.php?action=adminIngredients');
            exit;
        }
    }
    
    /**
     * Manage categories - Updated to include dietary preference data
     */
    public function adminCategories() {
        // Get search parameter
        $search = isset($_GET['search']) ? $_GET['search'] : '';
        
        // Get pagination parameters
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $items_per_page = 10; // Show 10 categories per page
        $offset = ($page - 1) * $items_per_page;
        
        // Get total categories count for pagination
        $total_categories = $this->adminModel->countCategories($search);
        $total_pages = ceil($total_categories / $items_per_page);
        
        // Get categories with recipe count
        $categories = $this->adminModel->getAllCategories($search, $items_per_page, $offset);
        
        // Get dietary preferences for each category
        $recipeModel = new Recipe($this->conn);
        foreach ($categories as &$category) {
            $category['preferences'] = $recipeModel->getCategoryPreferences($category['category_id']);
        }
        
        // Get all dietary preferences for the form
        $preferencesQuery = "SELECT * FROM dietary_preferences WHERE preference_name != 'None' ORDER BY preference_name";
        $dietary_preferences = $this->conn->query($preferencesQuery)->fetch_all(MYSQLI_ASSOC);
        
        // Include categories view
        include 'adminviews/categories.php';
    }
    
    /**
     * Add new category - Updated to handle dietary preferences
     */
    public function adminAddCategory() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $category_name = trim($_POST['category_name']);
            $preferences = isset($_POST['preferences']) ? $_POST['preferences'] : [];
            
            if (!empty($category_name)) {
                // Add the category
                $category_id = $this->adminModel->addCategoryWithReturn($category_name);
                
                if ($category_id) {
                    // Add preference mappings
                    $recipeModel = new Recipe($this->conn);
                    foreach ($preferences as $preference_id) {
                        $recipeModel->addPreferenceCategoryMapping($preference_id, $category_id);
                    }
                    
                    header('Location: index.php?action=adminCategories&success=1');
                    exit;
                } else {
                    header('Location: index.php?action=adminCategories&error=1');
                    exit;
                }
            } else {
                header('Location: index.php?action=adminCategories&error=1');
                exit;
            }
        } else {
            header('Location: index.php?action=adminCategories');
            exit;
        }
    }
    
    /**
     * Edit category - Updated to handle dietary preferences
     */
    public function adminEditCategory() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $category_id = $_POST['category_id'];
            $category_name = trim($_POST['category_name']);
            $preferences = isset($_POST['preferences']) ? $_POST['preferences'] : [];
            
            if (!empty($category_name)) {
                // Update the category name
                if ($this->adminModel->editCategory($category_id, $category_name)) {
                    // Update preference mappings
                    $recipeModel = new Recipe($this->conn);
                    
                    // Remove existing mappings
                    $this->removeCategoryPreferenceMappings($category_id);
                    
                    // Add new mappings
                    foreach ($preferences as $preference_id) {
                        $recipeModel->addPreferenceCategoryMapping($preference_id, $category_id);
                    }
                    
                    header('Location: index.php?action=adminCategories&success=2');
                    exit;
                } else {
                    header('Location: index.php?action=adminCategories&error=1');
                    exit;
                }
            } else {
                header('Location: index.php?action=adminCategories&error=1');
                exit;
            }
        } else {
            header('Location: index.php?action=adminCategories');
            exit;
        }
    }
    
    /**
     * Helper method to remove existing preference-category mappings
     */
    private function removeCategoryPreferenceMappings($category_id) {
        $query = "DELETE FROM preference_category_mapping WHERE category_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $category_id);
        $stmt->execute();
    }
    
    
    /**
     * Delete the specified category
     */
    public function adminDeleteCategory() {
        if (isset($_GET['id'])) {
            $category_id = $_GET['id'];
            
            if ($this->adminModel->deleteCategory($category_id)) {
                header('Location: index.php?action=adminCategories&success=3');
                exit;
            } else {
                header('Location: index.php?action=adminCategories&error=1');
                exit;
            }
        } else {
            header('Location: index.php?action=adminCategories');
            exit;
        }
    }
    
    /**
     * Delete user
     */
    public function adminDeleteUser() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'])) {
            $user_id = $_POST['user_id'];
            
            if ($this->adminModel->deleteUser($user_id)) {
                header('Location: index.php?action=adminUsers&success=1');
                exit;
            } else {
                header('Location: index.php?action=adminUsers&error=1');
                exit;
            }
        } else if (isset($_GET['id'])) {
            $user_id = $_GET['id'];
            
            if ($this->adminModel->deleteUser($user_id)) {
                header('Location: index.php?action=adminUsers&success=1');
                exit;
            } else {
                header('Location: index.php?action=adminUsers&error=1');
                exit;
            }
        } else {
            header('Location: index.php?action=adminUsers');
            exit;
        }
    }
    
    /**
     * Delete recipe
     */
    public function adminDeleteRecipe() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['recipe_id'])) {
            $recipe_id = $_POST['recipe_id'];
            
            if ($this->adminModel->deleteRecipe($recipe_id)) {
                header('Location: index.php?action=adminRecipes&success=1');
                exit;
            } else {
                header('Location: index.php?action=adminRecipes&error=1');
                exit;
            }
        } else if (isset($_GET['id'])) {
            $recipe_id = $_GET['id'];
            
            if ($this->adminModel->deleteRecipe($recipe_id)) {
                header('Location: index.php?action=adminRecipes&success=1');
                exit;
            } else {
                header('Location: index.php?action=adminRecipes&error=1');
                exit;
            }
        } else {
            header('Location: index.php?action=adminRecipes');
            exit;
        }
    }
    
    /**
     * Generate user engagement reports
     */
    public function adminUserReports() {
        // Get date range parameters
        $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-30 days'));
        $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
        
        // Get user engagement statistics
        $user_stats = $this->adminModel->getUserEngagementStats($start_date, $end_date);
        $recipe_creation_trend = $this->adminModel->getRecipeCreationTrend($start_date, $end_date);
        $top_active_users = $this->adminModel->getTopActiveUsers(10);
        
        // Include user reports view
        include 'adminviews/user_reports.php';
    }
    
    /**
 * Generate recipe favorite reports with enhanced details
 */
public function adminRecipeReports() {
    // Get date range parameters
    $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-30 days'));
    $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
    
    // Get recipe statistics
    $recipe_stats = $this->adminModel->getRecipeStats($start_date, $end_date);
    
    // Get top favorite recipes and include comment counts
    $query = "SELECT r.recipe_id, r.recipe_name, r.created_at, r.public, r.user_id, 
                    u.username,
                    COUNT(DISTINCT f.id) AS favorite_count,
                    (SELECT COUNT(*) FROM recipe_comments WHERE recipe_id = r.recipe_id) AS comment_count
              FROM recipes r
              LEFT JOIN users u ON r.user_id = u.user_id
              LEFT JOIN favorites f ON r.recipe_id = f.recipe_id
              GROUP BY r.recipe_id, r.recipe_name, r.created_at, r.public, r.user_id, u.username
              ORDER BY favorite_count DESC, comment_count DESC
              LIMIT 15";
    
    $result = $this->conn->query($query);
    $top_favorites = [];
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            // Get recipe categories
            $row['categories'] = $this->adminModel->getRecipeCategories($row['recipe_id']);
            
            // Calculate popularity score (favorites + comments, max 10)
            $popularity = ($row['favorite_count'] * 2) + $row['comment_count'];
            $row['popularity_score'] = min(10, $popularity);
            
            $top_favorites[] = $row;
        }
    }
    
    // Get trending categories
    $query = "SELECT rc.category_id, rc.category_name, COUNT(DISTINCT rcm.recipe_id) as recipe_count
              FROM recipe_categories rc
              JOIN recipe_category_mapping rcm ON rc.category_id = rcm.category_id
              JOIN recipes r ON rcm.recipe_id = r.recipe_id
              WHERE r.created_at BETWEEN ? AND ?
              GROUP BY rc.category_id, rc.category_name
              ORDER BY recipe_count DESC
              LIMIT 10";
    
    $stmt = $this->conn->prepare($query);
    $trending_categories = [];
    
    if ($stmt) {
        $stmt->bind_param("ss", $start_date, $end_date);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $trending_categories[] = $row;
            }
        }
    }
    
    // Include recipe reports view
    include 'adminviews/recipe_reports.php';
}

/**
 * Export recipe report as CSV with enhanced details
 */
public function adminExportRecipeReport() {
    // Get date range parameters
    $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-30 days'));
    $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
    
    // Get top favorite recipes with comments for export
    $query = "SELECT r.recipe_id, r.recipe_name, r.created_at, r.public, r.user_id, r.image,
                    u.username,
                    COUNT(DISTINCT f.id) AS favorite_count,
                    (SELECT COUNT(*) FROM recipe_comments WHERE recipe_id = r.recipe_id) AS comment_count
              FROM recipes r
              LEFT JOIN users u ON r.user_id = u.user_id
              LEFT JOIN favorites f ON r.recipe_id = f.recipe_id
              GROUP BY r.recipe_id, r.recipe_name, r.created_at, r.public, r.user_id, u.username, r.image
              ORDER BY favorite_count DESC, comment_count DESC
              LIMIT 100";
    
    $result = $this->conn->query($query);
    $recipes = [];
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            // Get recipe categories
            $categories = $this->adminModel->getRecipeCategories($row['recipe_id']);
            $row['categories'] = implode(', ', $categories);
            
            // Calculate popularity score (favorites + comments, max 10)
            $popularity = ($row['favorite_count'] * 2) + $row['comment_count'];
            $row['popularity_score'] = min(10, $popularity);
            
            $recipes[] = $row;
        }
    }
    
    // Set headers for CSV download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="recipe_report_' . date('Y-m-d') . '.csv"');
    
    // Create output stream
    $output = fopen('php://output', 'w');
    
    // Add CSV headers
    fputcsv($output, [
        'Recipe ID',
        'Recipe Name',
        'Author',
        'Creation Date',
        'Categories',
        'Status',
        'Favorites',
        'Comments',
        'Popularity Score'
    ]);
    
    // Add recipe data
    foreach ($recipes as $recipe) {
        fputcsv($output, [
            $recipe['recipe_id'],
            $recipe['recipe_name'],
            $recipe['username'],
            isset($recipe['created_at']) ? date('Y-m-d', strtotime($recipe['created_at'])) : 'N/A',
            $recipe['categories'],
            isset($recipe['public']) ? ($recipe['public'] ? 'Public' : 'Private') : 'N/A',
            $recipe['favorite_count'],
            $recipe['comment_count'],
            number_format($recipe['popularity_score'], 1)
        ]);
    }
    
    // Close the output stream
    fclose($output);
    exit;
}
    
    /**
     * Advertisement management
     */
    public function adminAds() {
        // Update ad statuses based on current date
        $this->updateAdStatus();
        
        // Handle form submissions first
        if (isset($_GET['subaction'])) {
            $subaction = $_GET['subaction'];
            
            if ($subaction === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
                // Process adding a new ad
                $this->addAd();
                return;
            } else if ($subaction === 'edit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
                // Process editing an ad
                $this->editAd($_POST['ad_id']);
                return;
            } else if ($subaction === 'delete' && isset($_GET['id'])) {
                // Process deleting an ad
                $this->deleteAd($_GET['id']);
                return;
            }
        }
        
        // Get all ads for display
        $query = "SELECT * FROM ads ORDER BY created_at DESC";
        $result = $this->conn->query($query);
        $ads = $result->fetch_all(MYSQLI_ASSOC);
        
        // Include ads view
        include 'adminviews/ads.php';
    }

    /**
     * Display ads in frontend
     */
    public function getActiveAds($position = null) {
        // Update ad statuses based on current date
        $this->updateAdStatus();
        
        // Build query based on position
        $query_params = [];
        $query = "SELECT * FROM ads WHERE status = 'active'";
        
        if ($position) {
            $query .= " AND position = ?";
            $query_params[] = $position;
        }
        
        $query .= " ORDER BY RAND() LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters if needed
        if (!empty($query_params)) {
            $stmt->bind_param(str_repeat("s", count($query_params)), ...$query_params);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_assoc();
    }

    /**
     * Update ad status based on current date, but respect manual admin changes
     */
    private function updateAdStatus() {
        $current_date = date('Y-m-d');
        
        // Update expired ads to inactive
        $query = "UPDATE ads 
                  SET status = 'inactive' 
                  WHERE end_date < ? AND status = 'active'";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $current_date);
        $stmt->execute();
        
        // Only auto-activate ads where the current date is within their date range
        // AND they haven't been manually set to "inactive" by an admin after their start date
        $query = "UPDATE ads 
                  SET status = 'active' 
                  WHERE start_date <= ? 
                  AND end_date >= ? 
                  AND status = 'inactive'
                  AND (updated_at IS NULL OR updated_at < start_date)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ss", $current_date, $current_date);
        $stmt->execute();
        
        // For debugging
        if ($stmt->affected_rows > 0) {
            error_log("Updated ad statuses based on date. Affected rows: " . $stmt->affected_rows);
        }
    }

    /**
     * Add a new ad
     */
    private function addAd() {
        // Get form data
        $title = trim($_POST['title']);
        $url = trim($_POST['url']);
        $position = $_POST['position'];
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];
        $status = $_POST['status'];
        
        // Handle image upload
        $image = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            $upload_dir = 'uploads/ads/';
            
            // Create directory if it doesn't exist
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_name = time() . '_' . basename($_FILES['image']['name']);
            $upload_path = $upload_dir . $file_name;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                $image = $upload_path;
            }
        }
        
        // Insert ad into database
        $query = "INSERT INTO ads (title, image, url, position, start_date, end_date, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
                
        if ($stmt = $this->conn->prepare($query)) {
            $stmt->bind_param("sssssss", $title, $image, $url, $position, $start_date, $end_date, $status);
            $stmt->execute();
            
            if ($stmt->affected_rows > 0) {
                $_SESSION['ad_message'] = "Ad added successfully!";
                $_SESSION['ad_message_type'] = "success";
            } else {
                $_SESSION['ad_message'] = "Failed to add ad.";
                $_SESSION['ad_message_type'] = "danger";
            }
        }
        
        // Redirect back to ads page
        header('Location: index.php?action=adminAds');
        exit;
    }

    /**
     * Edit an existing ad
     */
    private function editAd($ad_id) {
        // Get form data
        $title = trim($_POST['title']);
        $url = trim($_POST['url']);
        $position = $_POST['position'];
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];
        $status = $_POST['status'];
        $current_timestamp = date('Y-m-d H:i:s');
        
        // Handle image upload if a new image is provided
        $image_update = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            $upload_dir = 'uploads/ads/';
            
            // Create directory if it doesn't exist
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_name = time() . '_' . basename($_FILES['image']['name']);
            $upload_path = $upload_dir . $file_name;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                $image_update = ", image = '" . $this->conn->real_escape_string($upload_path) . "'";
            }
        }
        
        // Update ad in database with updated_at timestamp
        $query = "UPDATE ads SET title = ?, url = ?, position = ?, 
                  start_date = ?, end_date = ?, status = ?, updated_at = ? $image_update
                  WHERE ad_id = ?";
                  
        if ($stmt = $this->conn->prepare($query)) {
            $stmt->bind_param("sssssssi", $title, $url, $position, $start_date, $end_date, $status, $current_timestamp, $ad_id);
            $stmt->execute();
            
            if ($stmt->affected_rows > 0 || $stmt->errno === 0) {
                $_SESSION['ad_message'] = "Ad updated successfully!";
                $_SESSION['ad_message_type'] = "success";
            } else {
                $_SESSION['ad_message'] = "Failed to update ad or no changes made.";
                $_SESSION['ad_message_type'] = "danger";
            }
        }
        
        // Redirect back to ads page
        header('Location: index.php?action=adminAds');
        exit;
    }

    /**
     * Delete an ad
     */
    private function deleteAd($ad_id) {
        $query = "DELETE FROM ads WHERE ad_id = ?";
        
        if ($stmt = $this->conn->prepare($query)) {
            $stmt->bind_param("i", $ad_id);
            $stmt->execute();
            
            if ($stmt->affected_rows > 0) {
                $_SESSION['ad_message'] = "Ad deleted successfully!";
                $_SESSION['ad_message_type'] = "success";
            } else {
                $_SESSION['ad_message'] = "Failed to delete ad.";
                $_SESSION['ad_message_type'] = "danger";
            }
        }
        
        // Redirect back to ads page
        header('Location: index.php?action=adminAds');
        exit;
    }

    /**
     * Generate ad performance reports
     */
    public function adminAdReports() {
        // Get all ads for the report
        $top_performing_ads = [];
        $positions = []; // Initialize positions array
        
        try {
            // Get all ads
            $query = "SELECT * FROM ads ORDER BY title";
            $result = $this->conn->query($query);
            
            if ($result && $result->num_rows > 0) {
                while ($ad = $result->fetch_assoc()) {
                    // Count impressions directly
                    $imp_query = "SELECT COUNT(*) as total FROM ad_impressions WHERE ad_id = ?";
                    $imp_stmt = $this->conn->prepare($imp_query);
                    $imp_stmt->bind_param("i", $ad['ad_id']);
                    $imp_stmt->execute();
                    $imp_result = $imp_stmt->get_result();
                    $impressions = (int)$imp_result->fetch_assoc()['total'];
                    
                    // Count clicks directly
                    $click_query = "SELECT COUNT(*) as total FROM ad_clicks WHERE ad_id = ?";
                    $click_stmt = $this->conn->prepare($click_query);
                    $click_stmt->bind_param("i", $ad['ad_id']);
                    $click_stmt->execute();
                    $click_result = $click_stmt->get_result();
                    $clicks = (int)$click_result->fetch_assoc()['total'];
                    
                    // Calculate CTR
                    $ctr = $impressions > 0 ? round(($clicks / $impressions) * 100, 2) : 0;
                    
                    // Store metrics with ad
                    $ad['impressions'] = $impressions;
                    $ad['clicks'] = $clicks;
                    $ad['ctr'] = $ctr;
                    
                    $top_performing_ads[] = $ad;
                    
                    // Update position stats
                    $position = $ad['position'];
                    if (!isset($positions[$position])) {
                        $positions[$position] = [
                            'position' => $position,
                            'total_ads' => 0,
                            'impressions' => 0,
                            'clicks' => 0
                        ];
                    }
                    
                    $positions[$position]['total_ads']++;
                    $positions[$position]['impressions'] += $impressions;
                    $positions[$position]['clicks'] += $clicks;
                }
                
                // Calculate CTR for positions and convert to indexed array
                foreach ($positions as &$pos) {
                    $pos['ctr'] = $pos['impressions'] > 0 ? 
                        round(($pos['clicks'] / $pos['impressions']) * 100, 2) : 0;
                }
                $position_stats = array_values($positions);
                
                // Sort ads by impressions
                usort($top_performing_ads, function($a, $b) {
                    return $b['impressions'] - $a['impressions'];
                });
            }
        } catch (Exception $e) {
            error_log("Error in adminAdReports: " . $e->getMessage());
        }
        
        // Include ad reports view
        include 'adminviews/ad_reports.php';
    }

    /**
     * Log ad impression
     */
    public function logAdImpression() {
        if (isset($_GET['id'])) {
            $ad_id = $_GET['id'];
            $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
            $ip_address = $_SERVER['REMOTE_ADDR'];
            
            try {
                $query = "INSERT INTO ad_impressions (ad_id, user_id, ip_address) VALUES (?, ?, ?)";
                $stmt = $this->conn->prepare($query);
                $stmt->bind_param("iis", $ad_id, $user_id, $ip_address);
                $stmt->execute();
                
                // Log confirmation
                error_log("Recorded impression for ad ID: $ad_id from IP: $ip_address, User ID: " . ($user_id ?? 'null'));
                
                // Return success response for AJAX calls
                header('Content-Type: application/json');
                echo json_encode(['success' => true]);
                exit;
            } catch (Exception $e) {
                error_log("Error logging ad impression: " . $e->getMessage());
            }
        }
        
        // Return error response
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Error logging impression']);
        exit;
    }

    /**
     * Handle ad click and redirect
     */
    public function adClick() {
        if (isset($_GET['id'])) {
            $ad_id = $_GET['id'];
            $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
            $ip_address = $_SERVER['REMOTE_ADDR'];
            
            // Log the click
            try {
                $query = "INSERT INTO ad_clicks (ad_id, user_id, ip_address) VALUES (?, ?, ?)";
                $stmt = $this->conn->prepare($query);
                $stmt->bind_param("iis", $ad_id, $user_id, $ip_address);
                $stmt->execute();
                
                // Log confirmation
                error_log("Recorded click for ad ID: $ad_id from IP: $ip_address, User ID: " . ($user_id ?? 'null'));
                
                // Get the ad URL
                $query = "SELECT url FROM ads WHERE ad_id = ?";
                $stmt = $this->conn->prepare($query);
                $stmt->bind_param("i", $ad_id);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($row = $result->fetch_assoc()) {
                    $url = $row['url'];
                    
                    // If the request is AJAX (from our JavaScript), return JSON
                    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                        header('Content-Type: application/json');
                        echo json_encode(['success' => true, 'url' => $url]);
                        exit;
                    }
                    
                    // If not AJAX, redirect directly
                    header("Location: " . $url);
                    exit;
                }
            } catch (Exception $e) {
                error_log("Error tracking ad click: " . $e->getMessage());
            }
        }
        
        // If something went wrong, redirect to homepage
        header("Location: index.php");
        exit;
    }
    
    /**
     * Export ad performance report 
     */
    public function adminExportAdReport() {
        // Set headers for CSV download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="ad_performance_report_' . date('Y-m-d') . '.csv"');
        
        // Create output stream
        $output = fopen('php://output', 'w');
        
        // Add CSV headers
        fputcsv($output, ['Ad ID', 'Title', 'Position', 'Status', 'Impressions', 'Clicks', 'CTR', 'Start Date', 'End Date']);
        
        try {
            // Get all ads
            $query = "SELECT * FROM ads ORDER BY title";
            $result = $this->conn->query($query);
            
            if ($result && $result->num_rows > 0) {
                while ($ad = $result->fetch_assoc()) {
                    // Count impressions
                    $imp_query = "SELECT COUNT(*) as total FROM ad_impressions WHERE ad_id = ?";
                    $imp_stmt = $this->conn->prepare($imp_query);
                    $imp_stmt->bind_param("i", $ad['ad_id']);
                    $imp_stmt->execute();
                    $imp_result = $imp_stmt->get_result();
                    $impressions = (int)$imp_result->fetch_assoc()['total'];
                    
                    // Count clicks
                    $click_query = "SELECT COUNT(*) as total FROM ad_clicks WHERE ad_id = ?";
                    $click_stmt = $this->conn->prepare($click_query);
                    $click_stmt->bind_param("i", $ad['ad_id']);
                    $click_stmt->execute();
                    $click_result = $click_stmt->get_result();
                    $clicks = (int)$click_result->fetch_assoc()['total'];
                    
                    // Calculate CTR
                    $ctr = $impressions > 0 ? ($clicks / $impressions) * 100 : 0;
                    
                    // Write ad data to CSV
                    fputcsv($output, [
                        $ad['ad_id'],
                        $ad['title'],
                        $ad['position'],
                        $ad['status'],
                        $impressions,
                        $clicks,
                        number_format($ctr, 2) . '%',
                        $ad['start_date'],
                        $ad['end_date']
                    ]);
                }
            }
        } catch (Exception $e) {
            error_log("Error in exportAdReport: " . $e->getMessage());
        }
        
        // Close the output stream
        fclose($output);
        exit;
    }

    /**
     * Export user report
     */
    public function adminExportUserReport() {
        // Get date range parameters
        $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-30 days'));
        $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
        
        // Get user engagement statistics
        $user_stats = $this->adminModel->getUserEngagementStats($start_date, $end_date);
        $top_active_users = $this->adminModel->getTopActiveUsers(20); // Get more users for the export
        
        // Set headers for CSV download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="user_report_' . date('Y-m-d') . '.csv"');
        
        // Create output stream
        $output = fopen('php://output', 'w');
        
        // Add CSV headers
        fputcsv($output, [
            'User ID', 
            'Username', 
            'Email', 
            'Registration Date', 
            'Status',
            'Last Login',
            'Recipes Created', 
            'Favorites Added',
            'Comments Made',
            'Engagement Score',
            'Last Activity Date'
        ]);
        
        // Add user data
        foreach ($top_active_users as $user) {
            // Calculate engagement score
            $engagement_score = ($user['recipe_count'] * 5) + ($user['favorite_count'] * 2);
            
            // Format last login
            $last_login = isset($user['last_login']) && $user['last_login'] ? date('Y-m-d', strtotime($user['last_login'])) : 'Never';
            
            fputcsv($output, [
                $user['user_id'],
                $user['username'],
                $user['email'],
                date('Y-m-d', strtotime($user['created_at'])),
                isset($user['status']) ? $user['status'] : 'unknown',
                $last_login,
                $user['recipe_count'],
                $user['favorite_count'],
                isset($user['comment_count']) ? $user['comment_count'] : '0',
                $engagement_score,
                isset($user['last_activity']) ? date('Y-m-d', strtotime($user['last_activity'])) : 'Never'
            ]);
        }
        
        // Close the output stream
        fclose($output);
        exit;
    }

    // Display allergy-ingredient mappings
    public function adminAllergyIngredientMappings() {
        $recipeModel = new Recipe($this->conn);
        
        // Get all allergies
        $allergiesQuery = "SELECT * FROM allergies WHERE allergy_name != 'None' ORDER BY allergy_name";
        $allergies = $this->conn->query($allergiesQuery)->fetch_all(MYSQLI_ASSOC);
        
        // Get all ingredients
        $ingredientsQuery = "SELECT * FROM ingredients ORDER BY name";
        $ingredients = $this->conn->query($ingredientsQuery)->fetch_all(MYSQLI_ASSOC);
        
        // Get current mappings
        $mappings = $recipeModel->getAllergyIngredientMappings();
        
        include('adminviews/allergy_ingredient_mappings.php');
    }

    // Add a new allergy-ingredient mapping
    public function adminAddAllergyIngredientMapping() {
        $allergy_id = $_POST['allergy_id'];
        $ingredient_id = $_POST['ingredient_id'];
        
        $recipeModel = new Recipe($this->conn);
        if ($recipeModel->addAllergyIngredientMapping($allergy_id, $ingredient_id)) {
            header("Location: index.php?action=adminAllergyIngredientMappings&success=1");
        } else {
            header("Location: index.php?action=adminAllergyIngredientMappings&error=1");
        }
        exit;
    }

    // Remove an allergy-ingredient mapping
    public function adminRemoveAllergyIngredientMapping() {
        $allergy_id = $_POST['allergy_id'];
        $ingredient_id = $_POST['ingredient_id'];
        
        $recipeModel = new Recipe($this->conn);
        if ($recipeModel->removeAllergyIngredientMapping($allergy_id, $ingredient_id)) {
            header("Location: index.php?action=adminAllergyIngredientMappings&success=2");
        } else {
            header("Location: index.php?action=adminAllergyIngredientMappings&error=2");
        }
        exit;
    }

    /**
     * Display preference-category mappings
     */
    public function adminPreferenceCategoryMappings() {
        $recipeModel = new Recipe($this->conn);
        
        // Get all dietary preferences
        $preferencesQuery = "SELECT * FROM dietary_preferences WHERE preference_name != 'None' ORDER BY preference_name";
        $preferences = $this->conn->query($preferencesQuery)->fetch_all(MYSQLI_ASSOC);
        
        // Get all categories
        $categoriesQuery = "SELECT * FROM recipe_categories ORDER BY category_name";
        $categories = $this->conn->query($categoriesQuery)->fetch_all(MYSQLI_ASSOC);
        
        // Get current mappings
        $mappings = $recipeModel->getPreferenceCategoryMappings();
        
        include('adminviews/preference_category_mappings.php');
    }

    /**
     * Add a new preference-category mapping
     */
    public function adminAddPreferenceCategoryMapping() {
        $preference_id = $_POST['preference_id'];
        $category_id = $_POST['category_id'];
        
        $recipeModel = new Recipe($this->conn);
        if ($recipeModel->addPreferenceCategoryMapping($preference_id, $category_id)) {
            header("Location: index.php?action=adminPreferenceCategoryMappings&success=1");
        } else {
            header("Location: index.php?action=adminPreferenceCategoryMappings&error=1");
        }
        exit;
    }

    /**
     * Remove a preference-category mapping
     */
    public function adminRemovePreferenceCategoryMapping() {
        $preference_id = $_POST['preference_id'];
        $category_id = $_POST['category_id'];
        
        $recipeModel = new Recipe($this->conn);
        if ($recipeModel->removePreferenceCategoryMapping($preference_id, $category_id)) {
            header("Location: index.php?action=adminPreferenceCategoryMappings&success=2");
        } else {
            header("Location: index.php?action=adminPreferenceCategoryMappings&error=2");
        }
        exit;
    }

    /**
     * Update user activity status based on last login
     * Users who haven't logged in for more than 1 day will be marked inactive
     */
    private function updateUserActivityStatus() {
        // First, make sure the last_login column exists
        $this->ensureLastLoginColumnExists();
        
        $inactivity_threshold = date('Y-m-d H:i:s', strtotime('-1 day'));
        
        $query = "UPDATE users 
                  SET status = 'inactive' 
                  WHERE (last_login IS NULL OR last_login < ?) 
                  AND status = 'active'";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $inactivity_threshold);
        $stmt->execute();
        
        if ($stmt->affected_rows > 0) {
            error_log("Updated {$stmt->affected_rows} users to inactive status due to inactivity");
        }
    }

    /**
     * Check if the last_login column exists in the users table, create it if not
     */
    private function ensureLastLoginColumnExists() {
        // Check if column exists
        $check_query = "SHOW COLUMNS FROM users LIKE 'last_login'";
        $result = $this->conn->query($check_query);
        
        if ($result->num_rows == 0) {
            // Column doesn't exist, create it
            $alter_query = "ALTER TABLE users 
                            ADD COLUMN last_login DATETIME DEFAULT NULL,
                            ADD COLUMN status ENUM('active', 'inactive') DEFAULT 'active'";
            
            $this->conn->query($alter_query);
            error_log("Added last_login and status columns to users table");
        }
    }

    /**
     * Send inactivity notice to user
     */
    public function adminSendInactivityNotice() {
        // Check if admin is logged in
        if (!isset($_SESSION['admin_id'])) {
            header('Location: index.php?action=adminLogin');
            exit;
        }
        
        // Check if form was submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Get form data
            $user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
            $email = isset($_POST['email']) ? trim($_POST['email']) : '';
            $subject = isset($_POST['email_subject']) ? trim($_POST['email_subject']) : '';
            $message = isset($_POST['email_message']) ? trim($_POST['email_message']) : '';
            
            if ($user_id && $email && $subject && $message) {
                // Log attempt
                error_log("Attempting to send email to: $email");
                
                // For development/testing - use PHPMailer with Gmail
                require_once 'vendor/autoload.php';
                
                $email_sent = false;
                
                try {
                    // Create a new PHPMailer instance
                    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
                    
                    // Server settings
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.gmail.com';
                    $mail->SMTPAuth   = true;
                    $mail->Username   = 'xnxjuliana0311@gmail.com';  // Your Gmail address
                    $mail->Password   = 'pnez jcft jouz qwxx';     // Your App Password
                    $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port       = 587;
                    
                    // Enable debug output in development
                    $mail->SMTPDebug = 2;  // 0 = no debug, 1 = client messages, 2 = client and server messages
                    
                    // Set where debug output goes
                    $mail->Debugoutput = function($str, $level) {
                        error_log("PHPMailer DEBUG: $str");
                    };
                    
                    // Recipients
                    $mail->setFrom('xnxjuliana0311@gmail.com', 'SpiceShelf Admin');
                    $mail->addAddress($email);
                    $mail->addReplyTo('xnxjuliana0311@gmail.com');
                    
                    // Content
                    $mail->isHTML(false);
                    $mail->Subject = $subject;
                    $mail->Body    = $message;
                    
                    // Send the email
                    $mail->send();
                    $email_sent = true;
                    
                    error_log("Email sent successfully to {$email}");
                    
                    $_SESSION['admin_message'] = "Inactivity notice sent successfully to $email";
                    $_SESSION['admin_message_type'] = 'success';
                } catch (Exception $e) {
                    error_log("Mailer Error: {$e->getMessage()}");
                    
                    $_SESSION['admin_message'] = "Failed to send email: " . $e->getMessage();
                    $_SESSION['admin_message_type'] = 'danger';
                }
                
                // Track notification in database
                try {
                    // Check if table exists
                    $check_table = "SHOW TABLES LIKE 'user_notifications'";
                    $table_exists = $this->conn->query($check_table)->num_rows > 0;
                    
                    if (!$table_exists) {
                        // Create table if it doesn't exist
                        $create_table = "CREATE TABLE user_notifications (
                            notification_id INT AUTO_INCREMENT PRIMARY KEY,
                            user_id INT NOT NULL,
                            notification_type VARCHAR(50) NOT NULL,
                            subject VARCHAR(255) NOT NULL,
                            message TEXT NOT NULL,
                            status VARCHAR(20) DEFAULT 'pending',
                            sent_at DATETIME DEFAULT NULL,
                            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                            FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
                        )";
                        $this->conn->query($create_table);
                    }
                    
                    // Record notification status
                    $status = $email_sent ? 'sent' : 'failed';
                    $query = "INSERT INTO user_notifications 
                            (user_id, notification_type, subject, message, status, sent_at) 
                            VALUES (?, 'inactivity', ?, ?, ?, NOW())";
                    
                    $stmt = $this->conn->prepare($query);
                    $stmt->bind_param("isss", $user_id, $subject, $message, $status);
                    $stmt->execute();
                } catch (Exception $e) {
                    error_log("Database error: " . $e->getMessage());
                }
            } else {
                $_SESSION['admin_message'] = "Missing required information to send email.";
                $_SESSION['admin_message_type'] = 'danger';
            }
            
            // Redirect back to user view
            header("Location: index.php?action=adminViewUser&id={$user_id}");
            exit;
        }
    }
    }
?>