<?php
session_start();

if(!isset($_SESSION['loggedin'])) {
  header("Location: loginpage.php");
  exit();
}

$connect = mysqli_connect("localhost", "root", "") or die("Connection Failed");
mysqli_select_db($connect, "mygamelist") or die("Database Selection Failed");

$id = $_SESSION['user_id'];

$query = mysqli_query($connect, "DELETE FROM user WHERE user_id='$id'");
if($query) {
  session_destroy();
  header("Location: ../loginpage.php");
  exit();
}

?>