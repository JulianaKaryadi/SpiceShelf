/**
 * Profile Page JavaScript Functionality
 * Handles form validation and submission
 */
$(document).ready(function() {
    // Form validation
    const $profileForm = $('form');
    
    $profileForm.on('submit', function(e) {
        // Remove any existing status messages
        $('.status-message').remove();
        
        // Get form values
        const username = $('#username').val().trim();
        const email = $('#email').val().trim();
        const password = $('#password').val();
        
        // Basic validation
        let isValid = true;
        let errors = [];
        
        // Username validation
        if (username === '') {
            errors.push('Username is required');
            highlightField('#username');
            isValid = false;
        } else if (username.length < 3) {
            errors.push('Username must be at least 3 characters');
            highlightField('#username');
            isValid = false;
        }
        
        // Email validation
        if (email === '') {
            errors.push('Email is required');
            highlightField('#email');
            isValid = false;
        } else if (!isValidEmail(email)) {
            errors.push('Please enter a valid email address');
            highlightField('#email');
            isValid = false;
        }
        
        // Password validation - only if they're trying to change it
        if (password !== '' && password.length < 8) {
            errors.push('Password must be at least 8 characters');
            highlightField('#password');
            isValid = false;
        }
        
        // If validation fails, prevent form submission and show errors
        if (!isValid) {
            e.preventDefault();
            
            // Create error message container
            const $errorMessage = $('<div class="status-message error"></div>');
            
            // Add each error as a paragraph
            $.each(errors, function(index, error) {
                $errorMessage.append($('<p></p>').text(error));
            });
            
            // Add error message to the top of the form
            $profileForm.before($errorMessage);
            
            // Scroll to error message
            $('html, body').animate({
                scrollTop: $errorMessage.offset().top - 20
            }, 300);
            
            return false;
        }
        
        // If validation passes, show loading state on button
        const $submitButton = $('button[type="submit"]');
        const originalButtonText = $submitButton.text();
        $submitButton.prop('disabled', true).text('Saving...');
        
        // Note: Form will be submitted normally since we're not preventing the default action here
    });
    
    /**
     * Validate email format
     * @param {string} email - Email to validate
     * @returns {boolean} True if valid, false otherwise
     */
    function isValidEmail(email) {
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailPattern.test(email);
    }
    
    /**
     * Highlight a field to indicate validation error
     * @param {string} selector - CSS selector for the field
     */
    function highlightField(selector) {
        const $field = $(selector);
        
        // Add error styling
        $field.css('border-color', '#dc3545');
        
        // Remove error styling when field is edited
        $field.one('input', function() {
            $(this).css('border-color', '');
        });
    }
    
    /**
     * Toggle select/deselect all checkboxes in a container
     */
    $('.select-all').on('click', function() {
        const targetSelector = $(this).data('target');
        const isChecked = $(this).prop('checked');
        
        $(targetSelector).find('input[type="checkbox"]').prop('checked', isChecked);
    });
    
    /**
     * Update the counter for selected items
     * @param {string} containerSelector - CSS selector for the checkbox container
     * @param {string} counterSelector - CSS selector for the counter element
     */
    function updateSelectionCounter(containerSelector, counterSelector) {
        const totalChecked = $(containerSelector + ' input:checked').length;
        const totalOptions = $(containerSelector + ' input[type="checkbox"]').length;
        
        $(counterSelector).text(totalChecked + ' of ' + totalOptions + ' selected');
    }
    
    // Initialize counters
    $('.checkbox-container').each(function() {
        const containerId = $(this).attr('id');
        
        if (containerId) {
            const counterId = '#' + containerId + '-count';
            
            // Update counter when checkbox state changes
            $(this).on('change', 'input[type="checkbox"]', function() {
                updateSelectionCounter('#' + containerId, counterId);
            });
            
            // Initial counter update
            updateSelectionCounter('#' + containerId, counterId);
        }
    });
    
    // Show success message if it exists in URL parameters
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('success')) {
        const successMessage = urlParams.get('message') || 'Profile updated successfully!';
        
        const $successMessage = $('<div class="status-message success"></div>').text(successMessage);
        $profileForm.before($successMessage);
        
        // Auto-hide success message after 5 seconds
        setTimeout(function() {
            $successMessage.fadeOut('slow', function() {
                $(this).remove();
            });
        }, 5000);
    }
    
    // Show error message if it exists in URL parameters
    if (urlParams.has('error')) {
        const errorMessage = urlParams.get('message') || 'An error occurred. Please try again.';
        
        const $errorMessage = $('<div class="status-message error"></div>').text(errorMessage);
        $profileForm.before($errorMessage);
    }
    
    /**
     * Search functionality for preferences and allergies
     * @param {string} inputSelector - CSS selector for the search input
     * @param {string} containerSelector - CSS selector for the checkbox container
     */
    function initializeSearch(inputSelector, containerSelector) {
        $(inputSelector).on('input', function() {
            const searchTerm = $(this).val().toLowerCase();
            
            $(containerSelector + ' .checkbox-option').each(function() {
                const optionText = $(this).text().toLowerCase();
                
                if (optionText.includes(searchTerm) || searchTerm === '') {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });
        
        // Add clear search button functionality
        $(inputSelector + '-clear').on('click', function() {
            $(inputSelector).val('').trigger('input');
            $(this).hide();
        });
        
        // Show/hide clear button based on input content
        $(inputSelector).on('input', function() {
            if ($(this).val() === '') {
                $(inputSelector + '-clear').hide();
            } else {
                $(inputSelector + '-clear').show();
            }
        });
    }
    
    // Initialize search if search inputs exist
    if ($('#preferences-search').length) {
        initializeSearch('#preferences-search', '#preferences-container');
    }
    
    if ($('#allergies-search').length) {
        initializeSearch('#allergies-search', '#allergies-container');
    }
});