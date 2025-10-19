<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Pantry - SpiceShelf</title>
    <link rel="stylesheet" href="assets/css/pantry.css">
    <link rel="stylesheet" href="assets/css/pantry-modals.css">
</head>
<body>
    <?php include('includes/header.php'); ?>
    <div class="container">
        <h1>My Pantry</h1>
        
        <?php
        // Check for expiring items
        $expiring_soon = array_filter($items, function($item) {
            return $item['days_until_expiration'] >= 0 && $item['days_until_expiration'] <= 7;
        });
        
        $expired = array_filter($items, function($item) {
            return $item['days_until_expiration'] < 0;
        });
        ?>

        <?php if (!empty($expiring_soon) || !empty($expired)): ?>
            <div class="notifications">
                <?php if (!empty($expired)): ?>
                    <div class="notification expired">
                        <span class="notification-icon">⚠️</span>
                        <div class="notification-content">
                            <span class="notification-text">
                                <?php 
                                $count = count($expired);
                                echo $count . ' item' . ($count > 1 ? 's' : '') . ' ' . ($count > 1 ? 'have' : 'has') . ' expired!';
                                ?>
                            </span>
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($expiring_soon)): ?>
                    <div class="notification expiring">
                        <span class="notification-icon">⏰</span>
                        <div class="notification-content">
                            <span class="notification-text">
                                <?php 
                                $count = count($expiring_soon);
                                echo $count . ' item' . ($count > 1 ? 's' : '') . ' expiring within 7 days';
                                ?>
                            </span>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Mode Tabs -->
        <div class="mode-tabs">
            <button class="tab-button active" data-mode="recipe">
                <span class="tab-icon">🍳</span>
                <span class="tab-text">Recipe Mode</span>
            </button>
            <button class="tab-button" data-mode="manage">
                <span class="tab-icon">⚙️</span>
                <span class="tab-text">Manage Items</span>
            </button>
        </div>

        <!-- Recipe Mode Content -->
        <div class="mode-content active" id="recipe-mode">
            <div class="recipe-generation-section">
                <div class="recipe-options">
                    <h3>🤖 Generate AI Recipe</h3>
                    <div class="recipe-controls">
                        <div class="ingredient-selection">
                            <label class="selection-option">
                                <input type="radio" name="ingredient_selection" value="all" checked>
                                <span class="checkmark"></span>
                                <span>Use all fresh ingredients</span>
                            </label>
                            <label class="selection-option">
                                <input type="radio" name="ingredient_selection" value="selected">
                                <span class="checkmark"></span>
                                <span>Use only selected ingredients</span>
                            </label>
                        </div>
                        
                        <div class="meal-type-selector">
                            <label for="mealType">Meal Type:</label>
                            <select id="mealType" class="meal-type-dropdown">
                                <option value="">Any meal</option>
                                <option value="breakfast">🌅 Breakfast</option>
                                <option value="lunch">🌞 Lunch</option>
                                <option value="dinner">🌙 Dinner</option>
                                <option value="snack">🍿 Snack</option>
                                <option value="appetizer">🥗 Appetizer</option>
                                <option value="dessert">🍰 Dessert</option>
                                <option value="side_dish">🥖 Side Dish</option>
                                <option value="beverage">🥤 Beverage</option>
                            </select>
                        </div>
                        
                        <button class="generate-recipe-btn <?= empty($items) ? 'inactive' : '' ?>" id="generateRecipeBtn">
                            <span class="btn-icon">🤖</span>
                            <span class="btn-text">Generate Recipe</span>
                        </button>
                    </div>
                    
                    <div id="selectedIngredientsInfo" class="selected-info" style="display: none;">
                        <span class="info-icon">📋</span>
                        <span id="selectedIngredientsCount">0</span> ingredients selected
                    </div>
                </div>
            </div>
        </div>

        <!-- Manage Mode Content -->
        <div class="mode-content" id="manage-mode">
            <div class="manage-section">
                <div class="manage-actions">
                    <a href="index.php?action=add" class="action-btn add-btn">
                        <span class="btn-icon">➕</span>
                        <span class="btn-text">Add New Item</span>
                    </a>
                    <button id="deleteSelectedBtn" class="action-btn delete-btn" disabled>
                        <span class="btn-icon">🗑️</span>
                        <span class="btn-text">Delete Selected</span>
                    </button>
                </div>
                
                <div class="bulk-actions">
                    <label class="select-all-container">
                        <input type="checkbox" id="selectAll">
                        <span class="checkmark"></span>
                        <span>Select All</span>
                    </label>
                </div>
            </div>
        </div>

        <!-- Pantry Table -->
        <div class="table-container">
            <form id="bulkActionForm" method="post" action="index.php?action=pantry&subaction=bulkDelete">
                <table class="pantry-table">
                    <thead>
                        <tr>
                            <th class="checkbox-column">
                                <span class="recipe-header">Recipe</span>
                                <span class="manage-header">Select</span>
                            </th>
                            <th class="ingredient-column">Ingredient</th>
                            <th class="quantity-column">Quantity</th>
                            <th class="measurement-column">Measurement</th>
                            <th class="expiration-column" data-sort="expiration_date">Expiration Date</th>
                            <th class="actions-column">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (isset($items) && !empty($items)): ?>
                            <?php foreach ($items as $index => $row): 
                                $rowClass = $index % 2 === 0 ? 'even' : 'odd';
                                if ($row['days_until_expiration'] < 0) {
                                    $rowClass .= ' expired';
                                } elseif ($row['days_until_expiration'] <= 7) {
                                    $rowClass .= ' expiring-soon';
                                }
                            ?>
                                <tr class="<?= $rowClass ?>">
                                    <td class="checkbox-column">
                                        <input type="checkbox" 
                                               name="selected_items[]" 
                                               value="<?= $row['pantry_id'] ?>" 
                                               class="item-checkbox" 
                                               data-ingredient='<?= json_encode([
                                                   'pantry_id' => $row['pantry_id'],
                                                   'ingredient' => $row['ingredient'],
                                                   'quantity' => $row['quantity'],
                                                   'measurement' => $row['measurement'],
                                                   'days_until_expiration' => $row['days_until_expiration'],
                                                   'expiration_date' => $row['expiration_date']
                                               ]) ?>'
                                               data-expired="<?= ($row['days_until_expiration'] < 0) ? 'true' : 'false' ?>">
                                    </td>
                                    <td class="ingredient-column">
                                        <div class="ingredient-info">
                                            <span class="ingredient-name"><?= htmlspecialchars($row['ingredient']) ?></span>
                                        </div>
                                    </td>
                                    <td class="quantity-column"><?= htmlspecialchars($row['quantity']) ?></td>
                                    <td class="measurement-column"><?= htmlspecialchars($row['measurement']) ?></td>
                                    <td class="expiration-column">
                                        <div class="expiration-info">
                                            <span class="expiration-date"><?= htmlspecialchars($row['expiration_date']) ?></span>
                                            <?php if ($row['days_until_expiration'] < 0): ?>
                                                <span class="expiry-tag expired">Expired</span>
                                            <?php elseif ($row['days_until_expiration'] <= 7): ?>
                                                <span class="expiry-tag expiring">Expires in <?= $row['days_until_expiration'] ?> days</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="actions-column">
                                        <div class="item-actions">
                                            <a href="index.php?action=update&id=<?= $row['pantry_id'] ?>" class="action-btn update-btn">
                                                <span class="btn-icon">✏️</span>
                                                <span class="btn-text">Update</span>
                                            </a>
                                            <a href="index.php?action=delete&id=<?= $row['pantry_id'] ?>" 
                                               class="action-btn delete-btn" 
                                               onclick="return confirm('Are you sure you want to delete this item?');">
                                                <span class="btn-icon">🗑️</span>
                                                <span class="btn-text">Delete</span>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr class="empty-row">
                                <td colspan="6" class="empty-message">
                                    <div class="empty-state">
                                        <span class="empty-icon">📦</span>
                                        <span class="empty-text">No pantry items found.</span>
                                        <a href="index.php?action=add" class="empty-action">Add your first item</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                
                <!-- Delete Confirmation Modal -->
                <div id="deleteConfirmModal" class="modal">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h2>Confirm Deletion</h2>
                            <span class="close">&times;</span>
                        </div>
                        <div class="modal-body">
                            <p>Are you sure you want to delete the selected items?</p>
                            <p><span id="selectedItemCount">0</span> items will be removed from your pantry.</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" id="cancelDelete" class="button secondary">Cancel</button>
                            <button type="submit" id="confirmDelete" class="button delete-item">Delete</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script src="assets/js/pantry.js"></script>
</body>
</html>