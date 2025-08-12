<?php
session_start();
include 'db_connection.php'; // Adjust the path if needed

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get all form fields
    $book_id = $_POST['book_id'];
    $title = $_POST['title'];
    $author = $_POST['author'];
    $price = $_POST['price'];
    $condition = $_POST['condition'];
    $description = $_POST['description'];
    $genre = $_POST['genre'];
    $user_id = $_SESSION['user_id']; // For security verification
    
    // Create the base query
    $query = "UPDATE books SET 
              title = ?, 
              author = ?, 
              price = ?, 
              `condition` = ?, 
              description = ?, 
              genre = ?";
    $params = array($title, $author, $price, $condition, $description, $genre);
    $types = "ssdsss";
    
    // File upload handling if a new image is provided
    if(!empty($_FILES['image']['name'])) {
        $image = $_FILES['image']['name'];
        $tmp_image = $_FILES['image']['tmp_name'];
        
        // Define the absolute path for uploads
        $upload_dir = "uploads/";
        
        // Create uploads folder if it doesn't exist
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        // Generate a unique filename
        $unique_filename = time() . '_' . $image;
        $target_file = $upload_dir . $unique_filename;
        
        // Upload the file
        if (move_uploaded_file($tmp_image, $target_file)) {
            // Add image to query
            $query .= ", image = ?";
            $params[] = $target_file;
            $types .= "s";
        } else {
            echo "<script>alert('Failed to upload image. Other changes will still be applied.'); </script>";
        }
    }
    
    // Complete the query with WHERE clause
    $query .= " WHERE book_id = ? AND seller_id = ?";
    $params[] = $book_id;
    $params[] = $user_id;
    $types .= "ii";
    
    // Prepare statement
    $stmt = $conn->prepare($query);
    
    if ($stmt === false) {
        die("Prepare failed: " . $conn->error);
    }
    
    // Bind parameters dynamically
    $stmt->bind_param($types, ...$params);
    
    // Execute the query
    if ($stmt->execute()) {
        echo "<script>alert('Book updated successfully!'); window.location.href = 'buy.php';</script>";
    } else {
        echo "<script>alert('Failed to update book: " . $stmt->error . "'); window.history.back();</script>";
    }
    
    $stmt->close();
    $conn->close();
} else {
    echo "<script>alert('Invalid request method.'); window.history.back();</script>";
}
?>