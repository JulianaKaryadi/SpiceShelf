<?php
// Start session at the beginning
session_start();

// CRITICAL DEBUG LOG
error_log('========= NEW REQUEST =========');
error_log('GET params: ' . json_encode($_GET));
error_log('SESSION data: ' . json_encode($_SESSION));

require_once 'config/database.php';
require_once 'controllers/UserController.php';
require_once 'controllers/RecipeController.php';
require_once 'controllers/PantryController.php';
require_once 'Router.php';
require_once 'controllers/MealPlanController.php';
require_once 'controllers/ShoppingListController.php';
require_once 'controllers/AdminController.php';

// Create database connection
$database = new Database();
$conn = $database->getConnection();

// Initialize the Router
$router = new Router();

// Create instances of controllers
$userController = new UserController($conn);
$recipeController = new RecipeController($conn);
$pantryController = new PantryController($conn);
$mealPlanController = new MealPlanController($conn);
$shoppingListController = new ShoppingListController($conn);
$adminController = new AdminController($conn);

// Define user routes
$router->addRoute('login', [$userController, 'login']);
$router->addRoute('register', [$userController, 'register']);
$router->addRoute('logout', function () {
    session_unset();
    session_destroy();
    header('Location: index.php?action=login'); 
    exit;
});

// Add regular user routes
$router->addRoute('profile', [$userController, 'profile']);
$router->addRoute('add_recipe', [$recipeController, 'add']);
$router->addRoute('recipe_chat', [$recipeController, 'recipeChat']); // New chat route
$router->addRoute('pantry', [$pantryController, 'showPantryItems']);
$router->addRoute('add', [$pantryController, 'add']);
$router->addRoute('update', [$pantryController, 'update']);
$router->addRoute('delete', [$pantryController, 'delete']);
$router->addRoute('favorite', [$recipeController, 'favorite']);
$router->addRoute('search_recipes', [$recipeController, 'searchRecipes']);
$router->addRoute('home', [$recipeController, 'displayHome']);
$router->addRoute('view_recipe', [$recipeController, 'viewRecipe']);
$router->addRoute('delete_recipe', [$recipeController, 'deleteRecipe']);
$router->addRoute('edit_recipe', [$recipeController, 'editRecipe']);
$router->addRoute('update_recipe', [$recipeController, 'updateRecipe']);
$router->addRoute('browse_recipes', [$recipeController, 'browseRecipes']);
$router->addRoute('meal_plans', [$mealPlanController, 'showMealPlans']);
$router->addRoute('shopping_list', [$shoppingListController, 'showLists']);
$router->addRoute('refresh_home_sections', [$recipeController, 'refresh_home_sections']);
$router->addRoute('getDefaultMeasurement', [$recipeController, 'getDefaultMeasurement']);
$router->addRoute('add_comment', [$recipeController, 'addComment']);
$router->addRoute('delete_comment', [$recipeController, 'deleteComment']); 

// Add special routes with subactions
$router->addRoute('pantry/bulkDelete', [$pantryController, 'bulkDelete']);

// Add meal plan subaction routes
$router->addRoute('meal_plans/exportPDF', [$mealPlanController, 'exportPDF']);
$router->addRoute('meal_plans/searchRecipes', [$mealPlanController, 'searchRecipes']);
$router->addRoute('meal_plans/addMeal', [$mealPlanController, 'addMeal']);
$router->addRoute('meal_plans/getMeal', [$mealPlanController, 'getMeal']);
$router->addRoute('meal_plans/updateMeal', [$mealPlanController, 'updateMeal']);
$router->addRoute('meal_plans/removeMeal', [$mealPlanController, 'removeMeal']);
$router->addRoute('meal_plans/generateShoppingList', [$mealPlanController, 'generateShoppingList']);

// Add admin routes
$router->addRoute('adminLogin', [$adminController, 'adminLogin']);
$router->addRoute('adminLogout', [$adminController, 'adminLogout']);
$router->addRoute('adminDashboard', [$adminController, 'adminDashboard']);
$router->addRoute('adminUsers', [$adminController, 'adminUsers']);
$router->addRoute('adminViewUser', [$adminController, 'adminViewUser']);
$router->addRoute('adminRecipes', [$adminController, 'adminRecipes']);
$router->addRoute('adminViewRecipe', [$adminController, 'adminViewRecipe']);
$router->addRoute('adminIngredients', [$adminController, 'adminIngredients']);
$router->addRoute('adminAddIngredient', [$adminController, 'adminAddIngredient']);
$router->addRoute('adminEditIngredient', [$adminController, 'adminEditIngredient']);
$router->addRoute('adminDeleteIngredient', [$adminController, 'adminDeleteIngredient']);
$router->addRoute('adminCategories', [$adminController, 'adminCategories']);
$router->addRoute('adminAddCategory', [$adminController, 'adminAddCategory']);
$router->addRoute('adminEditCategory', [$adminController, 'adminEditCategory']);
$router->addRoute('adminDeleteCategory', [$adminController, 'adminDeleteCategory']);
$router->addRoute('adminDeleteUser', [$adminController, 'adminDeleteUser']);
$router->addRoute('adminDeleteRecipe', [$adminController, 'adminDeleteRecipe']);
$router->addRoute('adminUserReports', [$adminController, 'adminUserReports']);
$router->addRoute('adminRecipeReports', [$adminController, 'adminRecipeReports']);
$router->addRoute('adminAds', [$adminController, 'adminAds']);
$router->addRoute('adminAdReports', [$adminController, 'adminAdReports']);
$router->addRoute('adminExportAdReport', [$adminController, 'adminExportAdReport']);
$router->addRoute('adminExportUserReport', [$adminController, 'adminExportUserReport']);
$router->addRoute('adminExportRecipeReport', [$adminController, 'adminExportRecipeReport']);
$router->addRoute('adminSendInactivityNotice', [$adminController, 'adminSendInactivityNotice']);

// Ad tracking routes -  without authentication
$router->addRoute('logAdImpression', [$adminController, 'logAdImpression']);
$router->addRoute('adClick', [$adminController, 'adClick']);

// Get the action parameter and check for subactions
$action = isset($_GET['action']) ? $_GET['action'] : 'home';
$subaction = isset($_GET['subaction']) ? $_GET['subaction'] : '';

// If we have a subaction, modify the action string to include it
if ($subaction) {
    $fullAction = $action . '/' . $subaction;
    // Check if this combined action exists first
    if ($router->routeExists($fullAction)) {
        $action = $fullAction;
    }
}

// Define lists for authentication checks
$adminActions = ['adminLogin', 'adminLogout', 'adminDashboard', 'adminUsers', 'adminViewUser', 'adminRecipes', 
                'adminViewRecipe', 'adminIngredients', 'adminAddIngredient', 'adminEditIngredient', 
                'adminDeleteIngredient', 'adminCategories', 'adminAddCategory', 'adminEditCategory', 
                'adminDeleteCategory', 'adminDeleteUser', 'adminDeleteRecipe', 'adminUserReports', 
                'adminRecipeReports', 'adminAds', 'adminAdReports', 'adminExportAdReport'];

$publicActions = ['login', 'register', 'adminLogin', 'logAdImpression', 'adClick'];

$userProtectedActions = ['home', 'profile', 'add_recipe', 'recipe_chat', 'pantry', 'add', 'update', 'delete', 'favorite', 
                       'view_recipe', 'delete_recipe', 'edit_recipe', 'update_recipe', 'meal_plans', 
                       'shopping_list', 'refresh_home_sections', 'search_recipes', 'browse_recipes',
                       'pantry/bulkDelete', 'add_comment', 'delete_comment',
                       'meal_plans/exportPDF', 'meal_plans/searchRecipes', 'meal_plans/addMeal',
                       'meal_plans/getMeal', 'meal_plans/updateMeal', 'meal_plans/removeMeal',
                       'meal_plans/generateShoppingList'];

// Check if we're on an admin page
$isAdminPage = in_array($action, $adminActions);

error_log("Action: $action, Is Admin Page: " . ($isAdminPage ? 'Yes' : 'No'));

// AUTHENTICATION CHECKS
if ($isAdminPage) {
    // This is an admin action
    if ($action !== 'adminLogin' && !isset($_SESSION['admin_id'])) {
        error_log("Admin access without login, redirecting to admin login");
        header('Location: index.php?action=adminLogin');
        exit;
    }
} else if (in_array($action, $userProtectedActions) && !isset($_SESSION['user_id'])) {
    // This is a protected user action and user is not logged in
    error_log("Protected user action without login, redirecting to user login");
    header('Location: index.php?action=login');
    exit;
}

// Check if route exists
if (!$router->routeExists($action)) {
    error_log("Route does not exist: $action");
    // Display a 404 page
    echo "<h1>404 - Page Not Found</h1>";
    echo "<p>The page you were looking for could not be found.</p>";
    echo "<p><a href='index.php?action=home'>Return to home</a></p>";
    exit;
}

// Log before handling the request
error_log("About to handle request for action: $action");

// Handle the request
$router->handleRequest($action);

// Close the database connection
$database->closeConnection();
?>