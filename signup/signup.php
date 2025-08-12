<?php
header('Content-Type: application/json');

// Database configuration
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'bookswap_db';

// Get form data
$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
$otp = filter_input(INPUT_POST, 'otp', FILTER_SANITIZE_STRING);
$password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);
$confirm_password = filter_input(INPUT_POST, 'confirm_password', FILTER_SANITIZE_STRING);

// Validate inputs
if ($password !== $confirm_password) {
    echo json_encode(['success' => false, 'message' => 'Passwords do not match']);
    exit;
}

try {
    $conn = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check if email already exists
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => false, 'message' => 'Email already registered']);
        exit;
    }
    
    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert new user
    $stmt = $conn->prepare("INSERT INTO users (firstname, lastname, username, email, mobile, dob, gender, role, password) 
                           VALUES (:firstname, :lastname, :username, :email, :mobile, :dob, :gender, :role, :password)");
    
    $stmt->bindParam(':firstname', $_POST['firstname']);
    $stmt->bindParam(':lastname', $_POST['lastname']);
    $stmt->bindParam(':username', $_POST['username']);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':mobile', $_POST['mobile']);
    $stmt->bindParam(':dob', $_POST['dob']);
    $stmt->bindParam(':gender', $_POST['gender']);
    $stmt->bindParam(':role', $_POST['role']);
    $stmt->bindParam(':password', $hashed_password);
    
    $stmt->execute();
    
    echo json_encode([
        'success' => true,
        'message' => 'Registration successful!',
        'redirect' => '../login.html'
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>