<?php
session_start();

require __DIR__ . '/db_connect.php';

if(!isset($_SESSION['loggedin']) || $_SERVER["REQUEST_METHOD"] !== "POST") {
  http_response_code(403);
  die("Access denied.");
}

unset($_SESSION["errors"]);

$errors = [];
$content = trim($_POST["content"] ?? '');

if(empty($content)) {
  $errors['content'] = "Content is required";
}

$media_paths = [];
if(!empty($_FILES['media'])) {
  $upload_dir = __DIR__ . '/../uploads/';
  if(!is_dir($upload_dir)) {
    mkdir($upload_dir);
  }

  foreach($_FILES['media']['tmp_name'] as $key => $tmp_name) {
    $file_name = $_FILES['media']['name'][$key];
    $file_size = $_FILES['media']['size'][$key];
    $file_type = $_FILES['media']['type'][$key];
    $file_error = $_FILES['media']['error'][$key];

    if($file_error !== UPLOAD_ERR_OK) {
      continue;
    }


    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'video/mp4'];
    if(!in_array($file_type, $allowed_types)) {
      $errors["media-$key"] = "Invalid file type.";
      continue;
    }

    if($file_size > 5 * 1024 * 1024) {
      $errors["media-$key"] = "File size too large.";
      continue;
    }

    $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);
    $new_name = uniqid('media_', true) . '.' . $file_ext;
    $destination = $upload_dir . $new_name;

    if(move_uploaded_file($tmp_name, $destination)) {
      $media_paths[] = 'uploads/' . $new_name;
    }
  }
} 

if(empty($errors)) {
  try {
    $stmt = $conn->prepare("
      INSERT INTO `post` (user_id, text_content, media_content, post_date)
      VALUES(?, ?, ?, NOW())");

    $media_json = !empty($media_paths) ? json_encode($media_paths) : NULL;
    $stmt->bind_param("iss", $_SESSION["user_id"], $content, $media_json);
    $stmt->execute();
    $stmt->close();

    $_SESSION["saved"] = "Post created successfully.";

    header("Location: ../homepage.php");
    exit();
  } catch (Exception $e) {
    die("Database error: " . $e->getMessage());
  }
}

if(!empty($errors)) {
  $_SESSION['errors'] = $errors;
  header("Location: ../homepage.php");
  exit();
}

?>