<?php
// At the top of header.php
if (!isset($conn) || $conn === null) {
    // Check if the Database class exists
    if (!class_exists('Database')) {
        // Try to include the database file
        $database_paths = [
            __DIR__ . '/../config/database.php',
            'config/database.php',
            '../config/database.php',
            './config/database.php'
        ];
        
        $found = false;
        foreach ($database_paths as $path) {
            if (file_exists($path)) {
                require_once $path;
                $found = true;
                break;
            }
        }
        
        if (!$found) {
            // If database.php can't be found, disable ads
            $adsEnabled = false;
        }
    }
    
    // If Database class now exists, try to get a connection
    if (class_exists('Database')) {
        try {
            $database = new Database();
            $conn = $database->getConnection();
            $adsEnabled = true;
        } catch (Exception $e) {
            // If connection fails, disable ads
            error_log("Database connection failed: " . $e->getMessage());
            $adsEnabled = false;
        }
    } else {
        // If Database class doesn't exist, disable ads
        $adsEnabled = false;
    }
}
?>

<!-- header.php -->
<header class="main-header">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="assets/css/header.css">
    <link rel="stylesheet" href="assets/css/ads.css">
    
    <div class="logo">
        <a href="index.php?action=home">SpiceShelf</a>
    </div>

    <nav class="main-nav">
        <input type="checkbox" id="nav-toggle">
        <label for="nav-toggle" class="nav-toggle-label">
            <span></span>
        </label>
        <ul>
            <?php if (isset($_SESSION['user_id'])): ?>
                <li><a href="index.php?action=home"><i class="fas fa-home"></i> Home</a></li>
                <li><a href="index.php?action=add_recipe"><i class="fas fa-plus-circle"></i> Add Recipe</a></li>
                <li><a href="index.php?action=browse_recipes"><i class="fas fa-search"></i> Browse Recipes</a></li>
                <li><a href="index.php?action=meal_plans"><i class="fas fa-calendar-alt"></i> My Meal Plans</a></li>
                <li><a href="index.php?action=pantry"><i class="fas fa-shopping-basket"></i> My Pantry</a></li>
                <li><a href="index.php?action=shopping_list"><i class="fas fa-list-ul"></i> Shopping List</a></li>
                <li><a href="index.php?action=profile"><i class="fas fa-user"></i> My Profile</a></li>
                <li><a href="index.php?action=logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            <?php else: ?>
                <li><a href="index.php?action=login"><i class="fas fa-sign-in-alt"></i> Login</a></li>
                <li><a href="index.php?action=register"><i class="fas fa-user-plus"></i> Register</a></li>
            <?php endif; ?>
        </ul>
    </nav>

    <?php if (isset($adsEnabled) && $adsEnabled && isset($conn)): ?>
        <!-- Sidebar Ad Display -->
        <?php
        // Include Ad model if not already included
        if (!class_exists('Ad')) {
            require_once 'models/Ad.php';
        }

        try {
            // Create ad instance with error handling
            $adModel = new Ad($conn);

            // Get a random sidebar ad
            $sidebarAd = $adModel->getRandomAd('sidebar');

            // Display sidebar ad if available
            if ($sidebarAd): 
            ?>
                <div class="sidebar-ad" data-ad-id="<?php echo htmlspecialchars($sidebarAd['ad_id']); ?>">
                    <a href="<?php echo htmlspecialchars($sidebarAd['url']); ?>" target="_blank">
                        <img src="<?php echo htmlspecialchars($sidebarAd['image']); ?>" 
                             alt="<?php echo htmlspecialchars($sidebarAd['title']); ?>"
                             class="sidebar-ad-image">
                    </a>
                </div>
            <?php endif; ?>

            <!-- Popup Ad Script -->
            <?php
            // Get a random popup ad
            $popupAd = $adModel->getRandomAd('popup');

            // Display popup ad if available
            if ($popupAd): 
            ?>
                <script>
                document.addEventListener('DOMContentLoaded', function() {
                    // Create popup container
                    const popup = document.createElement('div');
                    popup.className = 'ad-popup';
                    popup.setAttribute('data-ad-id', '<?php echo htmlspecialchars($popupAd['ad_id']); ?>');
                    popup.innerHTML = `
                        <div class="ad-popup-content">
                            <span class="ad-popup-close">&times;</span>
                            <a href="<?php echo htmlspecialchars($popupAd['url']); ?>" target="_blank">
                                <img src="<?php echo htmlspecialchars($popupAd['image']); ?>" 
                                     alt="<?php echo htmlspecialchars($popupAd['title']); ?>">
                            </a>
                        </div>
                    `;
                    
                    // Add popup to body
                    document.body.appendChild(popup);
                    
                    // Show popup after 3 seconds
                    setTimeout(() => {
                        popup.style.display = 'block';
                    }, 3000);
                    
                    // Close popup when clicked
                    document.querySelector('.ad-popup-close').addEventListener('click', function() {
                        popup.style.display = 'none';
                    });
                });
                </script>
            <?php endif; ?>
        <?php
        } catch (Exception $e) {
            // Log the error but don't display anything to the user
            error_log("Error displaying ads: " . $e->getMessage());
        }
        ?>
    <?php endif; ?>

    <!-- Add the ad tracking script -->
    <script src="assets/js/ad-tracking.js"></script>
</header>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const navToggle = document.getElementById('nav-toggle');
        const navMenu = document.querySelector('.main-nav ul');

        if (navToggle && navMenu) {
            navToggle.addEventListener('change', function() {
                if (this.checked) {
                    navMenu.style.maxHeight = navMenu.scrollHeight + "px";
                } else {
                    navMenu.style.maxHeight = null;
                }
            });
        }
    });
</script><?php
// At the top of header.php
if (!isset($conn) || $conn === null) {
    // Check if the Database class exists
    if (!class_exists('Database')) {
        // Try to include the database file
        $database_paths = [
            __DIR__ . '/../config/database.php',
            'config/database.php',
            '../config/database.php',
            './config/database.php'
        ];
        
        $found = false;
        foreach ($database_paths as $path) {
            if (file_exists($path)) {
                require_once $path;
                $found = true;
                break;
            }
        }
        
        if (!$found) {
            // If database.php can't be found, disable ads
            $adsEnabled = false;
        }
    }
    
    // If Database class now exists, try to get a connection
    if (class_exists('Database')) {
        try {
            $database = new Database();
            $conn = $database->getConnection();
            $adsEnabled = true;
        } catch (Exception $e) {
            // If connection fails, disable ads
            error_log("Database connection failed: " . $e->getMessage());
            $adsEnabled = false;
        }
    } else {
        // If Database class doesn't exist, disable ads
        $adsEnabled = false;
    }
}
?>

<!-- header.php -->
<header class="main-header">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="assets/css/header.css">
    <link rel="stylesheet" href="assets/css/ads.css">
    
    <div class="logo">
        <a href="index.php?action=home">SpiceShelf</a>
    </div>

    <nav class="main-nav">
        <input type="checkbox" id="nav-toggle">
        <label for="nav-toggle" class="nav-toggle-label">
            <span></span>
        </label>
        <ul>
            <?php if (isset($_SESSION['user_id'])): ?>
                <li><a href="index.php?action=home"><i class="fas fa-home"></i> Home</a></li>
                <li><a href="index.php?action=add_recipe"><i class="fas fa-plus-circle"></i> Add Recipe</a></li>
                <li><a href="index.php?action=browse_recipes"><i class="fas fa-search"></i> Browse Recipes</a></li>
                <li><a href="index.php?action=meal_plans"><i class="fas fa-calendar-alt"></i> My Meal Plans</a></li>
                <li><a href="index.php?action=pantry"><i class="fas fa-shopping-basket"></i> My Pantry</a></li>
                <li><a href="index.php?action=shopping_list"><i class="fas fa-list-ul"></i> Shopping List</a></li>
                <li><a href="index.php?action=profile"><i class="fas fa-user"></i> My Profile</a></li>
                <li><a href="index.php?action=recipe_chat" class="chat-link"><i class="fas fa-robot"></i> Recipe Chef AI</a></li>
                <li><a href="index.php?action=logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            <?php else: ?>
                <li><a href="index.php?action=login"><i class="fas fa-sign-in-alt"></i> Login</a></li>
                <li><a href="index.php?action=register"><i class="fas fa-user-plus"></i> Register</a></li>
            <?php endif; ?>
        </ul>
    </nav>

    <?php if (isset($adsEnabled) && $adsEnabled && isset($conn)): ?>
        <!-- Sidebar Ad Display -->
        <?php
        // Include Ad model if not already included
        if (!class_exists('Ad')) {
            require_once 'models/Ad.php';
        }

        try {
            // Create ad instance with error handling
            $adModel = new Ad($conn);

            // Get a random sidebar ad
            $sidebarAd = $adModel->getRandomAd('sidebar');

            // Display sidebar ad if available
            if ($sidebarAd): 
            ?>
                <div class="sidebar-ad" data-ad-id="<?php echo htmlspecialchars($sidebarAd['ad_id']); ?>">
                    <a href="<?php echo htmlspecialchars($sidebarAd['url']); ?>" target="_blank">
                        <img src="<?php echo htmlspecialchars($sidebarAd['image']); ?>" 
                             alt="<?php echo htmlspecialchars($sidebarAd['title']); ?>"
                             class="sidebar-ad-image">
                    </a>
                </div>
            <?php endif; ?>

            <!-- Popup Ad Script -->
            <?php
            // Get a random popup ad
            $popupAd = $adModel->getRandomAd('popup');

            // Display popup ad if available
            if ($popupAd): 
            ?>
                <script>
                document.addEventListener('DOMContentLoaded', function() {
                    // Create popup container
                    const popup = document.createElement('div');
                    popup.className = 'ad-popup';
                    popup.setAttribute('data-ad-id', '<?php echo htmlspecialchars($popupAd['ad_id']); ?>');
                    popup.innerHTML = `
                        <div class="ad-popup-content">
                            <span class="ad-popup-close">&times;</span>
                            <a href="<?php echo htmlspecialchars($popupAd['url']); ?>" target="_blank">
                                <img src="<?php echo htmlspecialchars($popupAd['image']); ?>" 
                                     alt="<?php echo htmlspecialchars($popupAd['title']); ?>">
                            </a>
                        </div>
                    `;
                    
                    // Add popup to body
                    document.body.appendChild(popup);
                    
                    // Show popup after 3 seconds
                    setTimeout(() => {
                        popup.style.display = 'block';
                    }, 3000);
                    
                    // Close popup when clicked
                    document.querySelector('.ad-popup-close').addEventListener('click', function() {
                        popup.style.display = 'none';
                    });
                });
                </script>
            <?php endif; ?>
        <?php
        } catch (Exception $e) {
            // Log the error but don't display anything to the user
            error_log("Error displaying ads: " . $e->getMessage());
        }
        ?>
    <?php endif; ?>

    <!-- Add the ad tracking script -->
    <script src="assets/js/ad-tracking.js"></script>
</header>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const navToggle = document.getElementById('nav-toggle');
        const navMenu = document.querySelector('.main-nav ul');

        if (navToggle && navMenu) {
            navToggle.addEventListener('change', function() {
                if (this.checked) {
                    navMenu.style.maxHeight = navMenu.scrollHeight + "px";
                } else {
                    navMenu.style.maxHeight = null;
                }
            });
        }
    });
</script>

<style>
/* Special styling for the chat link */
.chat-link {
    background: linear-gradient(135deg, #e8a87c, #d89b6e) !important;
    border-radius: 6px !important;
    color: white !important;
    padding: 8px 12px !important;
    margin: 2px 0 !important;
    box-shadow: 0 2px 8px rgba(232, 168, 124, 0.3) !important;
    transition: all 0.3s ease !important;
}

.chat-link:hover {
    background: linear-gradient(135deg, #d89b6e, #c8966a) !important;
    transform: translateY(-1px) !important;
    box-shadow: 0 4px 12px rgba(232, 168, 124, 0.4) !important;
}

.chat-link i {
    color: white !important;
}

@media screen and (max-width: 768px) {
    .chat-link {
        margin: 5px 0 !important;
    }
}
</style>