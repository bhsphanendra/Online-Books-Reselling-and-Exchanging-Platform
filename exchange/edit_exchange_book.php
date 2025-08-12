<?php
session_start();
include 'db.php';
$id = $_GET['id'];
$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $author = $_POST['author'];
    $condition = $_POST['condition'];
    $description = $_POST['description'];
    $preference = $_POST['exchange_preference'];
    $image = $_FILES['image']['name'];

    if ($image != '') {
        $target = "uploads/" . basename($image);
        move_uploaded_file($_FILES['image']['tmp_name'], $target);
        $imgUpdate = ", image_path = '$target'";
    } else {
        $imgUpdate = "";
    }

    $query = "UPDATE exchange_books SET 
              title = '$title',
              author = '$author',
              `condition` = '$condition',
              description = '$description',
              exchange_preference = '$preference'
              $imgUpdate
              WHERE id = $id AND seller_id = $user_id";


    $conn->query($query);
    header("Location: exchange.php");
}

$book = $conn->query("SELECT * FROM exchange_books WHERE id = $id AND seller_id = $user_id")->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>

    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        h1, h2 {
            color: #333;
            text-align: center;
        }
        form, table {
            background: #fff;
            padding: 20px;
            margin: 20px auto;
            border-radius: 8px;
            max-width: 800px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        input[type="text"],
        input[type="number"],
        input[type="email"],
        input[type="submit"],
        select,
        textarea {
            width: 100%;
            padding: 10px;
            margin-top: 8px;
            margin-bottom: 16px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #45a049;
        }
        table {
            border-collapse: collapse;
            width: 100%;
        }
        th, td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        a.button {
            display: inline-block;
            padding: 8px 12px;
            margin: 4px;
            background-color: #007BFF;
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }
        a.button:hover {
            background-color: #0056b3;
        }
    </style>

    <title>Edit Book</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<h2>Edit Book Details</h2>
<form method="POST" enctype="multipart/form-data">
    <input type="text" name="title" value="<?= htmlspecialchars($book['title']) ?>" required><br>
    <input type="text" name="author" value="<?= htmlspecialchars($book['author']) ?>" required><br>
    <input type="text" name="condition" value="<?= htmlspecialchars($book['condition']) ?>" required><br>
    <textarea name="description" required><?= htmlspecialchars($book['description']) ?></textarea><br>
    <input type="text" name="exchange_preference" value="<?= htmlspecialchars($book['exchange_preference']) ?>" required><br>
    <label>Current Image:</label><br>
    <img src="<?= $book['image_path'] ?>" width="100"><br>
    <label>Change Image:</label>
    <input type="file" name="image" accept="image/*"><br>
    <button type="submit">Update Book</button>
</form>
</body>
</html>
