<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="styles/authentication.css" />
    <link
      href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400..900&family=Share+Tech+Mono&display=swap"
      rel="stylesheet"
    />
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"
    />
    <title>MyGameList</title>
  </head>
  <body>
    <div class="animation-container">
      <div class="gamepad">
        <div class="gamepad-top"></div>
        <div class="gamepad-body">
          <div class="d-pad">
            <div class="d-pad-center"></div>
            <div class="d-pad-up"></div>
            <div class="d-pad-right"></div>
            <div class="d-pad-down"></div>
            <div class="d-pad-left"></div>
          </div>
          <div class="buttons">
            <div class="button button-a"></div>
            <div class="button button-b"></div>
            <div class="button button-x"></div>
            <div class="button button-y"></div>
          </div>
          <div class="triggers">
            <div class="trigger trigger-left"></div>
            <div class="trigger trigger-right"></div>
          </div>
        </div>
      </div>
      <div class="pulse-effect"></div>
    </div>

    <div class="login-container">
      <h1>MyGameList</h1>
      <p class="subtitle">Enter the gaming universe</p>

      <form
        class="login-form"
        method="post"
        action="http://localhost/mygamelist/loginpage.php"
      >
        <?php
        if(isset($_SESSION['success'])) {
          echo '<div class="input-group">';
          echo '<p>' .  htmlspecialchars($_SESSION['success']) . '</p>';
          echo '</div>';
          unset($_SESSION['success']);
        }
        ?>
        <button type="submit" class="login-btn">Inapoi la pagina de Login</button>
        <div class="register-link">
          <p>
            Not a member?
            <a href="http://localhost/mygamelist/registerpage.php">Join now</a>
          </p>
        </div>
      </form>
    </div>
  </body>
</html>
