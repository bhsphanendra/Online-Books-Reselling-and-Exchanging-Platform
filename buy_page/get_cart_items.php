<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not logged in']);
    exit();
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "bookswap_db";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(['error' => 'Database connection failed']);
    exit();
}

// Get user_id from session
$user_id = $_SESSION['user_id'];

// Fetch cart items for the user
$query = "SELECT c.book_id, b.title, b.price, b.image 
          FROM cart c 
          JOIN books b ON c.book_id = b.book_id 
          WHERE c.user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$cart_items = [];
while ($row = $result->fetch_assoc()) {
    $cart_items[] = $row;
}

echo json_encode(['cart_items' => $cart_items]);

$stmt->close();
$conn->close();
?>