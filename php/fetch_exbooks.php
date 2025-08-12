<?php
session_start();
$host = "localhost";
$user = "root";
$password = "";
$dbname = "bookswap_db";

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["error" => "User not logged in"]);
    exit;
}

$user_id = $_SESSION['user_id']; // Get logged-in user's ID

// Fetch books where seller_id matches the logged-in user's ID
$sql = "SELECT * FROM exchange_books WHERE seller_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$books = array();
while ($row = $result->fetch_assoc()) {
    $books[] = $row;
}

echo json_encode($books);

$stmt->close();
$conn->close();
?>
