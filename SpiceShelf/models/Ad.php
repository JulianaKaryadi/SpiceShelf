<?php
// models/Ad.php

class Ad {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // Get active ads for a specific position
    public function getActiveAds($position) {
        // Check if connection exists
        if (!$this->conn) {
            error_log("No database connection in Ad model");
            return [];
        }
        
        try {
            $today = date('Y-m-d');
            $query = "SELECT * FROM ads 
                    WHERE position = ? 
                    AND status = 'active' 
                    AND start_date <= ? 
                    AND end_date >= ?";
                    
            if ($stmt = $this->conn->prepare($query)) {
                $stmt->bind_param("sss", $position, $today, $today);
                $stmt->execute();
                $result = $stmt->get_result();
                return $result->fetch_all(MYSQLI_ASSOC);
            }
        } catch (Exception $e) {
            error_log("Error in getActiveAds: " . $e->getMessage());
        }
        
        return [];
    }

    // Get random ad for a position
    public function getRandomAd($position) {
        try {
            $ads = $this->getActiveAds($position);
            if (count($ads) > 0) {
                return $ads[array_rand($ads)];
            }
        } catch (Exception $e) {
            error_log("Error in getRandomAd: " . $e->getMessage());
        }
        
        return null;
    }
}