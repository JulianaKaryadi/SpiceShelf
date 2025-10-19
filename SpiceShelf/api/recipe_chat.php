<?php
// api/recipe_chat.php - Enhanced with correct database schema
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

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'error' => 'Only POST requests allowed'
    ]);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['message'])) {
    echo json_encode([
        'success' => false,
        'error' => 'No message provided'
    ]);
    exit;
}

$user_message = trim($input['message']);
$conversation_history = isset($input['conversation_history']) ? $input['conversation_history'] : [];

if (empty($user_message)) {
    echo json_encode([
        'success' => false,
        'error' => 'Message cannot be empty'
    ]);
    exit;
}

// Rate limiting - prevent spam
$user_id = $_SESSION['user_id'];
$rate_limit_key = "chat_rate_limit_$user_id";

if (isset($_SESSION[$rate_limit_key])) {
    $requests = $_SESSION[$rate_limit_key];
    $now = time();
    
    $requests = array_filter($requests, function($timestamp) use ($now) {
        return ($now - $timestamp) < 3600;
    });
    
    if (count($requests) >= 30) {
        echo json_encode([
            'success' => false,
            'error' => 'Rate limit exceeded. Please wait before sending more messages.'
        ]);
        exit;
    }
    
    $requests[] = $now;
    $_SESSION[$rate_limit_key] = $requests;
} else {
    $_SESSION[$rate_limit_key] = [time()];
}

// Include necessary files
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/openaiAPI.php';

try {
    // Initialize database connection
    $database = new Database();
    $conn = $database->getConnection();
    
    // Get user profile context from database
    $user_context = getUserProfileContext($conn, $user_id);
    
    // Get OpenAI API configuration
    $openai_config = require_once __DIR__ . '/../config/openai_config.php';

    // Initialize OpenAI API
    $openai = new OpenAIAPI($openai_config['api_key'], $openai_config['model']);

    // Generate response using enhanced method with user profile context
    $response = $openai->generateChatResponse($user_message, $conversation_history, $user_context, $conn);

    if ($response['success']) {
        error_log("Recipe Chat - User: $user_id, Message: " . substr($user_message, 0, 100) . "...");
        
        echo json_encode([
            'success' => true,
            'response' => $response['response'],
            'usage' => $response['usage'] ?? null
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => $response['error']
        ]);
    }

    $database->closeConnection();

} catch (Exception $e) {
    error_log("Recipe Chat Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'An unexpected error occurred. Please try again later.'
    ]);
}

/**
 * Get user profile context using correct database schema
 */
function getUserProfileContext($conn, $user_id) {
    $context = [
        'dietary_preferences' => [],
        'allergies' => [],
        'favorite_recipes' => [],
        'recipe_categories' => []
    ];

    try {
        // Get user dietary preferences with proper JOIN
        $stmt = $conn->prepare("
            SELECT dp.preference_name 
            FROM user_dietary_preferences udp 
            JOIN dietary_preferences dp ON udp.preference_id = dp.preference_id 
            WHERE udp.user_id = ?
        ");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $context['dietary_preferences'][] = $row['preference_name'];
        }
        $stmt->close();

        // Get user allergies with proper JOIN
        $stmt = $conn->prepare("
            SELECT a.allergy_name 
            FROM user_allergies ua 
            JOIN allergies a ON ua.allergy_id = a.allergy_id 
            WHERE ua.user_id = ?
        ");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            // Skip "None" entries as they're not real allergies
            if ($row['allergy_name'] !== 'None') {
                $context['allergies'][] = $row['allergy_name'];
            }
        }
        $stmt->close();

        // Get user's favorite recipes (using favorites table)
        $stmt = $conn->prepare("
            SELECT r.recipe_name, r.description, rc.category_name
            FROM favorites f
            JOIN recipes r ON f.recipe_id = r.recipe_id
            LEFT JOIN recipe_category_mapping rcm ON r.recipe_id = rcm.recipe_id
            LEFT JOIN recipe_categories rc ON rcm.category_id = rc.category_id
            WHERE f.user_id = ?
            ORDER BY f.created_at DESC
            LIMIT 10
        ");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $context['favorite_recipes'][] = [
                'name' => $row['recipe_name'],
                'description' => $row['description'],
                'category' => $row['category_name']
            ];
        }
        $stmt->close();

        // Get available recipe categories
        $stmt = $conn->prepare("SELECT category_name FROM recipe_categories ORDER BY category_name");
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $context['recipe_categories'][] = $row['category_name'];
        }
        $stmt->close();

    } catch (Exception $e) {
        error_log("Error getting user profile context: " . $e->getMessage());
    }

    return $context;
}

/**
 * Search for recipes in database based on keywords (for chatbot reference)
 */
function searchRecipesForReference($conn, $keywords, $limit = 3) {
    $recipes = [];
    
    try {
        $search_term = "%" . $keywords . "%";
        $stmt = $conn->prepare("
            SELECT r.recipe_name, r.description, r.prep_time, r.cook_time, rc.category_name
            FROM recipes r 
            LEFT JOIN recipe_category_mapping rcm ON r.recipe_id = rcm.recipe_id
            LEFT JOIN recipe_categories rc ON rcm.category_id = rc.category_id
            WHERE r.public = 1 AND (r.recipe_name LIKE ? OR r.description LIKE ? OR rc.category_name LIKE ?)
            ORDER BY r.recipe_name
            LIMIT ?
        ");
        $stmt->bind_param("sssi", $search_term, $search_term, $search_term, $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $recipes[] = [
                'name' => $row['recipe_name'],
                'description' => $row['description'],
                'prep_time' => $row['prep_time'],
                'cook_time' => $row['cook_time'],
                'category' => $row['category_name']
            ];
        }
        $stmt->close();
        
    } catch (Exception $e) {
        error_log("Error searching recipes for reference: " . $e->getMessage());
    }
    
    return $recipes;
}
?>