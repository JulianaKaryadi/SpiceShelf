<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Recipes</title>
    <link rel="stylesheet" href="assets/css/browse.css">
</head>
<body>

    <!-- Header -->
    <?php include('includes/header.php'); ?>

    <div class="main-content">
    <!-- Filters -->
    <form method="GET" action="index.php?action=browse_recipes" class="filters-section">
        <input type="hidden" name="action" value="browse_recipes">
        
        <!-- Search -->
        <input type="text" name="search" placeholder="Search by name" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">

        <!-- Toggle Preferences -->
        <label>
            <input type="checkbox" name="disable_filters" value="1" <?= isset($_GET['disable_filters']) ? 'checked' : ''; ?>>
            Show all recipes (Disable preferences/allergy filters)
        </label>

        <!-- Ingredients Dropdown -->
        <div class="dropdown-container">
            <button type="button" class="dropdown-button">Select Ingredients</button>
            <div class="dropdown-content">
                <div class="dropdown-search">
                    <input type="text" class="search-input" placeholder="Search ingredients...">
                </div>
                <div class="checkbox-options">
                    <?php foreach ($predefinedIngredients as $ingredient): ?>
                        <div class="checkbox-option" data-search-text="<?= strtolower(htmlspecialchars($ingredient['name'])); ?>">
                            <label>
                                <input type="checkbox" name="ingredients[]" 
                                    value="<?= $ingredient['ingredient_id']; ?>"
                                    <?= in_array($ingredient['ingredient_id'], $_GET['ingredients'] ?? []) ? 'checked' : ''; ?>>
                                <?= htmlspecialchars($ingredient['name']); ?>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Categories Dropdown -->
        <div class="dropdown-container">
            <button type="button" class="dropdown-button">Select Categories</button>
            <div class="dropdown-content">
                <div class="dropdown-search">
                    <input type="text" class="search-input" placeholder="Search categories...">
                </div>
                <div class="checkbox-options">
                    <?php foreach ($categoriesList as $category): ?>
                        <div class="checkbox-option">
                            <label>
                                <input type="checkbox" name="categories[]" value="<?= $category['category_id']; ?>" 
                                    <?= in_array($category['category_id'], $_GET['categories'] ?? []) ? 'checked' : ''; ?>>
                                <?= htmlspecialchars($category['category_name']); ?>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <button type="submit">Apply Filters</button>
    </form>

    <div class="recipe-cards">
        <?php foreach ($recipes as $recipe): ?>
            <div class="recipe-card">
                <a href="index.php?action=view_recipe&recipe_id=<?= $recipe['recipe_id']; ?>">
                    <img src="<?= htmlspecialchars($recipe['image'] ?? 'assets/images/default_recipe.jpg'); ?>" alt="<?= htmlspecialchars($recipe['recipe_name']); ?>">
                    <div class="recipe-card-content">
                        <h3><?= htmlspecialchars($recipe['recipe_name']); ?></h3>
                        <p><?= htmlspecialchars($recipe['description']); ?></p>
                    </div>
                </a>
                <form action="index.php?action=favorite" method="POST" class="favorite-form">
                    <input type="hidden" name="recipe_id" value="<?= $recipe['recipe_id']; ?>">
                    <button type="submit" class="favorite-btn"><?= $recipeModel->isFavorited($user_id, $recipe['recipe_id']) ? "💔 Unfavorite" : "❤️ Favorite"; ?></button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- JavaScript -->
    <script src="assets/js/browse.js"></script>

</body>
</html>
