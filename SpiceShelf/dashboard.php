<?php
// adminviews/dashboard.php

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php?action=adminLogin');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - SpiceShelf</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <!-- Add Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Admin Dashboard Styles */
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
        }
        
        .admin-container {
            display: flex;
            min-height: 100vh;
        }
        
        .admin-sidebar {
            width: 260px;
            background-color: #333;
            color: #fff;
            padding: 0;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
            position: fixed;
            height: 100%;
            overflow-y: auto;
        }
        
        .admin-logo {
            padding: 20px;
            text-align: center;
            margin-bottom: 10px;
            border-bottom: 1px solid #444;
        }
        
        .admin-logo h2 {
            color: #F29E52;
            margin: 0;
            font-size: 24px;
        }
        
        .admin-logo p {
            color: #aaa;
            margin: 5px 0 0;
            font-size: 14px;
        }
        
        .admin-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .admin-menu-category {
            font-size: 12px;
            text-transform: uppercase;
            color: #999;
            padding: 15px 20px 5px;
            letter-spacing: 0.5px;
        }
        
        .admin-menu li a {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: #fff;
            text-decoration: none;
            transition: all 0.3s;
            border-left: 3px solid transparent;
        }
        
        .admin-menu li a i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        
        .admin-menu li a:hover, 
        .admin-menu li a.active {
            background-color: #444;
            border-left-color: #F29E52;
        }
        
        .admin-menu li a.active {
            background-color: #F29E52;
            color: #fff;
        }
        
        .admin-content {
            flex: 1;
            padding: 20px;
            margin-left: 260px;
        }
        
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid #ddd;
        }
        
        .admin-title {
            margin: 0;
            color: #333;
            font-size: 24px;
        }
        
        .admin-user-info {
            display: flex;
            align-items: center;
        }
        
        .admin-user-info span {
            margin-right: 15px;
            color: #555;
        }
        
        .admin-logout {
            padding: 8px 15px;
            background-color: #dc3545;
            color: white;
            border: none;
            border-radius: 4px;
            text-decoration: none;
            font-size: 14px;
            transition: background-color 0.3s;
        }
        
        .admin-logout:hover {
            background-color: #c82333;
        }
        
        .admin-stats {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .admin-stat-card {
            background-color: #fff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            position: relative;
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .admin-stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .admin-stat-card h3 {
            margin-top: 0;
            color: #6c757d;
            font-size: 16px;
            font-weight: 500;
        }
        
        .admin-stat-card .number {
            font-size: 36px;
            font-weight: bold;
            color: #333;
            margin: 10px 0;
        }
        
        .admin-stat-card .icon {
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 24px;
            color: #F29E52;
            opacity: 0.8;
        }
        
        .admin-stat-card.users {
            border-top: 3px solid #4e73df;
        }
        
        .admin-stat-card.recipes {
            border-top: 3px solid #1cc88a;
        }
        
        .admin-stat-card.ingredients {
            border-top: 3px solid #36b9cc;
        }
        
        .admin-stat-card.categories {
            border-top: 3px solid #f6c23e;
        }
        
        .admin-section-title {
            margin: 30px 0 15px;
            color: #333;
            font-size: 20px;
            font-weight: 600;
            display: flex;
            align-items: center;
        }
        
        .admin-section-title i {
            margin-right: 10px;
            color: #F29E52;
        }
        
        .admin-recent-section {
            background-color: #fff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }
        
        .admin-recent-section h3 {
            margin-top: 0;
            margin-bottom: 15px;
            color: #333;
            font-size: 18px;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        
        .admin-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .admin-table th, 
        .admin-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        .admin-table th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #333;
        }
        
        .admin-table tr:hover {
            background-color: #f8f9fa;
        }
        
        .admin-table a {
            color: #F29E52;
            text-decoration: none;
            transition: color 0.3s;
        }
        
        .admin-table a:hover {
            color: #e48a3c;
            text-decoration: underline;
        }
        
        .user-status {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .user-status.active {
            background-color: #d4edda;
            color: #155724;
        }
        
        .user-status.inactive {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .admin-action-btn {
            display: inline-block;
            padding: 6px 12px;
            background-color: #F29E52;
            color: white;
            border: none;
            border-radius: 4px;
            text-decoration: none;
            font-size: 13px;
            transition: background-color 0.3s;
            margin-right: 5px;
        }
        
        .admin-action-btn:hover {
            background-color: #e48a3c;
            color: white;
            text-decoration: none;
        }
        
        .admin-action-btn.delete {
            background-color: #dc3545;
        }
        
        .admin-action-btn.delete:hover {
            background-color: #c82333;
        }

        .admin-chart-section {
            background-color: #fff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 20px;
            overflow: hidden;
        }

        .admin-chart-section h3 {
            margin-top: 0;
            margin-bottom: 15px;
            color: #333;
            font-size: 18px;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }

        .admin-chart-section h3 i {
            margin-right: 8px;
            color: #F29E52;
        }

        .admin-stats {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        @media (max-width: 992px) {
            .admin-stats {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Admin Sidebar -->
        <aside class="admin-sidebar">
            <div class="admin-logo">
                <h2>SpiceShelf</h2>
                <p>Admin Portal</p>
            </div>
            
            <ul class="admin-menu">
                <li class="admin-menu-category">Dashboard</li>
                <li><a href="index.php?action=adminDashboard" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                
                <li class="admin-menu-category">User Management</li>
                <li><a href="index.php?action=adminUsers"><i class="fas fa-users"></i> View Users</a></li>
                
                <li class="admin-menu-category">Recipe Management</li>
                <li><a href="index.php?action=adminRecipes"><i class="fas fa-utensils"></i> View Recipes</a></li>
                <li><a href="index.php?action=adminIngredients"><i class="fas fa-apple-alt"></i> Ingredients</a></li>
                <li><a href="index.php?action=adminCategories"><i class="fas fa-tags"></i> Categories</a></li>
            
                <li class="admin-menu-category">Reports</li>
                <li><a href="index.php?action=adminUserReports"><i class="fas fa-chart-line"></i> User Engagement</a></li>
                <li><a href="index.php?action=adminRecipeReports"><i class="fas fa-chart-pie"></i> Recipe Favorites</a></li>
                <li><a href="index.php?action=adminAdReports"><i class="fas fa-chart-bar"></i> Ad Performance</a></li>
                
                <li class="admin-menu-category">Advertisements</li>
                <li><a href="index.php?action=adminAds"><i class="fas fa-ad"></i> Manage Ads</a></li>
            </ul>
        </aside>
        
        <!-- Admin Content -->
        <main class="admin-content">
            <header class="admin-header">
                <h1 class="admin-title">Dashboard</h1>
                <div class="admin-user-info">
                    <span>Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                    <a href="index.php?action=adminLogout" class="admin-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </header>
            
            <!-- Stats Cards -->
            <div class="admin-stats">
                <div class="admin-stat-card users">
                    <h3>Total Users</h3>
                    <div class="number"><?php echo $total_users; ?></div>
                    <div class="icon"><i class="fas fa-users"></i></div>
                </div>
                
                <div class="admin-stat-card recipes">
                    <h3>Total Recipes</h3>
                    <div class="number"><?php echo $total_recipes; ?></div>
                    <div class="icon"><i class="fas fa-utensils"></i></div>
                </div>
                
                <div class="admin-stat-card ingredients">
                    <h3>Total Ingredients</h3>
                    <div class="number"><?php echo isset($total_ingredients) ? $total_ingredients : '0'; ?></div>
                    <div class="icon"><i class="fas fa-apple-alt"></i></div>
                </div>
                
                <div class="admin-stat-card categories">
                    <h3>Recipe Categories</h3>
                    <div class="number"><?php echo isset($total_categories) ? $total_categories : '0'; ?></div>
                    <div class="icon"><i class="fas fa-tags"></i></div>
                </div>
            </div>
            
            <!-- Replace the tables with charts -->
            <h2 class="admin-section-title"><i class="fas fa-chart-line"></i> Dashboard Analytics</h2>

            <div class="admin-stats">
                <!-- User Engagement Chart -->
                <div class="admin-chart-section">
                    <h3><i class="fas fa-users"></i> User Engagement</h3>
                    <canvas id="userEngagementChart" width="400" height="300"></canvas>
                </div>
                
                <!-- Recipe Performance Chart -->
                <div class="admin-chart-section">
                    <h3><i class="fas fa-heart"></i> Recipe Performance</h3>
                    <canvas id="recipePerformanceChart" width="400" height="300"></canvas>
                </div>
                
                <!-- Category Distribution Chart -->
                <div class="admin-chart-section">
                    <h3><i class="fas fa-tags"></i> Recipe Categories</h3>
                    <canvas id="categoryDistributionChart" width="400" height="300"></canvas>
                </div>
                
                <!-- Ad Performance Chart -->
                <div class="admin-chart-section">
                    <h3><i class="fas fa-ad"></i> Ad Performance</h3>
                    <canvas id="adPerformanceChart" width="400" height="300"></canvas>
                </div>
            </div>

            <!-- Chart.js Library -->
            <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>

            <script>
                // Chart colors
                const colors = [
                    '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', 
                    '#5a5c69', '#F29E52', '#4e73df', '#1cc88a', '#36b9cc'
                ];

                // Function to create and maintain chart sizes
                const createCharts = () => {
                    // Clear any existing charts to prevent duplication
                    Chart.helpers.each(Chart.instances, (instance) => {
                        instance.destroy();
                    });

                    // 1. User Engagement Chart
                    const userEngagementCtx = document.getElementById('userEngagementChart').getContext('2d');
                    const userEngagementData = <?php echo json_encode($user_engagement_data); ?>;

                    new Chart(userEngagementCtx, {
                        type: 'bar',
                        data: {
                            labels: userEngagementData.map(user => user.username),
                            datasets: [
                                {
                                    label: 'Recipes Created',
                                    data: userEngagementData.map(user => user.recipe_count),
                                    backgroundColor: colors[0],
                                    borderColor: colors[0],
                                    borderWidth: 1
                                },
                                {
                                    label: 'Favorites Added',
                                    data: userEngagementData.map(user => user.favorite_count),
                                    backgroundColor: colors[1],
                                    borderColor: colors[1],
                                    borderWidth: 1
                                },
                                {
                                    label: 'Comments Made',
                                    data: userEngagementData.map(user => user.comment_count),
                                    backgroundColor: colors[2],
                                    borderColor: colors[2],
                                    borderWidth: 1
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: true,
                            aspectRatio: 1.5,
                            scales: {
                                y: {
                                    beginAtZero: true
                                }
                            },
                            plugins: {
                                title: {
                                    display: true,
                                    text: 'Top User Engagement'
                                }
                            }
                        }
                    });

                    // 2. Recipe Performance Chart
                    const recipePerformanceCtx = document.getElementById('recipePerformanceChart').getContext('2d');
                    const recipePerformanceData = <?php echo json_encode($recipe_performance_data); ?>;

                    new Chart(recipePerformanceCtx, {
                        type: 'bar',
                        data: {
                            labels: recipePerformanceData.map(recipe => recipe.recipe_name),
                            datasets: [
                                {
                                    label: 'Favorites',
                                    data: recipePerformanceData.map(recipe => recipe.favorite_count),
                                    backgroundColor: colors[3],
                                    borderColor: colors[3],
                                    borderWidth: 1
                                },
                                {
                                    label: 'Comments',
                                    data: recipePerformanceData.map(recipe => recipe.comment_count),
                                    backgroundColor: colors[4],
                                    borderColor: colors[4],
                                    borderWidth: 1
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: true,
                            aspectRatio: 1.5,
                            indexAxis: 'y',
                            scales: {
                                x: {
                                    beginAtZero: true
                                }
                            },
                            plugins: {
                                title: {
                                    display: true,
                                    text: 'Top Performing Recipes'
                                }
                            }
                        }
                    });

                    // 3. Category Distribution Chart
                    const categoryDistributionCtx = document.getElementById('categoryDistributionChart').getContext('2d');
                    const categoryDistributionData = <?php echo json_encode($category_distribution_data); ?>;

                    new Chart(categoryDistributionCtx, {
                        type: 'pie',
                        data: {
                            labels: categoryDistributionData.map(category => category.category_name),
                            datasets: [{
                                data: categoryDistributionData.map(category => category.recipe_count),
                                backgroundColor: colors,
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: true,
                            aspectRatio: 1.5,
                            plugins: {
                                title: {
                                    display: true,
                                    text: 'Recipe Category Distribution'
                                }
                            }
                        }
                    });

                    // 4. Ad Performance Chart
                    const adPerformanceCtx = document.getElementById('adPerformanceChart').getContext('2d');
                    const adPerformanceData = <?php echo json_encode($ad_performance_data); ?>;

                    new Chart(adPerformanceCtx, {
                        type: 'bar',
                        data: {
                            labels: adPerformanceData.map(ad => ad.title),
                            datasets: [
                                {
                                    label: 'Impressions',
                                    data: adPerformanceData.map(ad => ad.impressions),
                                    backgroundColor: colors[6],
                                    borderColor: colors[6],
                                    borderWidth: 1,
                                    yAxisID: 'y'
                                },
                                {
                                    label: 'Clicks',
                                    data: adPerformanceData.map(ad => ad.clicks),
                                    backgroundColor: colors[7],
                                    borderColor: colors[7],
                                    borderWidth: 1,
                                    yAxisID: 'y'
                                },
                                {
                                    label: 'CTR %',
                                    data: adPerformanceData.map(ad => ad.ctr),
                                    backgroundColor: colors[8],
                                    borderColor: colors[8],
                                    borderWidth: 1,
                                    type: 'line',
                                    yAxisID: 'y1'
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: true,
                            aspectRatio: 1.5,
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    title: {
                                        display: true,
                                        text: 'Count'
                                    }
                                },
                                y1: {
                                    position: 'right',
                                    beginAtZero: true,
                                    title: {
                                        display: true,
                                        text: 'CTR %'
                                    },
                                    grid: {
                                        drawOnChartArea: false
                                    }
                                }
                            },
                            plugins: {
                                title: {
                                    display: true,
                                    text: 'Ad Performance'
                                }
                            }
                        }
                    });
                };

                // Initialize charts on page load
                document.addEventListener('DOMContentLoaded', function() {
                    createCharts();
                    
                    // Prevent chart resizing issues by only resizing on window resize events
                    let resizeTimer;
                    window.addEventListener('resize', function() {
                        clearTimeout(resizeTimer);
                        resizeTimer = setTimeout(function() {
                            createCharts();
                        }, 250);
                    });
                });
            </script>
        </main>
    </div>
    
    <script>
        // Add any JavaScript functionality here
        document.addEventListener('DOMContentLoaded', function() {
            // Example: Highlight active menu item
            const currentPath = window.location.search;
            const menuLinks = document.querySelectorAll('.admin-menu a');
            
            menuLinks.forEach(link => {
                if (link.getAttribute('href').includes(currentPath)) {
                    link.classList.add('active');
                } else {
                    link.classList.remove('active');
                }
            });
        });
    </script>
</body>
</html>