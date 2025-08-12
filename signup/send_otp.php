<?php
header('Content-Type: application/json');

// Database configuration
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'bookswap_db';

// Check if required files exist before including
$required_files = [
    'vendor/phpmailer/phpmailer/src/PHPMailer.php',
    'vendor/phpmailer/phpmailer/src/SMTP.php',
    'vendor/phpmailer/phpmailer/src/Exception.php',
    'vendor/autoload.php'
];

foreach ($required_files as $file) {
    if (!file_exists($file)) {
        echo json_encode(['success' => false, 'message' => 'Server configuration error: Missing required files']);
        exit;
    }
}

require 'vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Get input data
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['email']) || !isset($input['otp'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid input data']);
    exit;
}

$email = filter_var($input['email'], FILTER_SANITIZE_EMAIL);
$otp = filter_var($input['otp'], FILTER_SANITIZE_STRING);

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email address']);
    exit;
}

try {
    // Database connection
    $conn = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check if email exists
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => false, 'message' => 'Email already registered']);
        exit;
    }
    
    // Send OTP email
    $mail = new PHPMailer(true);
    
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'phanendrabhs@gmail.com';
        $mail->Password = 'bqot nekg grdp eczy'; // Replace with your app password
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;
        $mail->setFrom('phanendrabhs@gmail.com', 'Mail');
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'Your OTP for Signup';
        $mail->Body = "Your OTP is: <b>$otp</b>";
        
        $mail->send();
        echo json_encode(['success' => true, 'message' => 'OTP sent successfully']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error sending email: ' . $e->getMessage()]);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>