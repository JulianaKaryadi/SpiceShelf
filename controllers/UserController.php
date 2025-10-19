<?php
class UserController {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // Register method
    public function register() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
            $username = trim($_POST['username']);
            $email = trim($_POST['email']);
            $password = trim($_POST['password']);
            $confirm_password = trim($_POST['confirm_password']);
            
            // Check if the passwords match
            if ($password !== $confirm_password) {
                echo "Passwords do not match.";
                return;
            }
    
            // Check if username or email already exists
            $query_check_user = "SELECT * FROM users WHERE username = ? OR email = ?";
            $stmt_check_user = $this->conn->prepare($query_check_user);
            $stmt_check_user->bind_param("ss", $username, $email);
            $stmt_check_user->execute();
            $result_check_user = $stmt_check_user->get_result();
            
            if ($result_check_user->num_rows > 0) {
                echo "Username or email already exists. Please choose a different one.";
                return;
            }
            
            // Hash the password
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            
            // Insert the user data into the database
            $query = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("sss", $username, $email, $hashed_password);
        
            if ($stmt->execute()) {
                // Get the user_id of the newly inserted user
                $user_id = $stmt->insert_id;
        
                // Insert dietary preferences
                if (isset($_POST['dietary_preferences'])) {
                    foreach ($_POST['dietary_preferences'] as $preference_id) {
                        $query_pref = "INSERT INTO user_dietary_preferences (user_id, preference_id) VALUES (?, ?)";
                        $stmt_pref = $this->conn->prepare($query_pref);
                        $stmt_pref->bind_param("ii", $user_id, $preference_id);
                        $stmt_pref->execute();
                    }
                }
        
                // Insert allergies
                if (isset($_POST['allergies'])) {
                    foreach ($_POST['allergies'] as $allergy_id) {
                        $query_allergy = "INSERT INTO user_allergies (user_id, allergy_id) VALUES (?, ?)";
                        $stmt_allergy = $this->conn->prepare($query_allergy);
                        $stmt_allergy->bind_param("ii", $user_id, $allergy_id);
                        $stmt_allergy->execute();
                    }
                }
        
                // Redirect to the login page upon successful registration
                header('Location: index.php?action=login');
                exit;
            } else {
                echo "Registration failed. Please try again.";
            }
        }
    
        // Fetch dietary preferences and allergies
        $query_preferences = "SELECT * FROM dietary_preferences";
        $result_preferences = $this->conn->query($query_preferences);
        $preferences = $result_preferences->fetch_all(MYSQLI_ASSOC);
    
        $query_allergies = "SELECT * FROM allergies";
        $result_allergies = $this->conn->query($query_allergies);
        $allergies = $result_allergies->fetch_all(MYSQLI_ASSOC);
    
        // Pass fetched data to the view
        require 'views/register.php';
    }    
    

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email']);
            $password = trim($_POST['password']);
    
            // Check for the user in the database
            $query = "SELECT * FROM users WHERE email = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
    
            $user = $result->fetch_assoc();
            if ($user && password_verify($password, $user['password'])) {
                // Start session if not already started
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }
                
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                
                // Update last_login timestamp and set status to active
                $update_query = "UPDATE users SET last_login = NOW(), status = 'active' WHERE user_id = ?";
                $update_stmt = $this->conn->prepare($update_query);
                $update_stmt->bind_param("i", $user['user_id']);
                $update_stmt->execute();
                
                header('Location: index.php?action=home'); // Redirect to the home page
                exit;
            } else {
                // Pass error message back to the view
                $error = "Invalid email or password.";
                require 'views/login.php';
                return;
            }
        }
    
        require 'views/login.php'; // Render the login view
    }     
    
    // Profile method to fetch and update user data
    public function profile() {
        // Ensure user is logged in
        if (!isset($_SESSION['user_id'])) {
            header('Location: login.php');
            exit;
        }
    
        $user_id = $_SESSION['user_id'];
    
        // Fetch user data
        $query = "SELECT * FROM users WHERE user_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
    
        if (!$user) {
            echo "User not found!";
            exit;
        }
    
        // Fetch dietary preferences for the logged-in user
        $query_preferences = "SELECT p.preference_id, p.preference_name
                              FROM dietary_preferences p
                              JOIN user_dietary_preferences udp ON udp.preference_id = p.preference_id
                              WHERE udp.user_id = ?";
        $stmt = $this->conn->prepare($query_preferences);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result_preferences = $stmt->get_result();
        $user_dietary_preferences = $result_preferences->fetch_all(MYSQLI_ASSOC);
    
        // Fetch allergies for the logged-in user
        $query_allergies = "SELECT a.allergy_id, a.allergy_name
                            FROM allergies a
                            JOIN user_allergies ua ON ua.allergy_id = a.allergy_id
                            WHERE ua.user_id = ?";
        $stmt = $this->conn->prepare($query_allergies);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result_allergies = $stmt->get_result();
        $user_allergies = $result_allergies->fetch_all(MYSQLI_ASSOC);
    
        // Fetch all available preferences and allergies
        $query_all_preferences = "SELECT * FROM dietary_preferences";
        $result_all_preferences = $this->conn->query($query_all_preferences);
        $all_preferences = $result_all_preferences->fetch_all(MYSQLI_ASSOC);
    
        $query_all_allergies = "SELECT * FROM allergies";
        $result_all_allergies = $this->conn->query($query_all_allergies);
        $all_allergies = $result_all_allergies->fetch_all(MYSQLI_ASSOC);
    
        // Handle form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Get form data
            $username = trim($_POST['username']);
            $email = trim($_POST['email']);
            $password = trim($_POST['password']);
            $dietary_preferences = isset($_POST['dietary_preferences']) ? $_POST['dietary_preferences'] : [];
            $allergies = isset($_POST['allergies']) ? $_POST['allergies'] : [];
    
            // Hash password if changed
            $hashed_password = !empty($password) ? password_hash($password, PASSWORD_BCRYPT) : $user['password'];
    
            // Update user info
            $query_update_user = "UPDATE users SET username = ?, email = ?, password = ? WHERE user_id = ?";
            $stmt_update_user = $this->conn->prepare($query_update_user);
            $stmt_update_user->bind_param("sssi", $username, $email, $hashed_password, $user_id);
            $stmt_update_user->execute();
    
            // Update dietary preferences
            $query_delete_preferences = "DELETE FROM user_dietary_preferences WHERE user_id = ?";
            $stmt_delete_preferences = $this->conn->prepare($query_delete_preferences);
            $stmt_delete_preferences->bind_param("i", $user_id);
            $stmt_delete_preferences->execute();
    
            foreach ($dietary_preferences as $preference_id) {
                $query_insert_pref = "INSERT INTO user_dietary_preferences (user_id, preference_id) VALUES (?, ?)";
                $stmt_insert_pref = $this->conn->prepare($query_insert_pref);
                $stmt_insert_pref->bind_param("ii", $user_id, $preference_id);
                $stmt_insert_pref->execute();
            }
    
            // Update allergies
            $query_delete_allergies = "DELETE FROM user_allergies WHERE user_id = ?";
            $stmt_delete_allergies = $this->conn->prepare($query_delete_allergies);
            $stmt_delete_allergies->bind_param("i", $user_id);
            $stmt_delete_allergies->execute();
    
            foreach ($allergies as $allergy_id) {
                $query_insert_allergy = "INSERT INTO user_allergies (user_id, allergy_id) VALUES (?, ?)";
                $stmt_insert_allergy = $this->conn->prepare($query_insert_allergy);
                $stmt_insert_allergy->bind_param("ii", $user_id, $allergy_id);
                $stmt_insert_allergy->execute();
            }
    
            // Redirect after saving changes to prevent form resubmission
            header('Location: index.php?action=profile');
            exit;
        }
    
        // Include the view and pass all necessary data
        include 'views/profile.php';
    }                           
}
?>
