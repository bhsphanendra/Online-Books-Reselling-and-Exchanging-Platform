<?php
include 'db_connect.php';

$conn = new mysqli("localhost", "root", "", "bookswap_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle user addition
if (isset($_POST['add_user'])) {
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $dob = $_POST['dob'];
    $gender = $_POST['gender'];
    $role = $_POST['role'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $sql = "INSERT INTO users (firstname, lastname, username, email, dob, gender, role, password) 
            VALUES ('$firstname', '$lastname', '$username', '$email', '$dob', '$gender', '$role', '$password')";
    $conn->query($sql);
    header("Location: admin_users.php");
}

// Handle user update
if (isset($_POST['edit_user'])) {
    $user_id = $_POST['user_id'];
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $dob = $_POST['dob'];
    $gender = $_POST['gender'];
    $role = $_POST['role'];

    $sql = "UPDATE users SET firstname='$firstname', lastname='$lastname', username='$username', 
            email='$email', dob='$dob', gender='$gender', role='$role' WHERE user_id=$user_id";
    $conn->query($sql);
    header("Location: admin_users.php");
}

// Handle user deletion
if (isset($_GET['delete_user'])) {
    $user_id = $_GET['delete_user'];
    $sql = "DELETE FROM users WHERE user_id=$user_id";
    $conn->query($sql);
    header("Location: admin_users.php");
}

$sql = "SELECT * FROM users";
$result = $conn->query($sql);
?>



<?php
// Assuming you have a session started and the username is stored in $_SESSION['username']
session_start();
$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Admin';
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
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
        <h1>Manage Users</h1>
        
        <button onclick="toggleForm('addUserForm')" class="btn">Add New User</button>
        
        <div id="addUserForm" class="form-container hidden">
            <h3>Add User</h3>
            <form method="POST">
                <div class="form-group">
                    <input type="text" name="firstname" placeholder="First Name" required>
                </div>
                <div class="form-group">
                    <input type="text" name="lastname" placeholder="Last Name" required>
                </div>
                <div class="form-group">
                    <input type="text" name="username" placeholder="Username" required>
                </div>
                <div class="form-group">
                    <input type="email" name="email" placeholder="Email" required>
                </div>
                <div class="form-group">
                    <input type="date" name="dob" required>
                </div>
                <div class="form-group">
                    <select name="gender">
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <select name="role">
                        <option value="user">User</option>
                        <option value="seller">Seller</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <div class="form-group">
                    <input type="password" name="password" placeholder="Password" required>
                </div>
                <button type="submit" name="add_user" class="btn">Save</button>
                <button type="button" onclick="toggleForm()" class="btn btn-danger">Cancel</button>
            </form>
        </div>

        <div id="editUserForm" class="form-container hidden">
            <h3>Edit User</h3>
            <form method="POST">
                <input type="hidden" name="user_id" id="edit_user_id">
                <div class="form-group">
                    <input type="text" name="firstname" id="edit_firstname" required>
                </div>
                <div class="form-group">
                    <input type="text" name="lastname" id="edit_lastname" required>
                </div>
                <div class="form-group">
                    <input type="text" name="username" id="edit_username" required>
                </div>
                <div class="form-group">
                    <input type="email" name="email" id="edit_email" required>
                </div>
                <div class="form-group">
                    <input type="date" name="dob" id="edit_dob" required>
                </div>
                <div class="form-group">
                    <select name="gender" id="edit_gender">
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <select name="role" id="edit_role">
                        <option value="user">User</option>
                        <option value="seller">Seller</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <button type="submit" name="edit_user" class="btn">Update</button>
                <button type="button" onclick="toggleForm()" class="btn btn-danger">Cancel</button>
            </form>
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
                <th>Actions</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()) { ?>
            <tr>
                <td><?= $row['user_id'] ?></td>
                <td><?= $row['firstname'] ?></td>
                <td><?= $row['lastname'] ?></td>
                <td><?= $row['username'] ?></td>
                <td><?= $row['email'] ?></td>
                <td><?= $row['dob'] ?></td>
                <td><?= $row['gender'] ?></td>
                <td><?= $row['role'] ?></td>
                <td class="action-buttons">
                    <button onclick="editUser(<?= $row['user_id'] ?>, '<?= $row['firstname'] ?>', '<?= $row['lastname'] ?>', '<?= $row['username'] ?>', '<?= $row['email'] ?>', '<?= $row['dob'] ?>', '<?= $row['gender'] ?>', '<?= $row['role'] ?>')" class="btn">Edit</button>
                    <a href="?delete_user=<?= $row['user_id'] ?>" onclick="return confirm('Are you sure?')" class="btn btn-danger">Delete</a>
                </td>
            </tr>
            <?php } ?>
        </table>
    </div>

    <script>
    function toggleForm(formId) {
        document.getElementById('addUserForm').classList.add('hidden');
        document.getElementById('editUserForm').classList.add('hidden');

        if (formId) {
            document.getElementById(formId).classList.remove('hidden');
        }
    }

    function editUser(userId, firstname, lastname, username, email, dob, gender, role) {
        document.getElementById('edit_user_id').value = userId;
        document.getElementById('edit_firstname').value = firstname;
        document.getElementById('edit_lastname').value = lastname;
        document.getElementById('edit_username').value = username;
        document.getElementById('edit_email').value = email;
        document.getElementById('edit_dob').value = dob;
        document.getElementById('edit_gender').value = gender;
        document.getElementById('edit_role').value = role;
        toggleForm('editUserForm');
    }
    </script>
</body>
</html>