<?php
// adminviews/recipe_reports.php
// This view displays recipe statistics and reports for admins
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recipe Reports - Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <!-- Add Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Add Chart.js for visualizations -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        
        /* Recipe Reports Specific Styles */
        .reports-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .date-selector {
            display: flex;
            margin-bottom: 20px;
            gap: 10px;
        }
        .date-selector .input-group {
            display: flex;
        }
        .date-selector .input-group-text {
            padding: 10px 15px;
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            border-right: none;
            border-radius: 4px 0 0 4px;
        }
        .date-selector input[type="date"] {
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 0 4px 4px 0;
        }
        .date-selector button {
            padding: 10px 15px;
            background-color: #F29E52;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .date-selector button:hover {
            background-color: #e48a3c;
        }
        
        /* Stat Cards */
        .stat-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background-color: #fff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            text-align: center;
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
        
        .stat-card.recipes {
            border-top: 3px solid #1cc88a;
        }
        
        .stat-card.favorites {
            border-top: 3px solid #36b9cc;
        }
        
        .stat-card.public {
            border-top: 3px solid #4e73df;
        }
        
        .stat-card.categories {
            border-top: 3px solid #f6c23e;
        }
        
        /* Charts */
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
        
        /* Tables */
        .table-container {
            background-color: #fff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 30px;
            overflow-x: auto;
        }
        
        .table-title {
            font-size: 18px;
            font-weight: 600;
            color: #333;
            margin-top: 0;
            margin-bottom: 20px;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        
        .report-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .report-table th, 
        .report-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        .report-table th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #333;
        }
        
        .report-table tr:hover {
            background-color: #f8f9fa;
        }
        
        .action-btn {
            display: inline-block;
            padding: 6px 12px;
            background-color: #F29E52;
            color: white;
            border: none;
            border-radius: 4px;
            text-decoration: none;
            font-size: 13px;
            transition: background-color 0.3s;
        }
        
        .action-btn:hover {
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
        }
        
        .export-btn:hover {
            background-color: #218838;
            color: white;
            text-decoration: none;
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
        
        .popular-tag {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            background-color: #d4edda;
            color: #155724;
            font-weight: bold;
            margin-right: 5px;
            margin-bottom: 5px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .status-badge.public {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-badge.private {
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
                <li><a href="index.php?action=adminRecipeReports" class="active"><i class="fas fa-chart-pie"></i> Recipe Favorites</a></li>
                <li><a href="index.php?action=adminAdReports"><i class="fas fa-chart-bar"></i> Ad Performance</a></li>
                
                <li class="admin-menu-category">Advertisements</li>
                <li><a href="index.php?action=adminAds"><i class="fas fa-ad"></i> Manage Ads</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="admin-content">
            <header class="admin-header">
                <h1 class="admin-title">Recipe Performance Reports</h1>
                <div class="admin-user-info">
                    <span>Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                    <a href="index.php?action=adminLogout" class="admin-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </header>

            <div class="reports-header">
                <h2><i class="fas fa-chart-pie"></i> Recipe Analytics</h2>
                <div class="actions">
                    <a href="index.php?action=adminExportRecipeReport<?php echo isset($_GET['start_date']) ? '&start_date=' . $_GET['start_date'] : ''; ?><?php echo isset($_GET['end_date']) ? '&end_date=' . $_GET['end_date'] : ''; ?>" class="export-btn">
                        <i class="fas fa-file-csv"></i> Export Report (CSV)
                    </a>
                </div>
            </div>
            
            <!-- Top Favorite Recipes Table (Enhanced) -->
            <h2 class="section-title"><i class="fas fa-heart"></i> Top Favorite Recipes</h2>
            <div class="table-container">
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>Recipe</th>
                            <th>Author</th>
                            <th>Creation Date</th>
                            <th>Categories</th>
                            <th>Status</th>
                            <th>Favorites</th>
                            <th>Comments</th>
                            <th>Popularity</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($top_favorites)): ?>
                            <?php foreach ($top_favorites as $recipe): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($recipe['recipe_name']); ?></td>
                                <td><?php echo htmlspecialchars($recipe['username']); ?></td>
                                <td>
                                    <?php echo (isset($recipe['created_at']) && $recipe['created_at']) ? 
                                        date('M d, Y', strtotime($recipe['created_at'])) : 'N/A'; ?>
                                </td>
                                <td>
                                    <?php 
                                    if (isset($recipe['categories']) && is_array($recipe['categories'])):
                                        foreach ($recipe['categories'] as $category): ?>
                                            <span class="popular-tag"><?php echo htmlspecialchars($category); ?></span>
                                        <?php endforeach;
                                    else: ?>
                                        <span>No categories</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (isset($recipe['public'])): ?>
                                        <span class="status-badge <?php echo $recipe['public'] ? 'public' : 'private'; ?>">
                                            <?php echo $recipe['public'] ? 'Public' : 'Private'; ?>
                                        </span>
                                    <?php else: ?>
                                        <span>N/A</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $recipe['favorite_count']; ?></td>
                                <td><?php echo isset($recipe['comment_count']) ? $recipe['comment_count'] : '0'; ?></td>
                                <td>
                                    <?php 
                                    // Display popularity score
                                    echo isset($recipe['popularity_score']) ? number_format($recipe['popularity_score'], 1) . '/10' : 'N/A';
                                    ?>
                                </td>
                                <td>
                                    <a href="index.php?action=adminViewRecipe&id=<?php echo $recipe['recipe_id']; ?>" 
                                       class="action-btn">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center">No data available</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Highlight active menu item
            const currentPath = window.location.search;
            const menuLinks = document.querySelectorAll('.admin-menu a');
            
            menuLinks.forEach(link => {
                if (link.getAttribute('href').includes('adminRecipeReports')) {
                    link.classList.add('active');
                } else {
                    link.classList.remove('active');
                }
            });
        });
    </script>
</body>
</html>