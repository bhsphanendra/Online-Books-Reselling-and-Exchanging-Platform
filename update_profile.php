<?php
session_start();
include 'db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];

// Retrieve form data
$firstname = mysqli_real_escape_string($conn, $_POST['firstname']);
$lastname = mysqli_real_escape_string($conn, $_POST['lastname']);
$username = mysqli_real_escape_string($conn, $_POST['username']);
$email = mysqli_real_escape_string($conn, $_POST['email']);
$mobile = mysqli_real_escape_string($conn, $_POST['mobile']); // Added mobile field
$dob = mysqli_real_escape_string($conn, $_POST['dob']);
$gender = mysqli_real_escape_string($conn, $_POST['gender']);
$new_password = mysqli_real_escape_string($conn, $_POST['new_password']);
$confirm_password = mysqli_real_escape_string($conn, $_POST['confirm_password']);
$old_password = isset($_POST['old_password']) ? mysqli_real_escape_string($conn, $_POST['old_password']) : null;

// Check if the user wants to change the password
if (!empty($new_password)) {
    // Fetch the current password from the database
    $query = "SELECT password FROM users WHERE user_id = '$userId'";
    $result = mysqli_query($conn, $query);
    $user = mysqli_fetch_assoc($result);

    if (!$user || !password_verify($old_password, $user['password'])) {
        echo "<script>alert('Old password is incorrect'); window.location.href='edit_profile.php';</script>";
        exit();
    }

    if ($new_password !== $confirm_password) {
        echo "<script>alert('New password and confirm password do not match'); window.location.href='edit_profile.php';</script>";
        exit();
    }

    // Hash the new password
    $hashedPassword = password_hash($new_password, PASSWORD_DEFAULT);

    // Update query with password
    $update = "UPDATE users 
               SET firstname='$firstname', lastname='$lastname', username='$username', email='$email', mobile='$mobile', dob='$dob', gender='$gender', password='$hashedPassword' 
               WHERE user_id='$userId'";
} else {
    // Update query without password
    $update = "UPDATE users 
               SET firstname='$firstname', lastname='$lastname', username='$username', email='$email', mobile='$mobile', dob='$dob', gender='$gender' 
               WHERE user_id='$userId'";
}

if (mysqli_query($conn, $update)) {
    echo "<script>alert('Profile Updated Successfully'); window.location.href='profile.php';</script>";
} else {
    echo "Error updating profile: " . mysqli_error($conn);
}

mysqli_close($conn);
?>
