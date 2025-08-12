<?php
include 'db_connect.php';

$conn = new mysqli("localhost", "root", "", "bookswap_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle Add Book
if (isset($_POST['add_book'])) {
    $title = $_POST['title'];
    $author = $_POST['author'];
    $price = $_POST['price'];
    $condition = $_POST['condition'];
    $description = $_POST['description'];
    $genre = $_POST['genre'];
    $seller_id = $_POST['seller_id'];
    
    $image = "uploads/" . basename($_FILES["image"]["name"]);
    move_uploaded_file($_FILES["image"]["tmp_name"], $image);

    $sql = "INSERT INTO books (title, author, price, `condition`, description, genre, image, seller_id) 
            VALUES ('$title', '$author', '$price', '$condition', '$description', '$genre', '$image', '$seller_id')";
    $conn->query($sql);
}

// Handle Edit Book
if (isset($_POST['edit_book'])) {
    $book_id = $_POST['book_id'];
    $title = $_POST['title'];
    $author = $_POST['author'];
    $price = $_POST['price'];
    $condition = $_POST['condition'];
    $description = $_POST['description'];
    $genre = $_POST['genre'];
    
    if (!empty($_FILES["image"]["name"])) {
        $image = "uploads/" . basename($_FILES["image"]["name"]);
        move_uploaded_file($_FILES["image"]["tmp_name"], $image);
        $sql = "UPDATE books SET title='$title', author='$author', price='$price', `condition`='$condition', description='$description', genre='$genre', image='$image' WHERE book_id='$book_id'";
    } else {
        $sql = "UPDATE books SET title='$title', author='$author', price='$price', `condition`='$condition', description='$description', genre='$genre' WHERE book_id='$book_id'";
    }
    
    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('Book updated successfully!'); window.location.href='admin_books.php';</script>";
    } else {
        echo "<script>alert('Error updating book: " . $conn->error . "');</script>";
    }
}

// Handle Delete Book
if (isset($_POST['delete_book'])) {
    $book_id = $_POST['book_id'];
    $sql = "DELETE FROM books WHERE book_id='$book_id'";
    $conn->query($sql);
    echo "<script>alert('Book deleted successfully!'); window.location.href='admin_books.php';</script>";
}

// Fetch Books
$books = $conn->query("SELECT * FROM books");
?>

<?php
// Assuming you have a session started and the username is stored in $_SESSION['username']
session_start();
$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Admin';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Books</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="admin.css">
    <style>
        .top-bar {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            padding: 10px 20px;

        }

        .top-bar .username {
            margin-right: 15px;
            font-size: 16px;
            color: #000;
        }

        .top-bar .logout-btn {
            text-decoration: none;
            background-color: #dc3545;
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 14px;
        }

        .top-bar .logout-btn:hover {
            background-color: #c82333;
        }

      
    .sidebar {
        position: fixed;
        top: 0;
        left: 0;
        height: 100%; /* Make the sidebar full height */
        width: 250px; /* Set a fixed width for the sidebar */
        color: white; /* Text color */
        padding-top: 20px; /* Add some padding at the top */
    }
</style>
</head>
<body>

    <div class="top-bar">
        <span class="username">Welcome, <?= htmlspecialchars($username); ?></span>
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>

    <div class="sidebar">
        <h2>Admin Panel</h2>
        <ul>
            <li><a href="admin_dashboard.php"><i class="fas fa-tachometer-alt"></i> <span>Dashboard</span></a></li>
            <li><a href="admin_users.php"><i class="fas fa-users"></i> <span>Manage Users</span></a></li>
            <li><a href="admin_books.php"><i class="fas fa-book"></i> <span>Manage Books</span></a></li>
            <li><a href="admin_exchange_books.php"><i class="fas fa-exchange-alt"></i> <span>Exchange Books</span></a></li>
            <li><a href="admin_exchange_requests.php"><i class="fas fa-handshake"></i> <span>Exchange Requests</span></a></li>
            <li><a href="admin_donations.php"><i class="fas fa-gift"></i> <span>Donations</span></a></li>
            <li><a href="admin_orders.php"><i class="fas fa-shopping-cart"></i> <span>Orders</span></a></li>
            <li><a href="admin_reports.php" class="active"><i class="fas fa-chart-bar"></i> <span>Reports</span></a></li>
        </ul>
    </div>

    <div class="content">
        <h1>Manage Books</h1>
        
        <div class="form-container">
            <h3>Add Book</h3>
            <form action="" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <input type="text" name="title" placeholder="Title" required>
                </div>
                <div class="form-group">
                    <input type="text" name="author" placeholder="Author" required>
                </div>
                <div class="form-group">
                    <input type="number" name="price" placeholder="Price" required>
                </div>
                <div class="form-group">
                    <input type="text" name="condition" placeholder="Condition" required>
                </div>
                <div class="form-group">
                    <input type="text" name="description" placeholder="Description" required>
                </div>
                <div class="form-group">
                    <input type="text" name="genre" placeholder="Genre" required>
                </div>
                <div class="form-group">
                    <input type="file" name="image" required>
                </div>
                <input type="hidden" name="seller_id" value="1">
                <button type="submit" name="add_book" class="btn">Add Book</button>
            </form>
        </div>

        <table>
            <tr>
                <th>Book ID</th>
                <th>Title</th>
                <th>Author</th>
                <th>Price</th>
                <th>Condition</th>
                <th>Genre</th>
                <th>Actions</th>
            </tr>
            <?php while ($row = $books->fetch_assoc()) { ?>
            <tr>
                <td><?= $row['book_id'] ?></td>
                <td><?= $row['title'] ?></td>
                <td><?= $row['author'] ?></td>
                <td><?= $row['price'] ?></td>
                <td><?= $row['condition'] ?></td>
                <td><?= $row['genre'] ?></td>
                <td class="action-buttons">
                    <button onclick="editBook(<?= $row['book_id'] ?>, '<?= $row['title'] ?>', '<?= $row['author'] ?>', '<?= $row['price'] ?>', '<?= $row['condition'] ?>', '<?= $row['description'] ?>', '<?= $row['genre'] ?>')" class="btn">Edit</button>
                    <form action="" method="POST" style="display:inline;">
                        <input type="hidden" name="book_id" value="<?= $row['book_id'] ?>">
                        <button type="submit" name="delete_book" class="btn btn-danger">Delete</button>
                    </form>
                </td>
            </tr>
            <?php } ?>
        </table>
    </div>

    <script>
    function editBook(bookId, title, author, price, condition, description, genre) {
        const editForm = `
            <div class="form-container">
                <h3>Edit Book</h3>
                <form action="" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="book_id" value="${bookId}">
                    <div class="form-group">
                        <input type="text" name="title" value="${title}" required>
                    </div>
                    <div class="form-group">
                        <input type="text" name="author" value="${author}" required>
                    </div>
                    <div class="form-group">
                        <input type="number" name="price" value="${price}" required>
                    </div>
                    <div class="form-group">
                        <input type="text" name="condition" value="${condition}" required>
                    </div>
                    <div class="form-group">
                        <input type="text" name="description" value="${description}" required>
                    </div>
                    <div class="form-group">
                        <input type="text" name="genre" value="${genre}" required>
                    </div>
                    <div class="form-group">
                        <input type="file" name="image">
                    </div>
                    <button type="submit" name="edit_book" class="btn">Save Changes</button>
                    <button type="button" onclick="window.location.href='admin_books.php'" class="btn btn-danger">Cancel</button>
                </form>
            </div>
        `;
        document.querySelector('.content').innerHTML = editForm + document.querySelector('table').outerHTML;
    }
    </script>
</body>
</html>