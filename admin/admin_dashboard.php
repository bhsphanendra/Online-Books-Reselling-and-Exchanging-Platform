<?php
include 'db_connect.php';

// Get total users, books, and orders
$userCount = $conn->query("SELECT COUNT(*) AS total FROM users")->fetch_assoc()['total'];
$bookCount = $conn->query("SELECT COUNT(*) AS total FROM books")->fetch_assoc()['total'];
$exchangeCount = $conn->query("SELECT COUNT(*) AS total FROM exchange_books")->fetch_assoc()['total'];
$exchangeRequestCount = $conn->query("SELECT COUNT(*) AS total FROM exchange_request")->fetch_assoc()['total'];
$donationCount = $conn->query("SELECT COUNT(*) AS total FROM donations")->fetch_assoc()['total'];

// Get latest 5 users
$latestUsers = $conn->query("SELECT * FROM users ORDER BY user_id DESC LIMIT 5");
// Get latest 5 books
$latestBooks = $conn->query("SELECT * FROM books ORDER BY book_id DESC LIMIT 5");
// Get latest 5 exchange books
$latestExchangeBooks = $conn->query("SELECT * FROM exchange_books ORDER BY id DESC LIMIT 5");
// Get latest 5 exchange requests
$latestExchangeRequests = $conn->query("SELECT * FROM exchange_request ORDER BY request_id DESC LIMIT 5");
// Get latest 5 donations
$latestDonations = $conn->query("SELECT * FROM donations ORDER BY id DESC LIMIT 5");
?>

<?php
// Assuming you have a session started and the username is stored in $_SESSION['username']
session_start();
$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Admin';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="admin.css">
    <style>
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

         table {
        margin-top: 10px; /* Added space between button and table */
        margin-bottom: 40px; /* Added more space after each table */
        width: 100%;
        border-collapse: collapse;
    }
        .view-all-btn {
            text-decoration: none;
            background-color: #007bff;
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 14px;
        }

        .view-all-btn:hover {
            background-color: #0056b3;
        }

        .section-title-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px; /* Increased from 10px */
        margin-top: 30px; /* Added top margin for separation from previous content */
    }

        .section-title {
            margin: 0;
        }

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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
        <h1>Admin Dashboard</h1>

        <!-- Summary Cards -->
        <div class="dashboard-cards">
            <div class="card">
                <h3>Total Users</h3>
                <p><?= $userCount; ?></p>
            </div>
            <div class="card">
                <h3>Total Books</h3>
                <p><?= $bookCount; ?></p>
            </div>
            <div class="card">
                <h3>Total Exchange Books</h3>
                <p><?= $exchangeCount; ?></p>
            </div>
            <div class="card">
                <h3>Total Exchange Requests</h3>
                <p><?= $exchangeRequestCount; ?></p>
            </div>
            <div class="card">
                <h3>Total Donations</h3>
                <p><?= $donationCount; ?></p>
            </div>
        </div>

        <!-- Latest Users -->
        <div class="section-title-container">
            <h2 class="section-title">Latest Users</h2>
            <a href="admin_users.php" class="view-all-btn">View All</a>
        </div>
        <table>
            <tr>
                <th>User ID</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Username</th>
                <th>Email</th>
                <th>DOB</th>
                <th>Gender</th>
                <th>Role</th>
            </tr>
            <?php while ($row = $latestUsers->fetch_assoc()): ?>
            <tr>
                <td><?= $row['user_id']; ?></td>
                <td><?= $row['firstname']; ?></td>
                <td><?= $row['lastname']; ?></td>
                <td><?= $row['username']; ?></td>
                <td><?= $row['email']; ?></td>
                <td><?= $row['dob']; ?></td>
                <td><?= $row['gender']; ?></td>
                <td><?= $row['role']; ?></td>
            </tr>
            <?php endwhile; ?>
        </table>

        <!-- Latest Books -->
        <div class="section-title-container">
            <h2 class="section-title">Latest Books</h2>
            <a href="admin_books.php" class="view-all-btn">View All</a>
        </div>
        <table>
            <tr>
                <th>Book ID</th>
                <th>Title</th>
                <th>Author</th>
                <th>Price</th>
                <th>Condition</th>
                <th>Description</th>
                <th>Genre</th>
                <th>Image</th>
                <th>Seller ID</th>
                <th>Created At</th>
            </tr>
            <?php while ($row = $latestBooks->fetch_assoc()): ?>
            <tr>
                <td><?= $row['book_id']; ?></td>
                <td><?= $row['title']; ?></td>
                <td><?= $row['author']; ?></td>
                <td>$<?= $row['price']; ?></td>
                <td><?= $row['condition']; ?></td>
                <td><?= $row['description']; ?></td>
                <td><?= $row['genre']; ?></td>
                <td><img src="<?= $row['image']; ?>" alt="Book Image" style="width:50px;height:50px;"></td>
                <td><?= $row['seller_id']; ?></td>
                <td><?= $row['created_at']; ?></td>
            </tr>
            <?php endwhile; ?>
        </table>
        
        <!-- Latest Exchange Books -->
        <div class="section-title-container">
            <h2 class="section-title">Latest Exchange Books</h2>
            <a href="admin_exchange_books.php" class="view-all-btn">View All</a>
        </div>
        <table>
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Author</th>
                <th>Condition</th>
                <th>Description</th>
                <th>Exchange Preference</th>
                <th>Image Path</th>
                <th>Seller ID</th>
                <th>Date Listed</th>
                <th>Status</th>
            </tr>
            <?php while ($row = $latestExchangeBooks->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id']; ?></td>
                <td><?= $row['title']; ?></td>
                <td><?= $row['author']; ?></td>
                <td><?= $row['condition']; ?></td>
                <td><?= $row['description']; ?></td>
                <td><?= $row['exchange_preference']; ?></td>
                <td><img src="<?= $row['image_path']; ?>" alt="Exchange Book Image" style="width:50px;height:50px;"></td>
                <td><?= $row['seller_id']; ?></td>
                <td><?= $row['date_listed']; ?></td>
                <td><?= $row['status']; ?></td>
            </tr>
            <?php endwhile; ?>
        </table>

        <!-- Latest Exchange Requests -->
        <div class="section-title-container">
            <h2 class="section-title">Latest Exchange Requests</h2>
            <a href="admin_exchange_requests.php" class="view-all-btn">View All</a>
        </div>
        <table>
            <tr>
                <th>Request ID</th>
                <th>Requester ID</th>
                <th>Book ID</th> 
                <th>Status</th>
                <th>Request Date</th>
                <th>Message</th>
            </tr>
            <?php while ($row = $latestExchangeRequests->fetch_assoc()): ?>
            <tr>
                <td><?= $row['request_id']; ?></td>
                <td><?= $row['requester_id']; ?></td>
                <td><?= $row['requested_book_id']; ?></td>
                <td><?= $row['status']; ?></td>
                <td><?= $row['request_date']; ?></td>
                <td><?= $row['message']; ?></td>
            </tr>
            <?php endwhile; ?>
        </table>

        <!-- Latest Donations -->
        <div class="section-title-container">
            <h2 class="section-title">Latest Donations</h2>
            <a href="admin_donations.php" class="view-all-btn">View All</a>
        </div>
        <table>
            <tr>
                <th>Donation ID</th>
                <th>Donar Name</th>
                <th>Donar Email</th>
                <th>Donar Phone</th>
                <th>Donation type</th>
                <th>Quantity</th>
                <th>Comments</th>
                <th>Donated_st</th>
            </tr>
            <?php while ($row = $latestDonations->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id']; ?></td>
                <td><?= $row['name']; ?></td>
                <td><?= $row['email']; ?></td>
                <td><?= $row['phone']; ?></td>
                <td><?= $row['donationType']; ?></td>
                <td><?= $row['quantity']; ?></td>
                <td><?= $row['comments']; ?></td>
                <td><?= $row['donated_at']; ?></td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
</body>
</html>