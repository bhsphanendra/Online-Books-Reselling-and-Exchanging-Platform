<?php
include 'db_connect.php';

// Delete exchange book
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $conn->query("DELETE FROM exchange_books WHERE id = $delete_id");
    header("Location: admin_exchange_books.php");
}

// Fetch all exchange books
$result = $conn->query("SELECT *, users.username AS owner
                        FROM exchange_books
                        JOIN users ON exchange_books.seller_id = users.user_id");
?>


<?php
// Assuming you have a session started and the username is stored in $_SESSION['username']
session_start();
$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Admin';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage Exchange Books</title>
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
        <h1>Manage Exchange Books</h1>
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
            <?php while ($row = $result->fetch_assoc()): ?>
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
    </div>
</body>
</html>
