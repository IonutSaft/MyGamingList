<?php
session_start();

function test_input($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}

$user = $_SESSION["user_id"];

if($_SERVER["REQUEST_METHOD"] == "POST") {
  $password = test_input($_POST["password"]);
  $cpassword = test_input($_POST["cpassword"]);

  if(empty($password)) {
    $_SESSION["error"] = "Password is required";
    header("Location: ../resetpass.php");
    exit();
  } elseif(strlen($password) < 8) {
    $_SESSION["error"] = "Password must be at least 8 characters";
    header("Location: ../resetpass.php");
    exit();
  } elseif (!preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password)) {
    $_SESSION["error"] = "Password must contain at least one uppercase letter, one lowercase letter, and one number";
    header("Location: ../resetpass.php");
    exit();
  } elseif($password != $cpassword) {
    $_SESSION["error"] = "Passwords do not match";
    header("Location: ../resetpass.php");
    exit();
  }

  if(!isset($_SESSION["error"])) {
    $connect = mysqli_connect("localhost", "root", "") or die("Connection Failed");
    mysqli_select_db($connect, "mygamelist") or die("Database Selection Failed");
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    $query = mysqli_query($connect, "UPDATE user SET password='$hashed_password' WHERE user_id=$user");

    if($query) {
      $_SESSION["reseted"] = "Password has been reset successfully";
      header("Location: ../waitingpage.php");
      exit();
    }
  } else {
    header("Location: ../chpasspage.php");
    exit();
  }
}
?>