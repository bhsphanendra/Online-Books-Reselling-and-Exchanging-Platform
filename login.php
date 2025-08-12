<?php
session_start();

// Database connection (replace with your actual database credentials)
$host = 'localhost';
$dbname = 'bookswap_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Could not connect to the database: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = $_POST['username'];
    $userPassword = $_POST['password'];
    
    // Check if input is email or username
    $isEmail = filter_var($input, FILTER_VALIDATE_EMAIL);
    
    // Prepare SQL query
    if ($isEmail) {
        $sql = "SELECT user_id, username, email, password, role FROM users WHERE email = :input";
    } else {
        $sql = "SELECT user_id, username, email, password, role FROM users WHERE username = :input";
    }
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':input', $input);
        $stmt->execute();
        
        if ($stmt->rowCount() === 1) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Verify password (assuming passwords are hashed)
            if (password_verify($userPassword, $user['password'])) {
                // Password is correct, set session variables
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                
                // Redirect based on role
                switch ($user['role']) {
                    case 'admin':
                        header('Location: admin/admin_dashboard.php');
                        exit();
                    case 'seller':
                        header('Location: seller.php');
                        exit();
                    case 'user':
                        header('Location: user.php');
                        exit();
                    default:
                        // Unknown role, redirect to default page
                        header('Location: user.php');
                        exit();
                }
            } else {
                // Invalid password
                header('Location: login.php?error=invalid_credentials');
                exit();
            }
        } else {
            // User not found
            header('Location: login.php?error=invalid_credentials');
            exit();
        }
    } catch (PDOException $e) {
        die("Error executing query: " . $e->getMessage());
    }
} else {
    // Not a POST request, redirect to login
    header('Location: login.php');
    exit();
}
?>