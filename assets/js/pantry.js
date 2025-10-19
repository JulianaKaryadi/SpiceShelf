document.addEventListener('DOMContentLoaded', function() {
    // Mode switching functionality
    const tabButtons = document.querySelectorAll('.tab-button');
    const modeContents = document.querySelectorAll('.mode-content');
    
    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            const mode = this.getAttribute('data-mode');
            switchMode(mode);
        });
    });
    
    function switchMode(mode) {
        // Update tab buttons
        tabButtons.forEach(btn => btn.classList.remove('active'));
        document.querySelector(`[data-mode="${mode}"]`).classList.add('active');
        
        // Update content sections
        modeContents.forEach(content => content.classList.remove('active'));
        document.getElementById(`${mode}-mode`).classList.add('active');
        
        // Clear checkboxes when switching modes
        document.querySelectorAll('.item-checkbox').forEach(checkbox => {
            checkbox.checked = false;
        });
        
        // Update counters
        updateSelectedIngredientsCount();
        updateDeleteButtonState();
        
        // Update select all checkbox
        const selectAllCheckbox = document.getElementById('selectAll');
        if (selectAllCheckbox) {
            selectAllCheckbox.checked = false;
        }
    }

    // Add dismiss functionality to notifications
    const notifications = document.querySelectorAll('.notification');
    notifications.forEach(notification => {
        // Check if dismiss button already exists to avoid duplicates
        if (notification.querySelector('.notification-dismiss')) {
            return;
        }

        // Make notification position relative for absolute positioning of dismiss button
        notification.style.position = 'relative';
        notification.style.paddingRight = '45px'; // Add space for dismiss button
        
        // Add dismiss button
        const dismissBtn = document.createElement('button');
        dismissBtn.innerHTML = '×';
        dismissBtn.className = 'notification-dismiss';
        dismissBtn.setAttribute('type', 'button'); // Prevent form submission
        dismissBtn.style.cssText = `
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: transparent;
            border: none;
            color: inherit;
            font-size: 1.5rem;
            cursor: pointer;
            padding: 5px;
            opacity: 0.7;
            transition: opacity 0.2s ease;
            z-index: 10;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        `;
        notification.appendChild(dismissBtn);

        // Dismiss handler with multiple safeguards
        dismissBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            
            console.log('Notification dismiss button clicked'); // Debug log
            
            // Add dismissing class for smooth animation
            notification.classList.add('dismissing');
            notification.style.opacity = '0';
            notification.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
            notification.style.transform = 'translateX(100%)';
            
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);
        }, true); // Use capture to ensure it fires first
        
        dismissBtn.addEventListener('mouseenter', () => {
            dismissBtn.style.opacity = '1';
            dismissBtn.style.background = 'rgba(0, 0, 0, 0.1)';
        });
        
        dismissBtn.addEventListener('mouseleave', () => {
            dismissBtn.style.opacity = '0.7';
            dismissBtn.style.background = 'transparent';
        });
    });

    // Recipe generation functionality
    const generateRecipeBtn = document.getElementById('generateRecipeBtn');
    const mealTypeSelect = document.getElementById('mealType');
    const ingredientSelectionRadios = document.querySelectorAll('input[name="ingredient_selection"]');
    const itemCheckboxes = document.querySelectorAll('.item-checkbox');
    const selectedIngredientsInfo = document.getElementById('selectedIngredientsInfo');
    const selectedIngredientsCount = document.getElementById('selectedIngredientsCount');
    
    // Check if there are any pantry items
    const pantryRows = document.querySelectorAll('tbody tr:not(.empty-row)');
    
    // Enable the button if we have actual ingredient rows
    if (generateRecipeBtn && pantryRows.length > 0) {
        generateRecipeBtn.classList.remove('inactive');
        
        generateRecipeBtn.addEventListener('click', function(e) {
            e.preventDefault();
            generateRecipe();
        });
    }

    // Handle ingredient selection mode change
    ingredientSelectionRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            updateIngredientSelectionMode();
        });
    });

    // Handle individual checkbox changes
    itemCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateSelectedIngredientsCount();
        });
    });

    function updateIngredientSelectionMode() {
        const selectedMode = document.querySelector('input[name="ingredient_selection"]:checked').value;
        
        // Clear all checkboxes when switching modes
        itemCheckboxes.forEach(checkbox => {
            checkbox.checked = false;
        });
        
        if (selectedMode === 'selected') {
            selectedIngredientsInfo.style.display = 'flex';
        } else {
            selectedIngredientsInfo.style.display = 'none';
        }
        
        updateSelectedIngredientsCount();
    }

    function updateSelectedIngredientsCount() {
        const selectedMode = document.querySelector('input[name="ingredient_selection"]:checked');
        if (selectedMode && selectedMode.value === 'selected') {
            // Count non-expired selected items
            const selectedCount = Array.from(itemCheckboxes).filter(checkbox => 
                checkbox.checked && checkbox.getAttribute('data-expired') !== 'true'
            ).length;
            
            selectedIngredientsCount.textContent = selectedCount;
            
            // Update generate button state
            if (generateRecipeBtn) {
                if (selectedCount === 0) {
                    generateRecipeBtn.disabled = true;
                    generateRecipeBtn.style.opacity = '0.5';
                } else {
                    generateRecipeBtn.disabled = false;
                    generateRecipeBtn.style.opacity = '1';
                }
            }
        } else {
            // Reset generate button for "all" mode
            if (generateRecipeBtn) {
                generateRecipeBtn.disabled = false;
                generateRecipeBtn.style.opacity = '1';
            }
        }
    }

    // Store the current recipe text globally to access it when saving
    let currentRecipeText = '';

    function generateRecipe() {
        // Get selected meal type
        const mealType = mealTypeSelect ? mealTypeSelect.value : '';
        
        // Get ingredient selection mode
        const selectedMode = document.querySelector('input[name="ingredient_selection"]:checked').value;
        
        // Get selected ingredients if in 'selected' mode
        let selectedIngredients = [];
        if (selectedMode === 'selected') {
            const selectedCheckboxes = Array.from(itemCheckboxes).filter(checkbox => 
                checkbox.checked && checkbox.getAttribute('data-expired') !== 'true'
            );
            
            if (selectedCheckboxes.length === 0) {
                alert('Please select ingredients for recipe generation');
                return;
            }
            
            selectedCheckboxes.forEach(checkbox => {
                const ingredientData = JSON.parse(checkbox.getAttribute('data-ingredient'));
                selectedIngredients.push(ingredientData);
            });
        }
        
        // Show loading state
        showGeneratingModal(mealType, selectedMode, selectedIngredients.length);

        // Prepare URL and data
        let url = 'api/generate_recipe.php';
        const requestData = {
            meal_type: mealType,
            ingredient_selection: selectedMode,
            selected_ingredients: selectedIngredients
        };

        // Fetch recipe from API
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(requestData)
        })
            .then(response => response.json())
            .then(data => {
                hideGeneratingModal();
                
                if (data.success) {
                    // Store recipe text and full data for later use
                    currentRecipeText = data.recipe;
                    window.lastRecipeData = data;
                    
                    // Show warning if expired items were excluded
                    if (data.warning) {
                        showWarningMessage(data.warning);
                    }
                    
                    showRecipeModal(data.recipe, mealType, selectedMode, selectedIngredients.length);
                } else {
                    // Check if error is due to all items being expired
                    if (data.expired_count && data.expired_count > 0) {
                        showExpiredItemsError(data.error, data.expired_count);
                    } else {
                        showErrorModal(data.error || 'An error occurred while generating the recipe.');
                    }
                }
            })
            .catch(error => {
                hideGeneratingModal();
                showErrorModal('Network error: Could not connect to the server.');
                console.error('Error:', error);
            });
    }

    function showGeneratingModal(mealType, selectionMode, selectedCount) {
        // Create modal if it doesn't exist
        let modal = document.getElementById('recipe-modal');
        if (!modal) {
            modal = document.createElement('div');
            modal.id = 'recipe-modal';
            modal.className = 'modal';
            document.body.appendChild(modal);
        }

        // Generate selection mode specific message
        let selectionMessage = '';
        if (selectionMode === 'selected') {
            selectionMessage = `Using ${selectedCount} selected ingredient${selectedCount !== 1 ? 's' : ''}`;
        } else {
            selectionMessage = 'Using all available pantry ingredients';
        }

        // Generate meal type specific message
        let mealMessage = mealType ? ` for ${mealType}` : '';
        let loadingText = `Please wait while we generate a delicious ${mealType || 'recipe'}${mealMessage}...`;

        // Set loading content
        modal.innerHTML = `
            <div class="modal-content">
                <div class="modal-header">
                    <h2>🤖 Generating ${mealType ? mealType.charAt(0).toUpperCase() + mealType.slice(1) : 'Recipe'}</h2>
                </div>
                <div class="modal-body text-center">
                    <div class="loading-spinner"></div>
                    <p>${loadingText}</p>
                    <p class="selection-mode-indicator">📋 ${selectionMessage}</p>
                    ${mealType ? `<p class="meal-type-indicator">🍽️ <strong>Meal Type:</strong> ${mealType.charAt(0).toUpperCase() + mealType.slice(1)}</p>` : ''}
                </div>
            </div>
        `;

        // Show modal
        modal.style.display = 'flex';
    }

    function hideGeneratingModal() {
        const modal = document.getElementById('recipe-modal');
        if (modal) {
            modal.style.display = 'none';
        }
    }

    function showRecipeModal(recipeText, mealType, selectionMode, selectedCount) {
        // Format recipe text (replace newlines with <br>)
        const formattedRecipe = recipeText.replace(/\n/g, '<br>');

        // Create modal if it doesn't exist
        let modal = document.getElementById('recipe-modal');
        if (!modal) {
            modal = document.createElement('div');
            modal.id = 'recipe-modal';
            modal.className = 'modal';
            document.body.appendChild(modal);
        }

        // Check if prioritized ingredients are available
        const prioritizedIngredientsHTML = window.lastRecipeData && 
                                          window.lastRecipeData.prioritized_ingredients && 
                                          window.lastRecipeData.prioritized_ingredients.length > 0 
            ? `
                <div class="prioritized-ingredients">
                    <h3>🔥 Prioritized Ingredients (Expiring Soon)</h3>
                    <ul>
                        ${window.lastRecipeData.prioritized_ingredients.map(ingredient => 
                            `<li>${ingredient}</li>`).join('')}
                    </ul>
                </div>
            ` 
            : '';

        // Generate meal type badge
        const mealTypeBadge = mealType ? `
            <div class="meal-type-badge">
                <span class="meal-icon">${getMealIcon(mealType)}</span>
                <span class="meal-text">${mealType.charAt(0).toUpperCase() + mealType.slice(1)} Recipe</span>
            </div>
        ` : '';

        // Generate selection info badge
        const selectionBadge = selectionMode === 'selected' ? `
            <div class="selection-info-badge">
                <span class="selection-icon">🎯</span>
                <span class="selection-text">Using ${selectedCount} selected ingredient${selectedCount !== 1 ? 's' : ''}</span>
            </div>
        ` : '';

        // Set recipe content
        modal.innerHTML = `
            <div class="modal-content">
                <div class="modal-header">
                    <h2>🤖 AI Generated Recipe</h2>
                    <span class="close">&times;</span>
                </div>
                <div class="modal-body recipe-content">
                    <div class="recipe-badges">
                        ${mealTypeBadge}
                        ${selectionBadge}
                    </div>
                    ${prioritizedIngredientsHTML}
                    ${formattedRecipe}
                </div>
                <div class="modal-footer">
                    <button id="download-recipe-pdf" class="button primary">📄 Download as PDF</button>
                    <button id="regenerate-recipe" class="button secondary">🔄 Generate Another</button>
                    <button id="close-modal" class="button">Close</button>
                </div>
            </div>
        `;

        // Show modal
        modal.style.display = 'flex';

        // Add event listeners
        document.querySelector('#recipe-modal .close').addEventListener('click', () => {
            modal.style.display = 'none';
        });

        document.getElementById('close-modal').addEventListener('click', () => {
            modal.style.display = 'none';
        });

        // Add regenerate functionality
        document.getElementById('regenerate-recipe').addEventListener('click', () => {
            modal.style.display = 'none';
            generateRecipe(); // Generate another recipe with same settings
        });

        // Add download PDF functionality
        document.getElementById('download-recipe-pdf').addEventListener('click', () => {
            downloadRecipeAsPDF(currentRecipeText);
        });
    }

    function getMealIcon(mealType) {
        const icons = {
            'breakfast': '🌅',
            'lunch': '🌞', 
            'dinner': '🌙',
            'snack': '🍿',
            'appetizer': '🥗',
            'dessert': '🍰',
            'side_dish': '🥖',
            'beverage': '🥤'
        };
        return icons[mealType] || '🍽️';
    }

    function downloadRecipeAsPDF(recipeText) {
        // Show saving/downloading indicator
        const downloadBtn = document.getElementById('download-recipe-pdf');
        const originalText = downloadBtn.textContent;
        downloadBtn.textContent = 'Generating PDF...';
        downloadBtn.disabled = true;
        
        // Create form data
        const formData = new FormData();
        formData.append('recipe_text', recipeText);
        
        // Send request to generate PDF
        fetch('api/download_recipe_pdf.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            downloadBtn.disabled = false;
            
            if (data.success) {
                // Create temporary link and trigger download
                const downloadLink = document.createElement('a');
                downloadLink.href = data.download_url;
                downloadLink.target = '_blank';
                downloadLink.download = data.filename;
                document.body.appendChild(downloadLink);
                downloadLink.click();
                document.body.removeChild(downloadLink);
                
                // Restore button text
                downloadBtn.textContent = 'Download Complete!';
                setTimeout(() => {
                    downloadBtn.textContent = originalText;
                }, 2000);
            } else {
                // Show error message
                alert('Error generating PDF: ' + (data.error || 'Unknown error'));
                downloadBtn.textContent = originalText;
            }
        })
        .catch(error => {
            downloadBtn.disabled = false;
            downloadBtn.textContent = originalText;
            alert('Network error: Could not generate PDF.');
            console.error('Error:', error);
        });
    }

    function showErrorModal(errorMessage) {
        // Create modal if it doesn't exist
        let modal = document.getElementById('recipe-modal');
        if (!modal) {
            modal = document.createElement('div');
            modal.id = 'recipe-modal';
            modal.className = 'modal';
            document.body.appendChild(modal);
        }

        // Set error content
        modal.innerHTML = `
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Error</h2>
                    <span class="close">&times;</span>
                </div>
                <div class="modal-body">
                    <p class="error-message">${errorMessage}</p>
                </div>
                <div class="modal-footer">
                    <button id="close-modal" class="button">Close</button>
                </div>
            </div>
        `;

        // Show modal
        modal.style.display = 'flex';

        // Add event listeners
        document.querySelector('#recipe-modal .close').addEventListener('click', () => {
            modal.style.display = 'none';
        });

        document.getElementById('close-modal').addEventListener('click', () => {
            modal.style.display = 'none';
        });
    }

    function showWarningMessage(message) {
        // Create a warning notification
        const warningDiv = document.createElement('div');
        warningDiv.className = 'notification warning';
        warningDiv.style.cssText = `
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        `;
        warningDiv.innerHTML = `
            <span class="warning-icon">⚠️</span>
            <span class="warning-text">${message}</span>
            <span class="dismiss" style="cursor: pointer; font-size: 1.2rem;">&times;</span>
        `;
        
        // Insert at the top of the container
        const container = document.querySelector('.container');
        container.insertBefore(warningDiv, container.firstChild);
        
        // Auto-dismiss after 10 seconds
        setTimeout(() => {
            if (warningDiv.parentNode) {
                warningDiv.remove();
            }
        }, 10000);
        
        // Add dismiss functionality
        warningDiv.querySelector('.dismiss').addEventListener('click', () => {
            warningDiv.remove();
        });
    }

    function showExpiredItemsError(message, expiredCount) {
        const errorModal = document.createElement('div');
        errorModal.className = 'modal error-modal';
        errorModal.innerHTML = `
            <div class="modal-content">
                <div class="modal-header">
                    <h2>❌ Cannot Generate Recipe</h2>
                    <span class="close">&times;</span>
                </div>
                <div class="modal-body">
                    <p>${message}</p>
                    <p><strong>Suggestion:</strong> Please clean out expired items from your pantry and add fresh ingredients.</p>
                    <div class="expired-actions">
                        <button id="view-expired-items" class="button secondary">View Expired Items</button>
                        <button id="add-fresh-items" class="button primary">Add Fresh Ingredients</button>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(errorModal);
        errorModal.style.display = 'flex';
        
        // Add event listeners
        errorModal.querySelector('.close').addEventListener('click', () => {
            errorModal.remove();
        });
        
        errorModal.querySelector('#view-expired-items').addEventListener('click', () => {
            // Highlight expired items in the table
            highlightExpiredItems();
            errorModal.remove();
        });
        
        errorModal.querySelector('#add-fresh-items').addEventListener('click', () => {
            // Redirect to add pantry item page
            window.location.href = 'index.php?action=add';
        });
    }

    function highlightExpiredItems() {
        // Add special highlighting to expired rows
        const expiredRows = document.querySelectorAll('tr.expired');
        expiredRows.forEach(row => {
            row.style.animation = 'pulse 2s infinite';
            row.style.backgroundColor = '#ffebee';
        });
        
        // Scroll to first expired item
        if (expiredRows.length > 0) {
            expiredRows[0].scrollIntoView({ behavior: 'smooth' });
        }
    }

    // Delete functionality
    const selectAllCheckbox = document.getElementById('selectAll');
    const deleteSelectedBtn = document.getElementById('deleteSelectedBtn');
    const deleteConfirmModal = document.getElementById('deleteConfirmModal');
    const selectedItemCount = document.getElementById('selectedItemCount');
    const cancelDeleteBtn = document.getElementById('cancelDelete');
    const closeModalBtn = document.querySelector('#deleteConfirmModal .close');
    
    // Function to update the Delete Selected button state
    function updateDeleteButtonState() {
        const checkedBoxes = document.querySelectorAll('.item-checkbox:checked');
        if (deleteSelectedBtn) {
            deleteSelectedBtn.disabled = checkedBoxes.length === 0;
        }
        
        if (selectedItemCount) {
            selectedItemCount.textContent = checkedBoxes.length;
        }
    }
    
    // Select all checkbox functionality
    if (selectAllCheckbox && itemCheckboxes.length > 0) {
        selectAllCheckbox.addEventListener('change', function() {
            itemCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateDeleteButtonState();
        });
    }
    
    // Individual checkbox functionality for delete state
    itemCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            // Update "select all" checkbox state
            if (selectAllCheckbox) {
                const allChecked = Array.from(itemCheckboxes).every(cb => cb.checked);
                const noneChecked = Array.from(itemCheckboxes).every(cb => !cb.checked);
                selectAllCheckbox.checked = allChecked;
                selectAllCheckbox.indeterminate = !allChecked && !noneChecked;
            }
            updateDeleteButtonState();
        });
    });
    
    // Delete selected button functionality
    if (deleteSelectedBtn) {
        deleteSelectedBtn.addEventListener('click', function(e) {
            e.preventDefault();
            
            const checkedBoxes = document.querySelectorAll('.item-checkbox:checked');
            if (checkedBoxes.length === 0) {
                alert('Please select items to delete.');
                return;
            }
            
            // Show confirmation modal if it exists, otherwise use confirm dialog
            if (deleteConfirmModal) {
                deleteConfirmModal.style.display = 'flex';
            } else {
                if (confirm(`Are you sure you want to delete ${checkedBoxes.length} selected items?`)) {
                    document.getElementById('bulkActionForm').submit();
                }
            }
        });
    }
    
    // Modal close button
    if (closeModalBtn) {
        closeModalBtn.addEventListener('click', function() {
            deleteConfirmModal.style.display = 'none';
        });
    }
    
    // Cancel button in modal
    if (cancelDeleteBtn) {
        cancelDeleteBtn.addEventListener('click', function(e) {
            e.preventDefault();
            deleteConfirmModal.style.display = 'none';
        });
    }
    
    // Click outside modal to close
    if (deleteConfirmModal) {
        window.addEventListener('click', function(event) {
            if (event.target === deleteConfirmModal) {
                deleteConfirmModal.style.display = 'none';
            }
        });
    }

    // Initialize states
    updateIngredientSelectionMode();
    updateDeleteButtonState();
});