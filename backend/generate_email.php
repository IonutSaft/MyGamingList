<?php

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

require "../PHPMailer/src/Exception.php";
require "../PHPMailer/src/PHPMailer.php";
require "../PHPMailer/src/SMTP.php";

define('MAILHOST', "smtp.gmail.com");
define('USERNAME', "test1.mygamelist@gmail.com");
define('PASSWORD', " esusrwebjlepjuzv");
define('SEND_FROM', "test1.mygamelist@gmail.com");
define('SEND_FROM_NAME', "MyGameList");


function sendMail($email, $subject, $message) {
  $mail = new PHPMailer(true);
  $mail->isSMTP();
  $mail->SMTPAuth = true;
  $mail->Host = MAILHOST;
  $mail->Username = USERNAME;
  $mail->Password = PASSWORD;
  $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
  $mail->Port = 587;
  $mail->setFrom(SEND_FROM, SEND_FROM_NAME);
  $mail->addAddress($email);
  $mail->isHTML(true);
  $mail->Subject = $subject;
  $mail->Body = $message;
  $mail->AltBody = $message;
  if(!$mail->send()) {
    return "fail";
  } else {
    return "success";
  }
}

?>