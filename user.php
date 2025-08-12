<?php
// Start the session to manage user login status
session_start();

// Database connection
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

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Get user's name for display
$loggedInUser = $_SESSION['username'];

// Fetch books for sale (limit 4)
$buyBooksQuery = "SELECT * FROM books LIMIT 4";
$buyBooksResult = $conn->query($buyBooksQuery);

// Fetch books for exchange (limit 4)
$exchangeBooksQuery = "SELECT * FROM exchange_books LIMIT 4";
$exchangeBooksResult = $conn->query($exchangeBooksQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Books Swap Hub - User Page</title>
    <link rel="stylesheet" href="user.css"> <!-- Link to your CSS file -->
</head>
<body>
    <!-- Header Section -->
    <header class="header">
        <div class="logo-container">
            <div class="logo">
                <img src="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='white'><path d='M6,2C4.89,2 4,2.89 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V4C20,2.89 19.1,2 18,2H12L10.59,3.41L9.17,2H6ZM12,12L16,16H13.5V20H10.5V16H8L12,12Z'/></svg>" alt="Book Icon">
            </div>
            <div class="site-title">Books Swap Hub</div>
        </div>
        <div class="user-actions">
            <div class="welcome-text">Welcome, <?php echo htmlspecialchars($loggedInUser); ?></div>
            <button class="logout-btn" onclick="location.href='main.html'">Logout</button>
        </div>
    </header>
    
    <!-- Navigation Bar -->
    <nav class="navbar">
        <button class="nav-btn" onclick="location.href='user.php'">
            <svg class="icon" viewBox="0 0 24 24">
                <path d="M10,20V14H14V20H19V12H22L12,3L2,12H5V20H10Z" />
            </svg>
            Home
        </button>
        <button class="nav-btn" onclick="location.href='user/buyer.html'">
            <svg class="icon" viewBox="0 0 24 24">
                <path d="M17,18C15.89,18 15,18.89 15,20A2,2 0 0,0 17,22A2,2 0 0,0 19,20C19,18.89 18.1,18 17,18M1,2V4H3L6.6,11.59L5.24,14.04C5.09,14.32 5,14.65 5,15A2,2 0 0,0 7,17H19V15H7.42A0.25,0.25 0 0,1 7.17,14.75C7.17,14.7 7.18,14.66 7.2,14.63L8.1,13H15.55C16.3,13 16.96,12.58 17.3,11.97L20.88,5.5C20.95,5.34 21,5.17 21,5A1,1 0 0,0 20,4H5.21L4.27,2M7,18C5.89,18 5,18.89 5,20A2,2 0 0,0 7,22A2,2 0 0,0 9,20C9,18.89 8.1,18 7,18Z" />
            </svg>
            Buy
        </button>
        <button class="nav-btn" onclick="location.href='user/exchange.php'">
            <svg class="icon" viewBox="0 0 24 24">
                <path d="M21,9L17,5V8H10V10H17V13M7,11L3,15L7,19V16H14V14H7V11Z" />
            </svg>
            Exchange
        </button>
        <button class="nav-btn" onclick="location.href='user/donate.html'">
            <svg class="icon" viewBox="0 0 24 24">
                <path d="M12,5.5A3.5,3.5 0 0,1 15.5,9A3.5,3.5 0 0,1 12,12.5A3.5,3.5 0 0,1 8.5,9A3.5,3.5 0 0,1 12,5.5M5,8C5.56,8 6.08,8.15 6.53,8.42C6.38,9.85 6.8,11.27 7.66,12.38C7.16,13.34 6.16,14 5,14A3,3 0 0,1 2,11A3,3 0 0,1 5,8M19,8A3,3 0 0,1 22,11A3,3 0 0,1 19,14C17.84,14 16.84,13.34 16.34,12.38C17.2,11.27 17.62,9.85 17.47,8.42C17.92,8.15 18.44,8 19,8M5.5,18.25C5.5,16.18 8.41,14.5 12,14.5C15.59,14.5 18.5,16.18 18.5,18.25V20H5.5V18.25M0,20V18.5C0,17.11 1.89,15.94 4.45,15.6C3.86,16.28 3.5,17.22 3.5,18.25V20H0M24,20H20.5V18.25C20.5,17.22 20.14,16.28 19.55,15.6C22.11,15.94 24,17.11 24,18.5V20Z" />
            </svg>
            Donate
        </button>
        <button class="nav-btn" onclick="location.href='profile.php'">
            <svg class="icon" viewBox="0 0 24 24">
                <path d="M12,12A5,5 0 1,0 7,7A5,5 0 0,0 12,12M12,14.2C9.5,14.2 4,15.5 4,18V20H20V18C20,15.5 14.5,14.2 12,14.2Z"/>
            </svg>
            Profile
        </button>
    </nav>
    
    <!-- Main Content -->
    <div class="main-content">
        <!-- Welcome Section -->
        <section class="welcome-section">
            <h1>Welcome to the Books Swap Hub</h1>
            <p>Buy, sell, exchange, or donate your books with ease. Connect with fellow book lovers and expand your collection!</p>
        </section>
        
        <!-- Buy Books Section -->
        <section>
            <div class="section-header">
                <h2>Buy Books</h2>
                <a href="user/buyer.html" class="view-all">View All</a>
            </div>
            <div class="books-container">
                <?php
                if ($buyBooksResult->num_rows > 0) {
                    while($book = $buyBooksResult->fetch_assoc()) {
                        echo '<div class="book-card">';
                        echo '<div class="book-image">';
                        if (!empty($book['image'])) {
                            echo '<img src="' . htmlspecialchars($book['image']) . '" alt="' . htmlspecialchars($book['title']) . '">';
                        } else {
                            echo '<img src="data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 24 24\' fill=\'%23cccccc\'><path d=\'M18,22A2,2 0 0,0 20,20V4C20,2.89 19.1,2 18,2H12V9L9.5,7.5L7,9V2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18Z\'/></svg>" alt="Book Cover">';
                        }
                        echo '</div>';
                        echo '<div class="book-info">';
                        echo '<div class="book-title">Title: ' . htmlspecialchars($book['title']) . '</div>';
                        echo '<div class="book-author">by ' . htmlspecialchars($book['author']) . '</div>';
                        echo '<div class="book-price">' . htmlspecialchars($book['price']) . '</div>';
                        echo '<button class="buy-now-btn" onclick="location.href=\'buy.php?id=' . htmlspecialchars($book['book_id']) . '\'">Buy Now</button>';
                        echo '</div>';
                        echo '</div>';
                    }
                } else {
                    echo '<div class="no-books">No books available for purchase at the moment.</div>';
                }
                ?>
            </div>
        </section>
        
        <!-- Exchange Books Section -->
        <section>
            <div class="section-header">
                <h2>Exchange Books</h2>
                <a href="user/exchange.php" class="view-all">View All</a>
            </div>
            <div class="books-container">
                <?php
                if ($exchangeBooksResult->num_rows > 0) {
                    while($book = $exchangeBooksResult->fetch_assoc()) {
                        echo '<div class="book-card">';
                        echo '<div class="book-image">';
                        if (!empty($book['image_path'])) {
                            echo '<img src="' . htmlspecialchars($book['image_path']) . '" alt="' . htmlspecialchars($book['title']) . '">';
                        } else {
                            echo '<img src="data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 24 24\' fill=\'%23cccccc\'><path d=\'M18,22A2,2 0 0,0 20,20V4C20,2.89 19.1,2 18,2H12V9L9.5,7.5L7,9V2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18Z\'/></svg>" alt="Book Cover">';
                        }
                        echo '</div>';
                        echo '<div class="book-info">';
                        echo '<div class="book-title">' . htmlspecialchars($book['title']) . '</div>';
                        echo '<div class="book-author">by ' . htmlspecialchars($book['author']) . '</div>';
                        echo '<div class="book-condition">Condition: ' . htmlspecialchars($book['condition']) . '</div>';
                        echo '</div>';
                        echo '</div>';
                    }
                } else {
                    echo '<div class="no-books">No books available for exchange at the moment.</div>';
                }
                ?>
            </div>
        </section>
    </div>
    
    <!-- Footer Section -->
    <footer class="footer">
        <p>&copy; <?php echo date("Y"); ?> Books Swap Hub. All rights reserved.</p>
    </footer>

    <script>
        // JavaScript for enhanced user experience
        document.addEventListener('DOMContentLoaded', function() {
            // Add hover effects for book cards
            const bookCards = document.querySelectorAll('.book-card');
            bookCards.forEach(card => {
                card.addEventListener('click', function() {
                    // You could add functionality here to view book details
                    console.log('Book card clicked');
                });
            });
            
            // You can add more interactive features as needed
        });
    </script>
</body>
</html>

<?php
// Close the database connection
$conn->close();
?>