<?php
session_start();
require_once 'db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['loggedin'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$user_id = (int)$data['user_id'];
$current_user_id = $_SESSION['user_id'];

// Prevent self-follow
if ($user_id == $current_user_id) {
    echo json_encode(['success' => false, 'error' => 'Cannot follow yourself']);
    exit();
}

try {
    $conn->begin_transaction();
    
    if ($data['action'] === 'follow') {
        // Check if follow relationship already exists
        $check = $conn->prepare("SELECT 1 FROM follow WHERE following_user_id = ? AND followed_user_id = ?");
        $check->bind_param("ii", $current_user_id, $user_id);
        $check->execute();
        
        if ($check->get_result()->num_rows == 0) {
            $stmt = $conn->prepare("INSERT INTO follow (following_user_id, followed_user_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $current_user_id, $user_id);
            $stmt->execute();
        }
    } else {
        $stmt = $conn->prepare("DELETE FROM follow WHERE following_user_id = ? AND followed_user_id = ?");
        $stmt->bind_param("ii", $current_user_id, $user_id);
        $stmt->execute();
    }
    
    $conn->commit();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

?>