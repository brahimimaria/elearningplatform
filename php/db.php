<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "elearning_platform";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Function to check if the user is logged in
function isLoggedIn() {
    return isset($_SESSION['username']);
}

// Function to handle user login
function loginUser($username, $password) {
    global $conn;
    
    // Prevent SQL injection
    $username = $conn->real_escape_string($username);
    
    // Query to check if the username exists
    $sql = "SELECT * FROM users WHERE username = '$username'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Verify the password
        if (password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['username'] = $username;
            $_SESSION['role'] = $user['role'];
            return true; // Login successful
        }
    }
    return false; // Login failed
}

// Function to handle user registration
function registerUser($username, $password, $role) {
    global $conn;
    
    // Prevent SQL injection
    $username = $conn->real_escape_string($username);
    $role = $conn->real_escape_string($role);
    $password = password_hash($password, PASSWORD_BCRYPT); // Hash password for security

    // Check if username already exists
    $check_sql = "SELECT * FROM users WHERE username = '$username'";
    $check_result = $conn->query($check_sql);
    
    if ($check_result->num_rows > 0) {
        return false; // Username already exists
    }

    // Query to insert new user into the database
    $sql = "INSERT INTO users (username, password, role) VALUES ('$username', '$password', '$role')";
    return $conn->query($sql) === TRUE;
}

// Function to logout the user
function logoutUser() {
    session_unset(); // Unset all session variables
    session_destroy(); // Destroy the session
}

// Close connection when done
function closeConnection() {
    global $conn;
    $conn->close();
}
?>
