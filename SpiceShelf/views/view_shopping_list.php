<?php
// views/view_shopping_list.php
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping List Details - SpiceShelf</title>
    <!-- Include Bootstrap and jQuery -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/view_shopping_list.css">
</head>
<body>

<?php include 'includes/header.php'; ?>

<div class="content-wrapper">
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-md-8">
                <h1><?= htmlspecialchars($list['name']) ?></h1>
                <p class="text-muted">Created on <?= date('M d, Y', strtotime($list['created_at'])) ?></p>
            </div>
            <div class="col-md-4 text-end">
                <a href="index.php?action=shopping_list&subaction=downloadPdf&id=<?= $list['shopping_list_id'] ?>" class="btn btn-info mb-2 me-2">
                    <i class="fas fa-file-pdf"></i> Download as PDF
                </a>
                <button class="btn btn-success mb-2 me-2" id="addToPantryBtn">
                    <i class="fas fa-shopping-basket"></i> Add Purchased to Pantry
                </button>
                <button class="btn btn-danger mb-2" id="deleteListBtn">
                    <i class="fas fa-trash"></i> Delete List
                </button>
            </div>
        </div>

        <!-- Add Item Form -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <form id="addItemForm" class="row g-3">
                            <input type="hidden" name="shopping_list_id" value="<?= $list['shopping_list_id'] ?>">
                            
                            <div class="col-md-5">
                                <label for="ingredientSearch" class="form-label">Ingredient:</label>
                                <input type="text" class="form-control" id="ingredientSearch" placeholder="Search ingredients...">
                                <input type="hidden" id="ingredientId" name="ingredient_id" required>
                            </div>
                            
                            <div class="col-md-3">
                                <label for="quantity" class="form-label">Quantity:</label>
                                <input type="number" class="form-control" id="quantity" name="quantity" value="1" min="0.1" step="0.1" required>
                            </div>
                            
                            <div class="col-md-3">
                                <label for="measurementId" class="form-label">Measurement:</label>
                                <select class="form-select" id="measurementId" name="measurement_id" required>
                                    <option value="">Select...</option>
                                    <?php foreach ($measurements as $measurement): ?>
                                        <option value="<?= $measurement['measurement_id'] ?>"><?= htmlspecialchars($measurement['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-1 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary">Add</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Shopping List Items -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center" style="background-color: #34554a; color: white;">
                        <h3 class="mb-0">Items</h3>
                        <button class="btn btn-light btn-sm" id="markAllPurchasedBtn">
                            <i class="fas fa-check"></i> Mark All as Purchased
                        </button>
                    </div>
                    <div class="card-body">
                        <?php if (empty($items)): ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> This shopping list is empty. Add some items above.
                            </div>
                        <?php else: ?>
                            <div id="itemsList">
                                <?php foreach ($items as $item): ?>
                                    <div class="card shopping-list-item <?= $item['purchased'] ? 'purchased' : '' ?>" data-item-id="<?= $item['id'] ?>">
                                        <div class="card-body">
                                            <div class="row align-items-center">
                                                <div class="col-md-1">
                                                    <label class="checkbox-container">
                                                        <input type="checkbox" class="purchase-checkbox" <?= $item['purchased'] ? 'checked' : '' ?>>
                                                        <span class="checkmark"></span>
                                                    </label>
                                                </div>
                                                <div class="col-md-5">
                                                    <h5 class="card-title ingredient-name"><?= htmlspecialchars($item['ingredient_name']) ?></h5>
                                                </div>
                                                <div class="col-md-3">
                                                    <p class="card-text"><?= number_format($item['quantity'], 2) ?> <?= htmlspecialchars($item['measurement_name']) ?></p>
                                                </div>
                                                <div class="col-md-3 text-end">
                                                    <button class="btn btn-sm btn-outline-primary edit-item-btn">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-danger remove-item-btn">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Item Modal -->
<div class="modal fade" id="editItemModal" tabindex="-1" aria-labelledby="editItemModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editItemModalLabel">Edit Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editItemForm">
                    <input type="hidden" id="editItemId" name="item_id">
                    <input type="hidden" name="shopping_list_id" value="<?= $list['shopping_list_id'] ?>">
                    
                    <div class="mb-3">
                        <label for="editIngredientName" class="form-label">Ingredient:</label>
                        <input type="text" class="form-control" id="editIngredientName" disabled>
                    </div>
                    
                    <div class="mb-3">
                        <label for="editQuantity" class="form-label">Quantity:</label>
                        <input type="number" class="form-control" id="editQuantity" name="quantity" min="0.1" step="0.1" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="editMeasurementId" class="form-label">Measurement:</label>
                        <select class="form-select" id="editMeasurementId" name="measurement_id" required>
                            <?php foreach ($measurements as $measurement): ?>
                                <option value="<?= $measurement['measurement_id'] ?>"><?= htmlspecialchars($measurement['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="updateItemBtn">Update</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete List Confirmation Modal -->
<div class="modal fade" id="deleteListModal" tabindex="-1" aria-labelledby="deleteListModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteListModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this shopping list? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteListBtn">Delete</button>
            </div>
        </div>
    </div>
</div>

<!-- Add to Pantry Modal -->
<div class="modal fade" id="addToPantryModal" tabindex="-1" aria-labelledby="addToPantryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addToPantryModalLabel">Add to Pantry</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Add all purchased items to your pantry?</p>
                <form id="addToPantryForm">
                    <input type="hidden" name="shopping_list_id" value="<?= $list['shopping_list_id'] ?>">
                    
                    <div class="mb-3">
                        <label for="expirationDays" class="form-label">Days until expiration:</label>
                        <input type="number" class="form-control" id="expirationDays" name="expiration_days" value="14" min="1">
                        <div class="form-text">Items will be set to expire this many days from today.</div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="confirmAddToPantryBtn">Add to Pantry</button>
            </div>
        </div>
    </div>
</div>

<!-- Remove Item Confirmation Modal -->
<div class="modal fade" id="removeItemModal" tabindex="-1" aria-labelledby="removeItemModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="removeItemModalLabel">Confirm Remove</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to remove this item from your shopping list?</p>
                <input type="hidden" id="removeItemId">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmRemoveItemBtn">Remove</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Pass ingredients data to JavaScript -->
<script>
    // Create ingredient data for use in autocomplete
    window.ingredientData = [
        <?php foreach ($ingredients as $ingredient): ?>
        {
            id: <?= $ingredient['ingredient_id'] ?>,
            name: "<?= addslashes($ingredient['name']) ?>"
        },
        <?php endforeach; ?>
    ];
</script>

<!-- Custom JavaScript -->
<script src="assets/js/view_shopping_list.js"></script>

</body>
</html>