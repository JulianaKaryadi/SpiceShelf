<?php
// controllers/PantryController.php

// Include necessary models
require_once __DIR__ . '/../models/Pantry.php';
require_once __DIR__ . '/../models/Ingredients.php';
require_once __DIR__ . '/../models/Measurements.php';

class PantryController {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // Display the pantry items for the logged-in user
    public function showPantryItems() {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            header('Location: login.php');
            exit;
        }

        $user_id = $_SESSION['user_id'];

        // Fetch pantry items using the Pantry model
        $pantry = new Pantry($this->conn);
        $items = $pantry->getPantryItems($user_id);

        // Pass data to the view
        $this->loadView('pantry', ['items' => $items]);
    }

    // Include the view and pass data to it
    private function loadView($viewName, $data = []) {
        // Extract data into variables to use in the view
        extract($data);
        include_once __DIR__ . '/../views/' . $viewName . '.php';
    } 
       
    // Add a new pantry item
    public function add() {  
        if (!isset($_SESSION['user_id'])) {
            header('Location: login.php');
            exit;
        }
        
        $user_id = $_SESSION['user_id'];
    
        // If the form is submitted, process the input
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ingredient_id = $_POST['ingredient_id'];
            $quantity = $_POST['quantity'];
            $measurement_id = $_POST['measurement_id'];
            $expiration_date = $_POST['expiration_date'];
    
            // Create an instance of Pantry and add the item
            $pantry = new Pantry($this->conn);
            $pantry->addPantryItem($user_id, $ingredient_id, $quantity, $measurement_id, $expiration_date);
    
            // After adding the item, redirect to the pantry page
            header('Location: index.php?action=pantry');
            exit;
        }
    
        // Fetch ingredients and measurements for the form
        $ingredients = new Ingredients($this->conn);
        $ingredient_list = $ingredients->getIngredients();
    
        $measurements = new Measurements($this->conn);
        $measurement_list = $measurements->getMeasurements();
    
        // Include the view to display the add form
        require_once 'C:/xampp/htdocs/SpiceShelf/views/add_pantry_item.php';
    }    

    // Update a pantry item's quantity
    public function update() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?action=login');
            exit;
        }

        $pantry = new Pantry($this->conn);

        // If the form is submitted, process the update
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $pantry_id = $_POST['pantry_id'];
            $quantity = $_POST['quantity'];
            $measurement_id = $_POST['measurement_id'];
            $expiration_date = $_POST['expiration_date'];
            $ingredient_id = $_POST['ingredient_id'] ?? null; // Get ingredient_id from form

            // Update the pantry item with all details
            $pantry->updatePantryItem($pantry_id, $quantity, $expiration_date, $measurement_id, $ingredient_id);

            // Redirect back to the pantry page
            header('Location: index.php?action=pantry');
            exit;
        }

        // Fetch the current pantry item
        $pantry_id = $_GET['id'] ?? null;
        if (!$pantry_id) {
            header('Location: index.php?action=pantry');
            exit;
        }

        $item = $pantry->getPantryItemById($pantry_id);

        // Fetch all available ingredients and measurements for the dropdown
        $ingredients = new Ingredients($this->conn);
        $ingredient_list = $ingredients->getIngredients();

        $measurements = new Measurements($this->conn);
        $measurement_list = $measurements->getMeasurements();

        // Load the update form
        $this->loadView('update_pantry_item', [
            'item' => $item,
            'ingredients' => $ingredient_list,
            'measurements' => $measurement_list
        ]);
    }      
    
    public function delete() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?action=login');
            exit;
        }
    
        if (isset($_GET['id'])) {
            $pantry_id = $_GET['id'];
    
            // Delete the pantry item
            $pantry = new Pantry($this->conn);
            $pantry->deletePantryItem($pantry_id);
    
            // Redirect back to the pantry page
            header('Location: index.php?action=pantry');
            exit;
        }
    }    

    /**
     * Handle bulk deletion of pantry items
     */
    public function bulkDelete() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?action=login');
            exit;
        }

        // Check if this is a POST request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?action=pantry');
            exit;
        }

        // Get the selected pantry items
        $selectedItems = isset($_POST['selected_items']) ? $_POST['selected_items'] : [];

        if (!empty($selectedItems)) {
            $pantry = new Pantry($this->conn);
            
            // Delete the selected pantry items
            $deletedCount = $pantry->deletePantryItems($selectedItems);
            
            // Set a success message
            $_SESSION['flash_message'] = "$deletedCount items deleted successfully";
        } else {
            // No items selected
            $_SESSION['flash_message'] = "No items selected for deletion";
        }

        // Redirect back to the pantry page
        header('Location: index.php?action=pantry');
        exit;
    }
}
?>
