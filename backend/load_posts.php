<?php
ini_set('html_errors', 0);
header('Content-Type: application/json');

session_start();
require __DIR__ . '/db_connect.php';

try {
  if(!isset($_SESSION['loggedin'])) {
    throw new Exception("User is not logged in", 401);
  }

  $page = max(1, (int)($_GET['page'] ?? 1));
  $perPage = 10;
  $offset = ($page - 1) * $perPage;


  $query = "SELECT p.*, u.username, u.avatar
            FROM post p
            JOIN user u ON p.user_id = u.user_id
            ORDER BY p.post_date DESC
            LIMIT ? OFFSET?";
  $stmt = $conn->prepare($query);

  if(!$stmt) {
    throw new Exception("Database prepare failed", 500);
  }

  $stmt->bind_param("ii", $perPage, $offset);
  $stmt->execute();
  $result = $stmt->get_result();
  $posts = [];

  while($row = $result->fetch_assoc()) {
    $row['media_content'] = json_decode($row['media_content'] ?? '[]', true);
    $row['time_ago'] = time_ago($row['post_date']);
    $posts[] = $row;
  }

  die(json_encode([
    'success' => true,
    'posts' => $posts
  ]));
} catch (Exception $e) {
  die(json_encode([
    'success' => false,
    'error' => $e->getMessage(),
    'code' => $e->getCode()
  ]));
}


function time_ago($dateTime) {
  $now = new DateTime;
  $ago = new DateTime($dateTime);
  $diff = $now->diff($ago);

  if($diff->days > 7) {
    return $ago->format('M j, Y');
  } elseif ($diff->days > 0) {
    return $diff->days . ' days ago';
  } elseif ($diff->h > 0) {
    return $diff->h . ' hours ago';
  } else {
    return 'Just now';
  }
}

?>