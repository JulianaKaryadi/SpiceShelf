/**
 * View Shopping List JavaScript functionality
 * Handles CRUD operations for shopping list items and list management
 */
$(document).ready(function() {
    // Initialize modals
    const editItemModal = new bootstrap.Modal(document.getElementById('editItemModal'));
    const deleteListModal = new bootstrap.Modal(document.getElementById('deleteListModal'));
    const addToPantryModal = new bootstrap.Modal(document.getElementById('addToPantryModal'));
    const removeItemModal = new bootstrap.Modal(document.getElementById('removeItemModal'));

    // Store shopping list ID for reuse
    const shoppingListId = $('#addItemForm input[name="shopping_list_id"]').val();
    
    /**
     * Initialize Ingredient Search Autocomplete
     */
    initializeAutocomplete();
    
    /**
     * Add item form submit handler
     */
    $('#addItemForm').on('submit', function(e) {
        e.preventDefault();
        
        // Validate form
        if (!validateAddItemForm()) {
            return;
        }
        
        const formData = $(this).serialize();
        const $form = $(this);
        
        // Disable form during submission
        const $submitBtn = $form.find('button[type="submit"]');
        const originalBtnText = $submitBtn.text();
        $submitBtn.prop('disabled', true).text('Adding...');
        
        $.ajax({
            url: 'index.php?action=shopping_list&subaction=addItem',
            method: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Reset form
                    resetAddItemForm();
                    
                    // Add item to list
                    if (response.item) {
                        addItemToList(response.item);
                    }
                } else {
                    showError(response.message || 'Failed to add item');
                }
            },
            error: function(xhr, status, error) {
                showError('An error occurred. Please try again.');
                console.error("AJAX Error:", status, error);
            },
            complete: function() {
                // Re-enable form
                $submitBtn.prop('disabled', false).text(originalBtnText);
            }
        });
    });

    /**
     * Edit item button click handler
     */
    $(document).on('click', '.edit-item-btn', function() {
        const itemCard = $(this).closest('.shopping-list-item');
        const itemId = itemCard.data('item-id');
        const ingredientName = itemCard.find('.ingredient-name').text();
        const quantityText = itemCard.find('.card-text').text();
        
        // Parse quantity and measurement
        const parts = quantityText.trim().split(' ');
        const quantity = parseFloat(parts[0]);
        const measurement = parts.slice(1).join(' ');
        
        // Set form values
        $('#editItemId').val(itemId);
        $('#editIngredientName').val(ingredientName);
        $('#editQuantity').val(quantity);
        
        // Find measurement ID by name
        $('#editMeasurementId option').each(function() {
            if ($(this).text() === measurement) {
                $('#editMeasurementId').val($(this).val());
                return false;
            }
        });
        
        // Show modal
        editItemModal.show();
    });

    /**
     * Update item button click handler
     */
    $('#updateItemBtn').on('click', function() {
        // Validate form
        if (!$('#editQuantity').val() || !$('#editMeasurementId').val()) {
            showError('Please fill in all required fields', 'edit');
            return;
        }
        
        const formData = $('#editItemForm').serialize();
        const $button = $(this);
        const originalText = $button.text();
        
        // Disable button during submission
        $button.prop('disabled', true).text('Updating...');
        
        $.ajax({
            url: 'index.php?action=shopping_list&subaction=updateItem',
            method: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    editItemModal.hide();
                    
                    const itemId = $('#editItemId').val();
                    const quantity = $('#editQuantity').val();
                    const measurementName = $('#editMeasurementId option:selected').text();
                    
                    // Update item in the list
                    const itemCard = $(`.shopping-list-item[data-item-id="${itemId}"]`);
                    itemCard.find('.card-text').text(`${parseFloat(quantity).toFixed(2)} ${measurementName}`);
                    
                    // Show success message
                    showToast('Item updated successfully');
                } else {
                    showError(response.message || 'Failed to update item', 'edit');
                }
            },
            error: function(xhr, status, error) {
                showError('An error occurred. Please try again.', 'edit');
                console.error("AJAX Error:", status, error);
            },
            complete: function() {
                // Re-enable button
                $button.prop('disabled', false).text(originalText);
            }
        });
    });

    /**
     * Purchase checkbox click handler
     */
    $(document).on('change', '.purchase-checkbox', function() {
        const itemCard = $(this).closest('.shopping-list-item');
        const itemId = itemCard.data('item-id');
        const isPurchased = $(this).is(':checked');
        const $checkbox = $(this);
        
        $.ajax({
            url: 'index.php?action=shopping_list&subaction=togglePurchased',
            method: 'POST',
            data: {
                item_id: itemId,
                shopping_list_id: shoppingListId
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    if (isPurchased) {
                        itemCard.addClass('purchased');
                    } else {
                        itemCard.removeClass('purchased');
                    }
                } else {
                    // Revert checkbox state on error
                    $checkbox.prop('checked', !isPurchased);
                    showError(response.message || 'Failed to update item status');
                }
            },
            error: function(xhr, status, error) {
                // Revert checkbox state on error
                $checkbox.prop('checked', !isPurchased);
                showError('An error occurred. Please try again.');
                console.error("AJAX Error:", status, error);
            }
        });
    });

    /**
     * Mark all as purchased button click handler
     */
    $('#markAllPurchasedBtn').on('click', function() {
        const $button = $(this);
        const originalText = $button.html();
        
        // Disable button during submission
        $button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing...');
        
        $.ajax({
            url: 'index.php?action=shopping_list&subaction=markAllPurchased',
            method: 'POST',
            data: {
                shopping_list_id: shoppingListId
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Mark all items as purchased
                    $('.shopping-list-item').addClass('purchased');
                    $('.purchase-checkbox').prop('checked', true);
                    
                    // Show success message
                    showToast('All items marked as purchased');
                } else {
                    showError(response.message || 'Failed to update items');
                }
            },
            error: function(xhr, status, error) {
                showError('An error occurred. Please try again.');
                console.error("AJAX Error:", status, error);
            },
            complete: function() {
                // Re-enable button
                $button.prop('disabled', false).html(originalText);
            }
        });
    });

    /**
     * Remove item button click handler
     */
    $(document).on('click', '.remove-item-btn', function() {
        const itemCard = $(this).closest('.shopping-list-item');
        const itemId = itemCard.data('item-id');
        const itemName = itemCard.find('.ingredient-name').text();
        
        // Set confirmation modal content
        $('#removeItemId').val(itemId);
        $('#removeItemModal .modal-body p').html(`Are you sure you want to remove <strong>${itemName}</strong> from your shopping list?`);
        
        // Show modal
        removeItemModal.show();
    });

    /**
     * Confirm remove item button click handler
     */
    $('#confirmRemoveItemBtn').on('click', function() {
        const itemId = $('#removeItemId').val();
        const $button = $(this);
        const originalText = $button.text();
        
        // Disable button during submission
        $button.prop('disabled', true).text('Removing...');
        
        $.ajax({
            url: 'index.php?action=shopping_list&subaction=removeItem',
            method: 'POST',
            data: {
                item_id: itemId,
                shopping_list_id: shoppingListId
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    removeItemModal.hide();
                    
                    // Remove item from list with animation
                    const $itemCard = $(`.shopping-list-item[data-item-id="${itemId}"]`);
                    $itemCard.fadeOut(300, function() {
                        $(this).remove();
                        
                        // Show empty message if no items left
                        if ($('.shopping-list-item').length === 0) {
                            $('#itemsList').html(`
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i> This shopping list is empty. Add some items above.
                                </div>
                            `);
                        }
                    });
                    
                    // Show success message
                    showToast('Item removed successfully');
                } else {
                    showError(response.message || 'Failed to remove item', 'remove');
                }
            },
            error: function(xhr, status, error) {
                showError('An error occurred. Please try again.', 'remove');
                console.error("AJAX Error:", status, error);
            },
            complete: function() {
                // Re-enable button
                $button.prop('disabled', false).text(originalText);
            }
        });
    });

    /**
     * Delete list button click handler
     */
    $('#deleteListBtn').on('click', function() {
        deleteListModal.show();
    });

    /**
     * Confirm delete list button click handler
     */
    $('#confirmDeleteListBtn').on('click', function() {
        const $button = $(this);
        const originalText = $button.text();
        
        // Disable button during submission
        $button.prop('disabled', true).text('Deleting...');
        
        $.ajax({
            url: 'index.php?action=shopping_list&subaction=deleteList',
            method: 'POST',
            data: {
                shopping_list_id: shoppingListId
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    window.location.href = 'index.php?action=shopping_list';
                } else {
                    showError(response.message || 'Failed to delete shopping list', 'delete');
                    $button.prop('disabled', false).text(originalText);
                }
            },
            error: function(xhr, status, error) {
                showError('An error occurred. Please try again.', 'delete');
                console.error("AJAX Error:", status, error);
                $button.prop('disabled', false).text(originalText);
            }
        });
    });

    /**
     * Add to pantry button click handler
     */
    $('#addToPantryBtn').on('click', function() {
        addToPantryModal.show();
    });

    /**
     * Confirm add to pantry button click handler
     */
    $('#confirmAddToPantryBtn').on('click', function() {
        const formData = $('#addToPantryForm').serialize();
        const $button = $(this);
        const originalText = $button.text();
        
        // Disable button during submission
        $button.prop('disabled', true).text('Processing...');
        
        $.ajax({
            url: 'index.php?action=shopping_list&subaction=addToPantry',
            method: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    addToPantryModal.hide();
                    
                    // Create a custom alert with more style
                    createCustomAlert('Success!', 'Purchased items added to your pantry!', 'success');
                } else {
                    showError(response.message || 'Failed to add items to pantry', 'pantry');
                }
            },
            error: function(xhr, status, error) {
                showError('An error occurred. Please try again.', 'pantry');
                console.error("AJAX Error:", status, error);
            },
            complete: function() {
                // Re-enable button
                $button.prop('disabled', false).text(originalText);
            }
        });
    });

    /**
     * Initialize ingredient search autocomplete
     */
    function initializeAutocomplete() {
        $('#ingredientSearch').autocomplete({
            source: function(request, response) {
                // This will be populated with ingredient data from PHP
                const ingredients = window.ingredientData || [];
                const term = request.term.toLowerCase();
                const matches = [];
                
                // Filter ingredients based on search term
                for (let i = 0; i < ingredients.length; i++) {
                    if (ingredients[i].name.toLowerCase().indexOf(term) !== -1) {
                        matches.push({
                            label: ingredients[i].name,
                            value: ingredients[i].name,
                            id: ingredients[i].id
                        });
                    }
                    
                    // Limit results for performance
                    if (matches.length >= 10) break;
                }
                
                response(matches);
            },
            minLength: 1,
            select: function(event, ui) {
                $('#ingredientId').val(ui.item.id);
            },
            response: function(event, ui) {
                // If no results, clear the hidden field
                if (ui.content.length === 0) {
                    $('#ingredientId').val('');
                }
            },
            close: function(event, ui) {
                // If input cleared, clear the hidden field too
                if (!$('#ingredientSearch').val()) {
                    $('#ingredientId').val('');
                }
            }
        });
    }

    /**
     * Add a new item to the shopping list in the UI
     */
    function addItemToList(item) {
        const itemHtml = `
            <div class="card shopping-list-item" data-item-id="${item.id}">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-1">
                            <label class="checkbox-container">
                                <input type="checkbox" class="purchase-checkbox">
                                <span class="checkmark"></span>
                            </label>
                        </div>
                        <div class="col-md-5">
                            <h5 class="card-title ingredient-name">${item.ingredient_name}</h5>
                        </div>
                        <div class="col-md-3">
                            <p class="card-text">${parseFloat(item.quantity).toFixed(2)} ${item.measurement_name}</p>
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
        `;
        
        // If this is the first item, remove the empty list message and create the list container
        if ($('#itemsList').length === 0) {
            $('.card-body').eq(1).html('<div id="itemsList"></div>');
        } else if ($('#itemsList .alert').length > 0) {
            $('#itemsList').empty();
        }
        
        // Add the new item with animation
        const $newItem = $(itemHtml).hide();
        $('#itemsList').append($newItem);
        $newItem.fadeIn(300);
        
        // Show success message
        showToast('Item added successfully');
    }

    /**
     * Reset the add item form
     */
    function resetAddItemForm() {
        $('#ingredientSearch').val('');
        $('#ingredientId').val('');
        $('#quantity').val('1');
        $('#measurementId').val('');
    }

    /**
     * Validate the add item form
     * @returns {boolean} True if valid, false otherwise
     */
    function validateAddItemForm() {
        // Check if an ingredient is selected
        if (!$('#ingredientId').val()) {
            showError('Please select a valid ingredient from the dropdown');
            return false;
        }
        
        // Check quantity
        if (!$('#quantity').val() || parseFloat($('#quantity').val()) <= 0) {
            showError('Please enter a valid quantity');
            return false;
        }
        
        // Check measurement
        if (!$('#measurementId').val()) {
            showError('Please select a measurement unit');
            return false;
        }
        
        return true;
    }

    /**
     * Show an error message
     * @param {string} message - The error message to show
     * @param {string} context - Optional context for where to show the error
     */
    function showError(message, context = 'add') {
        let $container;
        let errorId;
        
        switch (context) {
            case 'edit':
                $container = $('#editItemForm');
                errorId = 'editItemError';
                break;
            case 'pantry':
                $container = $('#addToPantryForm');
                errorId = 'addToPantryError';
                break;
            case 'delete':
                $container = $('#deleteListModal .modal-body');
                errorId = 'deleteListError';
                break;
            case 'remove':
                $container = $('#removeItemModal .modal-body');
                errorId = 'removeItemError';
                break;
            default:
                $container = $('#addItemForm');
                errorId = 'addItemError';
        }
        
        // Check if error element already exists
        let $errorEl = $('#' + errorId);
        
        if ($errorEl.length === 0) {
            // Create error element if it doesn't exist
            $errorEl = $('<div id="' + errorId + '" class="alert alert-danger mt-2"></div>');
            $container.append($errorEl);
        }
        
        // Set error message
        $errorEl.html('<i class="fas fa-exclamation-circle"></i> ' + message);
        
        // Auto-hide after 4 seconds
        setTimeout(function() {
            $errorEl.fadeOut('slow', function() {
                $(this).remove();
            });
        }, 4000);
    }

    /**
     * Show a toast notification
     * @param {string} message - The message to show
     */
    function showToast(message) {
        // Create toast container if it doesn't exist
        if ($('#toastContainer').length === 0) {
            $('body').append('<div id="toastContainer" style="position: fixed; top: 20px; right: 20px; z-index: 10500;"></div>');
        }
        
        // Create toast
        const toastId = 'toast-' + Date.now();
        const toastHtml = `
            <div                 id="${toastId}" class="toast" role="alert" aria-live="assertive" aria-atomic="true" style="min-width: 250px; background-color: #fff; border-left: 4px solid #A8B545; margin-bottom: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                <div class="toast-header" style="background-color: #f8f9fa; border: none;">
                    <strong class="me-auto" style="color: #34554a;"><i class="fas fa-check-circle" style="color: #A8B545;"></i> Success</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
                <div class="toast-body">
                    ${message}
                </div>
            </div>
        `;
        
        // Add toast to container
        $('#toastContainer').append(toastHtml);
        
        // Initialize and show toast
        const toastElement = document.getElementById(toastId);
        const toast = new bootstrap.Toast(toastElement, { delay: 3000 });
        toast.show();
        
        // Remove toast from DOM after it's hidden
        $(toastElement).on('hidden.bs.toast', function() {
            $(this).remove();
        });
    }

    /**
     * Create a custom alert dialog
     * @param {string} title - The alert title
     * @param {string} message - The alert message
     * @param {string} type - The alert type (success, info, warning, danger)
     */
    function createCustomAlert(title, message, type = 'info') {
        // Define color schemes for different alert types
        const colors = {
            success: { bgColor: '#A8B545', icon: 'fas fa-check-circle' },
            info: { bgColor: '#17a2b8', icon: 'fas fa-info-circle' },
            warning: { bgColor: '#ffc107', icon: 'fas fa-exclamation-triangle' },
            danger: { bgColor: '#dc3545', icon: 'fas fa-exclamation-circle' }
        };
        
        // Get color scheme for the specified type
        const color = colors[type] || colors.info;
        
        // Create alert container if it doesn't exist
        if ($('#customAlertContainer').length === 0) {
            $('body').append(`
                <div id="customAlertContainer" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); display: flex; justify-content: center; align-items: center; z-index: 10600; display: none;">
                    <div class="custom-alert" style="background-color: white; border-radius: 10px; padding: 20px; max-width: 400px; width: 90%; box-shadow: 0 5px 30px rgba(0,0,0,0.3); text-align: center;">
                        <div class="alert-icon" style="margin-bottom: 15px; font-size: 40px;"></div>
                        <h4 class="alert-title" style="margin-bottom: 10px;"></h4>
                        <p class="alert-message" style="margin-bottom: 20px;"></p>
                        <button class="btn" style="min-width: 100px;">OK</button>
                    </div>
                </div>
            `);
        }
        
        // Set alert content
        $('#customAlertContainer .alert-icon').html(`<i class="${color.icon}" style="color: ${color.bgColor};"></i>`);
        $('#customAlertContainer .alert-title').text(title);
        $('#customAlertContainer .alert-message').text(message);
        $('#customAlertContainer .btn').addClass(`btn-${type}`).css('background-color', color.bgColor);
        
        // Show alert with animation
        $('#customAlertContainer').fadeIn(300);
        
        // Close alert on button click
        $('#customAlertContainer .btn').off('click').on('click', function() {
            $('#customAlertContainer').fadeOut(300);
        });
        
        // Close alert when clicking outside
        $('#customAlertContainer').off('click').on('click', function(e) {
            if (e.target === this) {
                $(this).fadeOut(300);
            }
        });
    }
});