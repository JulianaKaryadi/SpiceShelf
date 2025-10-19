<?php
class Recipe {

    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    //categories
    public function getCategories() {
        $categories_query = "SELECT * FROM recipe_categories";
        $result = $this->conn->query($categories_query);
        return $result;
    }

    // ingredients
    public function getIngredients() {
        $ingredients_query = "SELECT * FROM ingredients";
        $result = $this->conn->query($ingredients_query);
        return $result;
    }

    // measurements
    public function getMeasurements() {
        $measurements_query = "SELECT * FROM measurements";
        $result = $this->conn->query($measurements_query);
        return $result;
    }

    // Add recipe
    public function addRecipe($user_id, $recipe_name, $description, $prep_time, $cook_time, $image, $steps, $serving_size, $public, $category_ids) {
        // Insert the recipe into the `recipes` table
        $query = "INSERT INTO recipes (user_id, recipe_name, description, prep_time, cook_time, image, steps, serving_size, public, created_at, updated_at) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
    
        if ($stmt = $this->conn->prepare($query)) {
            if ($image) {
                $stmt->bind_param("issiisssi", $user_id, $recipe_name, $description, $prep_time, $cook_time, $image, $steps, $serving_size, $public);
            } else {
                $stmt->bind_param("issiisssi", $user_id, $recipe_name, $description, $prep_time, $cook_time, NULL, $steps, $serving_size, $public);
            }
            $stmt->execute();
            $recipe_id = $stmt->insert_id;
            $stmt->close();
    
            // Insert categories into the `recipe_category_mapping` table
            $query = "INSERT INTO recipe_category_mapping (recipe_id, category_id) VALUES (?, ?)";
            $stmt = $this->conn->prepare($query);
            foreach ($category_ids as $category_id) {
                $stmt->bind_param("ii", $recipe_id, $category_id);
                $stmt->execute();
            }
            $stmt->close();
    
            return $recipe_id;
        } else {
            throw new Exception("Error preparing statement: " . $this->conn->error);
        }
    }      

    // Add ingredients to recipe
    public function addRecipeIngredient($recipe_id, $ingredient) {
        $query = "INSERT INTO recipe_ingredients (recipe_id, ingredient_id, quantity, measurement_id) VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            die("Database error: " . $this->conn->error);
        }
        $stmt->bind_param("iiid", $recipe_id, $ingredient['ingredient_id'], $ingredient['quantity'], $ingredient['measurement_id']);
        $stmt->execute();
        $stmt->close();
    }    

    public function getPublicRecipes($filters = [], $search = '') {
        $query = "SELECT DISTINCT r.recipe_id, r.recipe_name, r.description, r.prep_time, r.cook_time, r.image 
                  FROM recipes r
                  LEFT JOIN recipe_ingredients ri ON r.recipe_id = ri.recipe_id
                  LEFT JOIN recipe_category_mapping rcm ON r.recipe_id = rcm.recipe_id
                  WHERE r.public = 1";
    
        $params = [];
        $types = '';
    
        // Apply search filter
        if (!empty($search)) {
            $query .= " AND r.recipe_name LIKE ?";
            $params[] = '%' . $search . '%';
            $types .= 's';
        }
    
        // Apply ingredient filter (OR logic)
        if (!empty($filters['ingredients'])) {
            $placeholders = implode(',', array_fill(0, count($filters['ingredients']), '?'));
            $query .= " AND ri.ingredient_id IN ($placeholders)";
            $params = array_merge($params, $filters['ingredients']);
            $types .= str_repeat('i', count($filters['ingredients']));
        }
    
        // Apply category filter (AND logic)
        if (!empty($filters['categories'])) {
            foreach ($filters['categories'] as $category_id) {
                $query .= " AND EXISTS (
                    SELECT 1 FROM recipe_category_mapping rcm_sub 
                    WHERE rcm_sub.recipe_id = r.recipe_id AND rcm_sub.category_id = ?
                )";
                $params[] = $category_id;
                $types .= 'i';
            }
        }
    
        $query .= " ORDER BY r.created_at DESC";
    
        // Prepare statement and execute
        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            die("Database error: " . $this->conn->error);
        }
        if ($types) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
    
        $recipes = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $recipes[] = $row;
            }
        }
        return $recipes;
    }       
    
    public function getUserRecipes($user_id) {
        $query = "SELECT recipe_id, recipe_name, description, prep_time, cook_time, image, public 
                  FROM recipes 
                  WHERE user_id = ? 
                  ORDER BY created_at DESC";
    
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
    
        $recipes = [];
        while ($row = $result->fetch_assoc()) {
            $recipes[] = $row;
        }
    
        return $recipes;
    }

    public function getFavoriteRecipes($user_id) {
        $query = "SELECT r.recipe_id, r.recipe_name, r.description, r.prep_time, r.cook_time, r.image 
                  FROM favorites f
                  JOIN recipes r ON f.recipe_id = r.recipe_id
                  WHERE f.user_id = ? 
                  ORDER BY f.created_at DESC";
    
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
    
        $recipes = [];
        while ($row = $result->fetch_assoc()) {
            $recipes[] = $row;
        }
    
        return $recipes;
    }
    
    public function getRecipeById($recipe_id) {
        $query = "SELECT * FROM recipes WHERE recipe_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $recipe_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function getRecipeIngredients($recipe_id) {
        $query = "SELECT ri.id AS recipe_ingredient_id, ri.ingredient_id, ri.quantity, ri.measurement_id, 
                         m.name AS measurement, i.name AS ingredient_name 
                  FROM recipe_ingredients ri
                  JOIN ingredients i ON ri.ingredient_id = i.ingredient_id
                  JOIN measurements m ON ri.measurement_id = m.measurement_id
                  WHERE ri.recipe_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $recipe_id);
        $stmt->execute();
        $result = $stmt->get_result();
    
        $ingredients = [];
        while ($row = $result->fetch_assoc()) {
            $ingredients[] = $row;
        }
        return $ingredients;
    }          
       
    public function isFavorited($user_id, $recipe_id) {
        $query = "SELECT 1 FROM favorites WHERE user_id = ? AND recipe_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ii", $user_id, $recipe_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $isFavorited = $result->num_rows > 0;
        $stmt->close();
    
        return $isFavorited;
    }
    
    public function deleteRecipeById($recipe_id) {
        $query = "DELETE FROM recipes WHERE recipe_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $recipe_id);
        $stmt->execute();
        $stmt->close();
    }
    
    public function updateRecipeById($recipe_id, $recipe_name, $description, $prep_time, $cook_time, $serving_size, $steps, $image, $public) {
        $query = "UPDATE recipes 
                  SET recipe_name = ?, description = ?, prep_time = ?, cook_time = ?, serving_size = ?, steps = ?, image = ?, public = ?, updated_at = NOW() 
                  WHERE recipe_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ssiiisssi", $recipe_name, $description, $prep_time, $cook_time, $serving_size, $steps, $image, $public, $recipe_id);
        $stmt->execute();
        $stmt->close();
    }      

    public function updateRecipeIngredient($id, $ingredient_id, $quantity, $measurement_id) {
        $query = "UPDATE recipe_ingredients 
                  SET ingredient_id = ?, quantity = ?, measurement_id = ? 
                  WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            die("Database error: " . $this->conn->error);
        }
        $stmt->bind_param("idii", $ingredient_id, $quantity, $measurement_id, $id);
        $stmt->execute();
        $stmt->close();
    }               

    public function getRecipeCategories($recipe_id) {
        $query = "SELECT category_id FROM recipe_category_mapping WHERE recipe_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $recipe_id);
        $stmt->execute();
        $result = $stmt->get_result();
    
        $categories = [];
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row['category_id'];
        }
        return $categories;
    }  
    
    public function updateRecipeCategories($recipe_id, $category_ids) {
        // Delete existing categories for the recipe
        $delete_query = "DELETE FROM recipe_category_mapping WHERE recipe_id = ?";
        $stmt = $this->conn->prepare($delete_query);
        if (!$stmt) {
            die("Database error (delete categories): " . $this->conn->error);
        }
        $stmt->bind_param("i", $recipe_id);
        $stmt->execute();
        $stmt->close();
    
        // Insert new categories for the recipe
        $insert_query = "INSERT INTO recipe_category_mapping (recipe_id, category_id) VALUES (?, ?)";
        $stmt = $this->conn->prepare($insert_query);
        if (!$stmt) {
            die("Database error (insert categories): " . $this->conn->error);
        }
        foreach ($category_ids as $category_id) {
            $stmt->bind_param("ii", $recipe_id, $category_id);
            $stmt->execute();
        }
        $stmt->close();
    }
    
    public function getUserDietaryPreferences($user_id) {
        $query = "SELECT dp.preference_id, dp.preference_name 
                  FROM dietary_preferences dp
                  JOIN user_dietary_preferences udp ON dp.preference_id = udp.preference_id
                  WHERE udp.user_id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    public function getUserAllergies($user_id) {
        $query = "SELECT a.allergy_id, a.allergy_name 
                  FROM allergies a
                  JOIN user_allergies ua ON a.allergy_id = ua.allergy_id
                  WHERE ua.user_id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    public function getFilteredPublicRecipes($search, $ingredients, $categories, $user_id = null, $disableFilters = false) {
        // Start building the base query
        $query = "SELECT DISTINCT r.recipe_id, r.recipe_name, r.description, r.image 
                  FROM recipes r
                  LEFT JOIN recipe_ingredients ri ON r.recipe_id = ri.recipe_id
                  LEFT JOIN recipe_category_mapping rcm ON r.recipe_id = rcm.recipe_id";
    
        $params = [];
        $types = "";
    
        // If user is logged in and filters are not disabled, add the dietary preference and allergy filtering
        if ($user_id && !$disableFilters) {
            // Add WHERE clause for the main query
            $query .= " WHERE r.public = 1";
            
            // Get user's dietary preferences
            $userPrefsQuery = "SELECT preference_id FROM user_dietary_preferences WHERE user_id = ?";
            $userPrefsStmt = $this->conn->prepare($userPrefsQuery);
            $userPrefsStmt->bind_param("i", $user_id);
            $userPrefsStmt->execute();
            $userPrefsResult = $userPrefsStmt->get_result();
            $userPrefs = [];
            while ($row = $userPrefsResult->fetch_assoc()) {
                $userPrefs[] = $row['preference_id'];
            }
            $userPrefsStmt->close();
            
            // Filter based on dietary preferences only if user has preferences and they're not 'None'
            if (!empty($userPrefs) && !in_array(7, $userPrefs)) { // 7 is 'None' in your database
                $prefsPlaceholders = str_repeat('?,', count($userPrefs) - 1) . '?';
                $query .= " AND (
                    EXISTS (
                        SELECT 1 
                        FROM recipe_category_mapping rcm2
                        JOIN preference_category_mapping pcm ON rcm2.category_id = pcm.category_id
                        WHERE rcm2.recipe_id = r.recipe_id
                        AND pcm.preference_id IN ($prefsPlaceholders)
                    )
                )";
                $params = array_merge($params, $userPrefs);
                $types .= str_repeat("i", count($userPrefs));
            }
            
            // Get user's allergies
            $userAllergiesQuery = "SELECT allergy_id FROM user_allergies WHERE user_id = ?";
            $userAllergiesStmt = $this->conn->prepare($userAllergiesQuery);
            $userAllergiesStmt->bind_param("i", $user_id);
            $userAllergiesStmt->execute();
            $userAllergiesResult = $userAllergiesStmt->get_result();
            $userAllergies = [];
            while ($row = $userAllergiesResult->fetch_assoc()) {
                $userAllergies[] = $row['allergy_id'];
            }
            $userAllergiesStmt->close();
            
            // Filter out recipes with allergen ingredients only if user has allergies and they're not 'None'
            if (!empty($userAllergies) && !in_array(9, $userAllergies)) { // 9 is 'None' in your database
                $query .= " AND r.recipe_id NOT IN (
                    SELECT DISTINCT ri2.recipe_id 
                    FROM recipe_ingredients ri2
                    JOIN allergy_ingredient_mapping aim ON ri2.ingredient_id = aim.ingredient_id
                    WHERE aim.allergy_id IN (";
                
                $allergiesPlaceholders = str_repeat('?,', count($userAllergies) - 1) . '?';
                $query .= $allergiesPlaceholders . "))";
                $params = array_merge($params, $userAllergies);
                $types .= str_repeat("i", count($userAllergies));
            }
        } else {
            $query .= " WHERE r.public = 1";
        }
    
        // Apply search filter
        if (!empty($search)) {
            $query .= " AND r.recipe_name LIKE ?";
            $params[] = '%' . $search . '%';
            $types .= "s";
        }
    
        // Apply ingredient filter
        if (!empty($ingredients)) {
            $placeholders = str_repeat('?,', count($ingredients) - 1) . '?';
            $query .= " AND EXISTS (
                SELECT 1 FROM recipe_ingredients ri_filter 
                WHERE ri_filter.recipe_id = r.recipe_id 
                AND ri_filter.ingredient_id IN ($placeholders)
            )";
            $params = array_merge($params, $ingredients);
            $types .= str_repeat("i", count($ingredients));
        }
    
        // Apply category filter
        if (!empty($categories)) {
            $placeholders = str_repeat('?,', count($categories) - 1) . '?';
            $query .= " AND EXISTS (
                SELECT 1 FROM recipe_category_mapping rcm_filter 
                WHERE rcm_filter.recipe_id = r.recipe_id 
                AND rcm_filter.category_id IN ($placeholders)
            )";
            $params = array_merge($params, $categories);
            $types .= str_repeat("i", count($categories));
        }
    
        $query .= " GROUP BY r.recipe_id ORDER BY r.created_at DESC";
    
        $stmt = $this->conn->prepare($query);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
    
        return $result->fetch_all(MYSQLI_ASSOC);
    }           

    // Handle favorites
    public function toggleFavorite($user_id, $recipe_id) {
        if ($this->isFavorited($user_id, $recipe_id)) {
            $query = "DELETE FROM favorites WHERE user_id = ? AND recipe_id = ?";
        } else {
            $query = "INSERT INTO favorites (user_id, recipe_id) VALUES (?, ?)";
        }

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ii", $user_id, $recipe_id);
        $stmt->execute();
        $stmt->close();
    }

    public function searchRecipes($search_term, $user_id = null) {
        // Debug logging
        error_log("Searching for: " . $search_term . " for user: " . $user_id);
        
        $query = "SELECT r.recipe_id, r.recipe_name, r.description, r.image, 
                r.prep_time, r.cook_time, r.serving_size
                FROM recipes r 
                WHERE (r.recipe_name LIKE ? OR r.description LIKE ?)";
        
        $params = ["%{$search_term}%", "%{$search_term}%"];
        $types = "ss";
        
        // Include both public recipes and user's private recipes
        if ($user_id) {
            $query .= " AND (r.public = 1 OR r.user_id = ?)";
            $params[] = $user_id;
            $types .= "i";
        } else {
            $query .= " AND r.public = 1";
        }
        
        $query .= " ORDER BY r.recipe_name LIMIT 15";
        
        error_log("Query: " . $query);
        error_log("Params: " . json_encode($params));
        
        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            error_log("Statement preparation failed: " . $this->conn->error);
            return [];
        }
        
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $recipes = [];
        while ($row = $result->fetch_assoc()) {
            $recipes[] = $row;
        }
        
        error_log("Found recipes: " . count($recipes));
        
        return $recipes;
    }

    // Add to Recipe.php class

    // Get all allergies and their associated ingredients
    public function getAllergyIngredientMappings() {
        $query = "SELECT aim.allergy_id, aim.ingredient_id, a.allergy_name, i.name as ingredient_name
                FROM allergy_ingredient_mapping aim
                JOIN allergies a ON aim.allergy_id = a.allergy_id
                JOIN ingredients i ON aim.ingredient_id = i.ingredient_id
                ORDER BY a.allergy_name, i.name";
        
        $result = $this->conn->query($query);
        $mappings = [];
        
        while ($row = $result->fetch_assoc()) {
            if (!isset($mappings[$row['allergy_id']])) {
                $mappings[$row['allergy_id']] = [
                    'allergy_name' => $row['allergy_name'],
                    'ingredients' => []
                ];
            }
            
            $mappings[$row['allergy_id']]['ingredients'][] = [
                'ingredient_id' => $row['ingredient_id'],
                'ingredient_name' => $row['ingredient_name']
            ];
        }
        
        return $mappings;
    }

    // Get all ingredients for a specific allergy
    public function getAllergyIngredients($allergy_id) {
        $query = "SELECT i.ingredient_id, i.name
                FROM allergy_ingredient_mapping aim
                JOIN ingredients i ON aim.ingredient_id = i.ingredient_id
                WHERE aim.allergy_id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $allergy_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $ingredients = [];
        while ($row = $result->fetch_assoc()) {
            $ingredients[] = $row;
        }
        
        return $ingredients;
    }

    // Get all allergens for a specific ingredient
    public function getIngredientAllergies($ingredient_id) {
        $query = "SELECT a.allergy_id, a.allergy_name
                FROM allergy_ingredient_mapping aim
                JOIN allergies a ON aim.allergy_id = a.allergy_id
                WHERE aim.ingredient_id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $ingredient_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $allergies = [];
        while ($row = $result->fetch_assoc()) {
            $allergies[] = $row;
        }
        
        return $allergies;
    }

    // Associate an ingredient with an allergy
    public function addAllergyIngredientMapping($allergy_id, $ingredient_id) {
        // Check if mapping already exists
        $checkQuery = "SELECT 1 FROM allergy_ingredient_mapping 
                    WHERE allergy_id = ? AND ingredient_id = ?";
        $stmt = $this->conn->prepare($checkQuery);
        $stmt->bind_param("ii", $allergy_id, $ingredient_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            return true; // Mapping already exists
        }
        
        // Add the mapping
        $query = "INSERT INTO allergy_ingredient_mapping (allergy_id, ingredient_id) 
                VALUES (?, ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ii", $allergy_id, $ingredient_id);
        return $stmt->execute();
    }

    // Remove an allergen-ingredient mapping
    public function removeAllergyIngredientMapping($allergy_id, $ingredient_id) {
        $query = "DELETE FROM allergy_ingredient_mapping 
                WHERE allergy_id = ? AND ingredient_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ii", $allergy_id, $ingredient_id);
        return $stmt->execute();
    }

    // Similar methods for dietary preference - category mappings
    public function getPreferenceCategoryMappings() {
        $query = "SELECT pcm.preference_id, pcm.category_id, dp.preference_name, rc.category_name
                FROM preference_category_mapping pcm
                JOIN dietary_preferences dp ON pcm.preference_id = dp.preference_id
                JOIN recipe_categories rc ON pcm.category_id = rc.category_id
                ORDER BY dp.preference_name, rc.category_name";
        
        $result = $this->conn->query($query);
        $mappings = [];
        
        while ($row = $result->fetch_assoc()) {
            if (!isset($mappings[$row['preference_id']])) {
                $mappings[$row['preference_id']] = [
                    'preference_name' => $row['preference_name'],
                    'categories' => []
                ];
            }
            
            $mappings[$row['preference_id']]['categories'][] = [
                'category_id' => $row['category_id'],
                'category_name' => $row['category_name']
            ];
        }
        
        return $mappings;
    }

    // Get categories associated with a dietary preference
    public function getPreferenceCategories($preference_id) {
        $query = "SELECT rc.category_id, rc.category_name
                FROM preference_category_mapping pcm
                JOIN recipe_categories rc ON pcm.category_id = rc.category_id
                WHERE pcm.preference_id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $preference_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $categories = [];
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row;
        }
        
        return $categories;
    }

    /**
     * Get default measurement for an ingredient
     * 
     * @param int $ingredient_id The ingredient ID
     * @return array|null The default measurement information or null if not found
     */
    public function getDefaultMeasurement($ingredient_id) {
        $query = "SELECT m.measurement_id, m.name 
                  FROM ingredient_measurement_defaults imd
                  JOIN measurements m ON imd.measurement_id = m.measurement_id
                  WHERE imd.ingredient_id = ? AND imd.is_default = 1
                  LIMIT 1";
                  
        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            return null;
        }
        
        $stmt->bind_param("i", $ingredient_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        
        return null;
    }

    /**
     * Get all comments for a recipe
     * @param int $recipe_id
     * @return array Comments with user info
     */
    public function getRecipeComments($recipe_id) {
        $query = "SELECT c.comment_id, c.recipe_id, c.user_id, c.comment, c.created_at, 
                  u.username
                  FROM recipe_comments c
                  JOIN users u ON c.user_id = u.user_id
                  WHERE c.recipe_id = ?
                  ORDER BY c.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $recipe_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $comments = [];
        while ($row = $result->fetch_assoc()) {
            $comments[] = $row;
        }
        
        return $comments;
    }

    /**
     * Add a comment to a recipe
     * @param int $recipe_id
     * @param int $user_id
     * @param string $comment
     * @return bool Success status
     */
    public function addComment($recipe_id, $user_id, $comment) {
        $query = "INSERT INTO recipe_comments (recipe_id, user_id, comment) VALUES (?, ?, ?)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("iis", $recipe_id, $user_id, $comment);
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    }

    /**
     * Delete a comment
     * @param int $comment_id
     * @param int $user_id User trying to delete (for authorization)
     * @return bool Success status
     */
    public function deleteComment($comment_id, $user_id) {
        // First check if the user is authorized to delete this comment
        $check_query = "SELECT user_id FROM recipe_comments WHERE comment_id = ?";
        $check_stmt = $this->conn->prepare($check_query);
        $check_stmt->bind_param("i", $comment_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        $comment = $result->fetch_assoc();
        $check_stmt->close();
        
        // If comment doesn't exist or user isn't authorized
        if (!$comment || ($comment['user_id'] != $user_id)) {
            return false;
        }
        
        // Delete the comment
        $delete_query = "DELETE FROM recipe_comments WHERE comment_id = ?";
        $delete_stmt = $this->conn->prepare($delete_query);
        $delete_stmt->bind_param("i", $comment_id);
        $result = $delete_stmt->execute();
        $delete_stmt->close();
        
        return $result;
    }

    /**
     * Associate a category with a dietary preference
     */
    public function addPreferenceCategoryMapping($preference_id, $category_id) {
        // Check if mapping already exists
        $checkQuery = "SELECT 1 FROM preference_category_mapping 
                    WHERE preference_id = ? AND category_id = ?";
        $stmt = $this->conn->prepare($checkQuery);
        $stmt->bind_param("ii", $preference_id, $category_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            return true; // Mapping already exists
        }
        
        // Add the mapping
        $query = "INSERT INTO preference_category_mapping (preference_id, category_id) 
                VALUES (?, ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ii", $preference_id, $category_id);
        return $stmt->execute();
    }

    /**
     * Remove a preference-category mapping
     */
    public function removePreferenceCategoryMapping($preference_id, $category_id) {
        $query = "DELETE FROM preference_category_mapping 
                WHERE preference_id = ? AND category_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ii", $preference_id, $category_id);
        return $stmt->execute();
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
}
?>
