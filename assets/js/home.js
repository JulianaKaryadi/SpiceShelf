class RecipeManager {
    constructor() {
        this.initializeFavorites();
    }

    initializeFavorites() {
        document.addEventListener('click', async (e) => {
            if (e.target.matches('.favorite-btn')) {
                e.preventDefault();
                const form = e.target.closest('form');
                const recipeId = form.querySelector('input[name="recipe_id"]').value;
                await this.toggleFavorite(form, recipeId, e.target);
            }
        });
    }

    async toggleFavorite(form, recipeId, button) {
        try {
            const formData = new FormData(form);
            
            const response = await fetch('index.php?action=favorite', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                button.innerHTML = data.isFavorited ? '💔 Unfavorite' : '❤️ Favorite';
                await this.refreshRecipeSections();
            } else {
                if (data.error === 'Not logged in') {
                    alert('Please log in to favorite recipes');
                }
            }
        } catch (error) {
            console.error('Error:', error);
        }
    }

    async refreshRecipeSections() {
        try {
            const response = await fetch('index.php?action=refresh_home_sections');
            const data = await response.json();
            
            if (data.success) {
                // Use the correct IDs from home.php
                const userRecipesSection = document.getElementById('user-recipes');
                const favoriteRecipesSection = document.getElementById('favorite-recipes');
                
                if (userRecipesSection && data.userRecipesHtml) {
                    userRecipesSection.innerHTML = data.userRecipesHtml;
                }
                if (favoriteRecipesSection && data.favoriteRecipesHtml) {
                    favoriteRecipesSection.innerHTML = data.favoriteRecipesHtml;
                }
            }
        } catch (error) {
            console.error('Error refreshing sections:', error);
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    new RecipeManager();
});