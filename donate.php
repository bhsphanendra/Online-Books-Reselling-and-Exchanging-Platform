<?php
include 'db_connection.php'; // Ensure database connection is included

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Enable error reporting for debugging
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    // Retrieve and sanitize form data
    $name = isset($_POST['name']) ? $conn->real_escape_string(trim($_POST['name'])) : '';
    $email = isset($_POST['email']) ? $conn->real_escape_string(trim($_POST['email'])) : '';
    $phone = isset($_POST['phone']) ? $conn->real_escape_string(trim($_POST['phone'])) : '';
    $donationType = isset($_POST['donationType']) ? $conn->real_escape_string(trim($_POST['donationType'])) : '';
    
    // Handle book types - ensure this matches your database column name
    $bookTypes = '';
    if (isset($_POST['bookTypes']) && is_array($_POST['bookTypes'])) {
        $bookTypes = implode(", ", $_POST['bookTypes']);
        $bookTypes = $conn->real_escape_string($bookTypes);
    }
    
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 0;
    $comments = isset($_POST['comments']) ? $conn->real_escape_string(trim($_POST['comments'])) : '';

    // Validate required fields
    if (!empty($name) && !empty($email) && !empty($donationType) && $quantity > 0) {
        // Debug output - you can remove this after testing
        echo "<pre>";
        echo "Name: $name\n";
        echo "Email: $email\n";
        echo "Phone: $phone\n";
        echo "Donation Type: $donationType\n";
        echo "Book Types: $bookTypes\n";
        echo "Quantity: $quantity\n";
        echo "Comments: $comments\n";
        echo "</pre>";

        // Insert into database
        $sql = "INSERT INTO donations (name, email, phone, donationType, bookTypes, quantity, comments) 
                VALUES ('$name', '$email', '$phone', '$donationType', '$bookTypes', $quantity, '$comments')";

        // Debug SQL query
        echo "<pre>SQL: $sql</pre>";

        if ($conn->query($sql)) {
            // Redirect to avoid form resubmission
            header("Location: donate.html?success=1");
            exit();
        } else {
            echo "Database error: " . $conn->error;
            // Additional debug for column names
            $result = $conn->query("SHOW COLUMNS FROM donations");
            if ($result) {
                echo "<h3>Database Columns:</h3><ul>";
                while ($row = $result->fetch_assoc()) {
                    echo "<li>" . $row['Field'] . "</li>";
                }
                echo "</ul>";
            }
        }
    } else {
        echo "Please fill in all required fields.";
    }
} else {
    // If accessed directly, redirect to form
    header("Location: donate.html");
    exit();
}

$conn->close();
?>