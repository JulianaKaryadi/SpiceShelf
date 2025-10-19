<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Pantry Item - SpiceShelf</title>
    <link rel="stylesheet" href="assets/css/update_pantry.css">
</head>
<body>
    <?php 
    include('includes/header.php');

    if (!isset($_SESSION['user_id'])) {
        header('Location: index.php?action=login');
        exit;
    }
    ?>

    <h1>Update Pantry Item</h1>

    <form method="POST" action="index.php?action=update">
        <input type="hidden" name="pantry_id" value="<?= htmlspecialchars($item['pantry_id']); ?>">

        <div class="form-group">
            <label for="ingredient">Ingredient:</label>
            <select id="ingredient" name="ingredient_id" required>
                <?php foreach ($ingredients as $ingredient): ?>
                    <option value="<?= $ingredient['ingredient_id']; ?>" 
                        <?= $item['ingredient'] === $ingredient['name'] ? 'selected' : ''; ?>>
                        <?= htmlspecialchars($ingredient['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="quantity">Quantity:</label>
            <input type="number" id="quantity" name="quantity" 
                value="<?= htmlspecialchars($item['quantity']); ?>" required>
        </div>

        <div class="form-group">
            <label for="measurement">Measurement:</label>
            <select id="measurement" name="measurement_id" required>
                <?php foreach ($measurements as $measurement): ?>
                    <option value="<?= $measurement['measurement_id']; ?>" 
                        <?= $item['measurement'] === $measurement['name'] ? 'selected' : ''; ?>>
                        <?= htmlspecialchars($measurement['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="expiration_date">Expiration Date:</label>
            <input type="date" id="expiration_date" name="expiration_date" 
                value="<?= htmlspecialchars($item['expiration_date']); ?>" required>
        </div>

        <button type="submit">Update Item</button>
    </form>
</body>
</html>