<?php
// models/Ingredients.php
class Ingredients {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // Get all ingredients
    public function getIngredients() {
        $query = "SELECT * FROM ingredients";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // Get a specific ingredient by its ID
    public function getIngredientById($ingredient_id) {
        $query = "SELECT * FROM ingredients WHERE ingredient_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $ingredient_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
}
?>
