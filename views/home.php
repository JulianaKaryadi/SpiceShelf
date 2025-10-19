<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SpiceShelf - Home</title>
    <link rel="stylesheet" href="assets/css/home.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Header -->
    <?php include('includes/header.php'); ?>

    <!-- Main content -->
    <div class="main-content">
        <h1>Welcome to SpiceShelf</h1>
        <p>Your cozy corner for managing recipes, meal plans, and discovering culinary delights</p>

        <!-- Personalized Greeting -->
        <?php if (isset($_SESSION['username'])): ?>
            <p>Hi, <?= htmlspecialchars($_SESSION['username']); ?>! What would you like to cook today?</p>
        <?php endif; ?>

        <div class="recipe-sections">
            <!-- User's Recipes -->
            <section>
                <h2>Your Recipes</h2>
                <div class="recipe-cards" id="user-recipes">
                    <?php if (!empty($userRecipes)): ?>
                        <?php foreach ($userRecipes as $recipe): ?>
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
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>You haven't created any recipes yet. Ready to start your culinary journey?</p>
                    <?php endif; ?>
                </div>
            </section>

            <!-- Favorite Recipes -->
            <section>
                <h2>Favorite Recipes</h2>
                <div class="recipe-cards" id="favorite-recipes">
                    <?php if (!empty($favoriteRecipes)): ?>
                        <?php foreach ($favoriteRecipes as $recipe): ?>
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
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>You haven't favorited any recipes yet. Explore and find some recipes you love!</p>
                    <?php endif; ?>
                </div>
            </section>
    
    <script src="assets/js/home.js"></script>
</body>
</html>