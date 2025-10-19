<?php
class OpenAIAPI {
    private $api_key;
    private $model;
    private $api_url = 'https://api.openai.com/v1/chat/completions';

    public function __construct($api_key, $model = 'gpt-3.5-turbo') {
        $this->api_key = trim($api_key);
        $this->model = $model;
    }

    /**
     * Generate chat response with user profile context (separate from pantry recipe generation)
     */
    public function generateChatResponse($user_prompt, $conversation_history = [], $user_context = []) {
        // Validate API key
        if (empty($this->api_key)) {
            return [
                'success' => false,
                'error' => 'OpenAI API key is missing. Please configure a valid API key.'
            ];
        }

        // Build system prompt with user profile context
        $system_prompt = $this->buildChatSystemPrompt($user_context);

        // Build conversation messages
        $messages = [
            [
                'role' => 'system',
                'content' => $system_prompt
            ]
        ];

        // Add conversation history
        foreach ($conversation_history as $message) {
            $messages[] = $message;
        }

        // Add current user prompt
        $messages[] = [
            'role' => 'user',
            'content' => $user_prompt
        ];

        // Prepare request data
        $data = [
            'model' => $this->model,
            'messages' => $messages,
            'temperature' => 0.8,
            'max_tokens' => 1200
        ];

        return $this->makeAPIRequest($data);
    }

    /**
     * Build system prompt for general cooking chat with user preferences
     */
    private function buildChatSystemPrompt($user_context) {
        $system_prompt = "You are a professional chef and cooking assistant. You help users with cooking questions, recipe suggestions, techniques, meal planning, and culinary advice. ";
        
        // Add dietary preferences context
        if (!empty($user_context['dietary_preferences'])) {
            $preferences = implode(', ', $user_context['dietary_preferences']);
            $system_prompt .= "The user follows these dietary preferences: $preferences. Always consider these when suggesting recipes or ingredients. ";
        }

        // Add allergies context - this is critical for safety
        if (!empty($user_context['allergies'])) {
            $allergies = implode(', ', $user_context['allergies']);
            $system_prompt .= "CRITICAL: The user is allergic to: $allergies. Never suggest recipes, ingredients, or substitutions containing these allergens. Always double-check your suggestions. ";
        }

        // Add favorite recipe patterns for personalization
        if (!empty($user_context['favorite_recipes'])) {
            $favorite_names = array_slice(array_column($user_context['favorite_recipes'], 'name'), 0, 5);
            $favorite_categories = array_unique(array_filter(array_column($user_context['favorite_recipes'], 'category')));
            
            if (!empty($favorite_names)) {
                $names_list = implode(', ', $favorite_names);
                $system_prompt .= "The user has favorited these recipes: $names_list. This gives insight into their taste preferences. ";
            }
            
            if (!empty($favorite_categories)) {
                $category_list = implode(', ', array_slice($favorite_categories, 0, 5));
                $system_prompt .= "The user particularly enjoys these recipe categories: $category_list. ";
            }
        }

        // Add available categories for reference
        if (!empty($user_context['recipe_categories'])) {
            $categories = implode(', ', array_slice($user_context['recipe_categories'], 0, 15));
            $system_prompt .= "Available recipe categories on this platform include: $categories. ";
        }

        $system_prompt .= "

Guidelines for responses:
1. ALWAYS respect dietary preferences and allergies - this is non-negotiable
2. Provide detailed, step-by-step cooking instructions when requested
3. Suggest ingredient substitutions when appropriate, especially for allergens
4. Include cooking times, serving sizes, and helpful tips
5. When suggesting recipes, consider the user's preferred categories and past favorites
6. For ingredient questions, explain storage, preparation, and usage tips
7. For technique questions, provide clear explanations and common mistakes to avoid
8. Be encouraging and supportive for all skill levels
9. If asked about specific recipes from the platform, you can reference general categories but don't make up specific recipe details
10. Personalize suggestions based on their dietary preferences and favorite recipe patterns

You are NOT connected to the user's pantry inventory system - that's a separate feature. Focus on general cooking advice, recipe suggestions, and culinary education.";

        return $system_prompt;
    }

    /**
     * Enhance user prompt with recipe search if they're asking about specific recipes
     */
    private function enhancePromptWithRecipeSearch($user_prompt, $user_context, $conn) {
        // If no database connection, return original prompt
        if (!$conn) {
            return $user_prompt;
        }

        // Check if user is asking about recipes from the platform
        $recipe_keywords = ['recipe for', 'how to make', 'show me recipes', 'find recipes', 'recipes with', 'recipe like'];
        $asking_about_recipes = false;
        
        foreach ($recipe_keywords as $keyword) {
            if (stripos($user_prompt, $keyword) !== false) {
                $asking_about_recipes = true;
                break;
            }
        }

        // If asking about recipes, search for relevant ones
        if ($asking_about_recipes) {
            // Extract search terms (simple approach)
            $search_terms = $this->extractSearchTerms($user_prompt);
            
            if (!empty($search_terms)) {
                $recipes = $this->searchPlatformRecipes($conn, $search_terms, 3);
                
                if (!empty($recipes)) {
                    $recipe_info = "\n\nHere are some relevant recipes from the platform:\n";
                    foreach ($recipes as $recipe) {
                        $recipe_info .= "- {$recipe['name']}: {$recipe['description']} (Category: {$recipe['category']})\n";
                    }
                    
                    return $user_prompt . $recipe_info;
                }
            }
        }

        return $user_prompt;
    }

    /**
     * Extract search terms from user prompt
     */
    private function extractSearchTerms($prompt) {
        // Simple keyword extraction - look for food-related terms
        $food_words = ['chicken', 'beef', 'pork', 'fish', 'salmon', 'vegetarian', 'vegan', 'pasta', 'rice', 'salad', 'soup', 'cake', 'bread', 'pizza', 'burger', 'curry', 'stir fry', 'dessert', 'breakfast', 'lunch', 'dinner'];
        
        $found_terms = [];
        $prompt_lower = strtolower($prompt);
        
        foreach ($food_words as $word) {
            if (strpos($prompt_lower, $word) !== false) {
                $found_terms[] = $word;
            }
        }
        
        return implode(' ', $found_terms);
    }

    /**
     * Search platform recipes
     */
    private function searchPlatformRecipes($conn, $search_terms, $limit = 3) {
        try {
            $search_term = "%" . $search_terms . "%";
            $stmt = $conn->prepare("
                SELECT r.recipe_name, r.description, rc.category_name
                FROM recipes r 
                LEFT JOIN recipe_category_mapping rcm ON r.recipe_id = rcm.recipe_id
                LEFT JOIN recipe_categories rc ON rcm.category_id = rc.category_id
                WHERE r.public = 1 AND (r.recipe_name LIKE ? OR r.description LIKE ? OR rc.category_name LIKE ?)
                ORDER BY r.recipe_name
                LIMIT ?
            ");
            $stmt->bind_param("sssi", $search_term, $search_term, $search_term, $limit);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $recipes = [];
            while ($row = $result->fetch_assoc()) {
                $recipes[] = [
                    'name' => $row['recipe_name'],
                    'description' => $row['description'],
                    'category' => $row['category_name'] ?? 'General'
                ];
            }
            $stmt->close();
            
            return $recipes;
        } catch (Exception $e) {
            error_log("Error searching platform recipes: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Make API request to OpenAI
     */
    private function makeAPIRequest($data) {
        // Set up cURL
        $ch = curl_init($this->api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->api_key
        ]);

        // Execute request
        $response = curl_exec($ch);
        $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        curl_close($ch);

        // Handle errors
        if ($curl_error) {
            return [
                'success' => false,
                'error' => 'cURL Error: ' . $curl_error
            ];
        }

        // Decode response
        $response_data = json_decode($response, true);

        // Error handling
        if ($http_status !== 200) {
            $error_message = 'Unknown API error';
            
            if (isset($response_data['error']['message'])) {
                $error_message = $response_data['error']['message'];
            }

            return [
                'success' => false,
                'error' => "API Error ($http_status): " . $error_message,
                'raw_response' => $response_data
            ];
        }

        // Check for successful response
        if (!isset($response_data['choices'][0]['message']['content'])) {
            return [
                'success' => false,
                'error' => 'Unexpected API response format',
                'raw_response' => $response_data
            ];
        }

        // Extract response from AI
        $ai_response = $response_data['choices'][0]['message']['content'];

        return [
            'success' => true,
            'response' => $ai_response,
            'usage' => $response_data['usage'] ?? null
        ];
    }

    /**
     * Original pantry-based recipe generation (keep as is)
     */
    public function generateRecipe($ingredients, $preferences = [], $allergies = [], $meal_type = '') {
        // Validate API key
        if (empty($this->api_key)) {
            return [
                'success' => false,
                'error' => 'OpenAI API key is missing. Please configure a valid API key.'
            ];
        }

        // Sort ingredients by expiration date (prioritize those expiring sooner)
        usort($ingredients, function($a, $b) {
            // If days_until_expiration is available, use it for sorting
            if (isset($a['days_until_expiration']) && isset($b['days_until_expiration'])) {
                return $a['days_until_expiration'] - $b['days_until_expiration'];
            }
            
            // Fallback to expiration_date if days_until_expiration is not available
            if (isset($a['expiration_date']) && isset($b['expiration_date'])) {
                return strtotime($a['expiration_date']) - strtotime($b['expiration_date']);
            }
            
            // If neither is available, don't change order
            return 0;
        });

        // Identify soon-to-expire ingredients (within next 5 days)
        $soon_expiring = array_filter($ingredients, function($item) {
            if (isset($item['days_until_expiration'])) {
                return $item['days_until_expiration'] >= 0 && $item['days_until_expiration'] <= 5;
            }
            if (isset($item['expiration_date'])) {
                $days_remaining = (strtotime($item['expiration_date']) - time()) / (60 * 60 * 24);
                return $days_remaining >= 0 && $days_remaining <= 5;
            }
            return false;
        });

        // Format prioritized ingredients as a comma-separated list
        $prioritized_ingredients = array_map(function($item) {
            $expiry_info = isset($item['days_until_expiration']) 
                ? " (expires in {$item['days_until_expiration']} days)" 
                : "";
            return $item['ingredient'] . ' (' . $item['quantity'] . ' ' . $item['measurement'] . ')' . $expiry_info;
        }, $soon_expiring);

        // Format remaining ingredients
        $regular_ingredients = array_map(function($item) use ($soon_expiring) {
            // Skip if this is already in the prioritized list
            foreach ($soon_expiring as $expiring) {
                if ($item['ingredient'] === $expiring['ingredient']) {
                    return null;
                }
            }
            return $item['ingredient'] . ' (' . $item['quantity'] . ' ' . $item['measurement'] . ')';
        }, $ingredients);
        
        // Remove null values and combine arrays
        $regular_ingredients = array_filter($regular_ingredients);
        
        // Format preferences
        $preferences_text = !empty($preferences) 
            ? "Dietary preferences: " . implode(', ', $preferences) . ". " 
            : "";

        // Format allergies
        $allergies_text = !empty($allergies) 
            ? "Allergies to avoid: " . implode(', ', $allergies) . ". " 
            : "";

        // Format meal type
        $meal_type_text = "";
        if ($meal_type) {
            $meal_type_display = ucfirst(str_replace('_', ' ', $meal_type));
            $meal_type_text = "Create a $meal_type_display recipe. ";
        }

        // Create prompt with priority for soon-to-expire ingredients
        $prompt = $meal_type_text;
        
        if (!empty($prioritized_ingredients)) {
            $prioritized_list = implode(', ', $prioritized_ingredients);
            $prompt .= "PRIMARILY use these ingredients that will soon expire: $prioritized_list.\n\n";
        }
        
        // Add the remaining ingredients
        if (!empty($regular_ingredients)) {
            $regular_list = implode(', ', $regular_ingredients);
            $prompt .= "You can also use these additional ingredients if needed: $regular_list.\n\n";
        } else {
            $prompt .= "Use only the ingredients listed above.\n\n";
        }
        
        // Add preferences and allergies
        $prompt .= "{$preferences_text}{$allergies_text}\n";
        
        // Add meal-specific instructions
        if ($meal_type) {
            $meal_instructions = $this->getMealTypeInstructions($meal_type);
            $prompt .= $meal_instructions . "\n\n";
        }
        
        // Add formatting instructions
        $prompt .= "Please provide a structured response with the following sections:
                  1. Recipe name" . ($meal_type ? " (clearly indicating it's a $meal_type)" : "") . "
                  2. Brief description
                  3. Ingredients used (with measurements)
                  4. Preparation steps
                  5. Cooking time
                  6. Serving size";

        // Prepare request data
        $data = [
            'model' => $this->model,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are a helpful cooking assistant who generates recipes based on available ingredients. You prioritize using ingredients that will expire soon to reduce food waste. Only fresh, non-expired ingredients are provided to you for recipe generation. When a specific meal type is requested, ensure the recipe is appropriate for that meal time and include relevant preparation methods.'
                ],
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            'temperature' => 0.7,
            'max_tokens' => 1000
        ];

        $response = $this->makeAPIRequest($data);

        if ($response['success']) {
            return [
                'success' => true,
                'recipe' => $response['response'],
                'meal_type' => $meal_type,
                'prioritized_ingredients' => !empty($prioritized_ingredients) ? $prioritized_ingredients : [],
                'used_remaining_ingredients' => !empty($regular_ingredients) ? $regular_ingredients : []
            ];
        }

        return $response;
    }

    /**
     * Get meal-specific cooking instructions and considerations
     */
    private function getMealTypeInstructions($meal_type) {
        $instructions = [
            'breakfast' => 'Focus on nutritious, energizing ingredients. Consider quick preparation methods suitable for morning routines. Include proteins and healthy carbs for sustained energy.',
            'lunch' => 'Create a balanced, satisfying meal that provides sustained energy for the afternoon. Consider portable options if needed.',
            'dinner' => 'Develop a hearty, comforting meal perfect for evening dining. Can include longer cooking methods and more complex flavors.',
            'snack' => 'Keep it simple, quick, and satisfying. Focus on healthy options that can be prepared in 15 minutes or less.',
            'appetizer' => 'Create small, flavorful portions designed to stimulate appetite. Focus on presentation and bold flavors.',
            'dessert' => 'Design a sweet treat that can utilize fruits or other ingredients creatively. Consider both baked and no-bake options.',
            'side_dish' => 'Complement main dishes with flavors that enhance rather than compete. Keep portions moderate.',
            'beverage' => 'Create refreshing drinks, smoothies, or warm beverages. Consider both hot and cold options depending on available ingredients.'
        ];

        return isset($instructions[$meal_type]) ? $instructions[$meal_type] : '';
    }

    /**
     * Original free-form recipe method (keep for backward compatibility)
     */
    public function generateFreeFormRecipe($user_prompt, $conversation_history = []) {
        return $this->generateChatResponse($user_prompt, $conversation_history, [], null);
    }
}
?>