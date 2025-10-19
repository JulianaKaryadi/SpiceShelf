<?php include('includes/header.php'); ?>
<link rel="stylesheet" href="assets/css/add_recipe.css">

<div class="container">
    <h1>Add New Recipe</h1>
    
    <form method="POST" action="/SpiceShelf/index.php?action=add_recipe" enctype="multipart/form-data">
        <!-- Left Column - About Section -->
        <div class="recipe-left-column">
            <h2>About Recipe</h2>
            
            <div class="form-group">
                <label for="name">Recipe Name:</label>
                <input type="text" id="name" name="recipe_name" required>
            </div>

            <div class="form-group">
                <label for="description">Description:</label>
                <textarea id="description" name="description" required></textarea>
            </div>

            <div class="form-group">
                <label>Categories:</label>
                <div class="checkbox-container">
                    <?php foreach ($categories as $category): ?>
                        <div class="checkbox-option">
                            <input type="checkbox" 
                                id="category_<?= $category['category_id'] ?>"
                                name="category_ids[]" 
                                value="<?= $category['category_id'] ?>">
                            <label for="category_<?= $category['category_id'] ?>">
                                <?= htmlspecialchars($category['category_name']) ?>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="public-recipe">
                <input type="checkbox" id="public" name="public" value="1">
                <label for="public">Make this recipe public</label>
            </div>

            <div class="form-group">
                <label for="image">Recipe Image:</label>
                <input type="file" name="image" id="image" accept="image/*">
                <div id="image-preview"></div>
            </div>
        </div>

        <!-- Right Column - Details Section -->
        <div class="recipe-right-column">
            <h2>Recipe Details</h2>

            <div class="form-group">
                <label for="prep_time">Prep Time (minutes):</label>
                <input type="number" id="prep_time" name="prep_time" required min="0">
            </div>

            <div class="form-group">
                <label for="cook_time">Cook Time (minutes):</label>
                <input type="number" id="cook_time" name="cook_time" required min="0">
            </div>

            <div class="form-group">
                <label for="serving_size">Serving Size:</label>
                <input type="number" id="serving_size" name="serving_size" required min="1" max="20">
            </div>

            <div class="form-group">
                <label>Ingredients:</label>
                <div class="ingredients-container" id="ingredients-container">
                    <div class="ingredient-row">
                        <select name="ingredients[0][ingredient_id]" required>
                            <option value="">Select ingredient</option>
                            <?php foreach ($ingredients as $ingredient): ?>
                                <option value="<?= $ingredient['ingredient_id'] ?>">
                                    <?= htmlspecialchars($ingredient['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <input type="number" name="ingredients[0][quantity]" required placeholder="Quantity" min="0" step="0.1">
                        <select name="ingredients[0][measurement_id]" required>
                            <?php foreach ($measurements as $measurement): ?>
                                <option value="<?= $measurement['measurement_id'] ?>">
                                    <?= htmlspecialchars($measurement['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <button type="button" id="add-ingredient">+ Add Ingredient</button>
            </div>

            <div class="form-group">
                <label for="steps">Instructions:</label>
                <textarea id="steps" name="steps" required></textarea>
            </div>

            <button type="submit">Create Recipe</button>
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
            <option value="">Select ingredient</option>
            <?php foreach ($ingredients as $ingredient): ?>
                <option value="<?= $ingredient['ingredient_id'] ?>">
                    <?= htmlspecialchars($ingredient['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <input type="number" name="ingredients[${index}][quantity]" required placeholder="Quantity" min="0" step="0.1">
        <select name="ingredients[${index}][measurement_id]" required>
            <?php foreach ($measurements as $measurement): ?>
                <option value="<?= $measurement['measurement_id'] ?>">
                    <?= htmlspecialchars($measurement['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    `;
    document.getElementById('ingredients-container').appendChild(ingredientRow);
    
    // Set up event listeners for the new row
    setupIngredientEvents();
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

// Function to add ingredient selection event listeners
function setupIngredientEvents() {
    // Add event listeners to all existing ingredient selects
    document.querySelectorAll('.ingredient-row select[name*="ingredient_id"]').forEach(select => {
        if (!select.hasAttribute('data-listener-added')) {
            addIngredientChangeListener(select);
            select.setAttribute('data-listener-added', 'true');
        }
    });
}

// Function to add change listener to an ingredient select
function addIngredientChangeListener(select) {
    select.addEventListener('change', function() {
        const row = this.closest('.ingredient-row');
        const measurementSelect = row.querySelector('select[name*="measurement_id"]');
        const ingredientId = this.value;
        
        if (ingredientId) {
            // Show loading indicator
            measurementSelect.innerHTML = '<option>Loading...</option>';
            
            // Fetch default measurement for this ingredient
            fetch('index.php?action=getDefaultMeasurement&ingredient_id=' + ingredientId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Restore all measurement options
                        measurementSelect.innerHTML = `
                            <?php foreach ($measurements as $measurement): ?>
                                <option value="<?= $measurement['measurement_id'] ?>"><?= htmlspecialchars($measurement['name']) ?></option>
                            <?php endforeach; ?>
                        `;
                        
                        // Select the default measurement
                        measurementSelect.value = data.measurement_id;
                    }
                })
                .catch(error => {
                    console.error('Error fetching measurement:', error);
                    // Restore all measurement options on error
                    measurementSelect.innerHTML = `
                        <?php foreach ($measurements as $measurement): ?>
                            <option value="<?= $measurement['measurement_id'] ?>"><?= htmlspecialchars($measurement['name']) ?></option>
                        <?php endforeach; ?>
                    `;
                });
        }
    });
}

// Set up event listeners when the page loads
document.addEventListener('DOMContentLoaded', function() {
    setupIngredientEvents();
});
</script>