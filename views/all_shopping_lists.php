<?php
// views/all_shopping_lists.php
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Shopping Lists - SpiceShelf</title>
    <!-- Include Bootstrap and jQuery -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/shopping_lists.css">
</head>
<body>

<?php include 'includes/header.php'; ?>

<div class="content-wrapper">
    <div class="container-fluid p-4">
        <div class="row mb-4">
            <div class="col-md-8">
                <h1>Shopping Lists</h1>
                <p>Manage your shopping lists and add purchased items to your pantry.</p>
            </div>
        </div>

        <div class="row">
            <?php if (!empty($lists)): ?>
                <?php foreach ($lists as $list): ?>
                    <div class="col-md-4 col-sm-6 mb-4">
                        <div class="card shopping-list-card h-100">
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($list['name']) ?></h5>
                                <p class="card-text text-muted">Created on <?= date('M d, Y', strtotime($list['created_at'])) ?></p>
                                
                                <?php if ($list['item_count'] > 0): ?>
                                    <div class="progress">
                                        <div class="progress-bar" role="progressbar" 
                                            style="width: <?= ($list['purchased_count'] / $list['item_count']) * 100 ?>%;" 
                                            aria-valuenow="<?= ($list['purchased_count'] / $list['item_count']) * 100 ?>" 
                                            aria-valuemin="0" 
                                            aria-valuemax="100">
                                        </div>
                                    </div>
                                    <p class="small text-muted"><?= $list['purchased_count'] ?> of <?= $list['item_count'] ?> items purchased</p>
                                <?php else: ?>
                                    <p class="small text-muted">No items in this list</p>
                                <?php endif; ?>
                                
                                <a href="index.php?action=shopping_list&id=<?= $list['shopping_list_id'] ?>" class="btn btn-primary mt-2">
                                    <i class="fas fa-shopping-cart"></i> View List
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
            
            <!-- Create New List Card -->
            <div class="col-md-4 col-sm-6 mb-4">
                <div class="card create-list-card h-100" id="createListCard">
                    <div class="card-body text-center">
                        <i class="fas fa-plus-circle create-list-icon"></i>
                        <h5>Create New Shopping List</h5>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create List Modal -->
<div class="modal fade" id="createListModal" tabindex="-1" aria-labelledby="createListModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createListModalLabel">Create New Shopping List</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="createListForm">
                    <div class="mb-3">
                        <label for="listName" class="form-label">List Name:</label>
                        <input type="text" class="form-control" id="listName" name="name" placeholder="e.g., Weekly Groceries" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="createListBtn">Create List</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<?php if (isset($_SESSION['error'])): ?>
<script>
    // Pass PHP session errors to JavaScript
    var phpSessionError = "<?= addslashes($_SESSION['error']) ?>";
</script>
<?php unset($_SESSION['error']); endif; ?>
<script src="assets/js/shopping_lists.js"></script>

</body>
</html>