<?php
// adminviews/ad_reports.php
// This view displays ad statistics and reports for admins
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ad Reports - Admin Dashboard</title>
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
        
        /* Ad Reports Specific Styles */
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
        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stats-card {
            background-color: #fff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            border-left: 4px solid #F29E52;
        }
        .stats-card-title {
            font-size: 14px;
            text-transform: uppercase;
            font-weight: 600;
            color: #555;
            margin-top: 0;
            margin-bottom: 10px;
        }
        .stats-card-value {
            font-size: 24px;
            font-weight: 700;
            color: #333;
            margin: 0;
        }
        .stats-card-icon {
            float: right;
            font-size: 24px;
            color: #F29E52;
            opacity: 0.7;
        }
        .table-container {
            background-color: #fff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 30px;
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
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }
        .status-active {
            background-color: #d4edda;
            color: #155724;
        }
        .status-inactive {
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
                <li><a href="index.php?action=adminAdReports" class="active"><i class="fas fa-chart-bar"></i> Ad Performance</a></li>
                
                <li class="admin-menu-category">Advertisements</li>
                <li><a href="index.php?action=adminAds"><i class="fas fa-ad"></i> Manage Ads</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="admin-content">
            <header class="admin-header">
                <h1 class="admin-title">Ad Performance Reports</h1>
                <div class="admin-user-info">
                    <span>Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                    <a href="index.php?action=adminLogout" class="admin-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </header>
            
            <!-- Reports Header -->
            <div class="reports-header">
                <h2><i class="fas fa-chart-bar"></i> Ad Performance Metrics</h2>
                <div class="actions">
                    <a href="index.php?action=adminExportAdReport" class="export-btn">
                         <i class="fas fa-file-export"></i> Export Report (CSV)
                    </a> 
                </div>
            </div>

            <!-- Top Performing Ads Table -->
            <div class="table-container">
                <h3 class="table-title">
                    <i class="fas fa-trophy"></i> Top Performing Ads
                </h3>
                <div class="table-responsive">
                    <table class="report-table">
                        <thead>
                            <tr>
                                <th>Ad Title</th>
                                <th>Position</th>
                                <th>Impressions</th>
                                <th>Clicks</th>
                                <th>CTR</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (isset($top_performing_ads) && !empty($top_performing_ads)): ?>
                                <?php foreach ($top_performing_ads as $ad): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($ad['title']); ?></td>
                                    <td><?php echo htmlspecialchars($ad['position']); ?></td>
                                    <td><?php echo number_format($ad['impressions']); ?></td>
                                    <td><?php echo number_format($ad['clicks']); ?></td>
                                    <td>
                                        <?php 
                                        $ad_ctr = $ad['impressions'] > 0 ? ($ad['clicks'] / $ad['impressions']) * 100 : 0;
                                        echo number_format($ad_ctr, 2) . '%'; 
                                        ?>
                                    </td>
                                    <td>
                                        <span class="status-badge <?php echo $ad['status'] === 'active' ? 'status-active' : 'status-inactive'; ?>">
                                            <?php echo ucfirst($ad['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="index.php?action=adminAds" class="action-btn">
                                            <i class="fas fa-edit"></i> Manage
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center">No ad performance data available</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Ad Performance by Position -->
            <div class="table-container">
                <h3 class="table-title">
                    <i class="fas fa-map-marker-alt"></i> Performance by Position
                </h3>
                <div class="table-responsive">
                    <table class="report-table">
                        <thead>
                            <tr>
                                <th>Position</th>
                                <th>Total Ads</th>
                                <th>Impressions</th>
                                <th>Clicks</th>
                                <th>Average CTR</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (isset($position_stats) && !empty($position_stats)): ?>
                                <?php foreach ($position_stats as $position): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($position['position']); ?></td>
                                    <td><?php echo $position['total_ads']; ?></td>
                                    <td><?php echo number_format($position['impressions']); ?></td>
                                    <td><?php echo number_format($position['clicks']); ?></td>
                                    <td>
                                        <?php 
                                        $pos_ctr = $position['impressions'] > 0 ? ($position['clicks'] / $position['impressions']) * 100 : 0;
                                        echo number_format($pos_ctr, 2) . '%'; 
                                        ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center">No position data available</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Highlight active menu item
        document.addEventListener('DOMContentLoaded', function() {
            const currentPath = window.location.search;
            const menuLinks = document.querySelectorAll('.admin-menu a');
            
            menuLinks.forEach(link => {
                if (link.getAttribute('href').includes('adminAdReports')) {
                    link.classList.add('active');
                } else {
                    link.classList.remove('active');
                }
            });
        });
    </script>
</body>
</html>