<?php
// api/generate_recipe.php
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'error' => 'User not logged in'
    ]);
    exit;
}

// Include database connection
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Pantry.php';
require_once __DIR__ . '/../models/Recipe.php';
require_once __DIR__ . '/openaiAPI.php';

// Create DB connection
$db = new Database();
$conn = $db->getConnection();

// Get user's pantry items
$user_id = $_SESSION['user_id'];
$pantry = new Pantry($conn);

// Handle both GET and POST requests
$request_data = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if ($input) {
        $request_data = $input;
    }
} else {
    $request_data = $_GET;
}

// Get parameters from request
$meal_type = isset($request_data['meal_type']) ? $request_data['meal_type'] : '';
$ingredient_selection = isset($request_data['ingredient_selection']) ? $request_data['ingredient_selection'] : 'all';
$selected_ingredients_data = isset($request_data['selected_ingredients']) ? $request_data['selected_ingredients'] : [];

// Validate meal type if provided
$valid_meal_types = ['breakfast', 'lunch', 'dinner', 'snack', 'appetizer', 'dessert', 'side_dish', 'beverage'];
if ($meal_type && !in_array($meal_type, $valid_meal_types)) {
    $meal_type = ''; // Reset if invalid
}

// Get pantry items based on selection mode
if ($ingredient_selection === 'selected' && !empty($selected_ingredients_data)) {
    // Use selected ingredients provided by the client
    $pantry_items = $selected_ingredients_data;
} else {
    // Get all pantry items from database
    $pantry_items = $pantry->getPantryItems($user_id);
}

// Filter out expired items for food safety
$valid_pantry_items = array_filter($pantry_items, function($item) {
    return $item['days_until_expiration'] >= 0; // Only include items that haven't expired
});

// If no valid pantry items, return error
if (empty($valid_pantry_items)) {
    // Check if we had items but they were all expired
    if (!empty($pantry_items)) {
        echo json_encode([
            'success' => false,
            'error' => 'All selected ingredients have expired. Please choose fresh ingredients.',
            'expired_count' => count($pantry_items)
        ]);
    } else {
        $error_message = ($ingredient_selection === 'selected') 
            ? 'No ingredients selected for recipe generation' 
            : 'No ingredients in pantry';
        echo json_encode([
            'success' => false,
            'error' => $error_message
        ]);
    }
    exit;
}

// Get user preferences and allergies if available
$recipe_model = new Recipe($conn);
$preferences = [];
$allergies = [];

try {
    $preferences = array_column($recipe_model->getUserDietaryPreferences($user_id), 'preference_name');
    $allergies = array_column($recipe_model->getUserAllergies($user_id), 'allergy_name');
} catch (Exception $e) {
    // Continue even if we can't get preferences or allergies
}

// Get OpenAI API configuration
$openai_config = require_once __DIR__ . '/../config/openai_config.php';

// Initialize OpenAI API
$openai = new OpenAIAPI($openai_config['api_key'], $openai_config['model']);

// Generate recipe using only valid (non-expired) ingredients, including meal type
$response = $openai->generateRecipe($valid_pantry_items, $preferences, $allergies, $meal_type);

// Add information about filtered ingredients if some were expired
if (count($pantry_items) > count($valid_pantry_items)) {
    $expired_count = count($pantry_items) - count($valid_pantry_items);
    if ($response['success']) {
        $warning_message = ($ingredient_selection === 'selected') 
            ? "Note: $expired_count expired ingredients from your selection were excluded for food safety."
            : "Note: $expired_count expired ingredients were excluded from recipe generation for food safety.";
        $response['warning'] = $warning_message;
        $response['expired_count'] = $expired_count;
    }
}

// Add additional metadata to response
if ($response['success']) {
    $response['meal_type'] = $meal_type;
    $response['ingredient_selection'] = $ingredient_selection;
    $response['ingredients_used'] = count($valid_pantry_items);
}

// Return response
echo json_encode($response);
?>