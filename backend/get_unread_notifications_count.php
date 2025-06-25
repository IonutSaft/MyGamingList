<?php
session_start();
require_once('db_connect.php');

if (!isset($_SESSION['loggedin'])) {
  http_response_code(401);
  exit();
}

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare(
    "SELECT COUNT(*) as unread_count 
     FROM `notification` 
     WHERE user_id = ? AND is_read = 0"
);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

echo json_encode(['unread_count' => (int)$row['unread_count']]);
$stmt->close();
?>