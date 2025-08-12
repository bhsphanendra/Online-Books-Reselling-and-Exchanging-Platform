<?php
session_start();
include 'db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$userId = $_SESSION['user_id'];
$query = "SELECT * FROM users WHERE user_id = '$userId'";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);
$initial = strtoupper(substr($user['username'], 0, 1));
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Profile</title>
    <link rel="stylesheet" href="profile.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            min-height: 100vh;
        }
        .profile-card {
            background: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 50%;
            padding: 20px;
            text-align: center;
            margin: 20px 0;
        }
        .avatar {
            width: 100px;
            height: 100px;
            background-color: #007bff;
            color: #ffffff;
            font-size: 36px;
            font-weight: bold;
            line-height: 100px;
            border-radius: 50%;
            margin: 0 auto 20px;
        }
        h2 {
            margin: 10px 0;
            font-size: 24px;
            color: #333;
        }
        form {
            margin-top: 20px;
        }
        form div {
            margin-bottom: 15px;
            text-align: left;
        }
        label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
            color: #555;
        }
        input, select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            background-color: #f9f9f9;
        }
        .button-container {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }
        .save-btn, .cancel-btn {
            padding: 10px 20px;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
            border: none;
        }
        .save-btn {
            background-color: #007bff;
            color: #ffffff;
        }
        .save-btn:hover {
            background-color: #0056b3;
        }
        .cancel-btn {
            background-color: #e74c3c;
            color: #ffffff;
        }
        .cancel-btn:hover {
            background-color: #c0392b;
        }
    </style>
</head>
<body>
    <div class="profile-card">
        <div class="avatar"><?= $initial ?></div>
        <h2>Edit Your Profile</h2>
        <form action="update_profile.php" method="post">
            <div>
                <label for="firstname">First Name:</label>
                <input type="text" id="firstname" name="firstname" value="<?= htmlspecialchars($user['firstname']) ?>" required>
            </div>
            <div>
                <label for="lastname">Last Name:</label>
                <input type="text" id="lastname" name="lastname" value="<?= htmlspecialchars($user['lastname']) ?>" required>
            </div>
            <div>
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>
            </div>
            <div>
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
            </div>
            <div>
                <label for="dob">Date of Birth:</label>
                <input type="date" id="dob" name="dob" value="<?= htmlspecialchars($user['dob']) ?>" required>
            </div>
            <div>
                <label for="gender">Gender:</label>
                <select id="gender" name="gender" required>
                    <option value="male" <?= $user['gender'] == 'male' ? 'selected' : '' ?>>Male</option>
                    <option value="female" <?= $user['gender'] == 'female' ? 'selected' : '' ?>>Female</option>
                    <option value="other" <?= $user['gender'] == 'other' ? 'selected' : '' ?>>Other</option>
                </select>
            </div>
            <div>
                <label for="mobile">Mobile Number:</label>
                <input type="tel" id="mobile" name="mobile" value="<?= htmlspecialchars($user['mobile']) ?>" required>
            </div>
            <div>
                <button type="button" id="changePasswordBtn" class="save-btn">Change Password</button>
            </div>
            <div id="passwordFields" style="display: none;">
                <div>
                    <label for="old_password">Old Password:</label>
                    <input type="password" id="old_password" name="old_password" >
                </div>
                <div>
                    <label for="new_password">New Password:</label>
                    <input type="password" id="new_password" name="new_password" >
                </div>
                <div>
                    <label for="confirm_password">Confirm Password:</label>
                    <input type="password" id="confirm_password" name="confirm_password" >
                </div>
            </div>
            <div class="button-container">
                <button class="save-btn" type="submit">Save Changes</button>
                <button class="cancel-btn" type="button" onclick="location.href='profile.php'">Cancel</button>
            </div>
        </form>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const changePasswordBtn = document.getElementById('changePasswordBtn');
            const passwordFields = document.getElementById('passwordFields');
            const newPassword = document.getElementById('new_password');
            const oldPassword = document.getElementById('old_password');
            const confirmPassword = document.getElementById('confirm_password');

            // Toggle visibility of password fields
            changePasswordBtn.addEventListener('click', function () {
                if (passwordFields.style.display === 'none') {
                    passwordFields.style.display = 'block';
                    newPassword.required = true;
                    oldPassword.required = true;
                    confirmPassword.required = true;
                } else {
                    passwordFields.style.display = 'none';
                    newPassword.required = false;
                    oldPassword.required = false;
                    confirmPassword.required = false;
                }
            });
        });
    </script>
</body>
</html>
