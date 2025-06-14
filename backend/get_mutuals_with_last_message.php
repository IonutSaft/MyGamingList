<?php
session_start();
require_once('db_connect.php');

if (!isset($_SESSION['loggedin'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Not logged in']);
    exit();
}

$user_id = $_SESSION['user_id'];

// Get mutuals, last message, and unread count (no LATERAL needed)
$sql = "
SELECT
  u.user_id,
  u.username,
  u.avatar,
  (
    SELECT m.content
    FROM message m
    WHERE
      (m.sender_id = u.user_id AND m.receiver_id = ?) OR
      (m.sender_id = ? AND m.receiver_id = u.user_id)
    ORDER BY m.sent_at DESC
    LIMIT 1
  ) AS last_message,
  (
    SELECT m.sent_at
    FROM message m
    WHERE
      (m.sender_id = u.user_id AND m.receiver_id = ?) OR
      (m.sender_id = ? AND m.receiver_id = u.user_id)
    ORDER BY m.sent_at DESC
    LIMIT 1
  ) AS last_message_time,
  (
    SELECT m.sender_id
    FROM message m
    WHERE
      (m.sender_id = u.user_id AND m.receiver_id = ?) OR
      (m.sender_id = ? AND m.receiver_id = u.user_id)
    ORDER BY m.sent_at DESC
    LIMIT 1
  ) AS last_message_sender_id,
  (
    SELECT COUNT(*)
    FROM message m3
    WHERE m3.sender_id = u.user_id AND m3.receiver_id = ? AND m3.read_at IS NULL
  ) AS unread_count
FROM user u
JOIN follow f1 ON f1.followed_user_id = u.user_id AND f1.following_user_id = ?
JOIN follow f2 ON f2.following_user_id = u.user_id AND f2.followed_user_id = ?
WHERE u.user_id != ?
ORDER BY last_message_time DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param(
    "iiiiiiiiii",
    $user_id, $user_id,    // for last_message
    $user_id, $user_id,    // for last_message_time
    $user_id, $user_id,    // for last_message_sender_id
    $user_id,              // for unread_count
    $user_id, $user_id,    // for mutuals
    $user_id               // not myself
);

$stmt->execute();
$result = $stmt->get_result();

$mutuals = [];
while ($row = $result->fetch_assoc()) {
    $mutuals[] = [
        "user_id" => $row['user_id'],
        "username" => $row['username'],
        "avatar" => $row['avatar'],
        "last_message" => $row['last_message'],
        "last_message_time" => $row['last_message_time'],
        "last_message_sender_id" => $row['last_message_sender_id'],
        "unread_count" => (int)$row['unread_count']
    ];
}
$stmt->close();

header('Content-Type: application/json');
echo json_encode($mutuals);
?>