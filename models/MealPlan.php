<?php
// models/MealPlan.php

class MealPlan {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    /**
     * Add a recipe to meal plan
     */
    public function addToMealPlan($user_id, $meal_date, $meal_type, $recipe_id, $serving_size = 1, $notes = null) {
        $query = "INSERT INTO meal_plans (user_id, meal_date, meal_type, recipe_id, serving_size, notes, created_at) 
                 VALUES (?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            die("Statement preparation failed: " . $this->conn->error);
        }
        $stmt->bind_param("issiis", $user_id, $meal_date, $meal_type, $recipe_id, $serving_size, $notes);
        $stmt->execute();
        
        return $this->conn->insert_id;
    }

    /**
     * Update a meal plan entry
     */
    public function updateMealPlan($meal_plan_id, $meal_date, $meal_type, $recipe_id, $serving_size = 1, $notes = null) {
        $query = "UPDATE meal_plans SET 
                 meal_date = ?, 
                 meal_type = ?, 
                 recipe_id = ?, 
                 serving_size = ?, 
                 notes = ?, 
                 updated_at = NOW() 
                 WHERE meal_plan_id = ?";
        
        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            die("Statement preparation failed: " . $this->conn->error);
        }
        $stmt->bind_param("ssiisi", $meal_date, $meal_type, $recipe_id, $serving_size, $notes, $meal_plan_id);
        $stmt->execute();
        
        return $stmt->affected_rows > 0;
    }

    /**
     * Remove a meal from a meal plan
     */
    public function removeMeal($meal_plan_id) {
        $query = "DELETE FROM meal_plans WHERE meal_plan_id = ?";
        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            die("Statement preparation failed: " . $this->conn->error);
        }
        $stmt->bind_param("i", $meal_plan_id);
        $stmt->execute();
        
        return $stmt->affected_rows > 0;
    }

    /**
     * Get meal plan for a user within a date range
     */
    public function getUserMealPlan($user_id, $start_date, $end_date) {
        $query = "SELECT mp.*, r.recipe_name, r.image, r.prep_time, r.cook_time, r.description
                 FROM meal_plans mp
                 JOIN recipes r ON mp.recipe_id = r.recipe_id
                 WHERE mp.user_id = ? 
                 AND mp.meal_date BETWEEN ? AND ?
                 ORDER BY mp.meal_date, 
                 CASE 
                    WHEN mp.meal_type = 'Breakfast' THEN 1
                    WHEN mp.meal_type = 'Lunch' THEN 2
                    WHEN mp.meal_type = 'Dinner' THEN 3
                    WHEN mp.meal_type = 'Snack' THEN 4
                 END";
        
        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            die("Statement preparation failed: " . $this->conn->error);
        }
        $stmt->bind_param("iss", $user_id, $start_date, $end_date);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if (!$result) {
            die("Query failed: " . $this->conn->error);
        }
        
        $mealPlans = [];
        while ($row = $result->fetch_assoc()) {
            $mealPlans[] = $row;
        }
        return $mealPlans;
    }

    /**
     * Get a single meal plan entry
     */
    public function getMealById($meal_plan_id) {
        $query = "SELECT mp.*, r.recipe_name, r.image, r.prep_time, r.cook_time, r.description
                 FROM meal_plans mp
                 JOIN recipes r ON mp.recipe_id = r.recipe_id
                 WHERE mp.meal_plan_id = ?";
        
        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            die("Statement preparation failed: " . $this->conn->error);
        }
        $stmt->bind_param("i", $meal_plan_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_assoc();
    }

    /**
     * Get meals for a specific date
     */
    public function getMealsByDate($user_id, $meal_date) {
        $query = "SELECT mp.*, r.recipe_name, r.image, r.prep_time, r.cook_time, r.description
                 FROM meal_plans mp
                 JOIN recipes r ON mp.recipe_id = r.recipe_id
                 WHERE mp.user_id = ? AND mp.meal_date = ?
                 ORDER BY CASE 
                    WHEN mp.meal_type = 'Breakfast' THEN 1
                    WHEN mp.meal_type = 'Lunch' THEN 2
                    WHEN mp.meal_type = 'Dinner' THEN 3
                    WHEN mp.meal_type = 'Snack' THEN 4
                 END";
        
        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            die("Statement preparation failed: " . $this->conn->error);
        }
        $stmt->bind_param("is", $user_id, $meal_date);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if (!$result) {
            die("Query failed: " . $this->conn->error);
        }
        
        $meals = [];
        while ($row = $result->fetch_assoc()) {
            $meals[] = $row;
        }
        return $meals;
    }

    /**
     * Check if a recipe is already in the meal plan for a specific date and meal type
     */
    public function checkRecipeInMealPlan($user_id, $meal_date, $meal_type, $recipe_id) {
        $query = "SELECT COUNT(*) as count FROM meal_plans 
                 WHERE user_id = ? 
                 AND meal_date = ? 
                 AND meal_type = ? 
                 AND recipe_id = ?";
        
        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            die("Statement preparation failed: " . $this->conn->error);
        }
        $stmt->bind_param("issi", $user_id, $meal_date, $meal_type, $recipe_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        return $row['count'] > 0;
    }

    /**
     * Generate a shopping list from a meal plan for a date range
     */
    public function generateShoppingList($user_id, $start_date, $end_date) {
        $query = "SELECT i.ingredient_id, i.name AS ingredient_name, 
                  SUM(ri.quantity * mp.serving_size / r.serving_size) AS total_quantity,
                  m.name AS measurement_name, m.measurement_id
                  FROM meal_plans mp
                  JOIN recipes r ON mp.recipe_id = r.recipe_id
                  JOIN recipe_ingredients ri ON r.recipe_id = ri.recipe_id
                  JOIN ingredients i ON ri.ingredient_id = i.ingredient_id
                  JOIN measurements m ON ri.measurement_id = m.measurement_id
                  WHERE mp.user_id = ? 
                  AND mp.meal_date BETWEEN ? AND ?
                  GROUP BY i.ingredient_id, m.measurement_id
                  ORDER BY i.name";
        
        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            die("Statement preparation failed: " . $this->conn->error);
        }
        $stmt->bind_param("iss", $user_id, $start_date, $end_date);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if (!$result) {
            die("Query failed: " . $this->conn->error);
        }
        
        $shoppingList = [];
        while ($row = $result->fetch_assoc()) {
            $shoppingList[] = $row;
        }
        return $shoppingList;
    }

    /**
     * Check shopping list against pantry
     */
    public function checkAgainstPantry($user_id, $shopping_list) {
        // Get user's pantry items
        $query = "SELECT p.ingredient_id, p.quantity, p.measurement_id, p.expiration_date,
                m.name as measurement_name
                FROM pantry p
                JOIN measurements m ON p.measurement_id = m.measurement_id
                WHERE p.user_id = ?";
                
        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            die("Statement preparation failed: " . $this->conn->error);
        }
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if (!$result) {
            die("Query failed: " . $this->conn->error);
        }
        
        // Create a lookup array for pantry items by ingredient
        $pantry_lookup = [];
        while ($item = $result->fetch_assoc()) {
            $ingredient_id = $item['ingredient_id'];
            if (!isset($pantry_lookup[$ingredient_id])) {
                $pantry_lookup[$ingredient_id] = [];
            }
            $pantry_lookup[$ingredient_id][] = $item;
        }
        
        // Check shopping list against pantry
        foreach ($shopping_list as &$item) {
            $ingredient_id = $item['ingredient_id'];
            
            if (isset($pantry_lookup[$ingredient_id])) {
                // User has this ingredient in pantry
                $have_enough = false;
                $needed_quantity = $item['total_quantity'];
                $pantry_items = [];
                $total_available = 0;
                $pantry_display = [];
                
                foreach ($pantry_lookup[$ingredient_id] as $pantry_item) {
                    // Only consider matching measurement
                    if ($pantry_item['measurement_id'] == $item['measurement_id']) {
                        $available_quantity = floatval($pantry_item['quantity']); // Cast to float
                        $total_available += $available_quantity;
                        
                        $pantry_items[] = [
                            'quantity' => $available_quantity,
                            'measurement_name' => $pantry_item['measurement_name'],
                            'expiration_date' => $pantry_item['expiration_date']
                        ];
                        
                        // Format display string for this pantry item
                        $pantry_display[] = number_format($available_quantity, 2) . ' ' . $pantry_item['measurement_name'];
                    }
                }
                
                // If we have enough of this ingredient with the same measurement
                if ($total_available >= $needed_quantity) {
                    $have_enough = true;
                    $needed_quantity = 0;
                } else {
                    $needed_quantity -= $total_available;
                }
                
                $item['in_pantry'] = true;
                $item['pantry_items'] = $pantry_items;
                $item['have_enough'] = $have_enough;
                $item['needed_quantity'] = floatval($needed_quantity);
                $item['pantry_display'] = !empty($pantry_display) ? implode(", ", $pantry_display) : "0 " . $item['measurement_name'];
            } else {
                // User doesn't have this ingredient
                $item['in_pantry'] = false;
                $item['pantry_items'] = [];
                $item['have_enough'] = false;
                $item['needed_quantity'] = floatval($item['total_quantity']);
                $item['pantry_display'] = "0 " . $item['measurement_name'];
            }
            
            $item['total_quantity'] = floatval($item['total_quantity']);
        }
        
        return $shopping_list;
    }

    /**
     * Get all distinct dates with meal plans for a user
     */
    public function getMealPlanDates($user_id, $limit = null) {
        $query = "SELECT DISTINCT meal_date FROM meal_plans 
                 WHERE user_id = ? 
                 ORDER BY meal_date DESC";
        
        if ($limit !== null) {
            $query .= " LIMIT ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("ii", $user_id, $limit);
        } else {
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $user_id);
        }
        
        if (!$stmt) {
            die("Statement preparation failed: " . $this->conn->error);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        if (!$result) {
            die("Query failed: " . $this->conn->error);
        }
        
        $dates = [];
        while ($row = $result->fetch_assoc()) {
            $dates[] = $row;
        }
        return $dates;
    }

    /**
     * Get meal plan statistics for a user
     */
    public function getMealPlanStats($user_id) {
        $query = "SELECT 
                 COUNT(DISTINCT meal_date) as total_days,
                 COUNT(*) as total_meals,
                 COUNT(DISTINCT recipe_id) as unique_recipes,
                 MIN(meal_date) as first_meal_date,
                 MAX(meal_date) as last_meal_date
                 FROM meal_plans
                 WHERE user_id = ?";
        
        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            die("Statement preparation failed: " . $this->conn->error);
        }
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_assoc();
    }

    /**
     * Get most frequently planned recipes for a user
     */
    public function getFrequentRecipes($user_id, $limit = 5) {
        $query = "SELECT mp.recipe_id, r.recipe_name, r.image, COUNT(*) as frequency
                 FROM meal_plans mp
                 JOIN recipes r ON mp.recipe_id = r.recipe_id
                 WHERE mp.user_id = ?
                 GROUP BY mp.recipe_id
                 ORDER BY frequency DESC
                 LIMIT ?";
        
        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            die("Statement preparation failed: " . $this->conn->error);
        }
        $stmt->bind_param("ii", $user_id, $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if (!$result) {
            die("Query failed: " . $this->conn->error);
        }
        
        $recipes = [];
        while ($row = $result->fetch_assoc()) {
            $recipes[] = $row;
        }
        return $recipes;
    }

    /**
     * Export shopping list to user's shopping list table
     */
    public function exportToShoppingList($user_id, $start_date, $end_date, $list_name = null) {
        $shopping_list = $this->generateShoppingList($user_id, $start_date, $end_date);
        $shopping_list = $this->checkAgainstPantry($user_id, $shopping_list);
        
        if (empty($list_name)) {
            $list_name = 'Shopping List for ' . $start_date . ' to ' . $end_date;
        }
        
        // Begin transaction
        $this->conn->begin_transaction();
        
        try {
            // Create a new shopping list
            $query = "INSERT INTO shopping_lists (user_id, name, created_at) 
                     VALUES (?, ?, NOW())";
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                throw new Exception("Statement preparation failed: " . $this->conn->error);
            }
            $stmt->bind_param("is", $user_id, $list_name);
            $stmt->execute();
            
            $shopping_list_id = $this->conn->insert_id;
            
            // Add items to the shopping list
            foreach ($shopping_list as $item) {
                // Only add items that aren't fully in pantry
                if (!$item['have_enough']) {
                    $query = "INSERT INTO shopping_list_items (shopping_list_id, ingredient_id, quantity, measurement_id) 
                             VALUES (?, ?, ?, ?)";
                    $stmt = $this->conn->prepare($query);
                    if (!$stmt) {
                        throw new Exception("Statement preparation failed: " . $this->conn->error);
                    }
                    $stmt->bind_param("iidi", $shopping_list_id, $item['ingredient_id'], $item['needed_quantity'], $item['measurement_id']);
                    $stmt->execute();
                }
            }
            
            // Commit transaction
            $this->conn->commit();
            return $shopping_list_id;
            
        } catch (Exception $e) {
            // Rollback transaction on error
            $this->conn->rollback();
            return false;
        }
    }

    /**
     * Get ingredient list for specific meal
     */
    public function getMealIngredients($meal_plan_id) {
        $query = "SELECT i.name AS ingredient_name, 
                 (ri.quantity * mp.serving_size / r.serving_size) AS adjusted_quantity,
                 m.name AS measurement_name
                 FROM meal_plans mp
                 JOIN recipes r ON mp.recipe_id = r.recipe_id
                 JOIN recipe_ingredients ri ON r.recipe_id = ri.recipe_id
                 JOIN ingredients i ON ri.ingredient_id = i.ingredient_id
                 JOIN measurements m ON ri.measurement_id = m.measurement_id
                 WHERE mp.meal_plan_id = ?
                 ORDER BY i.name";
                         
        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            die("Statement preparation failed: " . $this->conn->error);
        }
        $stmt->bind_param("i", $meal_plan_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if (!$result) {
            die("Query failed: " . $this->conn->error);
        }
        
        $ingredients = [];
        while ($row = $result->fetch_assoc()) {
            $ingredients[] = $row;
        }
        return $ingredients;
    }
}
?>