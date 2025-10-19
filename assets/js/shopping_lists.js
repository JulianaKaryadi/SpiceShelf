/**
 * Shopping Lists JavaScript functionality
 * Handles the create list modal and AJAX operations
 */
$(document).ready(function() {
    // Initialize bootstrap modal
    const createListModal = new bootstrap.Modal(document.getElementById('createListModal'));
    
    /**
     * Click handler for the create list card
     * Opens the modal for creating a new shopping list
     */
    $('#createListCard').on('click', function() {
        // Clear any previous input
        $('#listName').val('');
        // Show the modal
        createListModal.show();
    });
    
    /**
     * Click handler for the create list button in the modal
     * Handles form submission via AJAX
     */
    $('#createListBtn').on('click', function() {
        const listName = $('#listName').val().trim();
        
        // Validate input
        if (!listName) {
            showError('Please enter a list name');
            return;
        }
        
        // Show loading state
        const $button = $(this);
        const originalText = $button.text();
        $button.prop('disabled', true).text('Creating...');
        
        // Send AJAX request to create list
        $.ajax({
            url: 'index.php?action=shopping_list&subaction=createList',
            method: 'POST',
            data: {
                name: listName
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Redirect to the new list page
                    window.location.href = 'index.php?action=shopping_list&id=' + response.list_id;
                } else {
                    // Show error message
                    showError(response.message || 'Failed to create shopping list');
                    
                    // Reset button state
                    $button.prop('disabled', false).text(originalText);
                }
            },
            error: function(xhr, status, error) {
                // Handle AJAX errors
                showError('An error occurred. Please try again.');
                console.error("AJAX Error:", status, error);
                
                // Reset button state
                $button.prop('disabled', false).text(originalText);
            }
        });
    });
    
    /**
     * Handle Enter key press in the list name field
     */
    $('#listName').on('keypress', function(e) {
        if (e.which === 13) { // Enter key
            e.preventDefault();
            $('#createListBtn').click();
        }
    });
    
    /**
     * Display error messages to the user
     * @param {string} message - The error message to display
     */
    function showError(message) {
        // Check if error element already exists
        let $errorEl = $('#createListError');
        
        if ($errorEl.length === 0) {
            // Create error element if it doesn't exist
            $errorEl = $('<div id="createListError" class="alert alert-danger mt-2"></div>');
            $('#createListForm').append($errorEl);
        }
        
        // Set error message
        $errorEl.text(message);
        
        // Auto-hide after 4 seconds
        setTimeout(function() {
            $errorEl.fadeOut('slow', function() {
                $(this).remove();
            });
        }, 4000);
    }
    
    /**
     * Handle any existing PHP session error messages
     */
    if (typeof phpSessionError !== 'undefined' && phpSessionError) {
        alert(phpSessionError);
    }
});