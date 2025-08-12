<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Handle add book form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_book'])) {
    $title = $_POST['title'];
    $author = $_POST['author'];
    $description = $_POST['description'];
    $condition = $_POST['condition'];
    $exchange_preference = $_POST['exchange_preference'];
    
    // Handle image upload
    $image_path = '';
    if (isset($_FILES['book_image']) && $_FILES['book_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $file_name = uniqid() . '_' . basename($_FILES['book_image']['name']);
        $target_path = $upload_dir . $file_name;
        if (move_uploaded_file($_FILES['book_image']['tmp_name'], $target_path)) {
            $image_path = $target_path;
        }
    }
    
    $stmt = $conn->prepare("INSERT INTO exchange_books (seller_id, title, author, description, `condition`, exchange_preference, image_path) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssss", $user_id, $title, $author, $description, $condition, $exchange_preference, $image_path);
    $stmt->execute();
    $stmt->close();
    
    header("Location: exchange.php");
    exit;
}

// Handle edit book form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_book'])) {
    $book_id = $_POST['book_id'];
    $title = $_POST['title'];
    $author = $_POST['author'];
    $description = $_POST['description'];
    $condition = $_POST['condition'];
    $exchange_preference = $_POST['exchange_preference'];
    
    // Get current image path first
    $stmt = $conn->prepare("SELECT image_path FROM exchange_books WHERE id = ? AND seller_id = ?");
    $stmt->bind_param("ii", $book_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $book = $result->fetch_assoc();
    $image_path = $book['image_path'];
    $stmt->close();
    
    // Handle image upload if a new one is provided
    if (isset($_FILES['book_image']) && $_FILES['book_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $file_name = uniqid() . '_' . basename($_FILES['book_image']['name']);
        $target_path = $upload_dir . $file_name;
        if (move_uploaded_file($_FILES['book_image']['tmp_name'], $target_path)) {
            // Delete old image if it exists
            if ($image_path && file_exists($image_path)) {
                unlink($image_path);
            }
            $image_path = $target_path;
        }
    }
    
    $stmt = $conn->prepare("UPDATE exchange_books SET title = ?, author = ?, description = ?, `condition` = ?, exchange_preference = ?, image_path = ? WHERE id = ? AND seller_id = ?");
    $stmt->bind_param("ssssssii", $title, $author, $description, $condition, $exchange_preference, $image_path, $book_id, $user_id);
    $stmt->execute();
    $stmt->close();
    
    header("Location: exchange.php");
    exit;
}

// Get book details for editing if ID is provided
$edit_book = null;
if (isset($_GET['edit_id'])) {
    $book_id = $_GET['edit_id'];
    $stmt = $conn->prepare("SELECT * FROM exchange_books WHERE id = ? AND seller_id = ?");
    $stmt->bind_param("ii", $book_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_book = $result->fetch_assoc();
    $stmt->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Exchange Books</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f4f6f8;
            color: #2c3e50;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            position: relative;
            overflow-x: hidden;
        }

        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #2c3e50;
            color: white;
            padding: 20px 30px;
            border-radius: 12px;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
            margin-bottom: 40px;
        }

        header h2 {
            font-size: 1.8rem;
            font-weight: 600;
            color: white;
        }

        header button {
            background-color: white;
            color: #2c3e50;
            border: none;
            padding: 10px 20px;
            border-radius: 20px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s ease;
            margin: 0 10px;
        }

        .nav-buttons {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        header button:hover {
            background-color: #ecf0f1;
        }

        h2 {
            font-size: 1.6rem;
            margin-bottom: 20px;
            padding-bottom: 8px;
            border-bottom: 2px solid #34495e;
            color: #2c3e50;
        }

        .book-card {
            background-color: #ffffff;
            width: 280px;
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
            padding: 20px;
            margin-bottom: 20px;
            transition: transform 0.2s ease;
            display: inline-block;
            margin-right: 20px;
            vertical-align: top;
        }

        .book-card:hover {
            transform: translateY(-3px);
        }

        .book-card h3 {
            font-size: 1.2rem;
            margin-bottom: 8px;
            color: #2c3e50;
        }

        .book-card p {
            font-size: 0.95rem;
            margin-bottom: 15px;
            color: #555;
        }

        .book-card button,
        .book-card input[type="submit"] {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 8px 14px;
            border-radius: 8px;
            margin-right: 10px;
            font-size: 0.9rem;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .book-card button:hover,
        .book-card input[type="submit"]:hover {
            background-color: #2980b9;
        }

        .book-card input[type="text"] {
            padding: 10px;
            width: 100%;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 0.9rem;
        }

        .requests-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }

        .request-card {
            background-color: #fdfdfd;
            padding: 15px;
            margin-bottom: 20px;
            border-left: 4px solid #3498db;
            border-radius: 8px;
            width: 280px;
            display: inline-block;
            vertical-align: top;
            margin-right: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s ease;
        }

        .request-card:hover {
            transform: translateY(-3px);
        }

        .request-card p {
            margin-bottom: 8px;
        }

        .request-card button {
            background-color: #2ecc71;
            color: white;
            border: none;
            padding: 8px 14px;
            border-radius: 6px;
            font-size: 0.9rem;
            margin-right: 8px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .request-card button:hover {
            background-color: #27ae60;
        }

        .request-card button:last-child {
            background-color: #e74c3c;
        }

        .request-card button:last-child:hover {
            background-color: #c0392b;
        }

        .no-books-message,
        p {
            color: #666;
            font-size: 1rem;
            margin-bottom: 20px;
        }

        /* Sidebar styles */
        .sidebar {
            position: fixed;
            top: 0;
            right: -400px;
            width: 400px;
            height: 100%;
            background-color: #fff;
            box-shadow: -5px 0 15px rgba(0, 0, 0, 0.1);
            transition: right 0.3s ease;
            z-index: 1000;
            padding: 30px;
            overflow-y: auto;
        }

        .sidebar.open {
            right: 0;
        }

        .sidebar h3 {
            font-size: 1.5rem;
            margin-bottom: 20px;
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
        }

        .sidebar label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
        }

        .sidebar input[type="text"],
        .sidebar textarea,
        .sidebar select {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
        }

        .sidebar textarea {
            height: 100px;
            resize: vertical;
        }

        .sidebar .form-actions {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }

        .sidebar button {
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .sidebar .submit-btn {
            background-color: #2ecc71;
            color: white;
        }

        .sidebar .submit-btn:hover {
            background-color: #27ae60;
        }

        .sidebar .cancel-btn {
            background-color: #e74c3c;
            color: white;
        }

        .sidebar .cancel-btn:hover {
            background-color: #c0392b;
        }

        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 999;
            display: none;
        }

        .overlay.active {
            display: block;
        }

        .current-image {
            margin-bottom: 20px;
        }

        .current-image img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            border: 1px solid #ddd;
        }
    </style>
</head>
<body>

<!-- Overlay for sidebar -->
<div class="overlay" id="overlay"></div>

<!-- Sidebar for adding books -->
<div class="sidebar" id="addBookSidebar">
    <h3>Add Book for Exchange</h3>
    <form action="exchange.php" method="post" enctype="multipart/form-data">
        <input type="hidden" name="add_book" value="1">
        
        <label for="title">Book Title</label>
        <input type="text" id="title" name="title" required>
        
        <label for="author">Author</label>
        <input type="text" id="author" name="author" required>
        
        <label for="description">Description</label>
        <textarea id="description" name="description" required></textarea>
        
        <label for="condition">Condition</label>
        <select id="condition" name="condition" required>
            <option value="">Select condition</option>
            <option value="New">New</option>
            <option value="Like New">Like New</option>
            <option value="Very Good">Very Good</option>
            <option value="Good">Good</option>
            <option value="Fair">Fair</option>
            <option value="Poor">Poor</option>
        </select>
        
        <label for="exchange_preference">Exchange Preference</label>
        <input type="text" id="exchange_preference" name="exchange_preference" placeholder="What kind of book would you like in exchange?" required>
        
        <label for="book_image">Book Image</label>
        <input type="file" id="book_image" name="book_image" accept="image/*" required>
        
        <div class="form-actions">
            <button type="button" class="cancel-btn" onclick="closeSidebar()">Cancel</button>
            <button type="submit" class="submit-btn">Add Book</button>
        </div>
    </form>
</div>

<!-- Sidebar for editing books -->
<div class="sidebar" id="editBookSidebar">
    <?php if ($edit_book): ?>
    <h3>Edit Book Details</h3>
    <form action="exchange.php" method="post" enctype="multipart/form-data">
        <input type="hidden" name="update_book" value="1">
        <input type="hidden" name="book_id" value="<?= $edit_book['id'] ?>">
        
        <div class="current-image">
            <p>Current Image:</p>
            <img src="<?= $edit_book['image_path'] ?>" alt="Current Book Image">
        </div>
        
        <label for="edit_title">Book Title</label>
        <input type="text" id="edit_title" name="title" value="<?= htmlspecialchars($edit_book['title']) ?>" required>
        
        <label for="edit_author">Author</label>
        <input type="text" id="edit_author" name="author" value="<?= htmlspecialchars($edit_book['author']) ?>" required>
        
        <label for="edit_description">Description</label>
        <textarea id="edit_description" name="description" required><?= htmlspecialchars($edit_book['description']) ?></textarea>
        
        <label for="edit_condition">Condition</label>
        <select id="edit_condition" name="condition" required>
            <option value="New" <?= $edit_book['condition'] == 'New' ? 'selected' : '' ?>>New</option>
            <option value="Like New" <?= $edit_book['condition'] == 'Like New' ? 'selected' : '' ?>>Like New</option>
            <option value="Very Good" <?= $edit_book['condition'] == 'Very Good' ? 'selected' : '' ?>>Very Good</option>
            <option value="Good" <?= $edit_book['condition'] == 'Good' ? 'selected' : '' ?>>Good</option>
            <option value="Fair" <?= $edit_book['condition'] == 'Fair' ? 'selected' : '' ?>>Fair</option>
            <option value="Poor" <?= $edit_book['condition'] == 'Poor' ? 'selected' : '' ?>>Poor</option>
        </select>
        
        <label for="edit_exchange_preference">Exchange Preference</label>
        <input type="text" id="edit_exchange_preference" name="exchange_preference" 
               value="<?= htmlspecialchars($edit_book['exchange_preference']) ?>" 
               placeholder="What kind of book would you like in exchange?" required>
        
        <label for="edit_book_image">Update Book Image (leave blank to keep current)</label>
        <input type="file" id="edit_book_image" name="book_image" accept="image/*">
        
        <div class="form-actions">
            <button type="button" class="cancel-btn" onclick="closeEditSidebar()">Cancel</button>
            <button type="submit" class="submit-btn">Update Book</button>
        </div>
    </form>
    <?php endif; ?>
</div>

<header>
    <h2>Exchange Books</h2>
    <div class="nav-buttons">
        <button onclick="location.href='../seller.php'">Home</button>
        <button onclick="openSidebar()">+ Add Book</button>
        <span>Welcome, <?= htmlspecialchars($username) ?></span>
        <button onclick="location.href='logout.php'">Logout</button>
    </div>
</header>

<div class="content">
    <h2>My Books for Exchange</h2>
    <?php
    $res = $conn->query("SELECT * FROM exchange_books WHERE seller_id = $user_id");
    if ($res && $res->num_rows > 0) {
        while ($row = $res->fetch_assoc()) {
            echo "<div class='book-card'>
                    <img src='{$row['image_path']}' alt='Book Image' style='width:100%; height:auto; border-radius:8px; margin-bottom:10px;'>
                    <h3><strong>Title: </strong>{$row['title']}</h3>
                    <h3><strong>Author: </strong>{$row['author']}</h3>
                    <p><strong>Description: </strong>{$row['description']}</p>
                    <p><strong>Condition: </strong>{$row['condition']}</p>
                    <p><strong>Exchange Preference: </strong>{$row['exchange_preference']}</p>
                    <button onclick=\"location.href='exchange.php?edit_id={$row['id']}'\">Edit</button>
                    <button onclick=\"location.href='delete_exchange_book.php?id={$row['id']}'\">Delete</button>
                </div>";
        }
    } else {
        echo "<p>You haven't added any books for exchange yet.</p>";
    }
    ?>

    <h2>Exchange Requests</h2>
    <div class="requests-container">
        <?php
        $res = $conn->query("SELECT * FROM exchange_request WHERE status = 'pending' AND requested_book_id IN (SELECT id FROM exchange_books WHERE seller_id = $user_id)");
        if ($res && $res->num_rows > 0) {
            while ($row = $res->fetch_assoc()) {
                echo "<div class='request-card'>
                        <p><strong>Request from User ID:</strong> {$row['requester_id']}</p>
                        <p><strong>Message:</strong> {$row['message']}</p>
                        <button onclick=\"location.href='accept_request.php?id={$row['request_id']}'\">Accept</button>
                        <button onclick=\"location.href='reject_request.php?id={$row['request_id']}'\">Reject</button>
                    </div>";
            }
        } else {
            echo "<p>No exchange requests yet.</p>";
        }
        ?>
    </div>

    <h2>All Available Exchange Books</h2>
    <?php
    $res = $conn->query("SELECT * FROM exchange_books WHERE seller_id != $user_id");
    if ($res && $res->num_rows > 0) {
        while ($row = $res->fetch_assoc()) {
            echo "<div class='book-card'>
                    <img src='{$row['image_path']}' alt='Book Image' style='width:100%; height:auto; border-radius:8px; margin-bottom:10px;'>
                    <h3><strong>Title: </strong>{$row['title']}</h3>
                    <h3><strong>Author: </strong>{$row['author']}</h3>
                    <p><strong>Description: </strong>{$row['description']}</p>
                    <p><strong>Condition: </strong>{$row['condition']}</p>
                    <p><strong>Exchange Preference: </strong>{$row['exchange_preference']}</p>
                    <form action='request_exchange.php' method='post'>
                        <input type='hidden' name='book_id' value='{$row['id']}'>
                        <input type='hidden' name='requester_id' value='$user_id'>
                       <input type='text' name='message' placeholder='Your message to the owner' required>
                        <button type='submit'>Send Request</button>
                    </form>
                </div>";
        }
    } else {
        echo "<p>No books available from other users right now.</p>";
    }
    ?>
</div>

<script>
function openSidebar() {
    document.getElementById('addBookSidebar').classList.add('open');
    document.getElementById('overlay').classList.add('active');
}

function closeSidebar() {
    document.getElementById('addBookSidebar').classList.remove('open');
    document.getElementById('overlay').classList.remove('active');
}

function closeEditSidebar() {
    document.getElementById('editBookSidebar').classList.remove('open');
    document.getElementById('overlay').classList.remove('active');
    // Remove the edit_id from URL
    window.history.replaceState({}, document.title, window.location.pathname);
}

// Close sidebar when clicking on overlay
document.getElementById('overlay').addEventListener('click', function() {
    closeSidebar();
    closeEditSidebar();
});

// Open edit sidebar if there's an edit_id in the URL
window.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('edit_id')) {
        document.getElementById('editBookSidebar').classList.add('open');
        document.getElementById('overlay').classList.add('active');
    }
});
</script>

</body>
</html>