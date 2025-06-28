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
    <title>MyGameWorld</title>
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
      <h1>Reset Password</h1>

      <form
        class="login-form"
        method="POST"
        action="https://localhost/mygamelist/backend/reset_pass_request.php"
      >
        <?php
        if(isset($_SESSION['error'])) {
          echo '<div class="error-message">';
          echo '<p>' .  htmlspecialchars($_SESSION['error']) . '</p>';
          echo '</div>';
          unset($_SESSION['error']);
        }
        ?>
        <div class="input-group">
          <p class="subtitle"></p>
          <label for="email" r>Enter your email address</label>
          <input
            type="email"
            id="email"
            name="email"
            placeholder="example@email.com"
            required
          />
        </div>
        <button type="submit" class="login-btn">Send Link</button>

        <div class="register-link">
          <p>Remember your password? <a href="loginpage.php">Login now</a></p>
        </div>
      </form>
    </div>
  </body>
</html>
