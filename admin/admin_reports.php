<?php
include 'db_connect.php';

// Fetch total counts for all categories
$stats = [
    'users' => $conn->query("SELECT COUNT(*) AS total FROM users")->fetch_assoc()['total'],
    'books' => $conn->query("SELECT COUNT(*) AS total FROM books")->fetch_assoc()['total'],
    'exchange_books' => $conn->query("SELECT COUNT(*) AS total FROM exchange_books")->fetch_assoc()['total'],
    'exchange_requests' => $conn->query("SELECT COUNT(*) AS total FROM exchange_request")->fetch_assoc()['total'],
    'donations' => $conn->query("SELECT COUNT(*) AS total FROM donations")->fetch_assoc()['total']
];

// Fetch daily statistics for all categories
$daily_stats = [];
$tables = [
    'users' => 'created_at',
    'books' => 'created_at',
    'exchange_books' => 'date_listed',
    'exchange_request' => 'request_date',
    'donations' => 'donated_at'
];

// Get the last 7 days dates
$dates = [];
for ($i = 6; $i >= 0; $i--) {
    $dates[] = date('Y-m-d', strtotime("-$i days"));
}

// Initialize daily stats with 0 counts for all dates
foreach ($tables as $table => $date_field) {
    $daily_stats[$table] = array_fill_keys($dates, 0);
}

// Fetch actual data for each table
foreach ($tables as $table => $date_field) {
    $query = "SELECT DATE($date_field) AS date, COUNT(*) AS count 
              FROM $table 
              WHERE $date_field >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
              GROUP BY DATE($date_field) 
              ORDER BY date";
    
    $result = $conn->query($query);
    while ($row = $result->fetch_assoc()) {
        $daily_stats[$table][$row['date']] = (int)$row['count'];
    }
}

// Prepare data for charts
$chart_labels = json_encode($dates);
$chart_data = [];
foreach ($tables as $table => $date_field) {
    $chart_data[$table] = json_encode(array_values($daily_stats[$table]));
}

// Fetch recent activity (last 5 entries from each table)
$recent_activity = [
    'users' => $conn->query("SELECT * FROM users ORDER BY user_id DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC),
    'books' => $conn->query("SELECT * FROM books ORDER BY book_id DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC),
    'exchange_books' => $conn->query("SELECT * FROM exchange_books ORDER BY id DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC),
    'exchange_requests' => $conn->query("SELECT * FROM exchange_request ORDER BY request_id DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC),
    'donations' => $conn->query("SELECT * FROM donations ORDER BY id DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC)
];

// Fetch monthly statistics for bar charts
$monthly_stats = [];
$current_year = date('Y');
foreach ($tables as $table => $date_field) {
    $query = "SELECT MONTH($date_field) AS month, COUNT(*) AS count 
              FROM $table 
              WHERE YEAR($date_field) = $current_year
              GROUP BY MONTH($date_field) 
              ORDER BY month";
    
    $result = $conn->query($query);
    $monthly_stats[$table] = array_fill(1, 12, 0); // Initialize all months with 0
    
    while ($row = $result->fetch_assoc()) {
        $monthly_stats[$table][$row['month']] = (int)$row['count'];
    }
}

// Prepare monthly data for charts
$monthly_labels = json_encode(['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']);
$monthly_data = [];
foreach ($tables as $table => $date_field) {
    $monthly_data[$table] = json_encode(array_values($monthly_stats[$table]));
}

session_start();
$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Admin';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin Reports</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="report.css">
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
        <h1>System Reports & Analytics</h1>

        <!-- Summary Statistics -->
        <div class="stats-container">
            <div class="stat-card users">
                <h3>Total Users</h3>
                <p><?= $stats['users'] ?></p>
                
            </div>
            
            <div class="stat-card books">
                <h3>Total Books</h3>
                <p><?= $stats['books'] ?></p>
                
            </div>
            
            <div class="stat-card exchange-books">
                <h3>Exchange Books</h3>
                <p><?= $stats['exchange_books'] ?></p>
                
            </div>
            
            <div class="stat-card exchange-requests">
                <h3>Exchange Requests</h3>
                <p><?= $stats['exchange_requests'] ?></p>
               
            </div>
            
            <div class="stat-card donations">
                <h3>Donations</h3>
                <p><?= $stats['donations'] ?></p>
                
            </div>
        </div>

        <!-- Combined Activity Chart -->
        <div class="chart-container">
            <h2>Combined Activity Overview <a href="#" class="view-all">View Detailed Report</a></h2>
            <canvas id="combinedChart" height="300"></canvas>
        </div>

        <!-- Individual Charts Row 1 -->
        <div class="chart-row">
            <div class="chart-box">
                <h3>User Growth <span class="badge badge-primary">Monthly</span></h3>
                <canvas id="usersChart" height="250"></canvas>
            </div>
            
            <div class="chart-box">
                <h3>Book Listings <span class="badge badge-success">Monthly</span></h3>
                <canvas id="booksChart" height="250"></canvas>
            </div>
        </div>

        <!-- Individual Charts Row 2 -->
        <div class="chart-row">
            <div class="chart-box">
                <h3>Exchange Books <span class="badge badge-warning">Monthly</span></h3>
                <canvas id="exchangeBooksChart" height="250"></canvas>
            </div>
            
            <div class="chart-box">
                <h3>Exchange Requests <span class="badge badge-danger">Monthly</span></h3>
                <canvas id="exchangeRequestsChart" height="250"></canvas>
            </div>
        </div>

        <!-- Individual Charts Row 3 -->
        <div class="chart-row">
            <div class="chart-box">
                <h3>Donations <span class="badge badge-purple">Monthly</span></h3>
                <canvas id="donationsChart" height="250"></canvas>
            </div>
            
            <div class="chart-box">
                <h3>Daily Activity Comparison <span class="badge">Last 7 Days</span></h3>
                <canvas id="dailyComparisonChart" height="250"></canvas>
            </div>
        </div>

        <!-- Recent Users -->
        <div class="table-container">
            <h2>Recent Users <a href="admin_users.php" class="view-all">View All</a></h2>
            <table class="activity-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Joined</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_activity['users'] as $user): ?>
                    <tr>
                        <td><?= $user['user_id'] ?></td>
                        <td><strong><?= htmlspecialchars($user['username']) ?></strong></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td><span class="badge <?= $user['role'] === 'admin' ? 'badge-primary' : 'badge-success' ?>"><?= ucfirst($user['role']) ?></span></td>
                        <td><?= date('M j, Y', strtotime($user['created_at'])) ?></td>
                        <td><span class="badge badge-success">Active</span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Recent Books -->
        <div class="table-container">
            <h2>Recent Books <a href="admin_books.php" class="view-all">View All</a></h2>
            <table class="activity-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Author</th>
                        <th>Price</th>
                        <th>Condition</th>
                        <th>Added</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_activity['books'] as $book): ?>
                    <tr>
                        <td><?= $book['book_id'] ?></td>
                        <td><strong><?= htmlspecialchars($book['title']) ?></strong></td>
                        <td><?= htmlspecialchars($book['author']) ?></td>
                        <td>$<?= number_format($book['price'], 2) ?></td>
                        <td><span class="badge <?= $book['condition'] === 'New' ? 'badge-success' : 'badge-warning' ?>"><?= $book['condition'] ?></span></td>
                        <td><?= date('M j, Y', strtotime($book['created_at'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Recent Exchange Books -->
        <div class="table-container">
            <h2>Recent Exchange Books <a href="admin_exchange_books.php" class="view-all">View All</a></h2>
            <table class="activity-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Author</th>
                        <th>Condition</th>
                        <th>Preference</th>
                        <th>Listed</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_activity['exchange_books'] as $book): ?>
                    <tr>
                        <td><?= $book['id'] ?></td>
                        <td><strong><?= htmlspecialchars($book['title']) ?></strong></td>
                        <td><?= htmlspecialchars($book['author']) ?></td>
                        <td><span class="badge <?= $book['condition'] === 'New' ? 'badge-success' : 'badge-warning' ?>"><?= $book['condition'] ?></span></td>
                        <td><?= $book['exchange_preference'] ?></td>
                        <td><?= date('M j, Y', strtotime($book['date_listed'])) ?></td>
                        <td><span class="badge <?= $book['status'] === 'Available' ? 'badge-success' : 'badge-danger' ?>"><?= $book['status'] ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Recent Exchange Requests -->
        <div class="table-container">
            <h2>Recent Exchange Requests <a href="admin_exchange_requests.php" class="view-all">View All</a></h2>
            <table class="activity-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Book ID</th>
                        <th>Requester ID</th>
                        <th>Request Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_activity['exchange_requests'] as $request): ?>
                    <tr>
                        <td><?= $request['request_id'] ?></td>
                        <td><?= $request['requested_book_id'] ?></td>
                        <td><?= $request['requester_id'] ?></td>
                        <td><?= date('M j, Y', strtotime($request['request_date'])) ?></td>
                        <td>
                            <span class="badge 
                                <?= $request['status'] === 'Pending' ? 'badge-warning' : 
                                   ($request['status'] === 'Approved' ? 'badge-success' : 'badge-danger') ?>">
                                <?= $request['status'] ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Recent Donations -->
        <div class="table-container">
            <h2>Recent Donations <a href="admin_donations.php" class="view-all">View All</a></h2>
            <table class="activity-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Donor Name</th>
                        <th>Type</th>
                        <th>Quantity</th>
                        <th>Donated</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_activity['donations'] as $donation): ?>
                    <tr>
                        <td><?= $donation['id'] ?></td>
                        <td><strong><?= htmlspecialchars($donation['name']) ?></strong></td>
                        <td><?= $donation['donationType'] ?></td>
                        <td><?= $donation['quantity'] ?></td>
                        <td><?= date('M j, Y', strtotime($donation['donated_at'])) ?></td>
                        <td><span class="badge badge-success">Completed</span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        // Colors
        const colors = {
            primary: '#3498db',
            success: '#2ecc71',
            warning: '#f39c12',
            danger: '#e74c3c',
            purple: '#9b59b6',
            gray: '#95a5a6'
        };

        // Combined Chart
        const combinedCtx = document.getElementById('combinedChart').getContext('2d');
        new Chart(combinedCtx, {
            type: 'line',
            data: {
                labels: <?= $chart_labels ?>,
                datasets: [
                    {
                        label: 'Users',
                        data: <?= $chart_data['users'] ?>,
                        borderColor: colors.primary,
                        backgroundColor: 'rgba(52, 152, 219, 0.1)',
                        borderWidth: 2,
                        tension: 0.3,
                        fill: true
                    },
                    {
                        label: 'Books',
                        data: <?= $chart_data['books'] ?>,
                        borderColor: colors.success,
                        backgroundColor: 'rgba(46, 204, 113, 0.1)',
                        borderWidth: 2,
                        tension: 0.3,
                        fill: true
                    },
                    {
                        label: 'Exchange Books',
                        data: <?= $chart_data['exchange_books'] ?>,
                        borderColor: colors.warning,
                        backgroundColor: 'rgba(243, 156, 18, 0.1)',
                        borderWidth: 2,
                        tension: 0.3,
                        fill: true
                    },
                    {
                        label: 'Exchange Requests',
                        data: <?= $chart_data['exchange_request'] ?>,
                        borderColor: colors.danger,
                        backgroundColor: 'rgba(231, 76, 60, 0.1)',
                        borderWidth: 2,
                        tension: 0.3,
                        fill: true
                    },
                    {
                        label: 'Donations',
                        data: <?= $chart_data['donations'] ?>,
                        borderColor: colors.purple,
                        backgroundColor: 'rgba(155, 89, 182, 0.1)',
                        borderWidth: 2,
                        tension: 0.3,
                        fill: true
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Daily Activity Across All Categories',
                        font: {
                            size: 16
                        }
                    },
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Count'
                        },
                        grid: {
                            drawBorder: false
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'nearest'
                }
            }
        });

        // Users Chart
        const usersCtx = document.getElementById('usersChart').getContext('2d');
        new Chart(usersCtx, {
            type: 'bar',
            data: {
                labels: <?= $monthly_labels ?>,
                datasets: [{
                    label: 'New Users',
                    data: <?= $monthly_data['users'] ?>,
                    backgroundColor: colors.primary,
                    borderColor: colors.primary,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            drawBorder: false
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });

        // Books Chart
        const booksCtx = document.getElementById('booksChart').getContext('2d');
        new Chart(booksCtx, {
            type: 'bar',
            data: {
                labels: <?= $monthly_labels ?>,
                datasets: [{
                    label: 'Books Added',
                    data: <?= $monthly_data['books'] ?>,
                    backgroundColor: colors.success,
                    borderColor: colors.success,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            drawBorder: false
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });

        // Exchange Books Chart
        const exchangeBooksCtx = document.getElementById('exchangeBooksChart').getContext('2d');
        new Chart(exchangeBooksCtx, {
            type: 'bar',
            data: {
                labels: <?= $monthly_labels ?>,
                datasets: [{
                    label: 'Exchange Books',
                    data: <?= $monthly_data['exchange_books'] ?>,
                    backgroundColor: colors.warning,
                    borderColor: colors.warning,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            drawBorder: false
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });

        // Exchange Requests Chart
        const exchangeRequestsCtx = document.getElementById('exchangeRequestsChart').getContext('2d');
        new Chart(exchangeRequestsCtx, {
            type: 'bar',
            data: {
                labels: <?= $monthly_labels ?>,
                datasets: [{
                    label: 'Exchange Requests',
                    data: <?= $monthly_data['exchange_request'] ?>,
                    backgroundColor: colors.danger,
                    borderColor: colors.danger,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            drawBorder: false
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });

        // Donations Chart
        const donationsCtx = document.getElementById('donationsChart').getContext('2d');
        new Chart(donationsCtx, {
            type: 'bar',
            data: {
                labels: <?= $monthly_labels ?>,
                datasets: [{
                    label: 'Donations',
                    data: <?= $monthly_data['donations'] ?>,
                    backgroundColor: colors.purple,
                    borderColor: colors.purple,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            drawBorder: false
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });

        // Daily Comparison Chart
        const dailyComparisonCtx = document.getElementById('dailyComparisonChart').getContext('2d');
        new Chart(dailyComparisonCtx, {
            type: 'radar',
            data: {
                labels: <?= $chart_labels ?>,
                datasets: [
                    {
                        label: 'Users',
                        data: <?= $chart_data['users'] ?>,
                        backgroundColor: 'rgba(52, 152, 219, 0.2)',
                        borderColor: colors.primary,
                        borderWidth: 2
                    },
                    {
                        label: 'Books',
                        data: <?= $chart_data['books'] ?>,
                        backgroundColor: 'rgba(46, 204, 113, 0.2)',
                        borderColor: colors.success,
                        borderWidth: 2
                    },
                    {
                        label: 'Exchange Books',
                        data: <?= $chart_data['exchange_books'] ?>,
                        backgroundColor: 'rgba(243, 156, 18, 0.2)',
                        borderColor: colors.warning,
                        borderWidth: 2
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                },
                scales: {
                    r: {
                        angleLines: {
                            display: true
                        },
                        suggestedMin: 0
                    }
                }
            }
        });
    </script>
</body>
</html>