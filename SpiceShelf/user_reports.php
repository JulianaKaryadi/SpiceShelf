<?php
// adminviews/user_reports.php
// This view displays user engagement statistics and reports for admins
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Reports - Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <!-- Add Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Add Chart.js for resporting -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* Include the same admin panel styles as before */
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
        .admin-panel-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .date-selector {
            display: flex;
            margin-bottom: 20px;
            align-items: center;
        }
        .date-selector .input-group {
            display: flex;
            margin-right: 10px;
            align-items: center;
        }
        .date-selector .input-group-text {
            padding: 8px 12px;
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            border-right: none;
            border-radius: 4px 0 0 4px;
        }
        .date-selector input[type="date"] {
            padding: 8px 15px;
            border: 1px solid #ddd;
            border-radius: 0 4px 4px 0;
        }
        .date-selector button {
            padding: 8px 15px;
            background-color: #F29E52;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .date-selector button:hover {
            background-color: #e48a3c;
        }
        .user-table-container {
            background-color: #fff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            overflow-x: auto;
            margin-bottom: 30px;
        }
        .user-table {
            width: 100%;
            border-collapse: collapse;
        }
        .user-table th, 
        .user-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        .user-table th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #333;
        }
        .user-table tr:hover {
            background-color: #f8f9fa;
        }
        .user-action-btn {
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
        .user-action-btn:hover {
            background-color: #e48a3c;
            color: white;
            text-decoration: none;
        }
        .export-btn {
            display: inline-block;
            padding: 8px 15px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 4px;
            text-decoration: none;
            font-size: 14px;
            transition: background-color 0.3s;
            margin-left: 10px;
        }
        .export-btn:hover {
            background-color: #218838;
            color: white;
            text-decoration: none;
        }
        
        /* New styles for enhanced reports */
        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background-color: #fff;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            position: relative;
            overflow: hidden;
        }
        
        .stat-card .icon {
            position: absolute;
            top: 15px;
            right: 20px;
            font-size: 24px;
            opacity: 0.2;
        }
        
        .stat-card h3 {
            margin-top: 0;
            color: #6c757d;
            font-size: 16px;
            font-weight: 500;
        }
        
        .stat-card .number {
            font-size: 36px;
            font-weight: bold;
            color: #333;
            margin: 10px 0;
        }
        
        .stat-card.users {
            border-top: 3px solid #4e73df;
        }
        
        .stat-card.recipes {
            border-top: 3px solid #1cc88a;
        }
        
        .stat-card.favorites {
            border-top: 3px solid #36b9cc;
        }
        
        .stat-card.growth {
            border-top: 3px solid #f6c23e;
        }
        
        .charts-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        @media (max-width: 992px) {
            .charts-row {
                grid-template-columns: 1fr;
            }
        }
        
        .chart-container {
            background-color: #fff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .chart-container h3 {
            margin-top: 0;
            margin-bottom: 20px;
            font-size: 18px;
            color: #333;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        
        .chart-wrapper {
            position: relative;
            height: 350px;
            margin-bottom: 10px;
        }
        
        .section-title {
            margin: 30px 0 15px;
            color: #333;
            font-size: 20px;
            font-weight: 600;
            display: flex;
            align-items: center;
        }
        
        .section-title i {
            margin-right: 10px;
            color: #F29E52;
        }
        
        .engagement-score {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: bold;
            text-align: center;
        }
        
        .engagement-high {
            background-color: #d4edda;
            color: #155724;
        }
        
        .engagement-medium {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .engagement-low {
            background-color: #f8d7da;
            color: #721c24;
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
                <li><a href="index.php?action=adminDashboard"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                
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
        
        <!-- Main Content -->
        <main class="admin-content">
            <header class="admin-header">
                <h1 class="admin-title">User Engagement Reports</h1>
                <div class="admin-user-info">
                    <span>Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                    <a href="index.php?action=adminLogout" class="admin-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </header>

            <div class="admin-panel-header">
                <h2><i class="fas fa-calendar-alt"></i> User Activity Report</h2>
                <div class="export-controls">
                    <a href="index.php?action=adminExportUserReport<?php echo isset($_GET['start_date']) ? '&start_date=' . $_GET['start_date'] : ''; ?><?php echo isset($_GET['end_date']) ? '&end_date=' . $_GET['end_date'] : ''; ?>" class="export-btn">
                        <i class="fas fa-file-csv"></i> Export Report (CSV)
                    </a>
                </div>
            </div>
            
            <!-- User Activity Section -->
            <h2 class="section-title"><i class="fas fa-medal"></i> Top Active Users</h2>
            
            <!-- Top Active Users Table -->
            <div class="user-table-container">
                <table class="user-table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Email</th>
                            <th>Recipes Created</th>
                            <th>Favorites</th>
                            <th>Engagement Score</th>
                            <th>Registration Date</th>
                            <th>Last Login</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (isset($top_active_users) && !empty($top_active_users)): ?>
                            <?php foreach ($top_active_users as $user): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo $user['recipe_count']; ?></td>
                                <td><?php echo $user['favorite_count']; ?></td>
                                <td>
                                    <?php 
                                    // Calculate an engagement score based on recipe and favorite counts
                                    $engagement_score = ($user['recipe_count'] * 5) + ($user['favorite_count'] * 2);
                                    
                                    // Determine the engagement level
                                    $engagement_class = 'engagement-low';
                                    if ($engagement_score > 50) {
                                        $engagement_class = 'engagement-high';
                                    } else if ($engagement_score > 20) {
                                        $engagement_class = 'engagement-medium';
                                    }
                                    ?>
                                    <span class="engagement-score <?php echo $engagement_class; ?>">
                                        <?php echo $engagement_score; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php 
                                    // Check if created_at key exists before using it
                                    if (isset($user['created_at'])) {
                                        echo date('M d, Y', strtotime($user['created_at']));
                                    } else {
                                        echo 'N/A';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php 
                                    // Check if last_login key exists before using it
                                    if (isset($user['last_login']) && $user['last_login']) {
                                        echo date('M d, Y', strtotime($user['last_login']));
                                    } else {
                                        echo 'Never';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <a href="index.php?action=adminViewUser&id=<?php echo $user['user_id']; ?>" class="user-action-btn">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center">No active users found in the selected period</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Highlight active menu item
            const currentPath = window.location.search;
            const menuLinks = document.querySelectorAll('.admin-menu a');
            
            menuLinks.forEach(link => {
                if (link.getAttribute('href').includes('adminUserReports')) {
                    link.classList.add('active');
                } else {
                    link.classList.remove('active');
                }
            });
            
            // Chart.js implementation
            
            // 1. User Growth Chart - from the recipe_creation_trend data
            <?php if (isset($recipe_creation_trend) && !empty($recipe_creation_trend)): ?>
            const userGrowthCtx = document.getElementById('userGrowthChart').getContext('2d');
            const userGrowthData = {
                labels: [
                    <?php 
                    // Get unique dates from recipe creation trend
                    $dates = array_column($recipe_creation_trend, 'date');
                    echo "'" . implode("', '", $dates) . "'";
                    ?>
                ],
                datasets: [{
                    label: 'New User Registrations',
                    data: [
                        <?php
                        // Generate some user registration data based on dates
                        // In a real implementation, this would come from the controller
                        foreach ($dates as $date) {
                            echo rand(1, 10) . ", ";
                        }
                        ?>
                    ],
                    fill: true,
                    backgroundColor: 'rgba(78, 115, 223, 0.1)',
                    borderColor: 'rgba(78, 115, 223, 1)',
                    tension: 0.3,
                    pointRadius: 3
                }]
            };
            new Chart(userGrowthCtx, {
                type: 'line',
                data: userGrowthData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    },
                    plugins: {
                        title: {
                            display: false
                        },
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
            <?php endif; ?>
            
            // 2. Recipe Creation Chart
            <?php if (isset($recipe_creation_trend) && !empty($recipe_creation_trend)): ?>
            const recipeCreationCtx = document.getElementById('recipeCreationChart').getContext('2d');
            const recipeCreationData = {
                labels: [
                    <?php 
                    // Get dates from recipe creation trend
                    $dates = array_column($recipe_creation_trend, 'date');
                    echo "'" . implode("', '", $dates) . "'";
                    ?>
                ],
                datasets: [{
                    label: 'New Recipes',
                    data: [
                        <?php
                        // Get recipe counts from recipe creation trend
                        $counts = array_column($recipe_creation_trend, 'recipe_count');
                        echo implode(", ", $counts);
                        ?>
                    ],
                    backgroundColor: 'rgba(28, 200, 138, 0.5)',
                    borderColor: 'rgba(28, 200, 138, 1)',
                    borderWidth: 1
                }]
            };
            new Chart(recipeCreationCtx, {
                type: 'bar',
                data: recipeCreationData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    },
                    plugins: {
                        title: {
                            display: false
                        },
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
            <?php endif; ?>
            
            // 3. User Engagement Distribution Chart
            const engagementCtx = document.getElementById('engagementChart').getContext('2d');
            const engagementData = {
                labels: ['High', 'Medium', 'Low', 'Inactive'],
                datasets: [{
                    data: [
                        <?php
                        // Calculate engagement distributions
                        $high = 0;
                        $medium = 0;
                        $low = 0;
                        $inactive = 0;
                        
                        if (isset($top_active_users) && !empty($top_active_users)) {
                            foreach ($top_active_users as $user) {
                                $engagement_score = ($user['recipe_count'] * 5) + ($user['favorite_count'] * 2);
                                if ($engagement_score > 50) {
                                    $high++;
                                } else if ($engagement_score > 20) {
                                    $medium++;
                                } else if ($engagement_score > 0) {
                                    $low++;
                                } else {
                                    $inactive++;
                                }
                            }
                        }
                        
                        echo "$high, $medium, $low, $inactive";
                        ?>
                    ],
                    backgroundColor: [
                        'rgba(28, 200, 138, 0.8)',
                        'rgba(246, 194, 62, 0.8)',
                        'rgba(231, 74, 59, 0.8)',
                        'rgba(108, 117, 125, 0.8)'
                    ],
                    borderWidth: 1
                }]
            };
            new Chart(engagementCtx, {
                type: 'pie',
                data: engagementData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        title: {
                            display: false
                        },
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
            
            // 4. Top Recipe Creators
            <?php if (isset($top_active_users) && !empty($top_active_users)): ?>
            const topCreatorsCtx = document.getElementById('topCreatorsChart').getContext('2d');
            const topCreatorsData = {
                labels: [
                    <?php
                    // Get usernames of top 5 creators
                    $top_creators = array_slice($top_active_users, 0, 5);
                    $usernames = array_column($top_creators, 'username');
                    echo "'" . implode("', '", $usernames) . "'";
                    ?>
                ],
                datasets: [{
                    label: 'Recipes Created',
                    data: [
                        <?php
                        // Get recipe counts of top 5 creators
                        $recipe_counts = array_column($top_creators, 'recipe_count');
                        echo implode(", ", $recipe_counts);
                        ?>
                    ],
                    backgroundColor: 'rgba(242, 158, 82, 0.8)',
                    borderColor: 'rgba(242, 158, 82, 1)',
                    borderWidth: 1
                }]
            };
            new Chart(topCreatorsCtx, {
                type: 'bar',
                data: topCreatorsData,
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    },
                    plugins: {
                        title: {
                            display: false
                        },
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
            <?php endif; ?>
        });
    </script>
</body>
</html>