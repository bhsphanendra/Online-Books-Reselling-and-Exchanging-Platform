<?php
include 'db.php';
$id = $_GET['id'];
$conn->query("UPDATE exchange_request SET status='accepted' WHERE request_id=$id");
header("Location: exchange.php");
?>
