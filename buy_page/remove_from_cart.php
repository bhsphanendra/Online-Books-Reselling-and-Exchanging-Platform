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

// Get the book ID from the request
$book_id = isset($_POST['book_id']) ? intval($_POST['book_id']) : 0;

if ($book_id > 0) {
    // Remove the book from the cart table
    $user_id = $_SESSION['user_id'];
    $query = "DELETE FROM cart WHERE book_id = ? AND user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $book_id, $user_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => 'Item removed from cart']);
    } else {
        echo json_encode(['error' => 'Failed to remove item from cart']);
    }

    $stmt->close();
} else {
    echo json_encode(['error' => 'Invalid book ID']);
}

$conn->close();
?>