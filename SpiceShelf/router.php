<?php

class Router {
    private $routes = []; // Holds all defined routes

    /**
     * Add a route with its associated callback.
     *
     * @param string $action
     * @param callable $callback
     */
    public function addRoute($action, $callback) {
        $this->routes[$action] = $callback;
    }

    /**
     * Handle the request by executing the appropriate callback.
     *
     * @param string $action
     */
    public function handleRequest($action) {
        if (isset($this->routes[$action])) {
            call_user_func($this->routes[$action]); // Execute the callback for the matched action
        } else {
            // Check if it's trying to access an admin route
            if (strpos($action, 'admin') === 0) {
                echo "<div style='margin: 50px auto; text-align: center; max-width: 600px;'>";
                echo "<h2>Admin Area</h2>";
                echo "<p>The requested admin action does not exist. Please navigate to a valid admin page.</p>";
                echo "<a href='index.php?action=adminDashboard' style='padding: 10px 15px; background-color: #F29E52; color: white; text-decoration: none; border-radius: 4px; display: inline-block; margin-top: 10px;'>Go to Admin Dashboard</a>";
                echo "</div>";
            } else {
                // Regular non-admin unknown route
                echo "<div style='margin: 50px auto; text-align: center; max-width: 600px;'>";
                echo "<h2>Page Not Found</h2>";
                echo "<p>The page you're looking for doesn't exist. You tried to access: <code>" . htmlspecialchars($action) . "</code></p>";
                echo "<a href='index.php?action=home' style='padding: 10px 15px; background-color: #F29E52; color: white; text-decoration: none; border-radius: 4px; display: inline-block; margin-top: 10px;'>Go Home</a>";
                echo "</div>";
            }
        }
    }
    
    /**
     * Check if a route exists.
     *
     * @param string $action
     * @return bool
     */
    public function routeExists($action) {
        return isset($this->routes[$action]);
    }
    
    /**
     * Get all registered routes.
     *
     * @return array
     */
    public function getRoutes() {
        return array_keys($this->routes);
    }
    
    /**
     * Get all admin routes.
     *
     * @return array
     */
    public function getAdminRoutes() {
        $adminRoutes = [];
        foreach ($this->routes as $route => $callback) {
            if (strpos($route, 'admin') === 0) {
                $adminRoutes[] = $route;
            }
        }
        return $adminRoutes;
    }
    
    /**
     * Check if an action is an admin route.
     *
     * @param string $action
     * @return bool
     */
    public function isAdminRoute($action) {
        return strpos($action, 'admin') === 0;
    }
}