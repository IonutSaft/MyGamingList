<?php
session_start();

$connect = mysqli_connect("localhost", "root", "", "mygamelist") or die("Connection Failed");
mysqli_select_db($connect, "mygamelist") or die("Database Selection Failed");

function test_input($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}

$errors = [];

if($_SERVER["REQUEST_METHOD"] == "POST") {
  $username = test_input($_POST["username"]);
  $email = test_input($_POST["email"]);
  $id = $_SESSION["user_id"];

  if($username == $_SESSION["username"] && $email == $_SESSION["email_address"]) {
    $_SESSION["saved"] = "No changes have been made";
    header("Location: ../settingspage.php");
    exit();
  }

  if(empty($username)) {
    $errors['username'] = "Username is invalid";
  } elseif (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
    $errors['username'] = "Username must be 3-20 characters (letters, numbers, underscores)";
  } else if($username != $_SESSION["username"]) {
    $query = mysqli_query($connect, "select * from user where username='$username'");
    if(mysqli_num_rows($query) > 0) {
      $errors['username'] = "Username already taken";
    }
  }

  if(empty($email)) {
    $errors['email'] = "Email is required";
  } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = "Invalid email format";
  } else if ($email != $_SESSION["email_address"]) {
    $query = mysqli_query($connect, "select * from user where email_address='$email'");
    if(mysqli_num_rows($query) > 0) {
      $errors['email'] = "Email address already registered!";
    }
  }
    
  if(empty($errors)) {
    $query1 = mysqli_query($connect, "UPDATE user SET username='$username' WHERE user_id='$id'");
    $query2 = mysqli_query($connect, "UPDATE user SET email_address='$email' WHERE user_id='$id'");
    if($query || $query2) {
      $_SESSION["username"] = $username;
      $_SESSION["email_address"] = $email;
      $_SESSION["saved"] = "Your changes have been saved";
      header("Location: ../settingspage.php");
      exit();
    } else {
      $errors['database'] = "Update Failed. Please try again.";
    }

  }

  if(!empty($errors)) {
    $_SESSION['errors'] = $errors;
    header("Location: ../settingspage.php");
    exit();
  }
}

mysqli_close($connect);
?>