<?php
session_start();
include 'db_connection.php'; // Adjust the path if needed

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not logged in']);
    exit();
}

// Check if book_id parameter exists
if (isset($_GET['book_id'])) {
    $book_id = $_GET['book_id'];
    $user_id = $_SESSION['user_id'];
    
    // Prepare the query - users should only be able to edit their own books
    $stmt = $conn->prepare("SELECT * FROM books WHERE book_id = ? AND seller_id = ?");
    $stmt->bind_param("ii", $book_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $book = $result->fetch_assoc();
        echo json_encode($book);
    } else {
        echo json_encode(['error' => 'Book not found or you do not have permission to edit it']);
    }
    
    $stmt->close();
} else {
    echo json_encode(['error' => 'No book ID provided']);
}

$conn->close();
?>