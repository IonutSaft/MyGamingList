<?php
session_start();
require_once('db_connect.php');

if (!isset($_SESSION['loggedin'])) {
  http_response_code(401);
  exit();
}

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("UPDATE `notification` SET is_read = 1 WHERE user_id = ? AND is_read = 0");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->close();

echo json_encode(['success' => true]);
?>