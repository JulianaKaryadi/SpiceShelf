<?php
// adminviews/ads.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ad Management - Admin Dashboard</title>
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
        
        /* Ads Management Specific Styles */
        .ads-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .add-ad-btn {
            padding: 10px 15px;
            background-color: #F29E52;
            color: white;
            border: none;
            border-radius: 4px;
            text-decoration: none;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            transition: background-color 0.3s;
        }
        .add-ad-btn i {
            margin-right: 8px;
        }
        .add-ad-btn:hover {
            background-color: #e48a3c;
            color: white;
            text-decoration: none;
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
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        .ads-table {
            width: 100%;
            border-collapse: collapse;
        }
        .ads-table th, 
        .ads-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        .ads-table th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #333;
        }
        .ads-table tr:hover {
            background-color: #f8f9fa;
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
        .action-btn {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 13px;
            margin-right: 5px;
            transition: background-color 0.3s;
        }
        .edit-btn {
            background-color: #ffc107;
            color: #212529;
        }
        .edit-btn:hover {
            background-color: #e0a800;
            color: #212529;
        }
        .delete-btn {
            background-color: #dc3545;
            color: white;
        }
        .delete-btn:hover {
            background-color: #c82333;
            color: white;
        }
        .action-btn i {
            margin-right: 0;
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
        
        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            overflow: auto;
        }
        .modal-content {
            background-color: #fff;
            margin: 10% auto;
            padding: 0;
            border-radius: 8px;
            width: 500px;
            max-width: 90%;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            position: relative;
        }
        .modal-header {
            padding: 15px 20px;
            background-color: #f8f9fa;
            border-bottom: 1px solid #eee;
            border-radius: 8px 8px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .modal-title {
            margin: 0;
            font-size: 18px;
            color: #333;
            font-weight: 600;
        }
        .close-modal {
            font-size: 24px;
            color: #aaa;
            cursor: pointer;
            background: none;
            border: none;
            padding: 0;
        }
        .close-modal:hover {
            color: #333;
        }
        .modal-body {
            padding: 20px;
        }
        .modal-footer {
            padding: 15px 20px;
            background-color: #f8f9fa;
            border-top: 1px solid #eee;
            border-radius: 0 0 8px 8px;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #333;
        }
        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        .form-select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            background-color: white;
        }
        .form-row {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
        }
        .form-col {
            flex: 1;
        }
        .btn {
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            font-size: 14px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }
        .btn-secondary:hover {
            background-color: #5a6268;
        }
        .btn-primary {
            background-color: #F29E52;
            color: white;
        }
        .btn-primary:hover {
            background-color: #e48a3c;
        }
        .btn-danger {
            background-color: #dc3545;
            color: white;
        }
        .btn-danger:hover {
            background-color: #c82333;
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
                <li><a href="index.php?action=adminAds" class="active"><i class="fas fa-ad"></i> Manage Ads</a></li>
            </ul>
        </aside>
        
        <!-- Admin Content -->
        <main class="admin-content">
            <header class="admin-header">
                <h1 class="admin-title">Ad Management</h1>
                <div class="admin-user-info">
                    <span>Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                    <a href="index.php?action=adminLogout" class="admin-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </header>
            
            <!-- Success/Error Messages -->
            <?php if (isset($_SESSION['ad_message'])): ?>
                <div class="<?php echo $_SESSION['ad_message_type'] === 'success' ? 'success-message' : 'error-message'; ?>">
                    <i class="fas <?php echo $_SESSION['ad_message_type'] === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
                    <?php echo $_SESSION['ad_message']; ?>
                </div>
                <?php 
                    // Clear the message after displaying
                    unset($_SESSION['ad_message']);
                    unset($_SESSION['ad_message_type']);
                ?>
            <?php endif; ?>
            
            <!-- Ads Header -->
            <div class="ads-header">
                <h2><i class="fas fa-ad"></i> Advertisement Management</h2>
                <button type="button" class="add-ad-btn" onclick="openAddModal()">
                    <i class="fas fa-plus"></i> Add New Ad
                </button>
            </div>
            
            <!-- Ad List -->
            <div class="table-container">
                <h3 class="table-title">Current Advertisements</h3>
                <div class="table-responsive">
                    <table class="ads-table">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Position</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (isset($ads) && !empty($ads)): ?>
                                <?php foreach ($ads as $ad): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($ad['title']); ?></td>
                                    <td><?php echo htmlspecialchars($ad['position']); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($ad['start_date'])); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($ad['end_date'])); ?></td>
                                    <td>
                                        <span class="status-badge <?php echo $ad['status'] === 'active' ? 'status-active' : 'status-inactive'; ?>">
                                            <?php echo ucfirst($ad['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button class="action-btn edit-btn" onclick="openEditModal(<?php echo htmlspecialchars(json_encode($ad)); ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <a href="index.php?action=adminAds&subaction=delete&id=<?php echo $ad['ad_id']; ?>" 
                                           class="action-btn delete-btn" 
                                           onclick="return confirm('Are you sure you want to delete this ad?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center">No ads found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Add Ad Modal -->
    <div id="addAdModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Advertisement</h5>
                <button type="button" class="close-modal" onclick="closeAddModal()">&times;</button>
            </div>
            <form action="index.php?action=adminAds&subaction=add" method="post" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="title" class="form-label">Title</label>
                        <input type="text" class="form-control" id="title" name="title" required>
                    </div>
                    <div class="form-group">
                        <label for="image" class="form-label">Image</label>
                        <input type="file" class="form-control" id="image" name="image" required>
                    </div>
                    <div class="form-group">
                        <label for="url" class="form-label">URL</label>
                        <input type="url" class="form-control" id="url" name="url" required>
                    </div>
                    <div class="form-group">
                        <label for="position" class="form-label">Position</label>
                        <select class="form-select" id="position" name="position" required>
                            <option value="sidebar">Sidebar</option>
                            <option value="popup">Popup</option>
                        </select>
                    </div>
                    <div class="form-row">
                        <div class="form-col">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" required>
                        </div>
                        <div class="form-col">
                            <label for="end_date" class="form-label">End Date</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeAddModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Ad Modal -->
    <div id="editAdModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Advertisement</h5>
                <button type="button" class="close-modal" onclick="closeEditModal()">&times;</button>
            </div>
            <form action="index.php?action=adminAds&subaction=edit" method="post" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="ad_id" id="edit_ad_id">
                    <div class="form-group">
                        <label for="edit_title" class="form-label">Title</label>
                        <input type="text" class="form-control" id="edit_title" name="title" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_image" class="form-label">Image</label>
                        <input type="file" class="form-control" id="edit_image" name="image">
                        <small class="text-muted">Leave empty to keep current image</small>
                    </div>
                    <div class="form-group">
                        <label for="edit_url" class="form-label">URL</label>
                        <input type="url" class="form-control" id="edit_url" name="url" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_position" class="form-label">Position</label>
                        <select class="form-select" id="edit_position" name="position" required>
                            <option value="sidebar">Sidebar</option>
                            <option value="popup">Popup</option>
                        </select>
                    </div>
                    <div class="form-row">
                        <div class="form-col">
                            <label for="edit_start_date" class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="edit_start_date" name="start_date" required>
                        </div>
                        <div class="form-col">
                            <label for="edit_end_date" class="form-label">End Date</label>
                            <input type="date" class="form-control" id="edit_end_date" name="end_date" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="edit_status" class="form-label">Status</label>
                        <select class="form-select" id="edit_status" name="status">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Add Modal Functions
        function openAddModal() {
            document.getElementById('addAdModal').style.display = 'block';
        }
        
        function closeAddModal() {
            document.getElementById('addAdModal').style.display = 'none';
        }
        
        // Edit Modal Functions
        function openEditModal(ad) {
            // Populate the form with the ad data
            document.getElementById('edit_ad_id').value = ad.ad_id;
            document.getElementById('edit_title').value = ad.title;
            document.getElementById('edit_url').value = ad.url;
            document.getElementById('edit_position').value = ad.position;
            
            // Format dates for the date inputs (YYYY-MM-DD)
            let startDate = new Date(ad.start_date);
            let endDate = new Date(ad.end_date);
            
            document.getElementById('edit_start_date').value = startDate.toISOString().split('T')[0];
            document.getElementById('edit_end_date').value = endDate.toISOString().split('T')[0];
            
            document.getElementById('edit_status').value = ad.status;
            
            // Display the modal
            document.getElementById('editAdModal').style.display = 'block';
        }
        
        function closeEditModal() {
            document.getElementById('editAdModal').style.display = 'none';
        }
        
        // Close modals when clicking outside
        window.onclick = function(event) {
            if (event.target == document.getElementById('addAdModal')) {
                closeAddModal();
            }
            if (event.target == document.getElementById('editAdModal')) {
                closeEditModal();
            }
        }
        
        // Highlight active sidebar item
        document.addEventListener('DOMContentLoaded', function() {
            const adminAdsLink = document.querySelector('a[href="index.php?action=adminAds"]');
            if (adminAdsLink) {
                adminAdsLink.classList.add('active');
            }
        });
    </script>
</body>
</html>