<?php
// controllers/MealPlanController.php

require_once 'models/MealPlan.php';
require_once 'models/Recipe.php';
require_once 'models/User.php';

class MealPlanController {
    private $conn;
    private $mealPlan;
    private $recipe;
    private $user;

    public function __construct($conn) {
        $this->conn = $conn;
        $this->mealPlan = new MealPlan($conn);
        $this->recipe = new Recipe($conn);
        $this->user = new User($conn);
        
        // Check if there's a subaction to process
        if (isset($_GET['subaction'])) {
            $subaction = $_GET['subaction'];
            
            switch ($subaction) {
                case 'addMeal':
                    $this->addMeal();
                    break;
                case 'updateMeal':
                    $this->updateMeal();
                    break;
                case 'removeMeal':
                    $this->removeMeal();
                    break;
                case 'generateShoppingList':
                    $this->generateShoppingList();
                    break;
                case 'searchRecipes':
                    $this->searchRecipes();
                    break;
                case 'getMeal':
                    $this->getMeal();
                    break;
                case 'exportPDF':
                    $this->exportPDF();
                    break;
            }
        }
    }

    // Display meal plan view (matching router's expected method name)
    public function showMealPlans() {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?action=login');
            exit;
        }

        $user_id = $_SESSION['user_id'];

        // Get date range (default to current week)
        $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d');
        $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d', strtotime('+6 days', strtotime($start_date)));

        // Get meal plans for date range
        $meal_plans = $this->mealPlan->getUserMealPlan($user_id, $start_date, $end_date);

        // Organize meal plans by date and meal type
        $organized_meal_plans = [];
        $date_range = [];

        // Create array of dates in range
        $current_date = new DateTime($start_date);
        $end_date_obj = new DateTime($end_date);

        while ($current_date <= $end_date_obj) {
            $date_str = $current_date->format('Y-m-d');
            $date_range[] = $date_str;
            $organized_meal_plans[$date_str] = [
                'Breakfast' => [],
                'Lunch' => [],
                'Dinner' => [],
                'Snack' => []
            ];
            $current_date->modify('+1 day');
        }

        // Organize meals into the structure
        foreach ($meal_plans as $meal) {
            $organized_meal_plans[$meal['meal_date']][$meal['meal_type']][] = $meal;
        }

        // Include the view
        include 'views/meal_plans.php';
    }
    
    // For backward compatibility with any code that might call index()
    public function index() {
        $this->showMealPlans();
    }

    // Get a single meal by ID for editing
    public function getMeal() {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'User not logged in']);
            exit;
        }

        $user_id = $_SESSION['user_id'];
        $meal_plan_id = $_GET['meal_plan_id'];

        // Get the meal
        $meal = $this->mealPlan->getMealById($meal_plan_id);

        if ($meal && $meal['user_id'] == $user_id) {
            echo json_encode(['success' => true, 'meal' => $meal]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Meal not found or not authorized']);
        }
        exit;
    }

    // Add meal to plan
    public function addMeal() {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'User not logged in']);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $user_id = $_SESSION['user_id'];
            $meal_date = $_POST['meal_date'];
            $meal_type = $_POST['meal_type'];
            $recipe_id = $_POST['recipe_id'];
            $serving_size = isset($_POST['serving_size']) ? $_POST['serving_size'] : 1;
            $notes = isset($_POST['notes']) ? $_POST['notes'] : null;

            // Check if recipe exists
            $recipe = $this->recipe->getRecipeById($recipe_id);
            if (!$recipe) {
                echo json_encode(['success' => false, 'message' => 'Recipe not found']);
                exit;
            }

            // Add to meal plan
            $meal_plan_id = $this->mealPlan->addToMealPlan($user_id, $meal_date, $meal_type, $recipe_id, $serving_size, $notes);

            if ($meal_plan_id) {
                // Get the added meal with recipe details
                $added_meal = $this->mealPlan->getMealById($meal_plan_id);
                echo json_encode(['success' => true, 'message' => 'Meal added successfully', 'meal' => $added_meal]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to add meal']);
            }
            exit;
        }

        echo json_encode(['success' => false, 'message' => 'Invalid request method']);
        exit;
    }

    // Update meal in plan
    public function updateMeal() {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'User not logged in']);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $user_id = $_SESSION['user_id'];
            $meal_plan_id = $_POST['meal_plan_id'];
            $meal_date = $_POST['meal_date'];
            $meal_type = $_POST['meal_type'];
            $recipe_id = $_POST['recipe_id'];
            $serving_size = isset($_POST['serving_size']) ? $_POST['serving_size'] : 1;
            $notes = isset($_POST['notes']) ? $_POST['notes'] : null;

            // Check if meal belongs to user
            $meal = $this->mealPlan->getMealById($meal_plan_id);
            if (!$meal || $meal['user_id'] != $user_id) {
                echo json_encode(['success' => false, 'message' => 'Meal not found or not authorized']);
                exit;
            }

            // Update meal
            $success = $this->mealPlan->updateMealPlan($meal_plan_id, $meal_date, $meal_type, $recipe_id, $serving_size, $notes);

            if ($success) {
                // Get the updated meal with recipe details
                $updated_meal = $this->mealPlan->getMealById($meal_plan_id);
                echo json_encode(['success' => true, 'message' => 'Meal updated successfully', 'meal' => $updated_meal]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update meal']);
            }
            exit;
        }

        echo json_encode(['success' => false, 'message' => 'Invalid request method']);
        exit;
    }

    // Remove meal from plan
    public function removeMeal() {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'User not logged in']);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $user_id = $_SESSION['user_id'];
            $meal_plan_id = $_POST['meal_plan_id'];

            // Check if meal belongs to user
            $meal = $this->mealPlan->getMealById($meal_plan_id);
            if (!$meal || $meal['user_id'] != $user_id) {
                echo json_encode(['success' => false, 'message' => 'Meal not found or not authorized']);
                exit;
            }

            // Remove meal
            $success = $this->mealPlan->removeMeal($meal_plan_id);

            if ($success) {
                echo json_encode(['success' => true, 'message' => 'Meal removed successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to remove meal']);
            }
            exit;
        }

        echo json_encode(['success' => false, 'message' => 'Invalid request method']);
        exit;
    }

    // Generate shopping list
    public function generateShoppingList() {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?action=login');
            exit;
        }

        $user_id = $_SESSION['user_id'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $start_date = $_POST['start_date'];
            $end_date = $_POST['end_date'];
            $list_name = isset($_POST['list_name']) ? $_POST['list_name'] : null;

            // Generate shopping list
            $shopping_list = $this->mealPlan->generateShoppingList($user_id, $start_date, $end_date);
            $shopping_list = $this->mealPlan->checkAgainstPantry($user_id, $shopping_list);

            // If export is requested
            if (isset($_POST['export']) && $_POST['export'] == 1) {
                $shopping_list_id = $this->mealPlan->exportToShoppingList($user_id, $start_date, $end_date, $list_name);
                if ($shopping_list_id) {
                    header('Location: index.php?action=shopping_list&id=' . $shopping_list_id);
                    exit;
                } else {
                    $_SESSION['error'] = 'Failed to export shopping list';
                    header('Location: index.php?action=meal_plans');
                    exit;
                }
            }

            // Return shopping list data for display
            echo json_encode(['success' => true, 'shopping_list' => $shopping_list]);
            exit;
        }

        // If not POST, redirect to meal plans page
        header('Location: index.php?action=meal_plans');
        exit;
    }

    // Search recipes to add to meal plan
    public function searchRecipes() {
        // Make sure session is started
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'User not logged in']);
            exit;
        }

        $user_id = $_SESSION['user_id'];
        $search_term = isset($_GET['term']) ? $_GET['term'] : '';
        
        // Debug
        error_log("Searching for recipes with term: " . $search_term);
        
        // Get recipes matching search term
        $recipes = $this->recipe->searchRecipes($search_term, $user_id);
        
        // Debug
        error_log("Found " . count($recipes) . " recipes");
        if (count($recipes) > 0) {
            error_log("First recipe: " . json_encode($recipes[0]));
        }
        
        echo json_encode(['success' => true, 'recipes' => $recipes]);
        exit;
    }

    // Display meal plan statistics
    public function statistics() {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?action=login');
            exit;
        }

        $user_id = $_SESSION['user_id'];

        // Get meal plan statistics
        $stats = $this->mealPlan->getMealPlanStats($user_id);
        $frequent_recipes = $this->mealPlan->getFrequentRecipes($user_id);

        // Include the view
        include 'views/meal_plan_statistics.php';
    }

/**
 * Export meal plan as PDF using mPDF
 */
public function exportPDF() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: index.php?action=login');
        exit;
    }
    
    require_once __DIR__ . '/../vendor/autoload.php'; // Adjust path as needed for your mPDF installation
    
    $user_id = $_SESSION['user_id'];
    
    // Get date range from request
    $start_date = $_GET['start_date'] ?? date('Y-m-d');
    $end_date = $_GET['end_date'] ?? date('Y-m-d', strtotime('+6 days'));
    
    // Ensure dates are in correct order
    if ($start_date > $end_date) {
        $temp = $start_date;
        $start_date = $end_date;
        $end_date = $temp;
    }
    
    // Get meal plan data
    $mealPlan = new MealPlan($this->conn);
    $meal_plans = $mealPlan->getUserMealPlan($user_id, $start_date, $end_date);
    
    // Generate date range
    $date_range = [];
    $current_date = new DateTime($start_date);
    $end = new DateTime($end_date);
    $end->modify('+1 day');
    
    while ($current_date < $end) {
        $date_range[] = $current_date->format('Y-m-d');
        $current_date->modify('+1 day');
    }
    
    // Organize meal plans by date and meal type
    $organized_meal_plans = [];
    foreach ($date_range as $date) {
        $organized_meal_plans[$date] = [
            'Breakfast' => [],
            'Lunch' => [],
            'Dinner' => [],
            'Snack' => []
        ];
    }
    
    foreach ($meal_plans as $meal) {
        $organized_meal_plans[$meal['meal_date']][$meal['meal_type']][] = $meal;
    }
    
    // Calculate summary statistics
    $totalMeals = 0;
    $mealCounts = ['Breakfast' => 0, 'Lunch' => 0, 'Dinner' => 0, 'Snack' => 0];
    $uniqueRecipes = [];
    
    foreach ($organized_meal_plans as $meals) {
        foreach ($meals as $mealType => $mealList) {
            $count = count($mealList);
            $totalMeals += $count;
            $mealCounts[$mealType] += $count;
            
            // Track unique recipes
            foreach ($mealList as $meal) {
                $uniqueRecipes[$meal['recipe_id']] = $meal['recipe_name'];
            }
        }
    }
    
    // Create new mPDF instance
    $mpdf = new \Mpdf\Mpdf([
        'mode' => 'utf-8',
        'format' => 'A4',
        'margin_left' => 15,
        'margin_right' => 15,
        'margin_top' => 16,
        'margin_bottom' => 16,
        'margin_header' => 9,
        'margin_footer' => 9,
        'tempDir' => __DIR__ . '/../tmp' // Make sure this directory exists and is writable
    ]);
    
    // Set document properties
    $mpdf->SetTitle('Meal Plan - ' . date('M d, Y', strtotime($start_date)) . ' to ' . date('M d, Y', strtotime($end_date)));
    $mpdf->SetAuthor('SpiceShelf');
    $mpdf->SetCreator('SpiceShelf Meal Planning System');
    
    // Generate HTML content
    $html = '
    <style>
        @page {
            margin-header: 5mm;
            margin-footer: 5mm;
        }
        
        body {
            font-family: "DejaVu Sans", sans-serif;
            font-size: 11pt;
            color: #333;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 3px solid #34554a;
        }
        
        h1 {
            color: #34554a;
            font-size: 28pt;
            margin: 0 0 10px 0;
            font-weight: normal;
        }
        
        .subtitle {
            color: #666;
            font-size: 10pt;
            margin: 5px 0;
        }
        
        .date-range {
            color: #e8a87c;
            font-size: 14pt;
            font-weight: bold;
            margin: 10px 0;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        th {
            background-color: #34554a;
            color: white;
            padding: 10px;
            text-align: left;
            font-weight: bold;
            font-size: 10pt;
        }
        
        td {
            padding: 10px;
            border: 1px solid #ddd;
            vertical-align: top;
            font-size: 9pt;
        }
        
        .date-cell {
            background-color: #f5efe6;
            font-weight: bold;
            color: #34554a;
            width: 15%;
        }
        
        .meal-cell {
            width: 21.25%;
        }
        
        .today {
            background-color: #fff9e6;
        }
        
        .meal-item {
            margin-bottom: 8px;
            padding: 6px;
            background-color: #f9f9f9;
            border-left: 3px solid #e8a87c;
            border-radius: 2px;
        }
        
        .meal-name {
            font-weight: bold;
            color: #34554a;
            font-size: 10pt;
            margin-bottom: 3px;
        }
        
        .meal-details {
            font-size: 8pt;
            color: #666;
            line-height: 1.4;
        }
        
        .empty-meal {
            color: #999;
            font-style: italic;
            font-size: 9pt;
        }
        
        .summary {
            background-color: #f5efe6;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
            page-break-inside: avoid;
        }
        
        .summary h2 {
            color: #34554a;
            font-size: 16pt;
            margin-top: 0;
            margin-bottom: 15px;
        }
        
        .summary-grid {
            width: 100%;
        }
        
        .summary-row {
            margin-bottom: 8px;
        }
        
        .summary-label {
            display: inline-block;
            width: 200px;
            font-weight: bold;
            color: #666;
            font-size: 10pt;
        }
        
        .summary-value {
            display: inline-block;
            font-size: 14pt;
            color: #34554a;
            font-weight: bold;
        }
        
        .footer {
            text-align: center;
            font-size: 8pt;
            color: #999;
            margin-top: 30px;
        }
        
        .recipe-list {
            margin-top: 20px;
            padding: 15px;
            background-color: #f9f9f9;
            border-radius: 5px;
        }
        
        .recipe-list h3 {
            color: #34554a;
            font-size: 14pt;
            margin-top: 0;
            margin-bottom: 10px;
        }
        
        .recipe-list ul {
            margin: 0;
            padding-left: 20px;
        }
        
        .recipe-list li {
            margin-bottom: 5px;
            color: #666;
        }
    </style>
    
    <div class="header">
        <h1>SpiceShelf Meal Plan</h1>
        <div class="subtitle">Prepared for: <strong>' . htmlspecialchars($_SESSION['username']) . '</strong></div>
        <div class="date-range">' . date('F d, Y', strtotime($start_date)) . ' - ' . date('F d, Y', strtotime($end_date)) . '</div>
        <div class="subtitle">Generated on ' . date('F d, Y \a\t g:i A') . '</div>
    </div>';
    
    // Main meal plan table
    $html .= '
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Breakfast</th>
                <th>Lunch</th>
                <th>Dinner</th>
                <th>Snack</th>
            </tr>
        </thead>
        <tbody>';
    
    foreach ($organized_meal_plans as $date => $meals) {
        $isToday = ($date == date('Y-m-d')) ? ' class="today"' : '';
        $dayOfWeek = date('D', strtotime($date));
        $dateFormatted = date('M d', strtotime($date));
        
        $html .= '<tr' . $isToday . '>';
        $html .= '<td class="date-cell">' . $dayOfWeek . '<br>' . $dateFormatted . '</td>';
        
        foreach (['Breakfast', 'Lunch', 'Dinner', 'Snack'] as $mealType) {
            $html .= '<td class="meal-cell">';
            
            if (empty($meals[$mealType])) {
                $html .= '<span class="empty-meal">No ' . strtolower($mealType) . ' planned</span>';
            } else {
                foreach ($meals[$mealType] as $meal) {
                    $html .= '<div class="meal-item">';
                    $html .= '<div class="meal-name">' . htmlspecialchars($meal['recipe_name']) . '</div>';
                    $html .= '<div class="meal-details">';
                    $html .= 'Servings: ' . $meal['serving_size'];
                    
                    if ($meal['prep_time'] || $meal['cook_time']) {
                        $timeInfo = [];
                        if ($meal['prep_time']) $timeInfo[] = 'Prep: ' . $meal['prep_time'] . 'min';
                        if ($meal['cook_time']) $timeInfo[] = 'Cook: ' . $meal['cook_time'] . 'min';
                        $html .= '<br>' . implode(' | ', $timeInfo);
                    }
                    
                    if (!empty($meal['notes'])) {
                        $html .= '<br><em>Note: ' . htmlspecialchars($meal['notes']) . '</em>';
                    }
                    
                    $html .= '</div></div>';
                }
            }
            
            $html .= '</td>';
        }
        
        $html .= '</tr>';
    }
    
    $html .= '</tbody></table>';
    
    // Summary section
    $html .= '
    <div class="summary">
        <h2>Meal Plan Summary</h2>
        <div class="summary-grid">
            <div class="summary-row">
                <span class="summary-label">Total Meals Planned:</span>
                <span class="summary-value">' . $totalMeals . '</span>
            </div>
            <div class="summary-row">
                <span class="summary-label">Days in Plan:</span>
                <span class="summary-value">' . count($date_range) . '</span>
            </div>
            <div class="summary-row">
                <span class="summary-label">Unique Recipes:</span>
                <span class="summary-value">' . count($uniqueRecipes) . '</span>
            </div>
            <div class="summary-row">
                <span class="summary-label">Breakfasts:</span>
                <span class="summary-value">' . $mealCounts['Breakfast'] . '</span>
            </div>
            <div class="summary-row">
                <span class="summary-label">Lunches:</span>
                <span class="summary-value">' . $mealCounts['Lunch'] . '</span>
            </div>
            <div class="summary-row">
                <span class="summary-label">Dinners:</span>
                <span class="summary-value">' . $mealCounts['Dinner'] . '</span>
            </div>
            <div class="summary-row">
                <span class="summary-label">Snacks:</span>
                <span class="summary-value">' . $mealCounts['Snack'] . '</span>
            </div>
        </div>
    </div>';
    
    // Add list of unique recipes if there are any
    if (count($uniqueRecipes) > 0) {
        $html .= '
        <div class="recipe-list">
            <h3>Recipes in This Meal Plan</h3>
            <ul>';
        
        foreach ($uniqueRecipes as $id => $name) {
            $html .= '<li>' . htmlspecialchars($name) . '</li>';
        }
        
        $html .= '
            </ul>
        </div>';
    }
    
    // Footer
    $html .= '
    <div class="footer">
        <p>This meal plan was generated by SpiceShelf</p>
    </div>';
    
    // Write HTML to PDF
    $mpdf->WriteHTML($html);
    
    // Generate filename
    $filename = 'SpiceShelf_Meal_Plan_' . date('Y-m-d', strtotime($start_date)) . '_to_' . date('Y-m-d', strtotime($end_date)) . '.pdf';
    
    // Output PDF for download
    $mpdf->Output($filename, \Mpdf\Output\Destination::DOWNLOAD);
    exit;
}

}