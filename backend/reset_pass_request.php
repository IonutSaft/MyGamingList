<?php
session_start();

require("generate_email.php");

function test_input($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}

$connect = mysqli_connect("localhost", "root", "") or die("Connection Failed");
mysqli_select_db($connect, "mygamelist") or die("Database Selection Failed");

if($_SERVER["REQUEST_METHOD"] == "POST") {
  $email = test_input($_POST["email"]);

  if(empty($email)) {
    $_SESSION["error"] = "Please fill in the field with your email address";
    header("Location: ../chpasspage.php");
    exit();
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION["error"] = "Invalid email format";
    header("Location: ../chpasspage.php");
    exit();
  } else {
    $query = mysqli_query($connect, "select * from user where email_address='$email'");
    if(mysqli_num_rows($query) == 0) {
      $_SESSION["error"] = "Email not registered";
      header("Location: ../chpasspage.php");
      exit();
    } else {
      $user = mysqli_fetch_assoc($query);

      $_SESSION["user_id"] = $user["user_id"]; 

      $token = bin2hex(random_bytes(50));
      $expires = date("Y-m-d H:i:s", strtotime("+1 hour"));

      $url = "http://localhost/mygamelist/resetpass.php?token=$token";

      $query = mysqli_query($connect, "INSERT INTO token(user_id, token, expires_at) VALUES ($user[user_id], '$token', '$expires')");

      if($query) {

        $subject = "Password Reset Request";
        $message = "<p>We received a password reset request. If you didn't make this request, you can ignore this email. Otherwise, you can reset your password by clicking ";
        $message .= "<a href='$url'>here</a></p>";

        if(sendMail($email, $subject, $message) == "success") {
          $_SESSION["success"] = "Password reset link has been sent to your email address";
          header("Location: ../waitingpage.php");
          exit();
        } else {
          $_SESSION["error"] = "Failed to send password reset link";
          header("Location: ../chpasspage.php");
          exit();
        }
      }
    }
  }


}
?>
