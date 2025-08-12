<?php
session_start();
include 'db_connection.php'; // Adjust the path if needed

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get all form fields
    $title = $_POST['title'];
    $author = $_POST['author'];
    $price = $_POST['price'];
    $condition = $_POST['condition'];
    $description = $_POST['description'];
    $genre = $_POST['genre'];
    $seller_id = $_SESSION['user_id']; // Assuming you're storing the user ID in session

    // File upload handling
    $image = $_FILES['image']['name'];
    $tmp_image = $_FILES['image']['tmp_name'];
    
    // Define the absolute path for uploads
    $upload_dir = "uploads/"; // Make sure this matches the admin's upload path
    
    // Create uploads folder if it doesn't exist
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // Generate a unique filename to prevent overwrites
    $unique_filename = time() . '_' . $image;
    $target_file = $upload_dir . $unique_filename;
    
    // Save the image path that will be visible to the frontend
    $image_path = $target_file;
    
    // Upload the file
    if (move_uploaded_file($tmp_image, $target_file)) {
        // Prepare and bind
        $stmt = $conn->prepare("INSERT INTO books (title, author, price, `condition`, description, genre, image, seller_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

        if ($stmt === false) {
            die("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("ssdssssi", $title, $author, $price, $condition, $description, $genre, $image_path, $seller_id);

        if ($stmt->execute()) {
            echo "<script>alert('Book added successfully!'); window.location.href = 'buy.php';</script>";
        } else {
            echo "<script>alert('Failed to add book: " . $stmt->error . "'); window.history.back();</script>";
        }

        $stmt->close();
    } else {
        echo "<script>alert('Failed to upload image. Please try again.'); window.history.back();</script>";
    }
    
    $conn->close();
} else {
    echo "<script>alert('Invalid request method.'); window.history.back();</script>";
}
?>