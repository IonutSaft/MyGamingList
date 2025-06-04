<?php

  $conn = mysqli_connect("localhost", "root", "", "mygamelist");
  

  if(!$conn) {
    die("Connection failed: " . mysqli_connect_error());
  }

?>