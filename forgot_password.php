<?php
// forgot_password.php - PHP backend for the forgot password system

// Database connection
function connect_db() {
    $host = 'localhost';     // Your database host
    $dbname = 'bookswap_db'; // Your database name
    $username = 'root';    // Your database username
    $password = ''; // Your database password
    
    try {
        $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    } catch(PDOException $e) {
        error_log("Database Connection Error: " . $e->getMessage());
        return null;
    }
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Process AJAX requests
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    $response = ['success' => false, 'message' => 'Invalid request'];
    
    switch ($action) {
        case 'checkEmail':
            $email = isset($_POST['email']) ? filter_var($_POST['email'], FILTER_SANITIZE_EMAIL) : '';
            
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $response = ['success' => false, 'message' => 'Invalid email format'];
                break;
            }
            
            // Check if email exists in database
            $db = connect_db();
            if (!$db) {
                $response = ['success' => false, 'message' => 'Database connection error'];
                break;
            }
            
            $stmt = $db->prepare("SELECT * FROM users WHERE email = :email");
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                // Store email in session for verification
                $_SESSION['reset_email'] = $email;
                $response = ['success' => true, 'message' => 'Email found'];
            } else {
                $response = ['success' => false, 'message' => 'Email not found in our system'];
            }
            break;
            
        case 'verifyDetails':
            $email = isset($_POST['email']) ? filter_var($_POST['email'], FILTER_SANITIZE_EMAIL) : '';
            $username = isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '';
            $mobile = isset($_POST['mobile']) ? preg_replace('/[^0-9+]/', '', $_POST['mobile']) : '';
            
            // Validate session email matches
            if (!isset($_SESSION['reset_email']) || $_SESSION['reset_email'] !== $email) {
                $response = ['success' => false, 'message' => 'Session expired or invalid. Please start again.'];
                break;
            }
            
            // Validate inputs
            if (empty($username) || empty($mobile)) {
                $response = ['success' => false, 'message' => 'All fields are required'];
                break;
            }
            
            // Verify user details in database
            $db = connect_db();
            if (!$db) {
                $response = ['success' => false, 'message' => 'Database connection error'];
                break;
            }
            
            $stmt = $db->prepare("SELECT * FROM users WHERE email = :email AND username = :username AND mobile = :mobile");
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':mobile', $mobile);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                // Store verification status in session
                $_SESSION['verified'] = true;
                $response = ['success' => true, 'message' => 'Details verified'];
            } else {
                $response = ['success' => false, 'message' => 'The details you provided do not match our records'];
            }
            break;
            
        case 'resetPassword':
            $email = isset($_POST['email']) ? filter_var($_POST['email'], FILTER_SANITIZE_EMAIL) : '';
            $password = isset($_POST['password']) ? $_POST['password'] : '';
            
            // Check if user is verified
            if (!isset($_SESSION['verified']) || $_SESSION['verified'] !== true || !isset($_SESSION['reset_email']) || $_SESSION['reset_email'] !== $email) {
                $response = ['success' => false, 'message' => 'Unauthorized access or session expired'];
                break;
            }
            
            // Validate password
            if (strlen($password) < 8) {
                $response = ['success' => false, 'message' => 'Password must be at least 8 characters long'];
                break;
            }
            
            // Update password in database
            $db = connect_db();
            if (!$db) {
                $response = ['success' => false, 'message' => 'Database connection error'];
                break;
            }
            
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $db->prepare("UPDATE users SET password = :password WHERE email = :email");
            $stmt->bindParam(':password', $hashed_password);
            $stmt->bindParam(':email', $email);
            
            if ($stmt->execute()) {
                // Clear session variables
                unset($_SESSION['reset_email']);
                unset($_SESSION['verified']);
                
                $response = ['success' => true, 'message' => 'Password updated successfully'];
            } else {
                $response = ['success' => false, 'message' => 'Failed to update password'];
            }
            break;
            
        default:
            $response = ['success' => false, 'message' => 'Invalid action'];
    }
    
    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}
?>