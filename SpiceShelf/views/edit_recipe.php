<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Recipe - SpiceShelf</title>
    <link rel="stylesheet" href="assets/css/edit_recipe.css">
</head>
<body>
    <?php include('includes/header.php'); ?>

    <div class="container">
        <h1>Edit Recipe: <?= htmlspecialchars($recipe['recipe_name']); ?></h1>
        
        <form method="POST" action="index.php?action=update_recipe" enctype="multipart/form-data">
            <!-- Hidden recipe ID for form submission -->
            <input type="hidden" name="recipe_id" value="<?= $recipe['recipe_id']; ?>">
            
            <!-- Left Column - About Section -->
            <div class="recipe-left-column">
                <h2>About Recipe</h2>
                
                <div class="form-group">
                    <label for="recipe_name">Recipe Name:</label>
                    <input type="text" id="recipe_name" name="recipe_name" value="<?= htmlspecialchars($recipe['recipe_name']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="description">Description:</label>
                    <textarea id="description" name="description" required><?= htmlspecialchars($recipe['description']); ?></textarea>
                </div>

                <div class="form-group">
                    <label>Categories:</label>
                    <div class="checkbox-container">
                        <?php foreach ($categories as $category): ?>
                            <div class="checkbox-option">
                                <input type="checkbox" 
                                    id="category_<?= $category['category_id'] ?>"
                                    name="category_ids[]" 
                                    value="<?= $category['category_id'] ?>"
                                    <?= in_array($category['category_id'], $recipeCategories) ? 'checked' : '' ?>>
                                <label for="category_<?= $category['category_id'] ?>">
                                    <?= htmlspecialchars($category['category_name']); ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="public-recipe">
                    <input type="checkbox" id="public" name="public" value="1" <?= $recipe['public'] === '1' ? 'checked' : ''; ?>>
                    <label for="public">Make this recipe public</label>
                </div>

                <div class="form-group">
                    <label for="image">Recipe Image:</label>
                    <input type="file" name="image" id="image" accept="image/*">
                    <div id="image-preview">
                        <?php if (!empty($recipe['image'])): ?>
                            <img src="<?= htmlspecialchars($recipe['image']); ?>" alt="Current recipe image">
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Right Column - Details Section -->
            <div class="recipe-right-column">
                <h2>Recipe Details</h2>
                
                <div class="form-group">
                    <label for="prep_time">Prep Time (minutes):</label>
                    <input type="number" id="prep_time" name="prep_time" value="<?= htmlspecialchars($recipe['prep_time']); ?>" required min="0">
                </div>

                <div class="form-group">
                    <label for="cook_time">Cook Time (minutes):</label>
                    <input type="number" id="cook_time" name="cook_time" value="<?= htmlspecialchars($recipe['cook_time']); ?>" required min="0">
                </div>

                <div class="form-group">  
                    <label for="serving_size">Serving Size:</label>
                    <input type="number" id="serving_size" name="serving_size" value="<?= htmlspecialchars($recipe['serving_size']); ?>" required min="1" max="20">
                </div>

                <div class="form-group">
                    <label>Ingredients:</label>
                    <div id="ingredients-container">
                        <?php foreach ($ingredients as $index => $ingredient): ?>
                            <div class="ingredient-row">
                                <input type="hidden" name="ingredients[<?= $index; ?>][id]" value="<?= htmlspecialchars($ingredient['recipe_ingredient_id']); ?>">
                                <select name="ingredients[<?= $index; ?>][ingredient_id]" required>
                                    <?php foreach ($predefinedIngredients as $predefinedIngredient): ?>
                                        <option value="<?= $predefinedIngredient['ingredient_id']; ?>" 
                                            <?= ($ingredient['ingredient_id'] == $predefinedIngredient['ingredient_id']) ? 'selected' : ''; ?>>
                                            <?= htmlspecialchars($predefinedIngredient['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <input type="number" name="ingredients[<?= $index; ?>][quantity]" 
                                    value="<?= htmlspecialchars($ingredient['quantity'] ?? ''); ?>" 
                                    placeholder="Quantity" required min="0" step="0.1">
                                <select name="ingredients[<?= $index; ?>][measurement_id]" required>
                                    <?php foreach ($measurements as $measurement): ?>
                                        <option value="<?= $measurement['measurement_id']; ?>" 
                                            <?= ($ingredient['measurement_id'] == $measurement['measurement_id']) ? 'selected' : ''; ?>>
                                            <?= htmlspecialchars($measurement['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" id="add-ingredient">+ Add Ingredient</button>
                </div>

                <div class="form-group">
                    <label for="steps">Instructions:</label>
                    <textarea id="steps" name="steps" required><?= htmlspecialchars($recipe['steps']); ?></textarea>
                </div>

                <button type="submit">Update Recipe</button>
            </div>
        </form>
    </div>

    <script>
    // Add ingredient row functionality
    document.getElementById('add-ingredient').addEventListener('click', function() {
        let ingredientRow = document.createElement('div');
        ingredientRow.classList.add('ingredient-row');
        let index = document.querySelectorAll('.ingredient-row').length;
        ingredientRow.innerHTML = `
            <select name="ingredients[${index}][ingredient_id]" required>
                <?php foreach ($predefinedIngredients as $predefinedIngredient): ?>
                    <option value="<?= $predefinedIngredient['ingredient_id']; ?>">
                        <?= htmlspecialchars($predefinedIngredient['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <input type="number" name="ingredients[${index}][quantity]" required 
                placeholder="Quantity" min="0" step="0.1">
            <select name="ingredients[${index}][measurement_id]" required>
                <?php foreach ($measurements as $measurement): ?>
                    <option value="<?= $measurement['measurement_id']; ?>">
                        <?= htmlspecialchars($measurement['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        `;
        document.getElementById('ingredients-container').appendChild(ingredientRow);
    });

    // Image preview functionality 
    document.getElementById('image').addEventListener('change', function(event) {
        const preview = document.getElementById('image-preview');
        preview.innerHTML = '';
        const file = event.target.files[0];
        
        if (file) {
            const img = document.createElement('img');
            img.src = URL.createObjectURL(file);
            img.alt = 'Recipe preview';
            preview.appendChild(img);
        }
    });
    </script>
</body>
</html>