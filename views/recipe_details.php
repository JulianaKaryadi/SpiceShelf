<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($recipe['recipe_name']); ?> - Recipe Details</title>
    <link rel="stylesheet" href="assets/css/recipe_details.css">
    <!-- Add Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Override styles to match view_recipe.php layout */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
            margin-left: 285px; /* Changed from 260px to 310px (280px sidebar + 30px gap) */
        }
        
        .recipe-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .back-button {
            display: inline-flex;
            align-items: center;
            padding: 8px 15px;
            background-color: #6c757d;
            color: white;
            border-radius: 4px;
            text-decoration: none;
            font-size: 14px;
            transition: background-color 0.3s;
        }
        
        .back-button:hover {
            background-color: #5a6268;
            color: white;
            text-decoration: none;
        }
        
        .back-button i {
            margin-right: 8px;
        }
        
        .recipe-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }
        
        .recipe-image-container {
            background-color: rgba(255, 255, 255, 0.8);
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .recipe-image {
            width: 100%;
            height: 300px;
            object-fit: cover;
            display: block;
        }
        
        .recipe-details {
            background-color: rgba(255, 255, 255, 0.8);
            border-radius: 8px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .recipe-name {
            font-family: 'Cormorant Garamond', serif;
            font-size: 24px;
            color: #34554a;
            margin: 0 0 10px;
        }
        
        .recipe-meta {
            margin-bottom: 20px;
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .recipe-meta-item {
            display: flex;
            align-items: center;
            font-size: 14px;
            color: #666;
        }
        
        .recipe-meta-item i {
            margin-right: 5px;
            color: #e8a87c;
        }
        
        .recipe-description {
            color: #555;
            margin-bottom: 20px;
            line-height: 1.6;
        }
        
        .visibility-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            margin-bottom: 15px;
        }
        
        .public-badge {
            background-color: #d4edda;
            color: #155724;
        }
        
        .private-badge {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .recipe-sections {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }
        
        .recipe-section {
            background-color: rgba(255, 255, 255, 0.8);
            border-radius: 8px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .section-title {
            font-family: 'Cormorant Garamond', serif;
            font-size: 18px;
            color: #34554a;
            margin: 0 0 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: center;
        }
        
        .section-title i {
            margin-right: 10px;
            color: #e8a87c;
        }
        
        .ingredients-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .ingredients-list li {
            display: flex;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px dashed #eee;
        }
        
        .ingredients-list li:last-child {
            border-bottom: none;
        }
        
        .ingredients-list li::before {
            content: "\f00c";
            font-family: "Font Awesome 5 Free";
            font-weight: 900;
            color: #e8a87c;
            margin-right: 10px;
            font-size: 12px;
        }
        
        .instructions-list {
            list-style: none;
            padding: 0;
            margin: 0;
            counter-reset: steps;
        }
        
        .instructions-list li {
            position: relative;
            padding: 15px 0 15px 45px;
            border-bottom: 1px dashed #eee;
            line-height: 1.6;
        }
        
        .instructions-list li:last-child {
            border-bottom: none;
        }
        
        .instructions-list li::before {
            counter-increment: steps;
            content: counter(steps);
            position: absolute;
            left: 0;
            top: 15px;
            width: 30px;
            height: 30px;
            background-color: #e8a87c;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
        
        .recipe-management {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-bottom: 30px;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            padding: 10px 20px;
            background-color: #34554a;
            color: white;
            border: none;
            border-radius: 4px;
            text-decoration: none;
            font-size: 14px;
            transition: background-color 0.3s;
            cursor: pointer;
        }
        
        .btn:hover {
            background-color: #2a4a40;
            color: white;
            text-decoration: none;
        }
        
        .btn.danger {
            background-color: #d35847;
        }
        
        .btn.danger:hover {
            background-color: #c04a39;
        }
        
        .btn i {
            margin-right: 8px;
        }
        
        .comments-section {
            background-color: rgba(255, 255, 255, 0.8);
            border-radius: 8px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            grid-column: 1 / -1;
        }
        
        .comment {
            background-color: rgba(245, 239, 230, 0.5);
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 15px;
            border-left: 3px solid #e8a87c;
        }
        
        .comment-form textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background-color: rgba(255, 255, 255, 0.9);
            font-family: inherit;
            font-size: 14px;
            color: #444;
            min-height: 80px;
            margin-bottom: 15px;
            resize: vertical;
            box-sizing: border-box;
        }
        
        .comment-form textarea:focus {
            border-color: #e8a87c;
            outline: none;
        }
        
        /* Responsive adjustments */
        @media (max-width: 992px) {
            .recipe-container, .recipe-sections {
                grid-template-columns: 1fr;
            }
            
            .container {
                margin-left: 0; /* Remove sidebar offset on mobile */
                padding: 20px;
                margin-top: 60px; /* Add top margin for mobile header */
            }
        }
        
        @media (max-width: 768px) {
            .recipe-header {
                flex-direction: column;
                gap: 15px;
            }
            
            .recipe-management {
                flex-direction: column;
                align-items: center;
            }
            
            .container {
                margin-left: 0;
                padding: 15px;
                margin-top: 80px; /* Increased top margin for mobile */
            }
        }
    </style>
</head>
<body>

    <!-- Header -->
    <?php include('includes/header.php'); ?>

    <!-- Main content -->
    <div class="container">
        <h1><?= htmlspecialchars($recipe['recipe_name']); ?></h1>

        <!-- Recipe Header with Back Button -->
        <div class="recipe-header">
            <a href="javascript:history.back()" class="back-button">
                <i class="fas fa-arrow-left"></i> Back
            </a>
            
            <!-- Management Buttons -->
            <?php if ($isOwner): ?>
                <div class="recipe-management">
                    <a href="index.php?action=edit_recipe&recipe_id=<?= $recipe['recipe_id']; ?>" class="btn">
                        <i class="fas fa-edit"></i> Edit Recipe
                    </a>
                    <form action="index.php?action=delete_recipe" method="POST" onsubmit="return confirm('Are you sure you want to delete this recipe?');" style="display: inline;">
                        <input type="hidden" name="recipe_id" value="<?= $recipe['recipe_id']; ?>">
                        <button type="submit" class="btn danger">
                            <i class="fas fa-trash"></i> Delete Recipe
                        </button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Recipe Overview -->
        <div class="recipe-container">
            <!-- Recipe Image -->
            <div class="recipe-image-container">
                <img src="<?= !empty($recipe['image']) ? htmlspecialchars($recipe['image']) : 'assets/images/default_recipe.jpg'; ?>" 
                     class="recipe-image" alt="<?= htmlspecialchars($recipe['recipe_name']); ?>">
            </div>
            
            <!-- Recipe Details -->
            <div class="recipe-details">
                <!-- Visibility Badge -->
                <?php if ($recipe['public']): ?>
                    <span class="visibility-badge public-badge">Public Recipe</span>
                <?php else: ?>
                    <span class="visibility-badge private-badge">Private Recipe</span>
                <?php endif; ?>
                
                <h2 class="recipe-name"><?= htmlspecialchars($recipe['recipe_name']); ?></h2>
                
                <!-- Recipe Meta Info -->
                <div class="recipe-meta">
                    <div class="recipe-meta-item">
                        <i class="fas fa-clock"></i> Prep: <?= htmlspecialchars($recipe['prep_time']); ?> mins
                    </div>
                    <div class="recipe-meta-item">
                        <i class="fas fa-fire"></i> Cook: <?= htmlspecialchars($recipe['cook_time']); ?> mins
                    </div>
                    <div class="recipe-meta-item">
                        <i class="fas fa-users"></i> Serves: <?= htmlspecialchars($recipe['serving_size']); ?>
                    </div>
                    <div class="recipe-meta-item">
                        <i class="fas fa-calendar"></i> Added: <?= date('M d, Y', strtotime($recipe['created_at'])); ?>
                    </div>
                </div> 
                
                <!-- Recipe Description -->
                <div class="recipe-description">
                    <?= nl2br(htmlspecialchars($recipe['description'])); ?>
                </div>
            </div>
        </div>
        
        <!-- Recipe Content Sections -->
        <div class="recipe-sections">
            <!-- Ingredients Section -->
            <div class="recipe-section">
                <h3 class="section-title">
                    <i class="fas fa-list"></i> Ingredients
                </h3>
                
                <?php if (empty($ingredients)): ?>
                    <p>No ingredients listed for this recipe.</p>
                <?php else: ?>
                    <ul class="ingredients-list">
                        <?php foreach ($ingredients as $ingredient): ?>
                            <li>
                                <?= htmlspecialchars($ingredient['quantity']) . ' ' . 
                                    htmlspecialchars($ingredient['measurement']) . ' ' . 
                                    htmlspecialchars($ingredient['ingredient_name']); ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
            
            <!-- Instructions Section -->
            <div class="recipe-section">
                <h3 class="section-title">
                    <i class="fas fa-tasks"></i> Instructions
                </h3>
                
                <?php if (empty($recipe['steps'])): ?>
                    <p>No instructions provided for this recipe.</p>
                <?php else: ?>
                    <?php
                        // Convert numbered list into array of steps
                        $steps = preg_split('/\r\n|\r|\n/', $recipe['steps']);
                        // Filter out empty lines and lines that are just numbers
                        $steps = array_filter($steps, function($step) {
                            $step = trim($step);
                            return !empty($step) && !preg_match('/^\d+\.?\s*$/', $step);
                        });
                        
                        // Clean up step numbers if present
                        $steps = array_map(function($step) {
                            return preg_replace('/^\d+\.?\s*/', '', $step);
                        }, $steps);
                    ?>
                    
                    <ol class="instructions-list">
                        <?php foreach ($steps as $step): ?>
                            <li><?= htmlspecialchars(trim($step)); ?></li>
                        <?php endforeach; ?>
                    </ol>
                <?php endif; ?>
            </div>
            
            <!-- Comments Section -->
            <div class="comments-section">
                <h3 class="section-title">
                    <i class="fas fa-comments"></i> Comments
                </h3>
                
                <!-- Comment Form -->
                <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="comment-form">
                        <form id="comment-form" action="index.php?action=add_comment" method="post">
                            <input type="hidden" name="recipe_id" value="<?= $recipe['recipe_id']; ?>">
                            <textarea name="comment" placeholder="Share your thoughts about this recipe..." required></textarea>
                            <button type="submit" class="btn">
                                <i class="fas fa-paper-plane"></i> Post Comment
                            </button>
                        </form>
                    </div>
                <?php else: ?>
                    <p class="login-prompt">Please <a href="index.php?action=login">log in</a> to leave a comment.</p>
                <?php endif; ?>
                
                <!-- Comments List -->
                <div class="comments-list" id="comments-container">
                    <?php if (!empty($comments)): ?>
                        <?php foreach ($comments as $comment): ?>
                            <div class="comment" id="comment-<?= $comment['comment_id']; ?>">
                                <div class="comment-header">
                                    <div class="comment-user">
                                        <span class="username"><?= htmlspecialchars($comment['username']); ?></span>
                                    </div>
                                    <span class="comment-date"><?= date('M j, Y g:i A', strtotime($comment['created_at'])); ?></span>
                                </div>
                                <div class="comment-content">
                                    <?= nl2br(htmlspecialchars($comment['comment'])); ?>
                                </div>
                                <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $comment['user_id']): ?>
                                    <div class="comment-actions">
                                        <form action="index.php?action=delete_comment" method="post" class="delete-comment-form">
                                            <input type="hidden" name="comment_id" value="<?= $comment['comment_id']; ?>">
                                            <input type="hidden" name="recipe_id" value="<?= $recipe['recipe_id']; ?>">
                                            <button type="submit" class="delete-btn">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </form>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="no-comments">No comments yet. Be the first to share your thoughts!</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript for AJAX comment submission -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const commentForm = document.getElementById('comment-form');
        
        if (commentForm) {
            commentForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                
                fetch('index.php?action=add_comment', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Clear the textarea
                        document.querySelector('textarea[name="comment"]').value = '';
                        
                        // Create new comment HTML
                        const commentsContainer = document.getElementById('comments-container');
                        
                        // Remove "no comments" message if present
                        const noComments = commentsContainer.querySelector('.no-comments');
                        if (noComments) {
                            noComments.remove();
                        }
                        
                        // Create the new comment element
                        const newComment = document.createElement('div');
                        newComment.className = 'comment';
                        newComment.id = `comment-${data.comment.comment_id}`;
                        
                        newComment.innerHTML = `
                            <div class="comment-header">
                                <div class="comment-user">
                                    <span class="username">${data.username}</span>
                                </div>
                                <span class="comment-date">${data.created_at}</span>
                            </div>
                            <div class="comment-content">
                                ${data.comment.comment.replace(/\n/g, '<br>')}
                            </div>
                            <div class="comment-actions">
                                <form action="index.php?action=delete_comment" method="post" class="delete-comment-form">
                                    <input type="hidden" name="comment_id" value="${data.comment.comment_id}">
                                    <input type="hidden" name="recipe_id" value="${data.comment.recipe_id}">
                                    <button type="submit" class="delete-btn">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </form>
                            </div>
                        `;
                        
                        // Add to the beginning of the comments list
                        commentsContainer.insertBefore(newComment, commentsContainer.firstChild);
                    } else {
                        alert('Error posting comment: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while posting your comment.');
                });
            });
        }
        
        // Handle comment deletion
        document.addEventListener('submit', function(e) {
            if (e.target.classList.contains('delete-comment-form')) {
                e.preventDefault();
                
                if (confirm('Are you sure you want to delete this comment?')) {
                    const formData = new FormData(e.target);
                    const commentId = formData.get('comment_id');
                    
                    fetch('index.php?action=delete_comment', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Remove the comment element
                            document.getElementById(`comment-${commentId}`).remove();
                            
                            // Check if there are no more comments
                            const commentsContainer = document.getElementById('comments-container');
                            if (commentsContainer.children.length === 0) {
                                commentsContainer.innerHTML = '<p class="no-comments">No comments yet. Be the first to share your thoughts!</p>';
                            }
                        } else {
                            alert('Error deleting comment: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while deleting the comment.');
                    });
                }
            }
        });
    });
    </script>
</body>
</html>