class RecipeManager {
    constructor() {
        this.initializeDropdowns();
        this.initializeDropdownSearch();
        this.initializeFavoriteButtons();
    }

    initializeDropdowns() {
        const dropdownContainers = document.querySelectorAll('.dropdown-container');

        dropdownContainers.forEach((dropdown) => {
            const button = dropdown.querySelector('.dropdown-button');
            const dropdownContent = dropdown.querySelector('.dropdown-content');

            // Toggle dropdown on button click
            button.addEventListener('click', (e) => {
                e.preventDefault();
                this.closeAllDropdowns(dropdown);
                dropdown.classList.toggle('active');
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', (e) => {
                if (!dropdown.contains(e.target)) {
                    dropdown.classList.remove('active');
                    this.resetSearch(dropdownContent);
                }
            });
        });
    }

    closeAllDropdowns(except) {
        const dropdownContainers = document.querySelectorAll('.dropdown-container');
        dropdownContainers.forEach((dropdown) => {
            if (dropdown !== except) {
                dropdown.classList.remove('active');
                const dropdownContent = dropdown.querySelector('.dropdown-content');
                this.resetSearch(dropdownContent);
            }
        });
    }

    initializeDropdownSearch() {
        const dropdownSearchInputs = document.querySelectorAll('.dropdown-search input');

        dropdownSearchInputs.forEach((searchInput) => {
            const dropdownContent = searchInput.closest('.dropdown-content');
            const checkboxOptions = dropdownContent.querySelectorAll('.checkbox-option');
            const noResultsMsg = this.getOrCreateNoResultsMessage(dropdownContent);

            // Filter options as user types
            searchInput.addEventListener('input', () => {
                const query = searchInput.value.toLowerCase().trim();
                let hasVisibleOptions = false;

                checkboxOptions.forEach((option) => {
                    const optionText = option.textContent.toLowerCase();
                    const isVisible = optionText.includes(query);
                    option.style.display = isVisible ? '' : 'none';
                    hasVisibleOptions = hasVisibleOptions || isVisible;
                });

                noResultsMsg.style.display = hasVisibleOptions ? 'none' : 'block';
            });

            // Add clear button functionality
            const clearButton = this.getOrCreateClearButton(searchInput, checkboxOptions, noResultsMsg);
            searchInput.parentNode.insertBefore(clearButton, searchInput.nextSibling);
        });
    }

    getOrCreateNoResultsMessage(dropdownContent) {
        let noResultsMsg = dropdownContent.querySelector('.no-results');
        if (!noResultsMsg) {
            noResultsMsg = document.createElement('div');
            noResultsMsg.className = 'no-results';
            noResultsMsg.textContent = 'No matches found';
            noResultsMsg.style.display = 'none';
            dropdownContent.appendChild(noResultsMsg);
        }
        return noResultsMsg;
    }

    getOrCreateClearButton(searchInput, checkboxOptions, noResultsMsg) {
        let clearButton = searchInput.parentNode.querySelector('.clear-search');
        if (!clearButton) {
            clearButton = document.createElement('button');
            clearButton.type = 'button';
            clearButton.className = 'clear-search';
            clearButton.textContent = '×';
            clearButton.style.display = 'none';

            clearButton.addEventListener('click', () => {
                this.resetSearch(searchInput.closest('.dropdown-content'), searchInput, checkboxOptions, noResultsMsg);
            });

            // Toggle clear button visibility
            searchInput.addEventListener('input', () => {
                clearButton.style.display = searchInput.value ? 'inline-block' : 'none';
            });
        }
        return clearButton;
    }

    resetSearch(dropdownContent, searchInput = null, checkboxOptions = null, noResultsMsg = null) {
        if (searchInput) searchInput.value = '';
        if (checkboxOptions) {
            checkboxOptions.forEach((option) => (option.style.display = ''));
        }
        if (noResultsMsg) noResultsMsg.style.display = 'none';
    }

    initializeFavoriteButtons() {
        const favoriteForms = document.querySelectorAll('.favorite-form');
        
        favoriteForms.forEach(form => {
            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                const button = form.querySelector('.favorite-btn');
                const recipeId = form.querySelector('input[name="recipe_id"]').value;
                
                try {
                    const formData = new FormData();
                    formData.append('recipe_id', recipeId);
                    
                    const response = await fetch('index.php?action=favorite', {
                        method: 'POST',
                        body: formData
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        // Update button text based on favorite status
                        button.innerHTML = data.isFavorited ? '💔 Unfavorite' : '❤️ Favorite';
                    } else if (data.error === 'Not logged in') {
                        // Redirect to login page or show login prompt
                        alert('Please log in to favorite recipes');
                        // Optional: window.location.href = 'index.php?action=login';
                    } else {
                        console.error('Error updating favorite status');
                    }
                } catch (error) {
                    console.error('Error:', error);
                }
            });
        });
    }

    
}    

document.addEventListener('DOMContentLoaded', () => {
    new RecipeManager();
});


