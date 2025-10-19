<?php
// views/meal_plans.php
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meal Plans - SpiceShelf</title>
    <!-- Include Bootstrap and jQuery -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    <link rel="stylesheet" href="assets/css/meal_plan.css">
</head>
<body>

<?php include 'includes/header.php'; ?>

<div class="content-wrapper">
    <div class="container-fluid p-5">
        <div class="row mb-4">
            <div class="col-md-8">
                <h1>Meal Planning</h1>
                <p>Plan your meals for the week and generate shopping lists based on your meal plan.</p>
            </div>
            <div class="col-md-4 text-end">
                <button class="btn btn-success me-2" id="generateShoppingListBtn">
                    <i class="fas fa-cart-plus"></i> Generate Shopping List
                </button>
                <a href="index.php?action=meal_plans&subaction=exportPDF&start_date=<?= $start_date ?>&end_date=<?= $end_date ?>" 
                class="btn btn-primary" target="_blank">
                    <i class="fas fa-file-pdf"></i> Export as PDF
                </a>
            </div>

        <!-- Date range selector -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <form id="dateRangeForm" method="get" action="index.php">
                            <input type="hidden" name="action" value="meal_plans">
                            <div class="row align-items-center">
                                <div class="col-md-3">
                                    <label for="start_date" class="form-label">Start Date:</label>
                                    <input type="date" class="form-control" id="start_date" name="start_date" value="<?= $start_date ?>">
                                </div>
                                <div class="col-md-3">
                                    <label for="end_date" class="form-label">End Date:</label>
                                    <input type="date" class="form-control" id="end_date" name="end_date" value="<?= $end_date ?>">
                                </div>
                                <div class="col-md-3 mt-4">
                                    <button type="submit" class="btn btn-primary">View Meal Plan</button>
                                </div>
                                <div class="col-md-3 mt-4 text-end">
                                    <a href="index.php?action=meal_plans&start_date=<?= date('Y-m-d') ?>&end_date=<?= date('Y-m-d', strtotime('+6 days')) ?>" class="btn btn-outline-secondary">This Week</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Meal Plan Calendar -->
        <div class="row">
            <div class="col-md-12 mb-4">
                <div class="card">
                    <div class="card-header" style="background-color: #34554a; color: white;">
                        <h3 class="mb-0">Meal Plan: <?= date('M d, Y', strtotime($start_date)) ?> - <?= date('M d, Y', strtotime($end_date)) ?></h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th width="14%">Date</th>
                                        <th width="21.5%">Breakfast</th>
                                        <th width="21.5%">Lunch</th>
                                        <th width="21.5%">Dinner</th>
                                        <th width="21.5%">Snack</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($date_range as $date): ?>
                                    <tr class="<?= (date('Y-m-d') == $date) ? 'table-warning' : '' ?>">
                                        <td class="align-middle text-nowrap">
                                            <?= date('D, M d', strtotime($date)) ?>
                                        </td>
                                        
                                        <!-- Breakfast -->
                                        <td class="meal-cell" data-date="<?= $date ?>" data-meal-type="Breakfast">
                                            <?php if (empty($organized_meal_plans[$date]['Breakfast'])): ?>
                                                <button class="btn btn-sm btn-outline-secondary add-meal-btn" data-date="<?= $date ?>" data-meal-type="Breakfast">
                                                    <i class="fas fa-plus-circle"></i> Add Breakfast
                                                </button>
                                            <?php else: ?>
                                                <?php foreach ($organized_meal_plans[$date]['Breakfast'] as $meal): ?>
                                                    <div class="meal-item card mb-2" data-meal-id="<?= $meal['meal_plan_id'] ?>">
                                                        <div class="card-body p-2">
                                                            <h6 class="card-title"><?= htmlspecialchars($meal['recipe_name']) ?></h6>
                                                            <p class="card-text small mb-1">Servings: <?= $meal['serving_size'] ?></p>
                                                            <div class="btn-group btn-group-sm">
                                                                <button class="btn btn-outline-primary edit-meal-btn" data-meal-id="<?= $meal['meal_plan_id'] ?>">
                                                                    <i class="fas fa-edit"></i>
                                                                </button>
                                                                <button class="btn btn-outline-danger remove-meal-btn" data-meal-id="<?= $meal['meal_plan_id'] ?>">
                                                                    <i class="fas fa-trash-alt"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                                <button class="btn btn-sm btn-outline-secondary add-meal-btn mt-2" data-date="<?= $date ?>" data-meal-type="Breakfast">
                                                    <i class="fas fa-plus-circle"></i> Add Another
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                        
                                        <!-- Lunch -->
                                        <td class="meal-cell" data-date="<?= $date ?>" data-meal-type="Lunch">
                                            <?php if (empty($organized_meal_plans[$date]['Lunch'])): ?>
                                                <button class="btn btn-sm btn-outline-secondary add-meal-btn" data-date="<?= $date ?>" data-meal-type="Lunch">
                                                    <i class="fas fa-plus-circle"></i> Add Lunch
                                                </button>
                                            <?php else: ?>
                                                <?php foreach ($organized_meal_plans[$date]['Lunch'] as $meal): ?>
                                                    <div class="meal-item card mb-2" data-meal-id="<?= $meal['meal_plan_id'] ?>">
                                                        <div class="card-body p-2">
                                                            <h6 class="card-title"><?= htmlspecialchars($meal['recipe_name']) ?></h6>
                                                            <p class="card-text small mb-1">Servings: <?= $meal['serving_size'] ?></p>
                                                            <div class="btn-group btn-group-sm">
                                                                <button class="btn btn-outline-primary edit-meal-btn" data-meal-id="<?= $meal['meal_plan_id'] ?>">
                                                                    <i class="fas fa-edit"></i>
                                                                </button>
                                                                <button class="btn btn-outline-danger remove-meal-btn" data-meal-id="<?= $meal['meal_plan_id'] ?>">
                                                                    <i class="fas fa-trash-alt"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                                <button class="btn btn-sm btn-outline-secondary add-meal-btn mt-2" data-date="<?= $date ?>" data-meal-type="Lunch">
                                                    <i class="fas fa-plus-circle"></i> Add Another
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                        
                                        <!-- Dinner -->
                                        <td class="meal-cell" data-date="<?= $date ?>" data-meal-type="Dinner">
                                            <?php if (empty($organized_meal_plans[$date]['Dinner'])): ?>
                                                <button class="btn btn-sm btn-outline-secondary add-meal-btn" data-date="<?= $date ?>" data-meal-type="Dinner">
                                                    <i class="fas fa-plus-circle"></i> Add Dinner
                                                </button>
                                            <?php else: ?>
                                                <?php foreach ($organized_meal_plans[$date]['Dinner'] as $meal): ?>
                                                    <div class="meal-item card mb-2" data-meal-id="<?= $meal['meal_plan_id'] ?>">
                                                        <div class="card-body p-2">
                                                            <h6 class="card-title"><?= htmlspecialchars($meal['recipe_name']) ?></h6>
                                                            <p class="card-text small mb-1">Servings: <?= $meal['serving_size'] ?></p>
                                                            <div class="btn-group btn-group-sm">
                                                                <button class="btn btn-outline-primary edit-meal-btn" data-meal-id="<?= $meal['meal_plan_id'] ?>">
                                                                    <i class="fas fa-edit"></i>
                                                                </button>
                                                                <button class="btn btn-outline-danger remove-meal-btn" data-meal-id="<?= $meal['meal_plan_id'] ?>">
                                                                    <i class="fas fa-trash-alt"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                                <button class="btn btn-sm btn-outline-secondary add-meal-btn mt-2" data-date="<?= $date ?>" data-meal-type="Dinner">
                                                    <i class="fas fa-plus-circle"></i> Add Another
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                        
                                        <!-- Snack -->
                                        <td class="meal-cell" data-date="<?= $date ?>" data-meal-type="Snack">
                                            <?php if (empty($organized_meal_plans[$date]['Snack'])): ?>
                                                <button class="btn btn-sm btn-outline-secondary add-meal-btn" data-date="<?= $date ?>" data-meal-type="Snack">
                                                    <i class="fas fa-plus-circle"></i> Add Snack
                                                </button>
                                            <?php else: ?>
                                                <?php foreach ($organized_meal_plans[$date]['Snack'] as $meal): ?>
                                                    <div class="meal-item card mb-2" data-meal-id="<?= $meal['meal_plan_id'] ?>">
                                                        <div class="card-body p-2">
                                                            <h6 class="card-title"><?= htmlspecialchars($meal['recipe_name']) ?></h6>
                                                            <p class="card-text small mb-1">Servings: <?= $meal['serving_size'] ?></p>
                                                            <div class="btn-group btn-group-sm">
                                                                <button class="btn btn-outline-primary edit-meal-btn" data-meal-id="<?= $meal['meal_plan_id'] ?>">
                                                                    <i class="fas fa-edit"></i>
                                                                </button>
                                                                <button class="btn btn-outline-danger remove-meal-btn" data-meal-id="<?= $meal['meal_plan_id'] ?>">
                                                                    <i class="fas fa-trash-alt"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                                <button class="btn btn-sm btn-outline-secondary add-meal-btn mt-2" data-date="<?= $date ?>" data-meal-type="Snack">
                                                    <i class="fas fa-plus-circle"></i> Add Another
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Meal Modal -->
<div class="modal fade" id="addMealModal" tabindex="-1" aria-labelledby="addMealModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addMealModalLabel">Add Meal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addMealForm">
                    <input type="hidden" id="mealDate" name="meal_date">
                    <input type="hidden" id="mealType" name="meal_type">
                    
                    <div class="mb-3">
                        <label for="recipeSearch" class="form-label">Search Recipe:</label>
                        <input type="text" class="form-control" id="recipeSearch" placeholder="Type to search recipes...">
                        <input type="hidden" id="recipeId" name="recipe_id" required>
                    </div>
                    
                    <div id="recipeDetails" class="d-none mb-3">
                        <div class="card">
                            <div class="card-body">
                                <h5 id="selectedRecipeName" class="card-title"></h5>
                                <p id="selectedRecipeDescription" class="card-text"></p>
                                <p class="card-text">
                                    <small class="text-muted">
                                        Prep Time: <span id="selectedRecipePrepTime"></span> min | 
                                        Cook Time: <span id="selectedRecipeCookTime"></span> min
                                    </small>
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="servingSize" class="form-label">Servings:</label>
                        <input type="number" class="form-control" id="servingSize" name="serving_size" value="1" min="1">
                    </div>
                    
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes (optional):</label>
                        <textarea class="form-control" id="notes" name="notes" rows="2"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveMealBtn">Add to Meal Plan</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Meal Modal -->
<div class="modal fade" id="editMealModal" tabindex="-1" aria-labelledby="editMealModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editMealModalLabel">Edit Meal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editMealForm">
                    <input type="hidden" id="editMealId" name="meal_plan_id">
                    <input type="hidden" id="editMealDate" name="meal_date">
                    <input type="hidden" id="editMealType" name="meal_type">
                    <input type="hidden" id="editRecipeId" name="recipe_id">
                    
                    <div class="mb-3">
                        <h5 id="editRecipeName"></h5>
                    </div>
                    
                    <div class="mb-3">
                        <label for="editServingSize" class="form-label">Servings:</label>
                        <input type="number" class="form-control" id="editServingSize" name="serving_size" min="1">
                    </div>
                    
                    <div class="mb-3">
                        <label for="editNotes" class="form-label">Notes (optional):</label>
                        <textarea class="form-control" id="editNotes" name="notes" rows="2"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="updateMealBtn">Update Meal</button>
            </div>
        </div>
    </div>
</div>

<!-- Shopping List Modal -->
<div class="modal fade" id="shoppingListModal" tabindex="-1" aria-labelledby="shoppingListModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="shoppingListModalLabel">Shopping List</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="shoppingListForm" method="post" action="index.php?action=meal_plans&subaction=generateShoppingList">
                    <input type="hidden" name="start_date" value="<?= $start_date ?>">
                    <input type="hidden" name="end_date" value="<?= $end_date ?>">
                    
                    <div class="mb-3">
                        <label for="listName" class="form-label">Shopping List Name:</label>
                        <input type="text" class="form-control" id="listName" name="list_name" value="Shopping List for <?= date('M d', strtotime($start_date)) ?> - <?= date('M d', strtotime($end_date)) ?>">
                    </div>
                    
                    <div id="shoppingListItems">
                        <div class="text-center py-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2">Generating shopping list...</p>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="exportShoppingListBtn">Export to Shopping List</button>
            </div>
        </div>
    </div>
</div>

<!-- Remove Meal Confirmation Modal -->
<div class="modal fade" id="confirmRemoveModal" tabindex="-1" aria-labelledby="confirmRemoveModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmRemoveModalLabel">Confirm Remove</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to remove this meal from your meal plan?</p>
                <input type="hidden" id="removeMealId">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmRemoveBtn">Remove</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/meal_plan.js"></script>

</body>
</html>