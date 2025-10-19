<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recipe Chef AI - SpiceShelf</title>
    <link rel="stylesheet" href="assets/css/chat.css">
    <link rel="stylesheet" href="assets/css/recipe-modal.css">
</head>
<body>
    <?php include('includes/header.php'); ?>
    
    <div class="chat-container">
        <div class="chat-header">
            <h1>🤖 Recipe Chef AI</h1>
            <p class="chat-subtitle">Your personal AI cooking assistant - Ask me anything about recipes, cooking tips, or ingredients!</p>
        </div>

        <div class="chat-suggestions" id="chatSuggestions">
            <h3>💡 Try asking me:</h3>
            <div class="suggestion-grid">
                <button class="suggestion-btn" data-prompt="What can I make with chicken, rice, and vegetables?">
                    🍗 Recipe with ingredients
                </button>
                <button class="suggestion-btn" data-prompt="Give me a quick 15-minute dinner idea">
                    ⏰ Quick meal ideas
                </button>
                <button class="suggestion-btn" data-prompt="How do I make homemade pasta from scratch?">
                    🍝 Cooking techniques
                </button>
                <button class="suggestion-btn" data-prompt="What's a good substitute for eggs in baking?">
                    🔄 Ingredient substitutions
                </button>
                <button class="suggestion-btn" data-prompt="Plan a healthy meal prep for the week">
                    📅 Meal planning
                </button>
                <button class="suggestion-btn" data-prompt="Give me a beginner-friendly dessert recipe">
                    🍰 Dessert recipes
                </button>
            </div>
        </div>

        <div class="chat-messages" id="chatMessages">
            <div class="message ai-message">
                <div class="message-avatar">🤖</div>
                <div class="message-content">
                    <p>Hello! I'm your Recipe Chef AI assistant. I can help you with:</p>
                    <ul>
                        <li>🍳 Creating recipes from ingredients you have</li>
                        <li>🔥 Cooking techniques and tips</li>
                        <li>🥗 Ingredient substitutions</li>
                        <li>📝 Meal planning suggestions</li>
                        <li>❓ Any cooking-related questions</li>
                    </ul>
                    <p>What would you like to cook today?</p>
                </div>
            </div>
        </div>

        <div class="chat-input-container">
            <form id="chatForm" class="chat-form">
                <div class="input-wrapper">
                    <textarea 
                        id="chatInput" 
                        placeholder="Ask me about recipes, cooking tips, or ingredients..." 
                        rows="1"
                        maxlength="1000"
                    ></textarea>
                    <button type="submit" id="sendButton" class="send-button" disabled>
                        <span class="send-icon">➤</span>
                    </button>
                </div>
                <div class="input-footer">
                    <span class="char-counter" id="charCounter">0/1000</span>
                    <span class="input-hint">Press Enter to send, Shift+Enter for new line</span>
                </div>
            </form>
        </div>

        <div class="chat-actions">
            <button id="clearChatBtn" class="action-btn secondary">
                🗑️ Clear Chat
            </button>
            <button id="exportChatBtn" class="action-btn primary">
                📄 Export Chat
            </button>
        </div>
    </div>

    <!-- Loading indicator -->
    <div id="typingIndicator" class="typing-indicator" style="display: none;">
        <div class="message ai-message">
            <div class="message-avatar">🤖</div>
            <div class="message-content">
                <div class="typing-dots">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Error modal -->
    <div id="errorModal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Error</h2>
                <span class="close">&times;</span>
            </div>
            <div class="modal-body">
                <p id="errorMessage"></p>
            </div>
            <div class="modal-footer">
                <button id="closeErrorModal" class="button">Close</button>
            </div>
        </div>
    </div>

    <script src="assets/js/chat.js"></script>
</body>
</html>