<?php
// models/Pantry.php

class Pantry {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getPantryItems($user_id) {
        $query = "SELECT 
                    p.pantry_id, 
                    i.name AS ingredient, 
                    p.quantity, 
                    m.name AS measurement, 
                    p.expiration_date,
                    DATEDIFF(p.expiration_date, CURRENT_DATE()) as days_until_expiration 
                  FROM pantry p
                  JOIN ingredients i ON p.ingredient_id = i.ingredient_id
                  JOIN measurements m ON p.measurement_id = m.measurement_id
                  WHERE p.user_id = ?
                  ORDER BY days_until_expiration ASC";
        
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
    
        $items = [];
        while ($row = $result->fetch_assoc()) {
            $items[] = $row;
        }
        return $items;
    }                

    // Add a new pantry item
    public function addPantryItem($user_id, $ingredient_id, $quantity, $measurement_id, $expiration_date) {
        $query = "INSERT INTO pantry (user_id, ingredient_id, quantity, measurement_id, expiration_date) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("iiiss", $user_id, $ingredient_id, $quantity, $measurement_id, $expiration_date);
        $stmt->execute();
    }


    // Update a pantry item's quantity, measurement, and ingredient
    public function updatePantryItem($pantry_id, $quantity, $expiration_date, $measurement_id, $ingredient_id = null) {
        // Check if ingredient_id is provided
        if ($ingredient_id) {
            $query = "UPDATE pantry 
                    SET quantity = ?, expiration_date = ?, measurement_id = ?, ingredient_id = ? 
                    WHERE pantry_id = ?";
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                die("Statement preparation failed: " . $this->conn->error);
            }
            $stmt->bind_param("dsiii", $quantity, $expiration_date, $measurement_id, $ingredient_id, $pantry_id);
        } else {
            $query = "UPDATE pantry 
                    SET quantity = ?, expiration_date = ?, measurement_id = ? 
                    WHERE pantry_id = ?";
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                die("Statement preparation failed: " . $this->conn->error);
            }
            $stmt->bind_param("dsii", $quantity, $expiration_date, $measurement_id, $pantry_id);
        }
        $stmt->execute();
        return $stmt->affected_rows;
    }
       

    /**
     * Delete multiple pantry items at once
     * 
     * @param array $pantry_ids Array of pantry item IDs to delete
     * @return int Number of deleted items
     */
    public function deletePantryItems($pantry_ids) {
        if (empty($pantry_ids)) {
            return 0;
        }
        
        // Get the user_id from the first item to ensure security
        $user_id = $_SESSION['user_id'];
        
        // Create placeholders for the IN clause
        $placeholders = implode(',', array_fill(0, count($pantry_ids), '?'));
        
        // Build the types string for bind_param
        $types = str_repeat('i', count($pantry_ids)) . 'i'; // All IDs plus user_id
        
        // Prepare the query with user_id check for security
        $query = "DELETE FROM pantry WHERE pantry_id IN ($placeholders) AND user_id = ?";
        $stmt = $this->conn->prepare($query);
        
        if (!$stmt) {
            die("Statement preparation failed: " . $this->conn->error);
        }
        
        // Create an array with all parameters
        $params = array_merge($pantry_ids, [$user_id]);
        
        // Bind parameters dynamically
        $bind_params = [$types];
        foreach ($params as $key => $value) {
            $bind_params[] = &$params[$key];
        }
        
        call_user_func_array([$stmt, 'bind_param'], $bind_params);
        
        // Execute the statement
        $stmt->execute();
        
        // Return the number of deleted rows
        return $stmt->affected_rows;
    }

    // Delete a pantry item
    public function deletePantryItem($pantry_id) {
        $query = "DELETE FROM pantry WHERE pantry_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $pantry_id);
        $stmt->execute();
        
        return $stmt->affected_rows;
    }

    public function getPantryItemById($pantry_id) {
        $query = "SELECT p.pantry_id, p.ingredient_id, i.name AS ingredient, 
                  p.quantity, p.measurement_id, m.name AS measurement, 
                  p.expiration_date
                  FROM pantry p
                  JOIN ingredients i ON p.ingredient_id = i.ingredient_id
                  JOIN measurements m ON p.measurement_id = m.measurement_id
                  WHERE p.pantry_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $pantry_id);
        $stmt->execute();
        $result = $stmt->get_result();
    
        return $result->fetch_assoc();
    }    
    
}
?>
