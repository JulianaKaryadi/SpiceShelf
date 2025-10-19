<?php

class Admin {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    /**
     * Admin login
     * 
     * @param string $username Admin username
     * @param string $password Admin password
     * @return array|bool Admin data if login successful, false otherwise
     */
    public function login($username, $password) {
        // Debug logging
        error_log("Admin login method called for username: " . $username);
        
        // Check if admins table exists
        $check_table = "SHOW TABLES LIKE 'admins'";
        $result = $this->conn->query($check_table);
        
        if ($result->num_rows == 0) {
            error_log("Admins table does not exist!");
            return false;
        }
        
        // Prepare the query
        $query = "SELECT * FROM admins WHERE username = ?";
        
        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            error_log("Statement preparation failed: " . $this->conn->error);
            return false;
        }
        
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if (!$result) {
            error_log("Query execution failed: " . $this->conn->error);
            return false;
        }
        
        if ($result->num_rows > 0) {
            $admin = $result->fetch_assoc();
            error_log("Found admin record for username: " . $username);
            
            // For debugging only - DO NOT use in production!
            error_log("Stored password hash: " . $admin['password']);
            error_log("Password verification result: " . (password_verify($password, $admin['password']) ? 'true' : 'false'));
            
            if (password_verify($password, $admin['password'])) {
                // Remove password before returning
                unset($admin['password']);
                return $admin;
            } else {
                error_log("Password verification failed for admin: " . $username);
            }
        } else {
            error_log("No admin found with username: " . $username);
        }
        
        return false;
    }
    
    /**
     * Get admin by ID
     * 
     * @param int $id Admin ID
     * @return array|bool Admin data if found, false otherwise
     */
    public function getAdminById($id) {
        $query = "SELECT id, username, email, created_at FROM admins WHERE id = ?";
        
        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            die("Statement preparation failed: " . $this->conn->error);
        }
        
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_assoc();
    }
    
    /**
     * Count total users
     */
    public function countUsers($search = '', $status = '') {
        $query_params = [];
        $query = "SELECT COUNT(*) as total FROM users WHERE 1=1";
        
        if (!empty($search)) {
            $query .= " AND (username LIKE ? OR email LIKE ?)";
            $search_param = "%$search%";
            $query_params[] = $search_param;
            $query_params[] = $search_param;
        }
        
        if (!empty($status)) {
            $query .= " AND status = ?";
            $query_params[] = $status;
        }
        
        // Set parameter types
        $types = str_repeat("s", count($query_params));
        
        $stmt = $this->conn->prepare($query);
        
        if (!empty($query_params)) {
            $stmt->bind_param($types, ...$query_params);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        return $row['total'];
    }
    
    /**
     * Count total recipes
     * 
     * @param string $search Optional search term
     * @return int Total number of recipes
     */
    public function countRecipes($search = '') {
        $query = "SELECT COUNT(*) as count FROM recipes";
        
        if (!empty($search)) {
            $query .= " WHERE recipe_name LIKE ? OR description LIKE ?";
            $stmt = $this->conn->prepare($query);
            $search_param = '%' . $search . '%';
            $stmt->bind_param("ss", $search_param, $search_param);
        } else {
            $stmt = $this->conn->prepare($query);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        return $row['count'];
    }
    
    /**
     * Get recently registered users
     * 
     * @param int $limit Number of users to return
     * @return array Array of recent users
     */
    public function getRecentUsers($limit = 5) {
        $query = "SELECT user_id, username, email, created_at 
                 FROM users 
                 ORDER BY created_at DESC 
                 LIMIT ?";
        
        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            die("Statement preparation failed: " . $this->conn->error);
        }
        
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
        
        return $users;
    }
    
    /**
     * Get recently added recipes
     * 
     * @param int $limit Number of recipes to return
     * @return array Array of recent recipes
     */
    public function getRecentRecipes($limit = 5) {
        $query = "SELECT r.recipe_id, r.recipe_name, r.created_at, u.username, r.public 
                 FROM recipes r
                 JOIN users u ON r.user_id = u.user_id
                 ORDER BY r.created_at DESC 
                 LIMIT ?";
        
        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            die("Statement preparation failed: " . $this->conn->error);
        }
        
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $recipes = [];
        while ($row = $result->fetch_assoc()) {
            $recipes[] = $row;
        }
        
        return $recipes;
    }
    
    /**
     * Get all users with recipe count
     */
    public function getAllUsers($search = '', $limit = 10, $offset = 0, $status = '') {
        $query_params = [];
        $query = "SELECT u.*, 
                COUNT(DISTINCT r.recipe_id) as recipe_count 
                FROM users u 
                LEFT JOIN recipes r ON u.user_id = r.user_id
                WHERE 1=1";
        
        if (!empty($search)) {
            $query .= " AND (u.username LIKE ? OR u.email LIKE ?)";
            $search_param = "%$search%";
            $query_params[] = $search_param;
            $query_params[] = $search_param;
        }
        
        if (!empty($status)) {
            $query .= " AND u.status = ?";
            $query_params[] = $status;
        }
        
        $query .= " GROUP BY u.user_id 
                    ORDER BY u.created_at DESC
                    LIMIT ? OFFSET ?";
        
        $query_params[] = $limit;
        $query_params[] = $offset;
        
        // Set parameter types
        $types = str_repeat("s", count($query_params) - 2) . "ii";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param($types, ...$query_params);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    /**
     * Get user by ID
     * 
     * @param int $user_id User ID
     * @return array|bool User data if found, false otherwise
     */
    public function getUserById($user_id) {
        $query = "SELECT * FROM users WHERE user_id = ?";
        
        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            die("Statement preparation failed: " . $this->conn->error);
        }
        
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_assoc();
    }

    public function getUserFavorites($user_id) {
        $query = "SELECT r.* FROM recipes r 
                JOIN favorites fr ON r.recipe_id = fr.recipe_id 
                WHERE fr.user_id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $favorites = [];
        
        while ($row = $result->fetch_assoc()) {
            $favorites[] = $row;
        }
        
        return $favorites;
    }
    
    /**
     * Get recipes created by a specific user
     * 
     * @param int $user_id User ID
     * @return array Array of user's recipes
     */
    public function getUserRecipes($user_id) {
        $query = "SELECT * FROM recipes WHERE user_id = ? ORDER BY created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            die("Statement preparation failed: " . $this->conn->error);
        }
        
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $recipes = [];
        while ($row = $result->fetch_assoc()) {
            $recipes[] = $row;
        }
        
        return $recipes;
    }
    
    /**
     * Get all recipes with pagination and search
     * 
     * @param string $search Search term
     * @param int $limit Limit number of results
     * @param int $offset Offset for pagination
     * @return array Array of recipes
     */
    public function getAllRecipes($search = '', $limit = null, $offset = 0) {
        $query = "SELECT r.recipe_id, r.recipe_name, r.description, r.created_at, 
                            u.username, r.public, r.image, r.user_id
                    FROM recipes r
                    JOIN users u ON r.user_id = u.user_id
                    WHERE 1=1";
        
        $params = [];
        $types = '';
        
        // Add search condition if provided
        if (!empty($search)) {
            $query .= " AND (r.recipe_name LIKE ? OR r.description LIKE ?)";
            $params[] = '%' . $search . '%';
            $params[] = '%' . $search . '%';
            $types .= 'ss';
        }
        
        // Add order by
        $query .= " ORDER BY r.created_at DESC";
        
        // Add limit if provided
        if ($limit !== null) {
            $query .= " LIMIT ?, ?";
            $params[] = $offset;
            $params[] = $limit;
            $types .= 'ii';
        }
        
        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            die("Statement preparation failed: " . $this->conn->error);
        }
        
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        $recipes = [];
        while ($row = $result->fetch_assoc()) {
            $recipes[] = $row;
        }
        
        return $recipes;
    }
    
    /**
     * Get recipe by ID
     * 
     * @param int $recipe_id Recipe ID
     * @return array|bool Recipe data if found, false otherwise
     */
    public function getRecipeById($recipe_id) {
        $query = "SELECT r.*, u.username 
                 FROM recipes r
                 JOIN users u ON r.user_id = u.user_id
                 WHERE r.recipe_id = ?";
        
        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            die("Statement preparation failed: " . $this->conn->error);
        }
        
        $stmt->bind_param("i", $recipe_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_assoc();
    }
    
    /**
     * Get recipe ingredients
     * 
     * @param int $recipe_id Recipe ID
     * @return array Array of recipe ingredients
     */
    public function getRecipeIngredients($recipe_id) {
        $query = "SELECT ri.quantity, m.name AS measurement, i.name AS ingredient 
                 FROM recipe_ingredients ri
                 JOIN ingredients i ON ri.ingredient_id = i.ingredient_id
                 JOIN measurements m ON ri.measurement_id = m.measurement_id
                 WHERE ri.recipe_id = ?";
        
        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            die("Statement preparation failed: " . $this->conn->error);
        }
        
        $stmt->bind_param("i", $recipe_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $ingredients = [];
        while ($row = $result->fetch_assoc()) {
            $ingredients[] = $row;
        }
        
        return $ingredients;
    }
    
    /**
     * Get recipe categories
     * 
     * @param int $recipe_id Recipe ID
     * @return array Array of recipe categories
     */
    public function getRecipeCategories($recipe_id) {
        $query = "SELECT rc.category_name 
                 FROM recipe_category_mapping rcm
                 JOIN recipe_categories rc ON rcm.category_id = rc.category_id
                 WHERE rcm.recipe_id = ?";
        
        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            die("Statement preparation failed: " . $this->conn->error);
        }
        
        $stmt->bind_param("i", $recipe_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $categories = [];
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row['category_name'];
        }
        
        return $categories;
    }
    
    /**
     * Get user's dietary preferences
     * 
     * @param int $user_id User ID
     * @return array Array of user's dietary preferences
     */
    public function getUserDietaryPreferences($user_id) {
        $query = "SELECT dp.preference_name 
                 FROM user_dietary_preferences udp
                 JOIN dietary_preferences dp ON udp.preference_id = dp.preference_id
                 WHERE udp.user_id = ?";
        
        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            die("Statement preparation failed: " . $this->conn->error);
        }
        
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $preferences = [];
        while ($row = $result->fetch_assoc()) {
            $preferences[] = $row['preference_name'];
        }
        
        return $preferences;
    }
    
    /**
     * Get user's allergies
     * 
     * @param int $user_id User ID
     * @return array Array of user's allergies
     */
    public function getUserAllergies($user_id) {
        $query = "SELECT a.allergy_name 
                 FROM user_allergies ua
                 JOIN allergies a ON ua.allergy_id = a.allergy_id
                 WHERE ua.user_id = ?";
        
        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            die("Statement preparation failed: " . $this->conn->error);
        }
        
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $allergies = [];
        while ($row = $result->fetch_assoc()) {
            $allergies[] = $row['allergy_name'];
        }
        
        return $allergies;
    }
    
    /**
     * Get all ingredients with recipe count
     * 
     * @param string $search Search term
     * @param int $limit Limit number of results
     * @param int $offset Offset for pagination
     * @return array Array of ingredients with recipe count
     */
    public function getAllIngredients($search = '', $limit = null, $offset = 0) {
        $query = "SELECT i.*, 
                        (SELECT COUNT(*) FROM recipe_ingredients ri 
                         WHERE ri.ingredient_id = i.ingredient_id) AS recipe_count 
                 FROM ingredients i 
                 WHERE 1=1";
        
        $params = [];
        $types = '';
        
        // Add search condition if provided
        if (!empty($search)) {
            $query .= " AND i.name LIKE ?";
            $params[] = '%' . $search . '%';
            $types .= 's';
        }
        
        // Add order by
        $query .= " ORDER BY i.name ASC";
        
        // Add limit if provided
        if ($limit !== null) {
            $query .= " LIMIT ?, ?";
            $params[] = $offset;
            $params[] = $limit;
            $types .= 'ii';
        }
        
        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            die("Statement preparation failed: " . $this->conn->error);
        }
        
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        $ingredients = [];
        while ($row = $result->fetch_assoc()) {
            $ingredients[] = $row;
        }
        
        return $ingredients;
    }
    
    /**
     * Add a new ingredient
     * 
     * @param string $name Ingredient name
     * @return bool True if successful, false otherwise
     */
    public function addIngredient($name) {
        $query = "INSERT INTO ingredients (name) VALUES (?)";
        
        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            die("Statement preparation failed: " . $this->conn->error);
        }
        
        $stmt->bind_param("s", $name);
        
        return $stmt->execute();
    }
    
    /**
     * Edit an ingredient
     * 
     * @param int $id Ingredient ID
     * @param string $name New ingredient name
     * @return bool True if successful, false otherwise
     */
    public function editIngredient($id, $name) {
        $query = "UPDATE ingredients SET name = ? WHERE ingredient_id = ?";
        
        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            die("Statement preparation failed: " . $this->conn->error);
        }
        
        $stmt->bind_param("si", $name, $id);
        
        return $stmt->execute();
    }
    
    /**
     * Delete ingredient
     * 
     * @param int $ingredient_id Ingredient ID
     * @return bool True if successful, false otherwise
     */
    public function deleteIngredient($ingredient_id) {
        // Check if ingredient is used in any recipes
        $query = "SELECT COUNT(*) as count FROM recipe_ingredients WHERE ingredient_id = ?";
        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            die("Statement preparation failed: " . $this->conn->error);
        }
        $stmt->bind_param("i", $ingredient_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        // If ingredient is used in recipes, don't delete it
        if ($row['count'] > 0) {
            return false;
        }
        
        // Delete ingredient from pantry
        $query = "DELETE FROM pantry WHERE ingredient_id = ?";
        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            die("Statement preparation failed: " . $this->conn->error);
        }
        $stmt->bind_param("i", $ingredient_id);
        $stmt->execute();
        
        // Delete the ingredient
        $query = "DELETE FROM ingredients WHERE ingredient_id = ?";
        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            die("Statement preparation failed: " . $this->conn->error);
        }
        $stmt->bind_param("i", $ingredient_id);
        
        return $stmt->execute();
    }
    
    /**
     * Count total ingredients
     * 
     * @param string $search Search term
     * @return int Total number of ingredients
     */
    public function countIngredients($search = '') {
        $query = "SELECT COUNT(*) as count FROM ingredients";
        
        if (!empty($search)) {
            $query .= " WHERE name LIKE ?";
            $stmt = $this->conn->prepare($query);
            $search_param = '%' . $search . '%';
            $stmt->bind_param("s", $search_param);
        } else {
            $stmt = $this->conn->prepare($query);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        return $row['count'];
    }
    
    /**
     * Get all recipe categories with recipe count
     * 
     * @param string $search Search term
     * @param int $limit Limit number of results
     * @param int $offset Offset for pagination
     * @return array Array of categories with recipe count
     */
    public function getAllCategories($search = '', $limit = null, $offset = 0) {
        $query = "SELECT rc.*, 
                        (SELECT COUNT(*) FROM recipe_category_mapping rcm 
                         WHERE rcm.category_id = rc.category_id) AS recipe_count 
                 FROM recipe_categories rc 
                 WHERE 1=1";
        
        $params = [];
        $types = '';
        
        // Add search condition if provided
        if (!empty($search)) {
            $query .= " AND rc.category_name LIKE ?";
            $params[] = '%' . $search . '%';
            $types .= 's';
        }
        
        // Add order by
        $query .= " ORDER BY rc.category_name ASC";
        
        // Add limit if provided
        if ($limit !== null) {
            $query .= " LIMIT ?, ?";
            $params[] = $offset;
            $params[] = $limit;
            $types .= 'ii';
        }
        
        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            die("Statement preparation failed: " . $this->conn->error);
        }
        
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        $categories = [];
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row;
        }
        
        return $categories;
    }
    
    /**
     * Add a new recipe category
     * 
     * @param string $category_name Category name
     * @return bool True if successful, false otherwise
     */
    public function addCategory($category_name) {
        $query = "INSERT INTO recipe_categories (category_name) VALUES (?)";
        
        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            die("Statement preparation failed: " . $this->conn->error);
        }
        
        $stmt->bind_param("s", $category_name);
        
        return $stmt->execute();
    }
    
    /**
     * Edit a recipe category
     * 
     * @param int $id Category ID
     * @param string $category_name New category name
     * @return bool True if successful, false otherwise
     */
    public function editCategory($id, $category_name) {
        $query = "UPDATE recipe_categories SET category_name = ? WHERE category_id = ?";
        
        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            die("Statement preparation failed: " . $this->conn->error);
        }
        
        $stmt->bind_param("si", $category_name, $id);
        
        return $stmt->execute();
    }
    
    /**
     * Count total recipe categories
     * 
     * @param string $search Search term
     * @return int Total number of categories
     */
    public function countCategories($search = '') {
        $query = "SELECT COUNT(*) as count FROM recipe_categories";
        
        if (!empty($search)) {
            $query .= " WHERE category_name LIKE ?";
            $stmt = $this->conn->prepare($query);
            $search_param = '%' . $search . '%';
            $stmt->bind_param("s", $search_param);
        } else {
            $stmt = $this->conn->prepare($query);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        return $row['count'];
    }
    
    /**
     * Delete category
     * 
     * @param int $category_id Category ID
     * @return bool True if successful, false otherwise
     */
    public function deleteCategory($category_id) {
        // Check if category is used in any recipes
        $query = "SELECT COUNT(*) as count FROM recipe_category_mapping WHERE category_id = ?";
        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            die("Statement preparation failed: " . $this->conn->error);
        }
        $stmt->bind_param("i", $category_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        // If category is used in recipes, don't delete it
        if ($row['count'] > 0) {
            return false;
        }
        
        // Delete the category
        $query = "DELETE FROM recipe_categories WHERE category_id = ?";
        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            die("Statement preparation failed: " . $this->conn->error);
        }
        $stmt->bind_param("i", $category_id);
        
        return $stmt->execute();
    }
    
    /**
     * Delete user
     * 
     * @param int $user_id User ID
     * @return bool True if successful, false otherwise
     */
    public function deleteUser($user_id) {
        // Begin transaction
        $this->conn->begin_transaction();
        
        try {
            // User's recipes, favorites, pantry items, etc. will be automatically deleted
            // due to ON DELETE CASCADE constraints in the database
            
            // Delete the user
            $query = "DELETE FROM users WHERE user_id = ?";
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                throw new Exception("Statement preparation failed: " . $this->conn->error);
            }
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            
            // Commit transaction
            $this->conn->commit();
            return true;
            
        } catch (Exception $e) {
            // Rollback transaction on error
            $this->conn->rollback();
            error_log("Error deleting user: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete recipe
     * 
     * @param int $recipe_id Recipe ID
     * @return bool True if successful, false otherwise
     */
    public function deleteRecipe($recipe_id) {
        // Begin transaction
        $this->conn->begin_transaction();
        
        try {
            // Recipe ingredients, category mappings, and favorites will be automatically deleted
            // due to ON DELETE CASCADE constraints in the database
            
            // Delete the recipe
            $query = "DELETE FROM recipes WHERE recipe_id = ?";
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                throw new Exception("Statement preparation failed: " . $this->conn->error);
            }
            $stmt->bind_param("i", $recipe_id);
            $stmt->execute();
            
            // Commit transaction
            $this->conn->commit();
            return true;
            
        } catch (Exception $e) {
            // Rollback transaction on error
            $this->conn->rollback();
            error_log("Error deleting recipe: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get user engagement statistics
     * 
     * @param string $start_date Start date
     * @param string $end_date End date
     * @return array Array of user statistics
     */
    public function getUserEngagementStats($start_date, $end_date) {
        $query = "SELECT 
                    (SELECT COUNT(*) FROM users) as total_users,
                    (SELECT COUNT(*) FROM users WHERE created_at BETWEEN ? AND ?) as new_users,
                    (SELECT COUNT(*) FROM recipes WHERE created_at BETWEEN ? AND ?) as new_recipes,
                    (SELECT COUNT(*) FROM favorites WHERE created_at BETWEEN ? AND ?) as new_favorites";
        
        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            die("Statement preparation failed: " . $this->conn->error);
        }
        
        $stmt->bind_param("ssssss", $start_date, $end_date, $start_date, $end_date, $start_date, $end_date);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_assoc();
    }
    
    /**
     * Get recipe creation trend
     * 
     * @param string $start_date Start date
     * @param string $end_date End date
     * @return array Array of recipe creation data
     */
    public function getRecipeCreationTrend($start_date, $end_date) {
        $query = "SELECT 
                    DATE_FORMAT(created_at, '%Y-%m-%d') as date,
                    COUNT(*) as recipe_count
                  FROM recipes
                  WHERE created_at BETWEEN ? AND ?
                  GROUP BY DATE_FORMAT(created_at, '%Y-%m-%d')
                  ORDER BY date";
        
        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            die("Statement preparation failed: " . $this->conn->error);
        }
        
        $stmt->bind_param("ss", $start_date, $end_date);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $trend = [];
        while ($row = $result->fetch_assoc()) {
            $trend[] = $row;
        }
        
        return $trend;
    }
    
/**
 * Get top active users
 * 
 * @param int $limit Limit number of results
 * @return array Array of top active users
 */
public function getTopActiveUsers($limit = 10) {
    $query = "SELECT 
                u.user_id,
                u.username,
                u.email,
                u.created_at,
                u.status,
                u.last_login,
                COUNT(DISTINCT r.recipe_id) as recipe_count,
                (SELECT COUNT(*) FROM favorites WHERE user_id = u.user_id) as favorite_count,
                (SELECT COUNT(*) FROM recipe_comments WHERE user_id = u.user_id) as comment_count
            FROM users u
            LEFT JOIN recipes r ON u.user_id = r.user_id
            GROUP BY u.user_id, u.username, u.email, u.created_at, u.status, u.last_login
            ORDER BY recipe_count DESC, favorite_count DESC
            LIMIT ?";
    
    $stmt = $this->conn->prepare($query);
    if (!$stmt) {
        die("Statement preparation failed: " . $this->conn->error);
    }
    
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $users = [];
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
    
    return $users;
}
    
    /**
     * Get recipe statistics
     * 
     * @param string $start_date Start date
     * @param string $end_date End date
     * @return array Array of recipe statistics
     */
    public function getRecipeStats($start_date, $end_date) {
        $query = "SELECT 
                    (SELECT COUNT(*) FROM recipes) as total_recipes,
                    (SELECT COUNT(*) FROM recipes WHERE public = 1) as public_recipes,
                    (SELECT COUNT(*) FROM recipes WHERE created_at BETWEEN ? AND ?) as new_recipes,
                    (SELECT COUNT(DISTINCT recipe_id) FROM favorites) as favorited_recipes,
                    (SELECT COUNT(*) FROM favorites WHERE created_at BETWEEN ? AND ?) as new_favorites";
        
        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            die("Statement preparation failed: " . $this->conn->error);
        }
        
        $stmt->bind_param("ssss", $start_date, $end_date, $start_date, $end_date);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_assoc();
    }
    
    /**
     * Get top favorite recipes
     * 
     * @param int $limit Limit number of results
     * @return array Array of top favorite recipes
     */
    public function getTopFavoriteRecipes($limit = 10) {
        $query = "SELECT 
                    r.recipe_id,
                    r.recipe_name,
                    r.image,
                    u.username,
                    COUNT(f.id) as favorite_count
                  FROM recipes r
                  JOIN users u ON r.user_id = u.user_id
                  LEFT JOIN favorites f ON r.recipe_id = f.recipe_id
                  GROUP BY r.recipe_id
                  HAVING favorite_count > 0
                  ORDER BY favorite_count DESC
                  LIMIT ?";
        
        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            die("Statement preparation failed: " . $this->conn->error);
        }
        
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $recipes = [];
        while ($row = $result->fetch_assoc()) {
            $recipes[] = $row;
        }
        
        return $recipes;
    }
    
    /**
     * Get trending categories
     * 
     * @param string $start_date Start date
     * @param string $end_date End date
     * @return array Array of trending categories
     */
    public function getTrendingCategories($start_date, $end_date) {
        $query = "SELECT 
                    rc.category_id,
                    rc.category_name,
                    COUNT(DISTINCT r.recipe_id) as recipe_count
                  FROM recipe_categories rc
                  JOIN recipe_category_mapping rcm ON rc.category_id = rcm.category_id
                  JOIN recipes r ON rcm.recipe_id = r.recipe_id
                  WHERE r.created_at BETWEEN ? AND ?
                  GROUP BY rc.category_id
                  ORDER BY recipe_count DESC
                  LIMIT 10";
        
        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            die("Statement preparation failed: " . $this->conn->error);
        }
        
        $stmt->bind_param("ss", $start_date, $end_date);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $categories = [];
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row;
        }
        
        return $categories;
    }

    /**
     * Get user engagement data for dashboard
     */
    public function getUserEngagementData() {
        $query = "SELECT 
                    u.username,
                    COUNT(DISTINCT r.recipe_id) as recipe_count,
                    COUNT(DISTINCT f.id) as favorite_count,
                    COUNT(DISTINCT c.comment_id) as comment_count
                FROM users u
                LEFT JOIN recipes r ON u.user_id = r.user_id
                LEFT JOIN favorites f ON u.user_id = f.user_id
                LEFT JOIN recipe_comments c ON u.user_id = c.user_id
                GROUP BY u.user_id
                ORDER BY recipe_count DESC
                LIMIT 10";
        
        $result = $this->conn->query($query);
        $engagement_data = [];
        
        while ($row = $result->fetch_assoc()) {
            $engagement_data[] = $row;
        }
        
        return $engagement_data;
    }

    /**
     * Get recipe performance data
     */
    public function getRecipePerformanceData() {
        $query = "SELECT 
                    r.recipe_name,
                    COUNT(DISTINCT f.id) as favorite_count,
                    COUNT(DISTINCT c.comment_id) as comment_count
                FROM recipes r
                LEFT JOIN favorites f ON r.recipe_id = f.recipe_id
                LEFT JOIN recipe_comments c ON r.recipe_id = c.recipe_id
                GROUP BY r.recipe_id
                ORDER BY favorite_count DESC
                LIMIT 10";
        
        $result = $this->conn->query($query);
        $performance_data = [];
        
        while ($row = $result->fetch_assoc()) {
            $performance_data[] = $row;
        }
        
        return $performance_data;
    }

    /**
     * Get recipe category distribution
     */
    public function getCategoryDistributionData() {
        $query = "SELECT 
                    rc.category_name,
                    COUNT(DISTINCT rcm.recipe_id) as recipe_count
                FROM recipe_categories rc
                LEFT JOIN recipe_category_mapping rcm ON rc.category_id = rcm.category_id
                GROUP BY rc.category_id
                ORDER BY recipe_count DESC";
        
        $result = $this->conn->query($query);
        $category_data = [];
        
        while ($row = $result->fetch_assoc()) {
            $category_data[] = $row;
        }
        
        return $category_data;
    }

    /**
     * Get dietary preferences associated with a category
     */
    public function getCategoryPreferences($category_id) {
        $query = "SELECT dp.preference_id, dp.preference_name
                FROM preference_category_mapping pcm
                JOIN dietary_preferences dp ON pcm.preference_id = dp.preference_id
                WHERE pcm.category_id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $category_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $preferences = [];
        while ($row = $result->fetch_assoc()) {
            $preferences[] = $row;
        }
        
        return $preferences;
    }

    /**
     * Add a new recipe category and return the ID
     * 
     * @param string $category_name Category name
     * @return int|false Category ID if successful, false otherwise
     */
    public function addCategoryWithReturn($category_name) {
        $query = "INSERT INTO recipe_categories (category_name) VALUES (?)";
        
        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            die("Statement preparation failed: " . $this->conn->error);
        }
        
        $stmt->bind_param("s", $category_name);
        
        if ($stmt->execute()) {
            return $stmt->insert_id;
        }
        return false;
    }

    /**
     * Get ad performance data
     */
    public function getAdPerformanceData() {
        $query = "SELECT 
                    a.title, 
                    (SELECT COUNT(*) FROM ad_impressions WHERE ad_id = a.ad_id) as impressions,
                    (SELECT COUNT(*) FROM ad_clicks WHERE ad_id = a.ad_id) as clicks
                FROM ads a
                ORDER BY impressions DESC";
        
        $result = $this->conn->query($query);
        $ad_data = [];
        
        while ($row = $result->fetch_assoc()) {
            // Calculate CTR
            $row['ctr'] = $row['impressions'] > 0 ? 
                round(($row['clicks'] / $row['impressions']) * 100, 2) : 0;
            $ad_data[] = $row;
        }
        
        return $ad_data;
    }
}
?>
