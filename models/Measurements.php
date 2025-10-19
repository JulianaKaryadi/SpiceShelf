<?php
// models/Measurements.php
class Measurements {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // Get all measurement units
    public function getMeasurements() {
        $query = "SELECT * FROM measurements";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // Get a specific measurement by its ID
    public function getMeasurementById($measurement_id) {
        $query = "SELECT * FROM measurements WHERE measurement_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $measurement_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
}
?>
