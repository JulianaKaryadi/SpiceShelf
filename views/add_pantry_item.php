<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Pantry Item - SpiceShelf</title>
    <link rel="stylesheet" href="assets/css/add_pantry.css">
</head>
<body>
    <?php include('includes/header.php'); ?>

    <h1>Add New Pantry Item</h1>

    <form method="POST" action="index.php?action=add">
        <div class="form-group">
            <label for="ingredient_id">Ingredient:</label>
            <select id="ingredient_id" name="ingredient_id" required>
                <?php foreach ($ingredient_list as $ingredient): ?>
                    <option value="<?= $ingredient['ingredient_id']; ?>">
                        <?= htmlspecialchars($ingredient['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label for="quantity">Quantity:</label>
            <input type="number" id="quantity" name="quantity" step="0.01" min="0" required>
        </div>
        
        <div class="form-group">
            <label for="measurement_id">Measurement:</label>
            <select id="measurement_id" name="measurement_id" required>
                <?php foreach ($measurement_list as $measurement): ?>
                    <option value="<?= $measurement['measurement_id']; ?>">
                        <?= htmlspecialchars($measurement['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label for="expiration_date">Expiration Date:</label>
            <input type="date" id="expiration_date" name="expiration_date" required>
        </div>

        <button type="submit">Add Item</button>
    </form>
</body>
</html>