<?php
// controllers/ShoppingListController.php

require_once 'models/ShoppingList.php';
require_once 'models/Ingredients.php';
require_once 'models/Measurements.php';
// Include mPDF library
require_once 'vendor/autoload.php';

class ShoppingListController {
    private $conn;
    private $shoppingList;
    private $ingredient;
    private $measurement;

    public function __construct($conn) {
        // Make sure session is started
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        $this->conn = $conn;
        $this->shoppingList = new ShoppingList($conn);
        $this->ingredient = new Ingredients($conn);
        $this->measurement = new Measurements($conn);
        
        // Check if there's a subaction to process
        if (isset($_GET['subaction'])) {
            $subaction = $_GET['subaction'];
            
            switch ($subaction) {
                case 'addItem':
                    $this->addItem();
                    break;
                case 'updateItem':
                    $this->updateItem();
                    break;
                case 'togglePurchased':
                    $this->togglePurchased();
                    break;
                case 'markAllPurchased':
                    $this->markAllPurchased();
                    break;
                case 'removeItem':
                    $this->removeItem();
                    break;
                case 'deleteList':
                    $this->deleteList();
                    break;
                case 'addToPantry':
                    $this->addToPantry();
                    break;
                case 'createList':
                    $this->createList();
                    break;
                case 'downloadPdf':  // Changed from downloadCsv to downloadPdf
                    $this->downloadPdf();
                    break;
            }
        }
    }

    /**
     * Show all shopping lists for the user
     */
    public function showLists() {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?action=login');
            exit;
        }

        $user_id = $_SESSION['user_id'];
        
        // If a specific list ID is provided, show that list
        if (isset($_GET['id'])) {
            return $this->viewList($_GET['id']);
        }
        
        // Otherwise, show all lists
        $lists = $this->shoppingList->getUserShoppingLists($user_id);
        
        // Include the view
        include 'views/all_shopping_lists.php';
    }
    
    /**
     * View a specific shopping list
     */
    private function viewList($list_id) {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?action=login');
            exit;
        }

        $user_id = $_SESSION['user_id'];
        
        // Get the shopping list details
        $list = $this->shoppingList->getShoppingListById($list_id);
        
        // Check if list exists and belongs to the user
        if (!$list || $list['user_id'] != $user_id) {
            $_SESSION['error'] = 'Shopping list not found';
            header('Location: index.php?action=shopping_list');
            exit;
        }
        
        // Get list items
        $items = $this->shoppingList->getShoppingListItems($list_id);
        
        // Get all ingredients and measurements for the add item form
        // Use the actual method names from your model classes
        $ingredients = $this->ingredient->getIngredients();
        $measurements = $this->measurement->getMeasurements();
        
        // Include the view
        include 'views/view_shopping_list.php';
    }
    
    /**
     * Download the shopping list as PDF using mPDF
     */
    public function downloadPdf() {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?action=login');
            exit;
        }

        $user_id = $_SESSION['user_id'];
        $list_id = $_GET['id'];
        
        // Get the shopping list details
        $list = $this->shoppingList->getShoppingListById($list_id);
        
        // Check if list exists and belongs to the user
        if (!$list || $list['user_id'] != $user_id) {
            $_SESSION['error'] = 'Shopping list not found';
            header('Location: index.php?action=shopping_list');
            exit;
        }
        
        // Get list items
        $items = $this->shoppingList->getShoppingListItems($list_id);
        
        // Create an instance of mPDF
        $mpdf = new \Mpdf\Mpdf([
            'margin_left' => 15,
            'margin_right' => 15,
            'margin_top' => 16,
            'margin_bottom' => 16,
            'margin_header' => 9,
            'margin_footer' => 9
        ]);
        
        // Set document metadata
        $mpdf->SetTitle('Shopping List - ' . htmlspecialchars($list['name']));
        $mpdf->SetAuthor('SpiceShelf');
        
        // Create the PDF content with HTML
        $html = $this->generatePdfHtml($list, $items);
        
        // Write HTML to the PDF
        $mpdf->WriteHTML($html);
        
        // Output the PDF for download
        $mpdf->Output('shopping_list_' . $list_id . '.pdf', \Mpdf\Output\Destination::DOWNLOAD);
        exit;
    }
    
    /**
     * Generate HTML content for the PDF
     */
    private function generatePdfHtml($list, $items) {
        // CSS styles for the PDF
        $css = '
            body {
                font-family: "DejaVu Sans", sans-serif;
                font-size: 12pt;
                line-height: 1.5;
                color: #333;
            }
            h1 {
                font-size: 20pt;
                color: #F29E52;
                margin-bottom: 5px;
            }
            .created-date {
                font-size: 11pt;
                color: #666;
                margin-bottom: 20px;
            }
            table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 20px;
            }
            th {
                background-color: #F29E52;
                color: white;
                font-weight: bold;
                text-align: left;
                padding: 8px;
            }
            td {
                border-bottom: 1px solid #ddd;
                padding: 8px;
            }
            .purchased {
                text-decoration: line-through;
                color: #666;
                background-color: #f8f8f8;
            }
            .status-badge {
                display: inline-block;
                padding: 3px 8px;
                border-radius: 12px;
                font-size: 10pt;
            }
            .status-not-purchased {
                background-color: #ffecb3;
                color: #856404;
            }
            .status-purchased {
                background-color: #d4edda;
                color: #155724;
            }
            .footer {
                font-size: 9pt;
                color: #666;
                text-align: center;
                margin-top: 30px;
            }
        ';
        
        // Start building the HTML
        $html = '
            <style>' . $css . '</style>
            <h1>' . htmlspecialchars($list['name']) . '</h1>
            <div class="created-date">Created on ' . date('M d, Y', strtotime($list['created_at'])) . '</div>
            
            <table>
                <thead>
                    <tr>
                        <th width="45%">Ingredient</th>
                        <th width="25%">Quantity</th>
                        <th width="30%">Status</th>
                    </tr>
                </thead>
                <tbody>';
        
        // Add each item to the table
        foreach ($items as $item) {
            $statusClass = $item['purchased'] ? 'status-purchased' : 'status-not-purchased';
            $statusText = $item['purchased'] ? 'Purchased' : 'Not Purchased';
            $rowClass = $item['purchased'] ? 'class="purchased"' : '';
            
            $html .= '
                <tr ' . $rowClass . '>
                    <td>' . htmlspecialchars($item['ingredient_name']) . '</td>
                    <td>' . number_format($item['quantity'], 2) . ' ' . htmlspecialchars($item['measurement_name']) . '</td>
                    <td><span class="status-badge ' . $statusClass . '">' . $statusText . '</span></td>
                </tr>';
        }
        
        // Close the table and add a footer
        $html .= '
                </tbody>
            </table>
            
            <div class="footer">
                Generated from SpiceShelf on ' . date('Y-m-d H:i:s') . 
            '</div>';
        
        return $html;
    }
    
    // [Rest of the controller methods remain unchanged]
    /**
     * Create a new shopping list
     */
    public function createList() {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'User not logged in']);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $user_id = $_SESSION['user_id'];
            $name = isset($_POST['name']) ? $_POST['name'] : 'Shopping List ' . date('Y-m-d');
            
            $list_id = $this->shoppingList->createShoppingList($user_id, $name);
            
            if ($list_id) {
                echo json_encode(['success' => true, 'list_id' => $list_id]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to create shopping list']);
            }
            exit;
        }
        
        echo json_encode(['success' => false, 'message' => 'Invalid request method']);
        exit;
    }
    
    /**
     * Add an item to a shopping list
     */
    public function addItem() {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'User not logged in']);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $user_id = $_SESSION['user_id'];
            $list_id = $_POST['shopping_list_id'];
            $ingredient_id = $_POST['ingredient_id'];
            $quantity = isset($_POST['quantity']) ? $_POST['quantity'] : 1;
            $measurement_id = $_POST['measurement_id'];
            
            // Check if list belongs to user
            $list = $this->shoppingList->getShoppingListById($list_id);
            if (!$list || $list['user_id'] != $user_id) {
                echo json_encode(['success' => false, 'message' => 'Shopping list not found or not authorized']);
                exit;
            }
            
            $item_id = $this->shoppingList->addShoppingListItem($list_id, $ingredient_id, $quantity, $measurement_id);
            
            if ($item_id) {
                // Get the added item with details
                $items = $this->shoppingList->getShoppingListItems($list_id);
                $added_item = null;
                
                foreach ($items as $item) {
                    if ($item['id'] == $item_id) {
                        $added_item = $item;
                        break;
                    }
                }
                
                echo json_encode(['success' => true, 'message' => 'Item added to shopping list', 'item' => $added_item]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to add item']);
            }
            exit;
        }
        
        echo json_encode(['success' => false, 'message' => 'Invalid request method']);
        exit;
    }
    
    /**
     * Update a shopping list item
     */
    public function updateItem() {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'User not logged in']);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $user_id = $_SESSION['user_id'];
            $item_id = $_POST['item_id'];
            $quantity = isset($_POST['quantity']) ? $_POST['quantity'] : 1;
            $measurement_id = isset($_POST['measurement_id']) ? $_POST['measurement_id'] : null;
            
            // Get the item to check ownership
            $items = $this->shoppingList->getShoppingListItems($_POST['shopping_list_id']);
            $item_exists = false;
            
            foreach ($items as $item) {
                if ($item['id'] == $item_id) {
                    $item_exists = true;
                    break;
                }
            }
            
            if (!$item_exists) {
                echo json_encode(['success' => false, 'message' => 'Item not found']);
                exit;
            }
            
            // Check if list belongs to user
            $list = $this->shoppingList->getShoppingListById($_POST['shopping_list_id']);
            if (!$list || $list['user_id'] != $user_id) {
                echo json_encode(['success' => false, 'message' => 'Shopping list not found or not authorized']);
                exit;
            }
            
            $success = $this->shoppingList->updateShoppingListItem($item_id, $quantity, $measurement_id);
            
            if ($success) {
                echo json_encode(['success' => true, 'message' => 'Item updated']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update item']);
            }
            exit;
        }
        
        echo json_encode(['success' => false, 'message' => 'Invalid request method']);
        exit;
    }
    
    /**
     * Toggle the purchased status of a shopping list item
     */
    public function togglePurchased() {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'User not logged in']);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $user_id = $_SESSION['user_id'];
            $item_id = $_POST['item_id'];
            
            // Check if list belongs to user
            $list = $this->shoppingList->getShoppingListById($_POST['shopping_list_id']);
            if (!$list || $list['user_id'] != $user_id) {
                echo json_encode(['success' => false, 'message' => 'Shopping list not found or not authorized']);
                exit;
            }
            
            $success = $this->shoppingList->toggleItemPurchased($item_id);
            
            if ($success) {
                echo json_encode(['success' => true, 'message' => 'Item status updated']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update item status']);
            }
            exit;
        }
        
        echo json_encode(['success' => false, 'message' => 'Invalid request method']);
        exit;
    }
    
    /**
     * Mark all items in a shopping list as purchased
     */
    public function markAllPurchased() {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'User not logged in']);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $user_id = $_SESSION['user_id'];
            $list_id = $_POST['shopping_list_id'];
            
            // Check if list belongs to user
            $list = $this->shoppingList->getShoppingListById($list_id);
            if (!$list || $list['user_id'] != $user_id) {
                echo json_encode(['success' => false, 'message' => 'Shopping list not found or not authorized']);
                exit;
            }
            
            $success = $this->shoppingList->markAllAsPurchased($list_id);
            
            if ($success) {
                echo json_encode(['success' => true, 'message' => 'All items marked as purchased']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update items']);
            }
            exit;
        }
        
        echo json_encode(['success' => false, 'message' => 'Invalid request method']);
        exit;
    }
    
    /**
     * Remove an item from a shopping list
     */
    public function removeItem() {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'User not logged in']);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $user_id = $_SESSION['user_id'];
            $item_id = $_POST['item_id'];
            
            // Check if list belongs to user
            $list = $this->shoppingList->getShoppingListById($_POST['shopping_list_id']);
            if (!$list || $list['user_id'] != $user_id) {
                echo json_encode(['success' => false, 'message' => 'Shopping list not found or not authorized']);
                exit;
            }
            
            $success = $this->shoppingList->removeShoppingListItem($item_id);
            
            if ($success) {
                echo json_encode(['success' => true, 'message' => 'Item removed from shopping list']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to remove item']);
            }
            exit;
        }
        
        echo json_encode(['success' => false, 'message' => 'Invalid request method']);
        exit;
    }
    
    /**
     * Delete a shopping list
     */
    public function deleteList() {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'User not logged in']);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $user_id = $_SESSION['user_id'];
            $list_id = $_POST['shopping_list_id'];
            
            // Check if list belongs to user
            $list = $this->shoppingList->getShoppingListById($list_id);
            if (!$list || $list['user_id'] != $user_id) {
                echo json_encode(['success' => false, 'message' => 'Shopping list not found or not authorized']);
                exit;
            }
            
            $success = $this->shoppingList->deleteShoppingList($list_id);
            
            if ($success) {
                echo json_encode(['success' => true, 'message' => 'Shopping list deleted']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to delete shopping list']);
            }
            exit;
        }
        
        echo json_encode(['success' => false, 'message' => 'Invalid request method']);
        exit;
    }
    
    /**
     * Add purchased items to the user's pantry
     */
    public function addToPantry() {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'User not logged in']);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $user_id = $_SESSION['user_id'];
            $list_id = $_POST['shopping_list_id'];
            $expiration_days = isset($_POST['expiration_days']) ? intval($_POST['expiration_days']) : 14;
            
            // Check if list belongs to user
            $list = $this->shoppingList->getShoppingListById($list_id);
            if (!$list || $list['user_id'] != $user_id) {
                echo json_encode(['success' => false, 'message' => 'Shopping list not found or not authorized']);
                exit;
            }
            
            $added_items = $this->shoppingList->addToPantry($list_id, $user_id, $expiration_days);
            
            if ($added_items !== false) {
                echo json_encode([
                    'success' => true, 
                    'message' => 'Purchased items added to pantry',
                    'items' => $added_items
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to add items to pantry']);
            }
            exit;
        }
        
        echo json_encode(['success' => false, 'message' => 'Invalid request method']);
        exit;
    }
}