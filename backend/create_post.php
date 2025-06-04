<?php
ob_start();
session_start();


ini_set('display_errors', 1);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', 'php_errors.log');

$conn = new mysqli("localhost", "root", "", "mygamelist");
if($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

header('Content-Type: application/json');

try {
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Not logged in', 401);
    }

    $content = trim($_POST['post_content'] ?? '');
    $userId = $_SESSION['user_id'];

    $hasContent = !empty($content);
    $hasMedia = isset($_FILES['media']) && !empty($_FILES['media']['name'][0]);

    if (!$hasContent && !$hasMedia) {
        throw new Exception('Post content or media is required', 400);
    }

    // Extract tags from content
    $tags = [];
    if ($hasContent) {
        preg_match_all('/#(\w+)/', $content, $matches);
        $tags = array_unique($matches[1]);
    }

    // Save media files
    $mediaPaths = [];
    if ($hasMedia) {
        // Create uploads directory if it doesn't exist
        if (!file_exists('uploads')) {
            mkdir('../uploads', 0777, true);
        }
        
        foreach ($_FILES['media']['tmp_name'] as $key => $tmpName) {
            if ($_FILES['media']['error'][$key] === UPLOAD_ERR_OK) {
                $fileName = uniqid() . '_' . basename($_FILES['media']['name'][$key]);
                $targetPath = "../uploads/" . $fileName;
                
                if (move_uploaded_file($tmpName, $targetPath)) {
                    $mediaPaths[] = $targetPath;
                } else {
                    throw new Exception('Failed to upload media file', 500);
                }
            }
        }
    }

    $mediaContent = !empty($mediaPaths) ? implode(',', $mediaPaths) : null;

    $conn->begin_transaction();

    // Insert post
    $stmt = $conn->prepare("INSERT INTO post (user_id, text_content, media_content, post_date) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param('iss', $userId, $content, $mediaContent);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to create post: ' . $stmt->error, 500);
    }
    
    $postId = $conn->insert_id;
    $stmt->close();

    // Process tags
    foreach ($tags as $tagName) {
        // Check if tag exists
        $checkStmt = $conn->prepare("SELECT tag_id FROM tag WHERE name = ?");
        $checkStmt->bind_param('s', $tagName);
        
        if (!$checkStmt->execute()) {
            throw new Exception('Failed to check tag: ' . $checkStmt->error, 500);
        }
        
        $result = $checkStmt->get_result();
        $tagId = $result->fetch_assoc()['tag_id'] ?? null;
        $checkStmt->close();

        // Create new tag if needed
        if (!$tagId) {
            $insertTag = $conn->prepare("INSERT INTO tag (name) VALUES (?)");
            $insertTag->bind_param('s', $tagName);
            
            if (!$insertTag->execute()) {
                throw new Exception('Failed to create tag: ' . $insertTag->error, 500);
            }
            
            $tagId = $conn->insert_id;
            $insertTag->close();
        }

        // Link tag to post
        $linkStmt = $conn->prepare("INSERT INTO post_tag (post_id, tag_id) VALUES (?, ?)");
        $linkStmt->bind_param('ii', $postId, $tagId);
        
        if (!$linkStmt->execute()) {
            throw new Exception('Failed to link tag: ' . $linkStmt->error, 500);
        }
        
        $linkStmt->close();
    }

    $conn->commit();
    
    // Clear output buffer and return JSON
    ob_end_clean();
    echo json_encode(['success' => true]);
    exit;
    
} catch (Exception $e) {
    // Rollback transaction if needed
    if ($conn instanceof mysqli && $conn->connect_errno === 0) {
        $conn->rollback();
    }
    
    // Clear output buffer and return error
    ob_end_clean();
    http_response_code($e->getCode() ?: 500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'error_code' => $e->getCode()
    ]);
    exit;
}

?>