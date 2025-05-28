<?php
session_start();

function test_input($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}

$connect = mysqli_connect("localhost", "root", "") or die("Connection Failed");
mysqli_select_db($connect, "mygamelist") or die("Database Selection Failed");

$errors = [];

$username = $password = $email = $birth_date = '';

if($_SERVER["REQUEST_METHOD"] == "POST") {
  $username= test_input($_POST["username"]);
  $email = test_input($_POST["email"]);
  $password = test_input($_POST["password"]);
  $cpassword = test_input($_POST["cpassword"]);

  if(empty($username)) {
    $errors['username'] = "Username is required";
  } elseif(!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
    $errors['username'] = "Username must be 3-20 characters (letters, numbers, underscores)";
  } else {
    $query = mysqli_query($connect, "select * from user where username='$username'");
    if(mysqli_num_rows($query) > 0) {
      $errors['username'] = "Username already taken";
    }
  }

  if(empty($email)) {
    $errors['email'] = "Email is required";
  } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = "Invalid email format";
  } else {
    $query = mysqli_query($connect, "select * from user where email_address='$email'");
    if(mysqli_num_rows($query) > 0) {
      $errors['email'] = "Email already registered";
    }
  }

  if(empty($password)) {
    $errors['password'] = "Password is required";
  } elseif(strlen($password) < 8) {
    $errors['password'] = "Password must be at least 8 characters";
  } elseif (!preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password)) {
    $errors['password'] = "Password must contain at least one uppercase letter, one lowercase letter, and one number";
  } elseif($password != $cpassword) {
    $errors['cpassword'] = "Passwords do not match";
  }

  $birth_day = (int)$_POST["birth-day"];
  $birth_month = (int)$_POST["birth-month"];
  $birth_year = (int)$_POST["birth-year"];

  if(!checkdate($birth_month, $birth_day, $birth_year)) {
    $errors['birth-date'] = "Invalid date of birth";
  } else {
    $birth_date = "$birth_year-$birth_month-$birth_day";
    $min_age_date = date('Y-m-d', strtotime('-13 years'));
    if($birth_date > $min_age_date) {
      $errors['birth-date'] = "You must be at least 13 years old to register";
    }
  }

  if(!isset($_POST["terms"])) {
    $errors['terms'] = "You must agree to the terms and conditions";
  }

  if(empty($errors)) {
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $avatar = "assets/default/default-avatar.png";
    $cover = "assets/default/default-cover.png";  
    $join_date = date('Y-m-d');

    $query = mysqli_query($connect, "INSERT INTO user (username, password, email_address, birth_date, avatar, cover, join_date) VALUES ('$username','$hashed_password', '$email', '$birth_date', '$avatar', '$cover', '$join_date')");

    if($query) {
      $_SESSION['registration_success'] = "Account created successfully! Please log in.";
      header("Location: ../loginpage.php");
      exit();
    } else{
      $errors['database'] = "Registration failed. Please try again.";
    }
  }

  if(!empty($errors)) {
    $_SESSION['errors'] = $errors;
    $_SESSION['old_input'] = [
      'username' => $username,
      'email' => $email,
      'birth-day' => $birth_day,
      'birth-month' => $birth_month,
      'birth-year' => $birth_year
    ];
    header("Location: ../registerpage.php");
    exit();
  }
}

mysqli_close($connect);
?>