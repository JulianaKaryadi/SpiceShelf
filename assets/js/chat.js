document.addEventListener('DOMContentLoaded', function() {
    // Chat elements
    const chatForm = document.getElementById('chatForm');
    const chatInput = document.getElementById('chatInput');
    const sendButton = document.getElementById('sendButton');
    const chatMessages = document.getElementById('chatMessages');
    const charCounter = document.getElementById('charCounter');
    const typingIndicator = document.getElementById('typingIndicator');
    const chatSuggestions = document.getElementById('chatSuggestions');
    const suggestionBtns = document.querySelectorAll('.suggestion-btn');
    const clearChatBtn = document.getElementById('clearChatBtn');
    const exportChatBtn = document.getElementById('exportChatBtn');

    // Conversation history for context
    let conversationHistory = [];
    let isWaitingForResponse = false;

    // Initialize chat functionality
    initializeChat();

    function initializeChat() {
        // Auto-resize textarea
        chatInput.addEventListener('input', function() {
            // Update character counter
            const length = this.value.length;
            charCounter.textContent = `${length}/1000`;
            
            // Update send button state
            updateSendButtonState();
            
            // Auto-resize textarea
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 120) + 'px';
        });

        // Handle Enter key (send) vs Shift+Enter (new line)
        chatInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                if (!isWaitingForResponse && this.value.trim()) {
                    sendMessage();
                }
            }
        });

        // Handle form submission
        chatForm.addEventListener('submit', function(e) {
            e.preventDefault();
            if (!isWaitingForResponse && chatInput.value.trim()) {
                sendMessage();
            }
        });

        // Handle suggestion buttons
        suggestionBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const prompt = this.getAttribute('data-prompt');
                chatInput.value = prompt;
                updateSendButtonState();
                sendMessage();
                hideSuggestions();
            });
        });

        // Handle clear chat
        clearChatBtn.addEventListener('click', function() {
            if (confirm('Are you sure you want to clear the chat history?')) {
                clearChat();
            }
        });

        // Handle export chat
        exportChatBtn.addEventListener('click', function() {
            exportChat();
        });

        // Initialize send button state
        updateSendButtonState();
    }

    function updateSendButtonState() {
        const hasText = chatInput.value.trim().length > 0;
        sendButton.disabled = !hasText || isWaitingForResponse;
    }

    function sendMessage() {
        const message = chatInput.value.trim();
        if (!message || isWaitingForResponse) return;

        // Add user message to chat
        addMessage(message, 'user');
        
        // Add to conversation history
        conversationHistory.push({
            role: 'user',
            content: message
        });

        // Clear input and hide suggestions
        chatInput.value = '';
        chatInput.style.height = 'auto';
        updateSendButtonState();
        hideSuggestions();

        // Show typing indicator and get AI response
        showTypingIndicator();
        getAIResponse(message);
    }

    function addMessage(content, sender) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${sender}-message`;

        const avatar = document.createElement('div');
        avatar.className = 'message-avatar';
        avatar.textContent = sender === 'user' ? '👤' : '🤖';

        const messageContent = document.createElement('div');
        messageContent.className = 'message-content';
        
        // Format message content (support for basic markdown-like formatting)
        const formattedContent = formatMessageContent(content);
        messageContent.innerHTML = formattedContent;

        messageDiv.appendChild(avatar);
        messageDiv.appendChild(messageContent);

        chatMessages.appendChild(messageDiv);
        
        // Scroll to bottom
        scrollToBottom();
    }

    function formatMessageContent(content) {
        // Convert newlines to <br>
        let formatted = content.replace(/\n/g, '<br>');
        
        // Bold text **text**
        formatted = formatted.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
        
        // Italic text *text*
        formatted = formatted.replace(/\*(.*?)\*/g, '<em>$1</em>');
        
        // Code blocks `code`
        formatted = formatted.replace(/`(.*?)`/g, '<code>$1</code>');
        
        // Simple numbered lists (assuming they start with numbers)
        formatted = formatted.replace(/^(\d+\.\s)/gm, '<br>$1');
        
        return formatted;
    }

    function showTypingIndicator() {
        isWaitingForResponse = true;
        updateSendButtonState();
        typingIndicator.style.display = 'block';
        scrollToBottom();
    }

    function hideTypingIndicator() {
        isWaitingForResponse = false;
        updateSendButtonState();
        typingIndicator.style.display = 'none';
    }

    function hideSuggestions() {
        chatSuggestions.classList.add('hidden');
    }

    function scrollToBottom() {
        setTimeout(() => {
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }, 100);
    }

    function getAIResponse(userMessage) {
        // Prepare request data
        const requestData = {
            message: userMessage,
            conversation_history: conversationHistory.slice(-10) // Keep last 10 messages for context
        };

        fetch('api/recipe_chat.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(requestData)
        })
        .then(response => response.json())
        .then(data => {
            hideTypingIndicator();
            
            if (data.success) {
                // Add AI response to chat
                addMessage(data.response, 'ai');
                
                // Add to conversation history
                conversationHistory.push({
                    role: 'assistant',
                    content: data.response
                });

                // Limit conversation history to prevent it from getting too long
                if (conversationHistory.length > 20) {
                    conversationHistory = conversationHistory.slice(-16); // Keep last 16 messages
                }
            } else {
                // Show error message
                showError(data.error || 'Sorry, I encountered an error. Please try again.');
            }
        })
        .catch(error => {
            hideTypingIndicator();
            console.error('Error:', error);
            showError('Sorry, I couldn\'t connect to the server. Please check your connection and try again.');
        });
    }

    function showError(message) {
        const errorModal = document.getElementById('errorModal');
        const errorMessage = document.getElementById('errorMessage');
        
        errorMessage.textContent = message;
        errorModal.style.display = 'block';
        
        // Close modal handlers
        const closeBtn = errorModal.querySelector('.close');
        const closeModalBtn = document.getElementById('closeErrorModal');
        
        closeBtn.onclick = () => errorModal.style.display = 'none';
        closeModalBtn.onclick = () => errorModal.style.display = 'none';
        
        // Close on outside click
        window.onclick = (event) => {
            if (event.target === errorModal) {
                errorModal.style.display = 'none';
            }
        };
    }

    function clearChat() {
        // Remove all messages except the initial AI greeting
        const messages = chatMessages.querySelectorAll('.message');
        messages.forEach((message, index) => {
            if (index > 0) { // Keep the first message (AI greeting)
                message.remove();
            }
        });
        
        // Clear conversation history
        conversationHistory = [];
        
        // Show suggestions again
        chatSuggestions.classList.remove('hidden');
        
        // Reset input
        chatInput.value = '';
        updateSendButtonState();
    }

    function exportChat() {
        const messages = chatMessages.querySelectorAll('.message');
        let chatText = 'Recipe Chef AI - Chat Export\n';
        chatText += '================================\n\n';
        
        messages.forEach(message => {
            const isUser = message.classList.contains('user-message');
            const sender = isUser ? 'You' : 'Chef AI';
            const content = message.querySelector('.message-content').textContent;
            
            chatText += `${sender}: ${content}\n\n`;
        });
        
        chatText += `\nExported on: ${new Date().toLocaleString()}\n`;
        
        // Create and trigger download
        const blob = new Blob([chatText], { type: 'text/plain' });
        const url = window.URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = `recipe-chat-${new Date().toISOString().split('T')[0]}.txt`;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        window.URL.revokeObjectURL(url);
    }

    // Auto-focus on chat input when page loads
    chatInput.focus();

    // Add some helpful keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Focus chat input with Ctrl/Cmd + K
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            chatInput.focus();
        }
        
        // Clear chat with Ctrl/Cmd + Shift + K
        if ((e.ctrlKey || e.metaKey) && e.shiftKey && e.key === 'K') {
            e.preventDefault();
            if (confirm('Clear chat history?')) {
                clearChat();
            }
        }
    });

    // Add visual feedback for suggestions
    suggestionBtns.forEach(btn => {
        btn.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px) scale(1.02)';
        });
        
        btn.addEventListener('mouseleave', function() {
            this.style.transform = '';
        });
    });
});