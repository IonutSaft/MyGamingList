<?php
$mysqli = new mysqli("localhost", "root", "", "mygamelist");
if ($mysqli->connect_errno) {
  echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
}

$mysqli->set_charset("utf8mb4");

?>