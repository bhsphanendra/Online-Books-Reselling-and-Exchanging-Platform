<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "bookswap_db";

// Database Connection
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];

// if (!isset($user_id)) {
//     echo "<script>alert('Please log in to access this page.'); window.location.href='../user.html';</script>";
//     exit();
// }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Books Exchange</title>
    <link rel="stylesheet" href="exchange.css">
    <style>
        /* Additional CSS for our improvements */
        .book-card {
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 15px;
            margin: 15px;
            width: 250px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
            background: white;
        }
        
        .book-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.15);
        }
        
        .book-card img {
            width: 100%;
            height: 200px;
            object-fit: contain;
            border-radius: 5px;
            margin-bottom: 10px;
            background: #f5f5f5;
        }
        
        .book-card h3 {
            margin: 10px 0;
            color: #333;
        }
        
        .book-card p {
            margin: 5px 0;
            color: #666;
            font-size: 14px;
        }
        
        .book-card form {
            margin-top: 15px;
        }
        
        .book-card input[type="text"] {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        
        .book-card button[type="submit"] {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            font-weight: bold;
            transition: background-color 0.3s;
        }
        
        .book-card button[type="submit"]:hover {
            background-color: #45a049;
        }
        
        .book-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            padding: 20px;
        }
        
        /* Default image if none is provided */
        .default-book-image {
            display: flex;
            align-items: center;
            justify-content: center;
            background: #e0e0e0;
            color: #888;
            font-weight: bold;
            height: 200px;
        }
    </style>
</head>
<body>

    <!-- Header Section -->
    <div class="header">
        <h2>Books Exchange</h2>
        <div class="nav">
            <button onclick="location.href='../user.php'">Home</button>
            <div class="search-container">
                <input type="text" id="searchBar" placeholder="Search books..." onkeyup="searchBooks()">
                <button onclick="searchBooks()">Search</button>
            </div>
            <button onclick="toggleFilterSidebar()">Filter</button>
            <span class="username"><?php echo isset($_SESSION['username']) ? $_SESSION['username'] : 'Guest'; ?></span>
            <button onclick="location.href='logout.php'">Logout</button>
        </div>
    </div>

    <!-- Filter Sidebar -->
    <div class="filter-sidebar" id="filterSidebar">
        <h3>Filter Books</h3>
        <input type="text" id="titleFilter" placeholder="Filter by Title">
        <input type="text" id="authorFilter" placeholder="Filter by Author">
        <input type="text" id="preferenceFilter" placeholder="Exchange Preference">
        <button onclick="filterBooks()">Apply Filter</button>
        <button class="close-btn" onclick="toggleFilterSidebar()">Close</button>
    </div>

    <!-- Book List -->
    <h2 style="text-align:center;">Available Books for Exchange</h2>
    <div class="book-container" id="bookList">
    <?php
    $res = $conn->query("SELECT * FROM exchange_books");
    if ($res && $res->num_rows > 0) {
        while ($row = $res->fetch_assoc()) {
            // Handle image display - show default if not available
            $image_html = '';
            if (!empty($row['image_path']) && file_exists($row['image_path'])) {
                $image_html = "<img src='{$row['image_path']}' alt='{$row['title']}'>";
            } else {
                $image_html = "<div class='default-book-image'>No Image Available</div>";
            }
            
            echo "<div class='book-card' data-title='".strtolower($row['title'])."' data-author='".strtolower($row['author'])."' data-preference='".strtolower($row['exchange_preference'])."'>
                    {$image_html}
                    <h3><strong>Title: </strong>{$row['title']} </h3>
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
        echo "<p style='text-align:center; width:100%;'>No books available for exchange at the moment.</p>";
    }
    ?>
    </div>

    <script>
        function toggleFilterSidebar() {
            let sidebar = document.getElementById("filterSidebar");
            sidebar.classList.toggle("active");
        }

        function filterBooks() {
            let titleFilter = document.getElementById("titleFilter").value.toLowerCase();
            let authorFilter = document.getElementById("authorFilter").value.toLowerCase();
            let preferenceFilter = document.getElementById("preferenceFilter").value.toLowerCase();
            
            let books = document.querySelectorAll(".book-card");

            books.forEach(book => {
                let title = book.getAttribute("data-title");
                let author = book.getAttribute("data-author");
                let preference = book.getAttribute("data-preference");

                let matchesTitle = title.includes(titleFilter);
                let matchesAuthor = author.includes(authorFilter);
                let matchesPreference = preference.includes(preferenceFilter);

                if (matchesTitle && matchesAuthor && matchesPreference) {
                    book.style.display = "block";
                } else {
                    book.style.display = "none";
                }
            });

            toggleFilterSidebar();
        }

        function searchBooks() {
            let searchQuery = document.getElementById("searchBar").value.toLowerCase();
            let books = document.querySelectorAll(".book-card");

            books.forEach(book => {
                let title = book.getAttribute("data-title");
                let author = book.getAttribute("data-author");
                if (title.includes(searchQuery) || author.includes(searchQuery)) {
                    book.style.display = "block";
                } else {
                    book.style.display = "none";
                }
            });
        }
    </script>

</body>
</html>
<?php
$conn->close();
?>