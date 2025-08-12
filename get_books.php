<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "bookswap_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$current_user = $_SESSION['username'];
$user_id = $_SESSION['user_id'];
// Get all books except those added by the current user
$stmt = $conn->prepare("SELECT * FROM books");
// $stmt->bind_param("s", $current_user);
$stmt->execute();
$result = $stmt->get_result();

$books = array();
while($row = $result->fetch_assoc()) {
    $books[] = $row;
}

$conn->close();

header('Content-Type: application/json');
echo json_encode($books);
?>