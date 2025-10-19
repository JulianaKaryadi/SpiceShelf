/**
 * SpiceShelf - Meal Plan JavaScript
 * Handles all meal planning functionality including adding, editing, and removing meals,
 * as well as generating shopping lists
 */
$(document).ready(function() {
    // Initialize Bootstrap tooltips
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
    
    // Initialize modals
    const addMealModal = new bootstrap.Modal(document.getElementById('addMealModal'));
    const editMealModal = new bootstrap.Modal(document.getElementById('editMealModal'));
    const shoppingListModal = new bootstrap.Modal(document.getElementById('shoppingListModal'));
    const confirmRemoveModal = new bootstrap.Modal(document.getElementById('confirmRemoveModal'));

    // Add meal button click
    $('.add-meal-btn').on('click', function() {
        const date = $(this).data('date');
        const mealType = $(this).data('meal-type');
        
        $('#mealDate').val(date);
        $('#mealType').val(mealType);
        $('#addMealModalLabel').text('Add ' + mealType + ' for ' + formatDate(date));
        
        // Reset form
        $('#addMealForm')[0].reset();
        $('#recipeDetails').addClass('d-none');
        $('#recipeId').val('');
        
        addMealModal.show();
    });

    // Recipe search autocomplete
    $('#recipeSearch').autocomplete({
        source: function(request, response) {
            console.log("Searching for:", request.term);
            $.ajax({
                url: 'index.php?action=meal_plans&subaction=searchRecipes',
                dataType: 'json',
                data: {
                    term: request.term
                },
                success: function(data) {
                    console.log("Search results:", data);
                    if (data.success && data.recipes && data.recipes.length > 0) {
                        response($.map(data.recipes, function(recipe) {
                            return {
                                label: recipe.recipe_name,
                                value: recipe.recipe_name,
                                id: recipe.recipe_id,
                                description: recipe.description || "No description available",
                                prep_time: recipe.prep_time || "N/A",
                                cook_time: recipe.cook_time || "N/A",
                                image: recipe.image || ""
                            };
                        }));
                    } else {
                        response([{
                            label: "No recipes found",
                            value: "",
                            id: "",
                            description: "",
                            prep_time: "",
                            cook_time: "",
                            image: ""
                        }]);
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error:", status, error);
                    response([]);
                }
            });
        },
        minLength: 2,
        select: function(event, ui) {
            console.log("Selected recipe:", ui.item);
            if (!ui.item.id) {
                return false; // Prevent selection of "No recipes found"
            }
            $('#recipeId').val(ui.item.id);
            $('#selectedRecipeName').text(ui.item.label);
            $('#selectedRecipeDescription').text(ui.item.description);
            $('#selectedRecipePrepTime').text(ui.item.prep_time);
            $('#selectedRecipeCookTime').text(ui.item.cook_time);
            $('#recipeDetails').removeClass('d-none');
            return true;
        }
    }).autocomplete("instance")._renderItem = function(ul, item) {
        console.log("Rendering item:", item);
        // Custom rendering of dropdown items with image
        if (!item.id) {
            return $("<li>")
                .append("<div class='autocomplete-item'><div class='autocomplete-content'><div class='autocomplete-title'>" + item.label + "</div></div></div>")
                .appendTo(ul);
        }
        
        var imgSrc = item.image ? item.image : 'assets/images/default-recipe.jpg';
        return $("<li>")
            .append("<div class='autocomplete-item'>" +
                "<div class='autocomplete-image'><img src='" + imgSrc + "' alt='" + item.label + "'></div>" +
                "<div class='autocomplete-content'>" +
                "<div class='autocomplete-title'>" + item.label + "</div>" +
                "<div class='autocomplete-subtitle'>Prep: " + item.prep_time + " min | Cook: " + item.cook_time + " min</div>" +
                "</div></div>")
            .appendTo(ul);
    };

    // Save meal button click
    $('#saveMealBtn').on('click', function() {
        const recipeId = $('#recipeId').val();
        if (!recipeId) {
            alert('Please select a recipe');
            return;
        }
        
        $.ajax({
            url: 'index.php?action=meal_plans&subaction=addMeal',
            method: 'POST',
            dataType: 'json',
            data: $('#addMealForm').serialize(),
            success: function(data) {
                if (data.success) {
                    addMealModal.hide();
                    // Reload page to show new meal
                    location.reload();
                } else {
                    alert(data.message || 'Failed to add meal');
                }
            },
            error: function() {
                alert('An error occurred. Please try again.');
            }
        });
    });

    // Edit meal button click
    $('.edit-meal-btn').on('click', function() {
        const mealId = $(this).data('meal-id');
        
        // Get meal details
        $.ajax({
            url: 'index.php?action=meal_plans&subaction=getMeal',
            method: 'GET',
            dataType: 'json',
            data: {
                meal_plan_id: mealId
            },
            success: function(data) {
                if (data.success) {
                    const meal = data.meal;
                    
                    $('#editMealId').val(meal.meal_plan_id);
                    $('#editMealDate').val(meal.meal_date);
                    $('#editMealType').val(meal.meal_type);
                    $('#editRecipeId').val(meal.recipe_id);
                    $('#editRecipeName').text(meal.recipe_name);
                    $('#editServingSize').val(meal.serving_size);
                    $('#editNotes').val(meal.notes);
                    
                    $('#editMealModalLabel').text('Edit ' + meal.meal_type + ' for ' + formatDate(meal.meal_date));
                    
                    editMealModal.show();
                } else {
                    alert(data.message || 'Failed to get meal details');
                }
            },
            error: function() {
                alert('An error occurred. Please try again.');
            }
        });
    });

    // Update meal button click
    $('#updateMealBtn').on('click', function() {
        $.ajax({
            url: 'index.php?action=meal_plans&subaction=updateMeal',
            method: 'POST',
            dataType: 'json',
            data: $('#editMealForm').serialize(),
            success: function(data) {
                if (data.success) {
                    editMealModal.hide();
                    // Reload page to show updated meal
                    location.reload();
                } else {
                    alert(data.message || 'Failed to update meal');
                }
            },
            error: function() {
                alert('An error occurred. Please try again.');
            }
        });
    });

    // Remove meal button click
    $('.remove-meal-btn').on('click', function() {
        const mealId = $(this).data('meal-id');
        $('#removeMealId').val(mealId);
        confirmRemoveModal.show();
    });

    // Confirm remove button click
    $('#confirmRemoveBtn').on('click', function() {
        const mealId = $('#removeMealId').val();
        
        $.ajax({
            url: 'index.php?action=meal_plans&subaction=removeMeal',
            method: 'POST',
            dataType: 'json',
            data: {
                meal_plan_id: mealId
            },
            success: function(data) {
                if (data.success) {
                    confirmRemoveModal.hide();
                    // Reload page to remove meal
                    location.reload();
                } else {
                    alert(data.message || 'Failed to remove meal');
                }
            },
            error: function() {
                alert('An error occurred. Please try again.');
            }
        });
    });

    // Generate shopping list button click
    $('#generateShoppingListBtn').on('click', function() {
        // Reset shopping list items
        $('#shoppingListItems').html(`
            <div class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Generating shopping list...</p>
            </div>
        `);
        
        shoppingListModal.show();
        
        // Get shopping list
        $.ajax({
            url: 'index.php?action=meal_plans&subaction=generateShoppingList',
            method: 'POST',
            dataType: 'json',
            data: {
                start_date: $('#start_date').val(),
                end_date: $('#end_date').val()
            },
            success: function(data) {
                if (data.success) {
                    renderShoppingList(data.shopping_list);
                } else {
                    $('#shoppingListItems').html('<div class="alert alert-danger">Failed to generate shopping list</div>');
                }
            },
            error: function() {
                $('#shoppingListItems').html('<div class="alert alert-danger">An error occurred. Please try again.</div>');
            }
        });
    });

    // Export shopping list button click
    $('#exportShoppingListBtn').on('click', function() {
        $('#shoppingListForm').append('<input type="hidden" name="export" value="1">');
        $('#shoppingListForm').submit();
    });

    // Helper function to format date
    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric' });
    }

    // Render shopping list
    function renderShoppingList(shoppingList) {
        if (shoppingList.length === 0) {
            $('#shoppingListItems').html('<div class="alert alert-warning">No ingredients found for this date range</div>');
            return;
        }
        
        let html = `
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Ingredient</th>
                            <th>Quantity</th>
                            <th>Measurement</th>
                            <th>In Pantry</th>
                            <th>Need to Buy</th>
                        </tr>
                    </thead>
                    <tbody>`;
        
        shoppingList.forEach(item => {
            // Get basic values with defaults
            const ingredientName = item.ingredient_name || '';
            const measurementName = item.measurement_name || '';
            
            // Parse quantities safely
            let totalQty;
            try {
                totalQty = parseFloat(item.total_quantity) || 0;
            } catch(e) {
                totalQty = 0;
            }
            
            let neededQty;
            try {
                neededQty = parseFloat(item.needed_quantity) || 0;
            } catch(e) {
                neededQty = 0;
            }
            
            // Determine pantry status text
            let pantryStatus;
            if (item.have_enough) {
                pantryStatus = "Available";
            } else if (item.in_pantry) {
                pantryStatus = "Partial";
            } else {
                pantryStatus = "None";
            }
            
            html += `
                <tr>
                    <td>${ingredientName}</td>
                    <td>${totalQty.toFixed(2)}</td>
                    <td>${measurementName}</td>
                    <td>
                        <span class="pantry-status-${pantryStatus.toLowerCase()}">${pantryStatus}</span>
                    </td>
                    <td>
                        ${item.have_enough ? 
                            '<span class="badge bg-success">In Stock</span>' : 
                            `<span class="badge bg-warning">${neededQty.toFixed(2)} ${measurementName}</span>`}
                    </td>
                </tr>`;
        });
        
        html += `
                    </tbody>
                </table>
            </div>`;
        
        $('#shoppingListItems').html(html);
    }
});