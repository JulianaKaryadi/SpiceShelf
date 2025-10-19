<?php
// adminviews/users.php

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
    <title>User Management - SpiceShelf Admin</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <!-- Add Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Include the same admin panel styles as dashboard.php */
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
        
        /* Users specific styles */
        .admin-panel-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .search-form {
            display: flex;
            margin-bottom: 20px;
        }
        .search-form input {
            flex: 1;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 4px 0 0 4px;
            font-size: 15px;
        }
        .search-form button {
            padding: 10px 20px;
            background-color: #F29E52;
            color: white;
            border: none;
            border-radius: 0 4px 4px 0;
            cursor: pointer;
        }
        .search-form button:hover {
            background-color: #e48a3c;
        }
        .user-filters {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }
        .user-filters select {
            padding: 8px 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .user-table-container {
            background-color: #fff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            overflow-x: auto;
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
        .user-action-btn.delete {
            background-color: #dc3545;
        }
        .user-action-btn.delete:hover {
            background-color: #c82333;
        }
        .user-status {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            text-align: center;
        }
        .user-status.active {
            background-color: #d4edda;
            color: #155724;
        }
        .user-status.inactive {
            background-color: #f8d7da;
            color: #721c24;
        }
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }
        .pagination-link {
            display: inline-block;
            padding: 8px 14px;
            margin: 0 3px;
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 4px;
            color: #333;
            text-decoration: none;
            transition: all 0.3s;
        }
        .pagination-link:hover {
            background-color: #f8f9fa;
            border-color: #F29E52;
        }
        .pagination-link.active {
            background-color: #F29E52;
            border-color: #F29E52;
            color: #fff;
        }
        .success-message {
            background-color: #d4edda;
            color: #155724;
            padding: 12px 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            border-left: 4px solid #28a745;
        }
        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 12px 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            border-left: 4px solid #dc3545;
        }
        .user-report-section {
            margin-top: 30px;
        }
        .user-report-card {
            background-color: #fff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }
        .user-report-title {
            margin-top: 0;
            margin-bottom: 15px;
            color: #333;
            font-size: 18px;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
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
                <li><a href="index.php?action=adminUsers" class="active"><i class="fas fa-users"></i> View Users</a></li>
                
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
                <h1 class="admin-title">User Management</h1>
                <div class="admin-user-info">
                    <span>Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                    <a href="index.php?action=adminLogout" class="admin-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </header>
            
            <!-- Success/Error Messages -->
            <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle"></i> User deleted successfully.
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['error']) && $_GET['error'] == 1): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i> Error deleting user. Please try again.
                </div>
            <?php endif; ?>
            
            <!-- Search and Filter -->
            <div class="admin-panel-header">
                <h2><i class="fas fa-users"></i> Registered Users</h2>
            </div>
            
            <form class="search-form" action="index.php" method="GET">
                <input type="hidden" name="action" value="adminUsers">
                <input type="text" name="search" placeholder="Search by username or email" 
                       value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                <button type="submit"><i class="fas fa-search"></i> Search</button>
            </form>
            
            <div class="user-filters">
                <select name="status" id="status-filter" onchange="window.location.href=this.value">
                    <option value="index.php?action=adminUsers">All Users</option>
                    <option value="index.php?action=adminUsers&status=active" <?php echo (isset($_GET['status']) && $_GET['status'] == 'active') ? 'selected' : ''; ?>>
                        Active Users
                    </option>
                    <option value="index.php?action=adminUsers&status=inactive" <?php echo (isset($_GET['status']) && $_GET['status'] == 'inactive') ? 'selected' : ''; ?>>
                        Inactive Users
                    </option>
                </select>
            </div>
            
            <!-- Users Table -->
            <div class="user-table-container">
                <table class="user-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Registration Date</th>
                            <th>Recipes</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo $user['user_id']; ?></td>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <?php 
                                        // Get recipe count for this user - this should be provided in the controller
                                        echo isset($user['recipe_count']) ? $user['recipe_count'] : '0'; 
                                    ?>
                                </td>
                                <td>
                                    <span class="user-status <?php echo $user['status']; ?>">
                                        <?php echo ucfirst($user['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="index.php?action=adminViewUser&id=<?php echo $user['user_id']; ?>" class="user-action-btn">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                    <a href="index.php?action=adminDeleteUser&id=<?php echo $user['user_id']; ?>" 
                                       class="user-action-btn delete"
                                       onclick="return confirm('Are you sure you want to delete this user? All their recipes and data will be permanently removed. This action cannot be undone.');">
                                        <i class="fas fa-trash"></i> Delete
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        
                        <?php if (empty($users)): ?>
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 20px;">No users found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="pagination">
                <?php 
                    // Calculate total pages - this should be provided in the controller
                    $total_pages = isset($total_pages) ? $total_pages : 1;
                    $current_page = isset($_GET['page']) ? intval($_GET['page']) : 1;
                    
                    // Previous page link
                    if ($current_page > 1): 
                ?>
                    <a href="index.php?action=adminUsers&page=<?php echo $current_page - 1; ?>" class="pagination-link">
                        <i class="fas fa-chevron-left"></i> Previous
                    </a>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="index.php?action=adminUsers&page=<?php echo $i; ?>" 
                       class="pagination-link <?php echo ($i == $current_page) ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
                
                <?php if ($current_page < $total_pages): ?>
                    <a href="index.php?action=adminUsers&page=<?php echo $current_page + 1; ?>" class="pagination-link">
                        Next <i class="fas fa-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </div>
            
            <!-- User Engagement Report Section -->
            <div class="user-report-section">
                <div class="user-report-card">
                    <h3 class="user-report-title">
                        <i class="fas fa-chart-line"></i> User Engagement Overview
                    </h3>
                    
                    <p>View comprehensive user engagement metrics and generate detailed reports.</p>
                    
                    <div style="margin-top: 15px;">
                        <a href="index.php?action=adminUserReports" class="user-action-btn">
                            <i class="fas fa-chart-bar"></i> View Complete User Reports
                        </a>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <script>
        // Add any JavaScript functionality here
        document.addEventListener('DOMContentLoaded', function() {
            // Example: Highlight active menu item
            const currentPath = window.location.search;
            const menuLinks = document.querySelectorAll('.admin-menu a');
            
            menuLinks.forEach(link => {
                if (link.getAttribute('href').includes('adminUsers')) {
                    link.classList.add('active');
                } else if (!currentPath.includes('admin') && link.getAttribute('href').includes('adminDashboard')) {
                    link.classList.add('active');
                } else {
                    link.classList.remove('active');
                }
            });
        });
    </script>
</body>
</html>