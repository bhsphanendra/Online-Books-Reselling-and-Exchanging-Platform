<?php
session_start();
include 'db_connection.php'; // contains $conn

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$book_id = $_POST['book_id'];
$book_title = $_POST['book_title'];
$price = $_POST['price'];

$sql = "INSERT INTO cart (user_id, username, book_id, book_title, price) VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("isisd", $user_id, $username, $book_id, $book_title, $price);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['error' => $stmt->error]);
}
?>
