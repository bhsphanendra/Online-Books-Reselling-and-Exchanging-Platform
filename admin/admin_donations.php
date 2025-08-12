<?php
include 'db_connect.php';

// Delete donation
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $conn->query("DELETE FROM donations WHERE donation_id = $delete_id");
    header("Location: admin_donations.php");
}

// Fetch all donations with donor details
$result = $conn->query("SELECT * from donations");
?>

<?php
session_start();
$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Admin';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage Donations</title>
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
            height: 100%;
            width: 250px;
            color: white;
            padding-top: 20px;
        }

        .content {
            margin-left: 270px;
            padding: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #f2f2f2;
        }

        tr:hover {
            background-color: #f5f5f5;
        }

        .action-btn {
            padding: 5px 10px;
            margin: 0 5px;
            text-decoration: none;
            border-radius: 4px;
            font-size: 14px;
        }

        .delete-btn {
            background-color: #dc3545;
            color: white;
        }

        .delete-btn:hover {
            background-color: #c82333;
        }

        .book-image {
            width: 50px;
            height: 50px;
            object-fit: cover;
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
        <h1>Manage Donations</h1>
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
            <?php while ($row = $result->fetch_assoc()): ?>
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