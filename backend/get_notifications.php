<?php
session_start();
require_once('db_connect.php');

if (!isset($_SESSION['loggedin'])) {
  http_response_code(401);
  exit();
}

$user_id = $_SESSION['user_id'];
$sql = "SELECT n.notification_id, n.content, n.created_at, n.is_read, a.user_id as actor_id, a.username as actor_username, a.avatar as actor_avatar
        FROM `notification` n
        JOIN user a ON n.actor_id = a.user_id
        WHERE n.user_id = ?
        ORDER BY n.created_at DESC
        LIMIT 20";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$notifications = [];
while ($row = $result->fetch_assoc()) {
  $notifications[] = $row;
}
$stmt->close();

header('Content-Type: application/json');
echo json_encode($notifications);
?>