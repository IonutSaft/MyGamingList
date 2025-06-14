<?php
session_start();
require_once('db_connect.php');

if(!isset($_SESSION['loggedin'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Not logged in']);
    exit();
}

$user_id = $_SESSION['user_id'];
$other_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;

if($other_id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid user']);
    exit();
}

// Check mutual follow
$mutual_sql = "
SELECT 1
FROM follow f1
JOIN follow f2 ON f2.following_user_id = f1.followed_user_id AND f2.followed_user_id = f1.following_user_id
WHERE f1.following_user_id = ? AND f1.followed_user_id = ?
LIMIT 1
";
$stmt = $conn->prepare($mutual_sql);
$stmt->bind_param("ii", $user_id, $other_id);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows == 0) {
    http_response_code(403);
    echo json_encode(['error' => 'You can only view conversations with mutuals.']);
    exit();
}
$stmt->close();

$sql = "SELECT m.*, u.username AS sender_name, u.avatar AS sender_avatar
        FROM message m
        JOIN user u ON m.sender_id = u.user_id
        WHERE (m.sender_id = ? AND m.receiver_id = ?)
           OR (m.sender_id = ? AND m.receiver_id = ?)
        ORDER BY m.sent_at ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iiii", $user_id, $other_id, $other_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

$messages = [];
while ($row = $result->fetch_assoc()) {
    $messages[] = $row;
}
$stmt->close();

header('Content-Type: application/json');
echo json_encode($messages);
?>