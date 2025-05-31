<?php
session_start();

function test_input($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}

if($_SERVER["REQUEST_METHOD"] == "POST") {
  $username = test_input($_POST["username"]);
  $password = test_input($_POST["password"]);
  $remember = isset($_POST["remember"]);

  if(empty($username) || empty($password)) {
    $_SESSION["error"] = "Please fill in all fields";
    $_SESSION["old_input"] = ["username" => $username];
    header("Location: ../loginpage.php");
    exit();
  }

  $connect = mysqli_connect("localhost", "root", "") or die("Connection Failed");
  mysqli_select_db($connect, "mygamelist") or die("Database Selection Failed");
  
  $query = mysqli_query($connect, "select * from user where username='$username' OR email_address='$username'");
  $result = mysqli_num_rows($query);

  if($result == 1) {
    $user = mysqli_fetch_assoc($query);

    if(password_verify($password, $user["password"])) {
      $_SESSION["user_id"] = $user["user_id"];
      $_SESSION["username"] = $user["username"];
      $_SESSION["avatar"] = $user["avatar"];

      if($remember) {
        setcookie("remember_user", $user["user_id"], time() + 86400 * 30, "/");
      }

      header("Location: ../homepage.php");
      exit();
    }
  }

  $_SESSION["error"] = "Invalid username or password";
  $_SESSION["old_input"] = ["username" => $username];
  header("Location: ../loginpage.php");
  exit();

  mysqli_close($connect);
}

?>