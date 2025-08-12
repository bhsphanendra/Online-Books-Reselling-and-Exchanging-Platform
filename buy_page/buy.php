<?php
session_start();
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.html");
    exit();
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "bookswap_db";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get user information
$user_id = $_SESSION['user_id'];
$user_query = "SELECT username FROM users WHERE user_id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user = $user_result->fetch_assoc();
$username = $user['username'];


// Handle book deletion
if (isset($_POST['delete_book'])) {
    $book_id = $_POST['book_id'];
    $delete_query = "DELETE FROM books WHERE book_id = ? AND seller_id = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("ii", $book_id, $user_id);
    $stmt->execute();
}

// Get user's books
$my_books_query = "SELECT * FROM books WHERE seller_id = ?";
$stmt = $conn->prepare($my_books_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$my_books_result = $stmt->get_result();

// Get books for purchase (excluding user's own books)
$buy_books_query = "SELECT * FROM books WHERE seller_id != ?";
$stmt = $conn->prepare($buy_books_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$buy_books_result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Exchange Platform</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="buy.css">
    <style>
        /* Add loader styles */
        .loader {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #3498db;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            animation: spin 2s linear infinite;
            margin: 20px auto;
            display: none;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Toast notification styles */
        .toast {
            visibility: hidden;
            min-width: 250px;
            margin-left: -125px;
            background-color: #333;
            color: #fff;
            text-align: center;
            border-radius: 4px;
            padding: 16px;
            position: fixed;
            z-index: 1000;
            left: 50%;
            bottom: 30px;
        }
        
        .toast.show {
            visibility: visible;
            animation: fadein 0.5s, fadeout 0.5s 2.5s;
        }
        
        @keyframes fadein {
            from {bottom: 0; opacity: 0;}
            to {bottom: 30px; opacity: 1;}
        }
        
        @keyframes fadeout {
            from {bottom: 30px; opacity: 1;}
            to {bottom: 0; opacity: 0;}
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>Book Exchange Platform</h1>
        <div class="nav-buttons">
            <button class="nav-btn home-btn" onclick="window.location.href='../seller.php';"><i class="fas fa-home"></i> Home</button>
            <button class="nav-btn filter-btn" id="filter-btn"><i class="fas fa-filter"></i> Filter</button>
            <button class="nav-btn add-btn" id="add-book-btn"><i class="fas fa-plus"></i> Add Book</button>
            <button class="nav-btn cart-btn" id="open-cart-btn"><i class="fas fa-shopping-cart"></i> Cart (<span id="cart-count">0</span>)</button>
            <span class="nav-btn"><?php echo $username; ?></span>
            <a href="../main.html" class="nav-btn logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>

    <!-- Search Bar -->
    <div class="search-container">
        <input type="text" class="search-box" id="search-box" placeholder="Search books...">
    </div>

    <!-- Tabs -->
    <div class="tab-container">
        <div class="tab active" id="tab-mybooks">My Books</div>
        <div class="tab" id="tab-buybooks">Buy Books</div>
    </div>

    <!-- My Books Section -->
    <div id="content-mybooks">
        <div class="section-title">
            <h2>My Books</h2>
        </div>
        <div class="books-container" id="my-books-container">
            <?php while ($book = $my_books_result->fetch_assoc()): ?>
            <div class="book-card" data-title="<?php echo strtolower($book['title']); ?>" data-author="<?php echo strtolower($book['author']); ?>" data-price="<?php echo $book['price']; ?>" data-condition="<?php echo strtolower($book['condition']); ?>" data-genre="<?php echo strtolower($book['genre']); ?>">
                <!-- Fix image path by making it absolute -->
                <img src="<?php echo $book['image']; ?>" alt="<?php echo $book['title']; ?>" class="book-image">
                <div class="book-details">
                    <div class="book-title"><?php echo $book['title']; ?></div>
                    <p class="book-info">Author: <?php echo $book['author']; ?></p>
                    <p class="book-info">Price: <?php echo number_format($book['price'], 2); ?></p>
                    <p class="book-info">Condition: <?php echo $book['condition']; ?></p>
                    <?php if (!empty($book['genre'])): ?>
                    <p class="book-info">Genre: <?php echo $book['genre']; ?></p>
                    <?php endif; ?>
                    <div class="book-action">
                        <button class="action-btn edit-btn" data-book-id="<?php echo $book['book_id']; ?>">Edit</button>
                        <form method="post" style="display: inline;">
                            <input type="hidden" name="book_id" value="<?php echo $book['book_id']; ?>">
                            <button type="submit" name="delete_book" class="action-btn delete-btn" onclick="return confirm('Are you sure you want to delete this book?')">Delete</button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
            <?php if ($my_books_result->num_rows === 0): ?>
            <div class="no-results">
                <p>You haven't added any books yet.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Buy Books Section -->
    <div id="content-buybooks">
        <div class="section-title">
            <h2>Buy Books</h2>
        </div>
        <div class="books-container" id="buy-books-container">
            <?php while ($book = $buy_books_result->fetch_assoc()): ?>
            <div class="book-card" data-title="<?php echo strtolower($book['title']); ?>" data-author="<?php echo strtolower($book['author']); ?>" data-price="<?php echo $book['price']; ?>" data-condition="<?php echo strtolower($book['condition']); ?>" data-genre="<?php echo strtolower($book['genre']); ?>">
                <!-- Fix image path by making it absolute -->
                <img src="<?php echo $book['image']; ?>" alt="<?php echo $book['title']; ?>" class="book-image">
                <div class="book-details">
                    <div class="book-title"><?php echo $book['title']; ?></div>
                    <p class="book-info">Author: <?php echo $book['author']; ?></p>
                    <p class="book-info">Price: <?php echo number_format($book['price'], 2); ?></p>
                    <p class="book-info">Condition: <?php echo $book['condition']; ?></p>
                    <?php if (!empty($book['genre'])): ?>
                    <p class="book-info">Genre: <?php echo $book['genre']; ?></p>
                    <?php endif; ?>
                    <div class="book-action">
                        <button class="action-btn add-cart-btn" data-book-id="<?php echo $book['book_id']; ?>" data-book-title="<?php echo $book['title']; ?>" data-book-price="<?php echo $book['price']; ?>" data-book-image="<?php echo $book['image']; ?>">Add to Cart</button>
                        <button class="action-btn buy-btn" data-book-id="<?php echo $book['book_id']; ?>" data-book-title="<?php echo $book['title']; ?>" data-book-price="<?php echo $book['price']; ?>" data-book-image="<?php echo $book['image']; ?>">Buy Now</button>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
            <?php if ($buy_books_result->num_rows === 0): ?>
            <div class="no-results">
                <p>No books available for purchase.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Add Book Sidebar -->
    <div id="add-book-sidebar" class="sidebar">
        <div class="sidebar-header">
            <h2>Add a Book</h2>
            <button class="close-btn" id="close-add-sidebar">&times;</button>
        </div>
        <div class="sidebar-content">
            <form action="add_book.php" method="post" enctype="multipart/form-data" id="add-book-form">
                <div class="form-group">
                    <label for="title">Title</label>
                    <input type="text" id="title" name="title" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="author">Author</label>
                    <input type="text" id="author" name="author" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="price">Price</label>
                    <input type="number" id="price" name="price" step="0.01" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="condition">Condition</label>
                    <select id="condition" name="condition" class="form-control" required>
                        <option value="">Select Condition</option>
                        <option value="New">New</option>
                        <option value="Like New">Like New</option>
                        <option value="Good">Good</option>
                        <option value="Fair">Fair</option>
                        <option value="Poor">Poor</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" class="form-control" rows="4"></textarea>
                </div>
                <div class="form-group">
                    <label for="genre">Genre</label>
                    <input type="text" id="genre" name="genre" class="form-control">
                </div>
                <div class="form-group">
                    <label for="image">Book Image</label>
                    <input type="file" id="image" name="image" class="form-control">
                </div>
                <div class="form-buttons">
                    <button type="button" class="form-btn cancel-btn" id="cancel-add-book">Cancel</button>
                    <button type="submit" class="form-btn submit-btn">Add Book</button>
                </div>
                <div class="loader" id="add-book-loader"></div>
            </form>
        </div>
    </div>

    <!-- Edit Book Sidebar -->
    <div id="edit-book-sidebar" class="sidebar">
        <div class="sidebar-header">
            <h2>Edit Book</h2>
            <button class="close-btn" id="close-edit-sidebar">&times;</button>
        </div>
        <div class="sidebar-content">
            <div class="loader" id="edit-book-loader"></div>
            <form action="edit_book.php" method="post" enctype="multipart/form-data" id="edit-book-form">
                <input type="hidden" id="edit-book-id" name="book_id">
                <div class="form-group">
                    <label for="edit-title">Title</label>
                    <input type="text" id="edit-title" name="title" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="edit-author">Author</label>
                    <input type="text" id="edit-author" name="author" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="edit-price">Price</label>
                    <input type="number" id="edit-price" name="price" step="0.01" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="edit-condition">Condition</label>
                    <select id="edit-condition" name="condition" class="form-control" required>
                        <option value="New">New</option>
                        <option value="Like New">Like New</option>
                        <option value="Good">Good</option>
                        <option value="Fair">Fair</option>
                        <option value="Poor">Poor</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="edit-description">Description</label>
                    <textarea id="edit-description" name="description" class="form-control" rows="4"></textarea>
                </div>
                <div class="form-group">
                    <label for="edit-genre">Genre</label>
                    <input type="text" id="edit-genre" name="genre" class="form-control">
                </div>
                <div class="form-group">
                    <label for="edit-image">Book Image</label>
                    <input type="file" id="edit-image" name="image" class="form-control">
                </div>
                <div class="form-buttons">
                    <button type="button" class="form-btn cancel-btn" id="cancel-edit-book">Cancel</button>
                    <button type="submit" class="form-btn submit-btn">Update Book</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Filter Sidebar -->
    <div id="filter-sidebar" class="sidebar">
        <div class="sidebar-header">
            <h2>Filter Books</h2>
            <button class="close-btn" id="close-filter-sidebar">&times;</button>
        </div>
        <div class="sidebar-content">
            <form id="filter-form">
                <div class="form-group">
                    <label for="filter-title">Title</label>
                    <input type="text" id="filter-title" name="title" class="form-control">
                </div>
                <div class="form-group">
                    <label for="filter-author">Author</label>
                    <input type="text" id="filter-author" name="author" class="form-control">
                </div>
                <div class="form-group">
                    <label for="filter-price">Max Price</label>
                    <input type="number" id="filter-price" name="price" class="form-control">
                </div>
                <div class="form-group">
                    <label for="filter-condition">Condition</label>
                    <select id="filter-condition" name="condition" class="form-control">
                        <option value="">Any</option>
                        <option value="New">New</option>
                        <option value="Like New">Like New</option>
                        <option value="Good">Good</option>
                        <option value="Fair">Fair</option>
                        <option value="Poor">Poor</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="filter-genre">Genre</label>
                    <input type="text" id="filter-genre" name="genre" class="form-control">
                </div>
                <div class="form-buttons">
                    <button type="button" class="form-btn cancel-btn" id="reset-filter">Reset</button>
                    <button type="submit" class="form-btn submit-btn">Apply Filters</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Cart Sidebar -->
    <div id="cart-sidebar" class="sidebar">
        <div class="sidebar-header">
            <h2>Your Cart</h2>
            <button class="close-btn" id="close-cart-sidebar">&times;</button>
        </div>
        <div class="sidebar-content">
            <div id="cart-items">
                <p>Your cart is empty.</p>
            </div>
            <div class="cart-total">
                <p>Total: <span id="cart-total">0.00</span></p>
            </div>
            <button class="form-btn submit-btn" id="checkout-btn" style="width: 100%; margin-top: 20px;">Checkout</button>
        </div>
    </div>

    <!-- Payment Sidebar -->
    <div id="payment-sidebar" class="sidebar">
        <div class="sidebar-header">
            <h2>Payment Options</h2>
            <button class="close-btn" id="close-payment-sidebar">&times;</button>
        </div>
        <div class="sidebar-content">
            <h3>Select Payment Method</h3>
            <div class="payment-options">
                <div class="payment-option">
                    <label class="payment-label">
                        <input type="radio" name="payment_method" value="cod" checked>
                        Cash on Delivery
                    </label>
                </div>
                <div class="payment-option">
                    <label class="payment-label">
                        <input type="radio" name="payment_method" value="upi">
                        UPI Payment
                    </label>
                    <div id="upi-details" style="display: none; margin-top: 10px;">
                        <input type="text" class="form-control" placeholder="Enter UPI ID">
                    </div>
                </div>
                <div class="payment-option">
                    <label class="payment-label">
                        <input type="radio" name="payment_method" value="qr">
                        QR Code
                    </label>
                    <div id="qr-details" style="display: none; margin-top: 10px; text-align: center;">
                        <img src="qr-code.png" alt="QR Code" style="width: 200px; height: 200px;">
                    </div>
                </div>
            </div>
            <button id="complete-purchase-btn" class="form-btn submit-btn" style="width: 100%; margin-top: 20px;">Complete Purchase</button>
        </div>
    </div>

    <!-- Success Message -->
    <div id="success-message" class="success-message">
        <h3>Order Placed Successfully!</h3>
        <p>Thank you for your purchase. Your books will be delivered soon.</p>
        <button class="success-close" id="close-success">Close</button>
    </div>

    <!-- Toast notification -->
    <div id="toast" class="toast">A notification message</div>

    <script>
        // Initialize cart
        let cart = [];
        
        // Fetch cart items from the server
        function fetchCartItems() {
            fetch('get_cart_items.php')
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        console.error('Error:', data.error);
                        showToast('Failed to load cart items: ' + data.error);
                    } else {
                        cart = data.cart_items.map(item => ({
                            id: item.book_id,
                            title: item.title,
                            price: item.price,
                            image: item.image
                        }));

                        // Update localStorage
                        localStorage.setItem('bookCart', JSON.stringify(cart));

                        // Update UI
                        updateCartCount();
                        updateCartDisplay();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('Failed to load cart items');
                });
        }

        // Call fetchCartItems on page load
        document.addEventListener('DOMContentLoaded', fetchCartItems);

        // Check if there's cart data in localStorage
        if (localStorage.getItem('bookCart')) {
            try {
                cart = JSON.parse(localStorage.getItem('bookCart'));
                updateCartCount();
                updateCartDisplay();
            } catch (e) {
                localStorage.removeItem('bookCart');
                cart = [];
            }
        }

        // Show toast notification function
        function showToast(message) {
            const toast = document.getElementById("toast");
            toast.textContent = message;
            toast.className = "toast show";
            setTimeout(function(){ toast.className = toast.className.replace("show", ""); }, 3000);
        }

        // Tab switching functionality
        document.getElementById('tab-mybooks').addEventListener('click', function() {
            document.getElementById('tab-mybooks').classList.add('active');
            document.getElementById('tab-buybooks').classList.remove('active');
            document.getElementById('content-mybooks').style.display = 'block';
            document.getElementById('content-buybooks').style.display = 'none';
        });

        document.getElementById('tab-buybooks').addEventListener('click', function() {
            document.getElementById('tab-buybooks').classList.add('active');
            document.getElementById('tab-mybooks').classList.remove('active');
            document.getElementById('content-buybooks').style.display = 'block';
            document.getElementById('content-mybooks').style.display = 'none';
        });

        // Add book sidebar functionality
        const addBookSidebar = document.getElementById('add-book-sidebar');
        const addBookBtn = document.getElementById('add-book-btn');
        const closeAddSidebarBtn = document.getElementById('close-add-sidebar');
        const cancelAddBookBtn = document.getElementById('cancel-add-book');

        addBookBtn.addEventListener('click', function() {
            addBookSidebar.style.right = '0';
        });

        closeAddSidebarBtn.addEventListener('click', function() {
            addBookSidebar.style.right = '-400px';
        });

        cancelAddBookBtn.addEventListener('click', function() {
            addBookSidebar.style.right = '-400px';
        });

        // Add form submission with validation
        document.getElementById('add-book-form').addEventListener('submit', function(e) {
            const title = document.getElementById('title').value.trim();
            const author = document.getElementById('author').value.trim();
            const price = document.getElementById('price').value;
            
            if (!title || !author || !price) {
                e.preventDefault();
                showToast("Please fill in all required fields");
                return false;
            }
            
            document.getElementById('add-book-loader').style.display = 'block';
            return true;
        });

        // Filter sidebar functionality
        const filterSidebar = document.getElementById('filter-sidebar');
        const filterBtn = document.getElementById('filter-btn');
        const closeFilterSidebarBtn = document.getElementById('close-filter-sidebar');
        const resetFilterBtn = document.getElementById('reset-filter');

        filterBtn.addEventListener('click', function() {
            filterSidebar.style.right = '0';
        });

        closeFilterSidebarBtn.addEventListener('click', function() {
            filterSidebar.style.right = '-400px';
        });

        resetFilterBtn.addEventListener('click', function() {
            document.getElementById('filter-form').reset();
        });

        // Filter form submission
        document.getElementById('filter-form').addEventListener('submit', function(e) {
            e.preventDefault();
            applyFilters();
            filterSidebar.style.right = '-400px';
        });

        function applyFilters() {
            const titleFilter = document.getElementById('filter-title').value.toLowerCase();
            const authorFilter = document.getElementById('filter-author').value.toLowerCase();
            const priceFilter = document.getElementById('filter-price').value;
            const conditionFilter = document.getElementById('filter-condition').value.toLowerCase();
            const genreFilter = document.getElementById('filter-genre').value.toLowerCase();
            
            // Apply to current active tab
            const activeTab = document.querySelector('.tab.active').id;
            let container;
            
            if (activeTab === 'tab-mybooks') {
                container = document.getElementById('my-books-container');
            } else {
                container = document.getElementById('buy-books-container');
            }
            
            const books = container.querySelectorAll('.book-card');
            let resultsFound = false;
            
            books.forEach(book => {
                const title = book.getAttribute('data-title');
                const author = book.getAttribute('data-author');
                const price = parseFloat(book.getAttribute('data-price'));
                const condition = book.getAttribute('data-condition');
                const genre = book.getAttribute('data-genre');
                
                let match = true;
                
                if (titleFilter && !title.includes(titleFilter)) match = false;
                if (authorFilter && !author.includes(authorFilter)) match = false;
                if (priceFilter && price > parseFloat(priceFilter)) match = false;
                if (conditionFilter && condition !== conditionFilter && conditionFilter !== '') match = false;
                if (genreFilter && (!genre || !genre.includes(genreFilter))) match = false;
                
                if (match) {
                    book.style.display = 'flex';
                    resultsFound = true;
                } else {
                    book.style.display = 'none';
                }
            });
            
            // Show/hide no results message
            let noResultsDiv = container.querySelector('.no-results');
            if (!noResultsDiv) {
                noResultsDiv = document.createElement('div');
                noResultsDiv.className = 'no-results';
                noResultsDiv.innerHTML = '<p>No books match your filters.</p>';
                container.appendChild(noResultsDiv);
            }
            
            noResultsDiv.style.display = resultsFound ? 'none' : 'block';
        }

        // Search functionality
        document.getElementById('search-box').addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            
            // Apply to current active tab
            const activeTab = document.querySelector('.tab.active').id;
            let container;
            
            if (activeTab === 'tab-mybooks') {
                container = document.getElementById('my-books-container');
            } else {
                container = document.getElementById('buy-books-container');
            }
            
            const books = container.querySelectorAll('.book-card');
            let resultsFound = false;
            
            books.forEach(book => {
                const title = book.getAttribute('data-title');
                const author = book.getAttribute('data-author');
                
                if (title.includes(searchTerm) || author.includes(searchTerm)) {
                    book.style.display = 'flex';
                    resultsFound = true;
                } else {
                    book.style.display = 'none';
                }
            });
            
            // Show/hide no results message
            let noResultsDiv = container.querySelector('.no-results');
            if (!noResultsDiv) {
                noResultsDiv = document.createElement('div');
                noResultsDiv.className = 'no-results';
                noResultsDiv.innerHTML = '<p>No books match your search.</p>';
                container.appendChild(noResultsDiv);
            }
            
            noResultsDiv.style.display = resultsFound ? 'none' : 'block';
        });

        // Edit book functionality
        const editBookBtns = document.querySelectorAll('.edit-btn');
        const editBookSidebar = document.getElementById('edit-book-sidebar');
        const closeEditSidebarBtn = document.getElementById('close-edit-sidebar');
        const cancelEditBookBtn = document.getElementById('cancel-edit-book');
        const editBookLoader = document.getElementById('edit-book-loader');

        editBookBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const bookId = this.getAttribute('data-book-id');
                editBookLoader.style.display = 'block';
                
                // Clear previous form data
                document.getElementById('edit-book-form').reset();
                editBookSidebar.style.right = '0';
                
                // Fetch book data using AJAX - fixed URL parameter name
                fetch('get_book.php?book_id=' + bookId)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.error) {
                            throw new Error(data.error);
                        }
                        
                        // Populate form with book data
                        document.getElementById('edit-book-id').value = data.book_id;
                        document.getElementById('edit-title').value = data.title;
                        document.getElementById('edit-author').value = data.author;
                        document.getElementById('edit-price').value = data.price;
                        document.getElementById('edit-condition').value = data.condition;
                        document.getElementById('edit-description').value = data.description || '';
                        document.getElementById('edit-genre').value = data.genre || '';
                        
                        editBookLoader.style.display = 'none';
                    })
                    .catch(error => {
                        console.error('Error fetching book details:', error);
                        showToast('Failed to load book details: ' + error.message);
                        editBookLoader.style.display = 'none';
                        editBookSidebar.style.right = '-400px';
                    });
            });
        });

        closeEditSidebarBtn.addEventListener('click', function() {
            editBookSidebar.style.right = '-400px';
        });

        cancelEditBookBtn.addEventListener('click', function() {
            editBookSidebar.style.right = '-400px';
        });

        // Edit form validation
        document.getElementById('edit-book-form').addEventListener('submit', function(e) {
            const title = document.getElementById('edit-title').value.trim();
            const author = document.getElementById('edit-author').value.trim();
            const price = document.getElementById('edit-price').value;
            
            if (!title || !author || !price) {
                e.preventDefault();
                showToast("Please fill in all required fields");
                return false;
            }
            
            editBookLoader.style.display = 'block';
            return true;
        });

        // Cart functionality
        function updateCartCount() {
            document.getElementById('cart-count').textContent = cart.length;
        }

        function updateCartDisplay() {
            const cartItemsDiv = document.getElementById('cart-items');
            const cartTotalSpan = document.getElementById('cart-total');

            if (cart.length === 0) {
                cartItemsDiv.innerHTML = '<p>Your cart is empty.</p>';
                cartTotalSpan.textContent = '0.00';
                return;
            }

            let totalPrice = 0;
            let cartHTML = '';

            cart.forEach((item, index) => {
                totalPrice += parseFloat(item.price);
                cartHTML += `
                    <div class="cart-item">
                        <div class="cart-item-details">
                            <img src="${item.image}" alt="${item.title}" class="cart-item-image">
                            <div class="cart-item-info">
                                <p class="cart-item-title">${item.title}</p>
                                <p class="cart-item-price">${parseFloat(item.price).toFixed(2)}</p>
                            </div>
                        </div>
                        <button class="remove-cart-item" data-index="${index}">×</button>
                    </div>
                `;
            });

            cartItemsDiv.innerHTML = cartHTML;
            cartTotalSpan.textContent = totalPrice.toFixed(2);

            // Add event listeners to remove buttons
            document.querySelectorAll('.remove-cart-item').forEach(btn => {
                btn.addEventListener('click', function () {
                    const index = parseInt(this.getAttribute('data-index'));
                    removeFromCart(index);
                });
            });
        }
        
        function addToCart(bookId, title, price, image) {
            // Add to local cart
            cart.push({
                id: bookId,
                title: title,
                price: price,
                image: image
            });

            // Update localStorage
            localStorage.setItem('bookCart', JSON.stringify(cart));

            // Update UI
            updateCartCount();
            updateCartDisplay();
            showToast(title + ' added to cart');

            // Send AJAX request to add to database
            fetch('add_to_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    book_id: bookId,
                    book_title: title,
                    price: price
                })
            })
            .then(response => response.json())
            .then(data => {
                console.log('Response from server:', data); // Log server response
                if (data.error) {
                    console.error('Error:', data.error);
                    showToast('Failed to add to cart: ' + data.error);
                } else {
                    console.log('Success:', data.success);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Failed to add to cart');
            });
        }
        
        function removeFromCart(index) {
            if (index >= 0 && index < cart.length) {
                const removedItem = cart.splice(index, 1)[0];

                // Update localStorage
                localStorage.setItem('bookCart', JSON.stringify(cart));

                // Update UI
                updateCartCount();
                updateCartDisplay();
                showToast(removedItem.title + ' removed from cart');

                // Send AJAX request to remove from database
                fetch('remove_from_cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        book_id: removedItem.id
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        console.error('Error:', data.error);
                        showToast('Failed to remove from cart: ' + data.error);
                    } else {
                        console.log('Success:', data.success);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('Failed to remove from cart');
                });
            }
        }
        
        // Add to cart button event listeners
        document.querySelectorAll('.add-cart-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const bookId = this.getAttribute('data-book-id');
                const title = this.getAttribute('data-book-title');
                const price = this.getAttribute('data-book-price');
                const image = this.getAttribute('data-book-image');
                
                addToCart(bookId, title, price, image);
            });
        });
        
        // Buy now buttons
        document.querySelectorAll('.buy-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const bookId = this.getAttribute('data-book-id');
                const title = this.getAttribute('data-book-title');
                const price = this.getAttribute('data-book-price');
                const image = this.getAttribute('data-book-image');
                
                // Clear cart and add just this item
                cart = [{
                    id: bookId,
                    title: title,
                    price: price,
                    image: image
                }];
                
                // Update localStorage
                localStorage.setItem('bookCart', JSON.stringify(cart));
                
                // Update UI
                updateCartCount();
                updateCartDisplay();
                
                // Open cart sidebar
                cartSidebar.style.right = '0';
            });
        });
        
        // Cart sidebar functionality
        const cartSidebar = document.getElementById('cart-sidebar');
        const openCartBtn = document.getElementById('open-cart-btn');
        const closeCartSidebarBtn = document.getElementById('close-cart-sidebar');
        
        openCartBtn.addEventListener('click', function() {
            cartSidebar.style.right = '0';
        });
        
        closeCartSidebarBtn.addEventListener('click', function() {
            cartSidebar.style.right = '-400px';
        });
        
        // Payment sidebar functionality
        const paymentSidebar = document.getElementById('payment-sidebar');
        const checkoutBtn = document.getElementById('checkout-btn');
        const closePaymentSidebarBtn = document.getElementById('close-payment-sidebar');
        
        checkoutBtn.addEventListener('click', function() {
            if (cart.length === 0) {
                showToast('Your cart is empty');
                return;
            }
            
            cartSidebar.style.right = '-400px';
            paymentSidebar.style.right = '0';
        });
        
        closePaymentSidebarBtn.addEventListener('click', function() {
            paymentSidebar.style.right = '-400px';
        });
        
        // Payment method selection
        const paymentOptions = document.querySelectorAll('input[name="payment_method"]');
        const upiDetails = document.getElementById('upi-details');
        const qrDetails = document.getElementById('qr-details');
        
        paymentOptions.forEach(option => {
            option.addEventListener('change', function() {
                if (this.value === 'upi') {
                    upiDetails.style.display = 'block';
                    qrDetails.style.display = 'none';
                } else if (this.value === 'qr') {
                    upiDetails.style.display = 'none';
                    qrDetails.style.display = 'block';
                } else {
                    upiDetails.style.display = 'none';
                    qrDetails.style.display = 'none';
                }
            });
        });
        
        // Complete purchase button
        const completePurchaseBtn = document.getElementById('complete-purchase-btn');
        const successMessage = document.getElementById('success-message');
        const closeSuccessBtn = document.getElementById('close-success');
        
        completePurchaseBtn.addEventListener('click', function() {
            // In a real application, you would handle the payment processing here
            
            // Show success message
            paymentSidebar.style.right = '-400px';
            successMessage.style.display = 'flex';
            
            // Clear cart
            cart = [];
            localStorage.removeItem('bookCart');
            updateCartCount();
            updateCartDisplay();
            
            // Send order to server
            fetch('place_order.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    items: cart,
                    payment_method: document.querySelector('input[name="payment_method"]:checked').value
                })
            })
            .then(response => response.json())
            .then(data => {
                console.log('Order placed:', data);
            })
            .catch(error => {
                console.error('Error placing order:', error);
            });
        });
        
        closeSuccessBtn.addEventListener('click', function() {
            successMessage.style.display = 'none';
        });
        
        // Initial tab setup
        document.getElementById('content-buybooks').style.display = 'none';
    </script>
</body>
</html>