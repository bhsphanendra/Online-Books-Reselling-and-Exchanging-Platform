<?php
session_start();

if (isset($_SESSION['username'])) {
    echo json_encode(['username' => $_SESSION['username']]);
} else {
    echo json_encode(['error' => 'Not logged in']);
    http_response_code(401);
}
?> 