<?php
// api/save_ai_recipe.php
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

// Check if the request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'error' => 'Invalid request method'
    ]);
    exit;
}

// Get recipe data from POST request
$recipe_text = $_POST['recipe_text'] ?? '';
if (empty($recipe_text)) {
    echo json_encode([
        'success' => false,
        'error' => 'Recipe text is required'
    ]);
    exit;
}

// Include database connection and models
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Recipe.php';

// Create DB connection
$db = new Database();
$conn = $db->getConnection();

// Parse recipe text to extract information
// This is a simple implementation - you might want to enhance it based on your AI output format
function parseRecipeText($text) {
    // Default values
    $data = [
        'recipe_name' => 'AI Generated Recipe',
        'description' => '',
        'prep_time' => 15,
        'cook_time' => 30,
        'serving_size' => 4,
        'steps' => $text, // Default to storing the whole text as steps
    ];
    
    // Try to extract recipe name (first line or "Recipe name:" section)
    if (preg_match('/^(.+?)(\n|$)/', $text, $matches)) {
        $data['recipe_name'] = trim($matches[1]);
    }
    
    // Try to extract description (usually second paragraph)
    if (preg_match('/\n\n(.+?)\n\n/s', $text, $matches)) {
        $data['description'] = trim($matches[1]);
    }
    
    // Try to extract cook time if it exists
    if (preg_match('/cook(?:ing)?\s*time:?\s*(\d+)/i', $text, $matches)) {
        $data['cook_time'] = (int)$matches[1];
    }
    
    // Try to extract prep time if it exists
    if (preg_match('/prep(?:aration)?\s*time:?\s*(\d+)/i', $text, $matches)) {
        $data['prep_time'] = (int)$matches[1];
    }
    
    // Try to extract serving size if it exists
    if (preg_match('/serv(?:ing|es)\s*(?:size|for)?:?\s*(\d+)/i', $text, $matches)) {
        $data['serving_size'] = (int)$matches[1];
    }
    
    return $data;
}

// Get user ID from session
$user_id = $_SESSION['user_id'];

// Parse recipe text
$recipe_data = parseRecipeText($recipe_text);

try {
    // Create a new recipe model
    $recipe = new Recipe($conn);
    
    // Default category for AI-generated recipes (you might need to check if this exists in your DB)
    $default_category_ids = [1]; // Assuming 1 is a valid category ID
    
    // Add the recipe to the database
    $recipe_id = $recipe->addRecipe(
        $user_id,
        $recipe_data['recipe_name'],
        $recipe_data['description'],
        $recipe_data['prep_time'],
        $recipe_data['cook_time'],
        null, // No image for AI-generated recipes
        $recipe_data['steps'],
        $recipe_data['serving_size'],
        0, // Not public by default
        $default_category_ids
    );
    
    // Return success response with recipe ID
    echo json_encode([
        'success' => true,
        'recipe_id' => $recipe_id,
        'message' => 'Recipe saved successfully!'
    ]);
    
} catch (Exception $e) {
    // Return error response
    echo json_encode([
        'success' => false,
        'error' => 'Failed to save recipe: ' . $e->getMessage()
    ]);
}
?>