<?php
session_start();
include 'db_connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Get form data
$book_id = $_POST['book_id'];  // Changed from 'id' to 'book_id'
$requester_id = $_POST['requester_id'];
$message = $_POST['message'];

// Prepare and execute the query safely
$stmt = $conn->prepare("INSERT INTO exchange_request (requester_id, requested_book_id, message, status, request_date) VALUES (?, ?, ?, 'pending', ?)");
$stmt->bind_param("iiss", $requester_id, $book_id, $message, $date);
$stmt->execute();
$stmt->close();

header("Location: exchange.php");
exit;
?>