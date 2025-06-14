<?php 
session_start();
require_once('db_connect.php');

if(!isset($_SESSION['loggedin']) ) {
 http_response_code(401);
 echo json_encode(['error' => 'Not logged in']);
 exit();
}

$user_id = $_SESSION['user_id'];
$offset = isset($_GET['offset']) ? (int)$_GET['offset']: 0;
$limit = isset($_GET['limit']) ? (int)$_GET['limit']: 10;

$sql = "
  SELECT u.user_id, u.username, u.avatar, m.content AS last_message, m.sent_at AS last_message_time
  FROM user u
  JOIN follow f1 ON f1.followed_user_id = u.user_id AND f1.following_user_id = ?
  JOIN follow f2 ON f2.following_user_id = u.user_id AND f2.followed_user_id = ?
  LEFT JOIN (
    SELECT 
      CASE
        WHEN sender_id = ? THEN receiver_id
        ELSE sender_id
      END AS other_user_id,
      content,
      sent_at
    FROM message
    WHERE sender_id = ? OR receiver_id = ?
    ORDER BY sent_at DESC
  ) m ON m.other_user_id = u.user_id
  GROUP BY u.user_id
  ORDER BY MAX(m.sent_at) DESC NULLS LAST
  LIMIT ? OFFSET ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iiiiiii",
  $user_id, $user_id,
  $user_id, $user_id, $user_id,
  $limit, $offset
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
    "last_message_time" => $row['last_message_time']
  ];
}
$stmt->close();
header("Content-Type: application/json");
echo json_encode($mutuals);
?>