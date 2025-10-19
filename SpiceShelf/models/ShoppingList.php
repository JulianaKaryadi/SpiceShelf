<?php
// models/ShoppingList.php

class ShoppingList {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    /**
     * Get all shopping lists for a user
     * 
     * @param int $user_id User ID
     * @return array Array of shopping lists
     */
    public function getUserShoppingLists($user_id) {
        $query = "SELECT sl.shopping_list_id, sl.name, sl.created_at, 
                  COUNT(sli.id) as item_count, 
                  SUM(CASE WHEN sli.purchased = 1 THEN 1 ELSE 0 END) as purchased_count
                  FROM shopping_lists sl
                  LEFT JOIN shopping_list_items sli ON sl.shopping_list_id = sli.shopping_list_id
                  WHERE sl.user_id = ?
                  GROUP BY sl.shopping_list_id
                  ORDER BY sl.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            die("Statement preparation failed: " . $this->conn->error);
        }
        
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $lists = [];
        while ($row = $result->fetch_assoc()) {
            $lists[] = $row;
        }
        
        return $lists;
    }

    /**
     * Get a specific shopping list by ID
     * 
     * @param int $shopping_list_id Shopping list ID
     * @return array|bool Shopping list data if found, false otherwise
     */
    public function getShoppingListById($shopping_list_id) {
        $query = "SELECT * FROM shopping_lists WHERE shopping_list_id = ?";
        
        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            die("Statement preparation failed: " . $this->conn->error);
        }
        
        $stmt->bind_param("i", $shopping_list_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_assoc();
    }

    /**
     * Get items in a shopping list
     * 
     * @param int $shopping_list_id Shopping list ID
     * @return array Array of items in the shopping list
     */
    public function getShoppingListItems($shopping_list_id) {
        $query = "SELECT sli.id, sli.ingredient_id, i.name as ingredient_name, 
                  sli.quantity, m.name as measurement_name, sli.purchased
                  FROM shopping_list_items sli
                  JOIN ingredients i ON sli.ingredient_id = i.ingredient_id
                  JOIN measurements m ON sli.measurement_id = m.measurement_id
                  WHERE sli.shopping_list_id = ?
                  ORDER BY sli.purchased ASC, i.name ASC";
        
        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            die("Statement preparation failed: " . $this->conn->error);
        }
        
        $stmt->bind_param("i", $shopping_list_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $items = [];
        while ($row = $result->fetch_assoc()) {
            $items[] = $row;
        }
        
        return $items;
    }

    /**
     * Create a new shopping list
     * 
     * @param int $user_id User ID
     * @param string $name Name of the shopping list
     * @return int|bool Shopping list ID if successful, false otherwise
     */
    public function createShoppingList($user_id, $name) {
        $query = "INSERT INTO shopping_lists (user_id, name, created_at) 
                 VALUES (?, ?, NOW())";
        
        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            die("Statement preparation failed: " . $this->conn->error);
        }
        
        $stmt->bind_param("is", $user_id, $name);
        
        if ($stmt->execute()) {
            return $this->conn->insert_id;
        } else {
            return false;
        }
    }

    /**
     * Add an item to a shopping list
     * 
     * @param int $shopping_list_id Shopping list ID
     * @param int $ingredient_id Ingredient ID
     * @param float $quantity Quantity
     * @param int $measurement_id Measurement ID
     * @return int|bool Item ID if successful, false otherwise
     */
    public function addShoppingListItem($shopping_list_id, $ingredient_id, $quantity, $measurement_id) {
        // Check if item already exists in list
        $query = "SELECT id FROM shopping_list_items 
                 WHERE shopping_list_id = ? AND ingredient_id = ? AND measurement_id = ?";
        
        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            die("Statement preparation failed: " . $this->conn->error);
        }
        
        $stmt->bind_param("iii", $shopping_list_id, $ingredient_id, $measurement_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            // Item exists, update quantity
            $id = $row['id'];
            $query = "UPDATE shopping_list_items 
                     SET quantity = quantity + ? 
                     WHERE id = ?";
            
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                die("Statement preparation failed: " . $this->conn->error);
            }
            
            $stmt->bind_param("di", $quantity, $id);
            
            if ($stmt->execute()) {
                return $id;
            } else {
                return false;
            }
        } else {
            // Item doesn't exist, insert new
            $query = "INSERT INTO shopping_list_items (shopping_list_id, ingredient_id, quantity, measurement_id, purchased) 
                     VALUES (?, ?, ?, ?, 0)";
            
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                die("Statement preparation failed: " . $this->conn->error);
            }
            
            $stmt->bind_param("iidi", $shopping_list_id, $ingredient_id, $quantity, $measurement_id);
            
            if ($stmt->execute()) {
                return $this->conn->insert_id;
            } else {
                return false;
            }
        }
    }

    /**
     * Update a shopping list item
     * 
     * @param int $item_id Item ID
     * @param float $quantity New quantity
     * @param int $measurement_id New measurement ID
     * @param bool $purchased Purchase status
     * @return bool True if successful, false otherwise
     */
    public function updateShoppingListItem($item_id, $quantity, $measurement_id = null, $purchased = null) {
        // Build query based on provided parameters
        $query = "UPDATE shopping_list_items SET quantity = ?";
        $types = "d";
        $params = [$quantity];
        
        if ($measurement_id !== null) {
            $query .= ", measurement_id = ?";
            $types .= "i";
            $params[] = $measurement_id;
        }
        
        if ($purchased !== null) {
            $query .= ", purchased = ?";
            $types .= "i";
            $params[] = $purchased ? 1 : 0;
        }
        
        $query .= " WHERE id = ?";
        $types .= "i";
        $params[] = $item_id;
        
        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            die("Statement preparation failed: " . $this->conn->error);
        }
        
        $stmt->bind_param($types, ...$params);
        
        return $stmt->execute();
    }

    /**
     * Toggle the purchased status of a shopping list item
     * 
     * @param int $item_id Item ID
     * @return bool True if successful, false otherwise
     */
    public function toggleItemPurchased($item_id) {
        $query = "UPDATE shopping_list_items 
                 SET purchased = NOT purchased 
                 WHERE id = ?";
        
        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            die("Statement preparation failed: " . $this->conn->error);
        }
        
        $stmt->bind_param("i", $item_id);
        
        return $stmt->execute();
    }

    /**
     * Mark all items in a shopping list as purchased
     * 
     * @param int $shopping_list_id Shopping list ID
     * @return bool True if successful, false otherwise
     */
    public function markAllAsPurchased($shopping_list_id) {
        $query = "UPDATE shopping_list_items 
                 SET purchased = 1 
                 WHERE shopping_list_id = ?";
        
        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            die("Statement preparation failed: " . $this->conn->error);
        }
        
        $stmt->bind_param("i", $shopping_list_id);
        
        return $stmt->execute();
    }

    /**
     * Remove an item from a shopping list
     * 
     * @param int $item_id Item ID
     * @return bool True if successful, false otherwise
     */
    public function removeShoppingListItem($item_id) {
        $query = "DELETE FROM shopping_list_items WHERE id = ?";
        
        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            die("Statement preparation failed: " . $this->conn->error);
        }
        
        $stmt->bind_param("i", $item_id);
        
        return $stmt->execute();
    }

    /**
     * Delete a shopping list and all its items
     * 
     * @param int $shopping_list_id Shopping list ID
     * @return bool True if successful, false otherwise
     */
    public function deleteShoppingList($shopping_list_id) {
        // First delete all items in the shopping list
        $query = "DELETE FROM shopping_list_items WHERE shopping_list_id = ?";
        
        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            die("Statement preparation failed: " . $this->conn->error);
        }
        
        $stmt->bind_param("i", $shopping_list_id);
        $stmt->execute();
        
        // Then delete the shopping list itself
        $query = "DELETE FROM shopping_lists WHERE shopping_list_id = ?";
        
        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            die("Statement preparation failed: " . $this->conn->error);
        }
        
        $stmt->bind_param("i", $shopping_list_id);
        
        return $stmt->execute();
    }

    /**
     * Add purchased items to user's pantry
     * 
     * @param int $shopping_list_id Shopping list ID
     * @param int $user_id User ID
     * @param int $expiration_days Number of days until expiration (default 14)
     * @return array|bool Array of added items if successful, false otherwise
     */
    public function addToPantry($shopping_list_id, $user_id, $expiration_days = 14) {
        // Get all purchased items
        $query = "SELECT sli.ingredient_id, sli.quantity, sli.measurement_id 
                 FROM shopping_list_items sli
                 WHERE sli.shopping_list_id = ? AND sli.purchased = 1";
        
        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            die("Statement preparation failed: " . $this->conn->error);
        }
        
        $stmt->bind_param("i", $shopping_list_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $addedItems = [];
        $expiration_date = date('Y-m-d', strtotime("+{$expiration_days} days"));
        
        // Begin transaction
        $this->conn->begin_transaction();
        
        try {
            while ($item = $result->fetch_assoc()) {
                // Check if item already exists in pantry
                $query = "SELECT pantry_id, quantity FROM pantry 
                         WHERE user_id = ? AND ingredient_id = ? AND measurement_id = ?";
                
                $stmt = $this->conn->prepare($query);
                if (!$stmt) {
                    throw new Exception("Statement preparation failed: " . $this->conn->error);
                }
                
                $stmt->bind_param("iii", $user_id, $item['ingredient_id'], $item['measurement_id']);
                $stmt->execute();
                $pantry_result = $stmt->get_result();
                
                if ($pantry_item = $pantry_result->fetch_assoc()) {
                    // Update existing pantry item
                    $new_quantity = $pantry_item['quantity'] + $item['quantity'];
                    $query = "UPDATE pantry 
                             SET quantity = ?, expiration_date = ? 
                             WHERE pantry_id = ?";
                    
                    $stmt = $this->conn->prepare($query);
                    if (!$stmt) {
                        throw new Exception("Statement preparation failed: " . $this->conn->error);
                    }
                    
                    $stmt->bind_param("dsi", $new_quantity, $expiration_date, $pantry_item['pantry_id']);
                    $stmt->execute();
                    
                    $addedItems[] = [
                        'pantry_id' => $pantry_item['pantry_id'],
                        'ingredient_id' => $item['ingredient_id'],
                        'quantity' => $item['quantity'],
                        'updated' => true
                    ];
                } else {
                    // Add new pantry item
                    $query = "INSERT INTO pantry (user_id, ingredient_id, quantity, measurement_id, added_at, expiration_date) 
                             VALUES (?, ?, ?, ?, NOW(), ?)";
                    
                    $stmt = $this->conn->prepare($query);
                    if (!$stmt) {
                        throw new Exception("Statement preparation failed: " . $this->conn->error);
                    }
                    
                    $stmt->bind_param("iidis", $user_id, $item['ingredient_id'], $item['quantity'], $item['measurement_id'], $expiration_date);
                    $stmt->execute();
                    
                    $pantry_id = $this->conn->insert_id;
                    
                    $addedItems[] = [
                        'pantry_id' => $pantry_id,
                        'ingredient_id' => $item['ingredient_id'],
                        'quantity' => $item['quantity'],
                        'updated' => false
                    ];
                }
            }
            
            // Commit transaction
            $this->conn->commit();
            return $addedItems;
            
        } catch (Exception $e) {
            // Rollback transaction on error
            $this->conn->rollback();
            error_log("Error adding to pantry: " . $e->getMessage());
            return false;
        }
    }
}