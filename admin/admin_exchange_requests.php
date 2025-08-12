<?php
include 'db_connect.php';
session_start();
$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Admin';

// Fetch all exchange requests
$result = $conn->query("SELECT * FROM exchange_request ORDER BY request_date DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage Exchange Requests</title>
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

<div class="top-bar">
    <div class="username">Welcome, <?php echo htmlspecialchars($username); ?></div>
    <a class="logout-btn" href="logout.php">Logout</a>
</div>

<div class="content">
    <h2>Exchange Requests</h2>

    <table>
        <thead>
            <tr>
                <th>request_id</th>
                <th>requester_id</th>
                <th>requested_book_id</th>
                <th>message</th>
                <th>status</th>
                <th>request_date</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>{$row['request_id']}</td>";
                    echo "<td>{$row['requester_id']}</td>";
                    echo "<td>{$row['requested_book_id']}</td>";
                    echo "<td>{$row['message']}</td>";
                    echo "<td>{$row['status']}</td>";
                    echo "<td>{$row['request_date']}</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='6'>No exchange requests found.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

</body>
</html>
