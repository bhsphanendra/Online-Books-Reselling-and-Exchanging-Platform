<?php
include 'db.php';
$id = $_GET['id'];
$conn->query("DELETE FROM exchange_books WHERE id = $id");
header("Location: exchange.php");
?>
