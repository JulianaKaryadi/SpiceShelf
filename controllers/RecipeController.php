<?php
// controllers/RecipeController.php
require_once __DIR__ . '/../models/Recipe.php';

class RecipeController {
    private $conn;

    // Constructor to initialize the database connection
    public function __construct($conn) {
        $this->conn = $conn;
    }

    /**
     * Display the Recipe Chat AI interface
     */
    public function recipeChat() {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?action=login');
            exit;
        }

        // Include the chat view
        include('views/recipe_chat.php');
    }

    public function add() {
        $recipe = new Recipe($this->conn);
    
        // Fetch categories, ingredients, measurements from the database
        $categories = $recipe->getCategories();
        $ingredients = $recipe->getIngredients();
        $measurements = $recipe->getMeasurements();
    
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            
            if (!isset($_SESSION['user_id'])) {
                echo "You must be logged in to submit a recipe.";
                exit;
            }
    
            // Form data
            $user_id = $_SESSION['user_id'];
            $recipe_name = $_POST['recipe_name'];
            $description = $_POST['description'];
            $category_ids = $_POST['category_ids']; // Array of category IDs
            $serving_size = $_POST['serving_size'];
            $prep_time = $_POST['prep_time'];
            $cook_time = $_POST['cook_time'];
            $ingredients_data = $_POST['ingredients'];
            $steps = $_POST['steps'];
            $public = isset($_POST['public']) ? 1 : 0;
    
            // Handle image upload
            $image = null;
            if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                $image_path = 'uploads/recipes/' . basename($_FILES['image']['name']);
                move_uploaded_file($_FILES['image']['tmp_name'], $image_path);
                $image = $image_path;
            }
    
            // Add the recipe
            $recipe_id = $recipe->addRecipe($user_id, $recipe_name, $description, $prep_time, $cook_time, $image, $steps, $serving_size, $public, $category_ids);
    
            // Add ingredients to the recipe
            if (!empty($ingredients_data)) {
                foreach ($ingredients_data as $ingredient) {
                    $recipe->addRecipeIngredient($recipe_id, $ingredient);
                }
            }
    
            // Redirect after submission
            header("Location: index.php?action=add_recipe");
            exit;
        }
    
        require_once 'views/add_recipe.php';
    }           

    public function displayHome() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: index.php?action=login");
            exit;
        }
    
        $user_id = $_SESSION['user_id'];
        $recipeModel = new Recipe($this->conn);
    
        // Fetch user's recipes
        $userRecipes = $recipeModel->getUserRecipes($user_id);
    
        // Fetch user's favorite recipes
        $favoriteRecipes = $recipeModel->getFavoriteRecipes($user_id);
    
        // Include the home view
        include('views/home.php');
    }    
    
    public function refresh_home_sections() {
        if (!isset($_SESSION['user_id'])) {
            echo json_encode([
                'success' => false,
                'error' => 'Not logged in'
            ]);
            exit;
        }
    
        $user_id = $_SESSION['user_id'];
        $recipeModel = new Recipe($this->conn);
    
        // Fetch updated user recipes and favorite recipes
        $userRecipes = $recipeModel->getUserRecipes($user_id);
        $favoriteRecipes = $recipeModel->getFavoriteRecipes($user_id);
    
        // Generate HTML for user recipes
        ob_start();
        if (!empty($userRecipes)) {
            foreach ($userRecipes as $recipe) {
                ?>
                <div class="recipe-card">
                    <a href="index.php?action=view_recipe&recipe_id=<?= $recipe['recipe_id']; ?>" style="text-decoration:none;">
                        <img src="<?= htmlspecialchars($recipe['image'] ?? 'assets/images/default_recipe.jpg'); ?>" 
                            alt="<?= htmlspecialchars($recipe['recipe_name']); ?>">
                        <div class="recipe-card-content">
                            <h3><?= htmlspecialchars($recipe['recipe_name']); ?></h3>
                            <p><?= htmlspecialchars($recipe['description']); ?></p>
                        </div>
                    </a>
                    
                    <form action="index.php?action=favorite" method="POST" class="favorite-form">
                        <input type="hidden" name="recipe_id" value="<?= $recipe['recipe_id']; ?>">
                        <button type="submit" class="favorite-btn" data-recipe-id="<?= $recipe['recipe_id']; ?>">
                            <?= $recipeModel->isFavorited($user_id, $recipe['recipe_id']) ? "💔 Unfavorite" : "❤️ Favorite"; ?>
                        </button>
                    </form>
                </div>
                <?php
            }
        } else {
            echo '<p>You have not created any recipes yet.</p>';
        }
        $userRecipesHtml = ob_get_clean();
    
        // Generate HTML for favorite recipes
        ob_start();
        if (!empty($favoriteRecipes)) {
            foreach ($favoriteRecipes as $recipe) {
                ?>
                <div class="recipe-card">
                    <a href="index.php?action=view_recipe&recipe_id=<?= $recipe['recipe_id']; ?>" style="text-decoration:none;">
                        <img src="<?= htmlspecialchars($recipe['image'] ?? 'assets/images/default_recipe.jpg'); ?>" 
                            alt="<?= htmlspecialchars($recipe['recipe_name']); ?>">
                        <div class="recipe-card-content">
                            <h3><?= htmlspecialchars($recipe['recipe_name']); ?></h3>
                            <p><?= htmlspecialchars($recipe['description']); ?></p>
                        </div>
                    </a>
                    
                    <form action="index.php?action=favorite" method="POST" class="favorite-form">
                        <input type="hidden" name="recipe_id" value="<?= $recipe['recipe_id']; ?>">
                        <button type="submit" class="favorite-btn" data-recipe-id="<?= $recipe['recipe_id']; ?>">
                            <?= $recipeModel->isFavorited($user_id, $recipe['recipe_id']) ? "💔 Unfavorite" : "❤️ Favorite"; ?>
                        </button>
                    </form>
                </div>
                <?php
            }
        } else {
            echo '<p>You have not favorited any recipes yet.</p>';
        }
        $favoriteRecipesHtml = ob_get_clean();
    
        echo json_encode([
            'success' => true,
            'userRecipesHtml' => $userRecipesHtml,
            'favoriteRecipesHtml' => $favoriteRecipesHtml
        ]);
        exit;
    }

    public function favorite() {
        if (!isset($_SESSION['user_id'])) {
            // Return JSON error if not logged in
            echo json_encode([
                'success' => false,
                'error' => 'Not logged in'
            ]);
            exit;
        }
    
        $user_id = $_SESSION['user_id'];
        $recipe_id = $_POST['recipe_id'];
        $recipeModel = new Recipe($this->conn);
    
        // Check if recipe is currently favorited
        $query = "SELECT * FROM favorites WHERE user_id = ? AND recipe_id = ?";
        if ($stmt = $this->conn->prepare($query)) {
            $stmt->bind_param("ii", $user_id, $recipe_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $isFavorited = false;
    
            if ($result->num_rows > 0) {
                // Remove from favorites
                $delete_query = "DELETE FROM favorites WHERE user_id = ? AND recipe_id = ?";
                if ($delete_stmt = $this->conn->prepare($delete_query)) {
                    $delete_stmt->bind_param("ii", $user_id, $recipe_id);
                    $delete_stmt->execute();
                    $delete_stmt->close();
                }
                $isFavorited = false;
            } else {
                // Add to favorites
                $insert_query = "INSERT INTO favorites (user_id, recipe_id, created_at) VALUES (?, ?, NOW())";
                if ($insert_stmt = $this->conn->prepare($insert_query)) {
                    $insert_stmt->bind_param("ii", $user_id, $recipe_id);
                    $insert_stmt->execute();
                    $insert_stmt->close();
                }
                $isFavorited = true;
            }
    
            $stmt->close();
    
            // Return JSON response
            echo json_encode([
                'success' => true,
                'isFavorited' => $isFavorited
            ]);
            exit;
        }
    }                  
    
    public function viewRecipe() {
        if (!isset($_GET['recipe_id'])) {
            header("Location: index.php?action=home");
            exit;
        }
    
        $recipe_id = $_GET['recipe_id'];
        $recipeModel = new Recipe($this->conn);
    
        // Fetch the recipe details
        $recipe = $recipeModel->getRecipeById($recipe_id);
    
        // Check if the recipe belongs to the logged-in user
        $isOwner = isset($_SESSION['user_id']) && $recipe['user_id'] == $_SESSION['user_id'];
    
        // Fetch ingredients for the recipe
        $ingredients = $recipeModel->getRecipeIngredients($recipe_id);
        
        // Fetch comments for the recipe
        $comments = $recipeModel->getRecipeComments($recipe_id);
    
        // Include the view
        include('views/recipe_details.php');
    }    
    
    public function deleteRecipe() {
        if (!isset($_POST['recipe_id']) || !isset($_SESSION['user_id'])) {
            header("Location: index.php?action=home");
            exit;
        }
    
        $recipe_id = $_POST['recipe_id'];
        $user_id = $_SESSION['user_id'];
    
        $recipeModel = new Recipe($this->conn);
    
        // Check if the recipe belongs to the logged-in user
        $recipe = $recipeModel->getRecipeById($recipe_id);
        if ($recipe['user_id'] !== $user_id) {
            echo "You are not authorized to delete this recipe.";
            exit;
        }
    
        // Delete the recipe
        $recipeModel->deleteRecipeById($recipe_id);
    
        // Redirect back to the home page
        header("Location: index.php?action=home");
        exit;
    }

    public function editRecipe() {
        if (!isset($_GET['recipe_id']) || !isset($_SESSION['user_id'])) {
            header("Location: index.php?action=home");
            exit;
        }
    
        $recipe_id = (int)$_GET['recipe_id']; // Typecast to ensure it's an integer
        $user_id = (int)$_SESSION['user_id']; // Typecast for consistency
    
        $recipeModel = new Recipe($this->conn);
    
        // Fetch recipe details
        $recipe = $recipeModel->getRecipeById($recipe_id);
    
        // Check if the recipe exists and belongs to the user
        if (!$recipe || $recipe['user_id'] !== $user_id) {
            echo "You are not authorized to edit this recipe.";
            exit;
        }
    
        // Fetch ingredients for the recipe
        $ingredients = $recipeModel->getRecipeIngredients($recipe_id);
    
        // Fetch categories assigned to the recipe
        $recipeCategories = $recipeModel->getRecipeCategories($recipe_id);
    
        // Fetch all predefined data
        $predefinedIngredients = $recipeModel->getIngredients();
        $measurements = $recipeModel->getMeasurements();
        $categories = $recipeModel->getCategories();
    
        // Include the edit_recipe view
        include('views/edit_recipe.php');
    }         

    public function updateRecipe() {
        if (!isset($_POST['recipe_id']) || !isset($_SESSION['user_id'])) {
            header("Location: index.php?action=home");
            exit;
        }
    
        $recipe_id = $_POST['recipe_id'];
        $user_id = $_SESSION['user_id'];
    
        $recipeModel = new Recipe($this->conn);
    
        // Fetch the existing recipe
        $recipe = $recipeModel->getRecipeById($recipe_id);
        if ($recipe['user_id'] !== $user_id) {
            echo "You are not authorized to update this recipe.";
            exit;
        }
    
        // Gather form data
        $recipe_name = $_POST['recipe_name'] ?? '';
        $description = $_POST['description'] ?? '';
        $prep_time = $_POST['prep_time'] ?? 0;
        $cook_time = $_POST['cook_time'] ?? 0;
        $serving_size = $_POST['serving_size'] ?? 0;
        $steps = $_POST['steps'] ?? '';
        $ingredients = $_POST['ingredients'] ?? [];
        $category_ids = $_POST['category_ids'] ?? [];
        $public = isset($_POST['public']) && $_POST['public'] === '1' ? '1' : '0';
    
        // Handle image upload
        $image = $recipe['image'];
        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            $image_path = 'uploads/recipes/' . basename($_FILES['image']['name']);
            move_uploaded_file($_FILES['image']['tmp_name'], $image_path);
            $image = $image_path;
        }
    
        // Update the recipe
        $recipeModel->updateRecipeById($recipe_id, $recipe_name, $description, $prep_time, $cook_time, $serving_size, $steps, $image, $public);
    
        // Update the categories
        $recipeModel->updateRecipeCategories($recipe_id, $category_ids);
    
        // Update the ingredients
        $existingIngredients = $recipeModel->getRecipeIngredients($recipe_id);
    
        foreach ($ingredients as $ingredient) {
            $ingredient_id = $ingredient['ingredient_id'] ?? null;
            $quantity = $ingredient['quantity'] ?? null;
            $measurement_id = $ingredient['measurement_id'] ?? null;
    
            if ($ingredient_id && $quantity && $measurement_id) {
                // Check if the ingredient already exists
                $found = false;
                foreach ($existingIngredients as $existing) {
                    if ($existing['ingredient_id'] == $ingredient_id) {
                        $recipeModel->updateRecipeIngredient($existing['recipe_ingredient_id'], $ingredient_id, $quantity, $measurement_id);
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    // Add new ingredient if not found in existing ingredients
                    $recipeModel->addRecipeIngredient($recipe_id, $ingredient);
                }
            }
        }
    
        // Redirect back to the recipe details page
        header("Location: index.php?action=view_recipe&recipe_id=$recipe_id");
        exit;
    }     
    
    public function browseRecipes() {
        $user_id = $_SESSION['user_id'] ?? null;
        $recipeModel = new Recipe($this->conn);
    
        // Get search parameters
        $search = $_GET['search'] ?? '';
        $ingredients = $_GET['ingredients'] ?? [];
        $categories = $_GET['categories'] ?? [];
        $disableFilters = isset($_GET['disable_filters']) && $_GET['disable_filters'] == '1';
    
        // Get all available categories and predefined ingredients
        $predefinedIngredients = $recipeModel->getIngredients();
        $categoriesList = $recipeModel->getCategories()->fetch_all(MYSQLI_ASSOC);
    
        // If user is logged in, get their preferences and allergies
        $userPreferences = [];
        $userAllergies = [];
        if ($user_id && !$disableFilters) {
            $userPreferences = $recipeModel->getUserDietaryPreferences($user_id);
            $userAllergies = $recipeModel->getUserAllergies($user_id);
        }
    
        // Fetch recipes with filters
        $recipes = $recipeModel->getFilteredPublicRecipes($search, $ingredients, $categories, $user_id, $disableFilters);
    
        // Pass data to view
        include('views/browse_recipes.php');
    }                    
    
    public function favoriteRecipe() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: index.php?action=login");
            exit;
        }
    
        $user_id = $_SESSION['user_id'];
        $recipe_id = $_POST['recipe_id'];
    
        $recipeModel = new Recipe($this->conn);
        $recipeModel->toggleFavorite($user_id, $recipe_id);
    
        header("Location: index.php?action=browse_recipes");
        exit;
    }
    
    /**
     * Get the default measurement for an ingredient
     */
    public function getDefaultMeasurement() {
        // Check if this is an AJAX request
        if (!isset($_GET['ingredient_id'])) {
            echo json_encode(['success' => false, 'message' => 'Missing ingredient ID']);
            return;
        }
        
        $ingredient_id = (int)$_GET['ingredient_id'];
        
        // Get default measurement
        $recipe = new Recipe($this->conn);
        $measurement = $recipe->getDefaultMeasurement($ingredient_id);
        
        if ($measurement) {
            echo json_encode([
                'success' => true,
                'measurement_id' => $measurement['measurement_id'],
                'measurement_name' => $measurement['name']
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'No default measurement found'
            ]);
        }
    }

    /**
     * Add a comment to a recipe
     */
    public function addComment() {
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'User not logged in']);
            exit;
        }
        
        // Check if required data is present
        if (!isset($_POST['recipe_id']) || !isset($_POST['comment']) || empty(trim($_POST['comment']))) {
            echo json_encode(['success' => false, 'message' => 'Missing required data']);
            exit;
        }
        
        $user_id = $_SESSION['user_id'];
        $recipe_id = (int)$_POST['recipe_id'];
        $comment = trim($_POST['comment']);
        
        $recipeModel = new Recipe($this->conn);
        
        // Add the comment
        $success = $recipeModel->addComment($recipe_id, $user_id, $comment);
        
        // If this is an AJAX request
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            if ($success) {
                // Get the full comment data including user info
                $comments = $recipeModel->getRecipeComments($recipe_id);
                $newComment = $comments[0]; // The newest comment should be at the top
                
                echo json_encode([
                    'success' => true,
                    'comment' => $newComment,
                    'username' => $_SESSION['username'],
                    'created_at' => date('M j, Y g:i A')
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to add comment']);
            }
            exit;
        }
        
        // For non-AJAX requests, redirect back to the recipe page
        header("Location: index.php?action=view_recipe&recipe_id=$recipe_id");
        exit;
    }

    /**
     * Delete a comment
     */
    public function deleteComment() {
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'User not logged in']);
            exit;
        }
        
        // Check if required data is present
        if (!isset($_POST['comment_id']) || !isset($_POST['recipe_id'])) {
            echo json_encode(['success' => false, 'message' => 'Missing required data']);
            exit;
        }
        
        $user_id = $_SESSION['user_id'];
        $comment_id = (int)$_POST['comment_id'];
        $recipe_id = (int)$_POST['recipe_id'];
        
        $recipeModel = new Recipe($this->conn);
        
        // Delete the comment
        $success = $recipeModel->deleteComment($comment_id, $user_id);
        
        // If this is an AJAX request
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            echo json_encode(['success' => $success]);
            exit;
        }
        
        // For non-AJAX requests, redirect back to the recipe page
        header("Location: index.php?action=view_recipe&recipe_id=$recipe_id");
        exit;
    }
}
?>