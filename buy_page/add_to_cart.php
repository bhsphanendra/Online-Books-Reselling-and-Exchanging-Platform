<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not logged in']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $username = $_SESSION['username']; // Assuming username is stored in session
    $book_id = $_POST['book_id'];
    $book_title = $_POST['book_title'];
    $price = $_POST['price'];

    // Database connection
    $servername = "localhost";
    $db_username = "root";
    $db_password = "";
    $dbname = "bookswap_db";

    $conn = new mysqli($servername, $db_username, $db_password, $dbname);
    if ($conn->connect_error) {
        echo json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]);
        exit();
    }

    // Insert into cart table
    $query = "INSERT INTO cart (user_id, username, book_id, book_title, price, added_at) VALUES (?, ?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        echo json_encode(['error' => 'Failed to prepare statement: ' . $conn->error]);
        exit();
    }

    $stmt->bind_param("isisd", $user_id, $username, $book_id, $book_title, $price);

    if ($stmt->execute()) {
        echo json_encode(['success' => 'Book added to cart']);
    } else {
        echo json_encode(['error' => 'Failed to execute statement: ' . $stmt->error]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['error' => 'Invalid request method']);
}
?>