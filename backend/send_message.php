<?php
session_start();
require_once('db_connect.php');

if(!isset($_SESSION['loggedin'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Not logged in']);
    exit();
}

$user_id = $_SESSION['user_id'];
$receiver_id = isset($_POST['receiver_id']) ? (int)$_POST['receiver_id'] : 0;
$content = isset($_POST['content']) ? trim($_POST['content']) : '';

if($receiver_id <= 0 || $content === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Missing data']);
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
$stmt->bind_param("ii", $user_id, $receiver_id);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows == 0) {
    http_response_code(403);
    echo json_encode(['error' => 'You can only message mutuals.']);
    exit();
}
$stmt->close();

$stmt = $conn->prepare("INSERT INTO message (sender_id, receiver_id, content) VALUES (?, ?, ?)");
$stmt->bind_param("iis", $user_id, $receiver_id, $content);
if($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to send']);
}
$stmt->close();
?>