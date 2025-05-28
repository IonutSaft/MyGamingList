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

      <form class="login-form" method="post" action=http://localhost/mygamelist/backend/registration.php>
        <?php
        if(isset($_SESSION['errors'])) {
          echo '<div class="error-message">';
          foreach ($_SESSION['errors'] as $error) {
            echo '<p>' .  htmlspecialchars($error) . '</p>';
          }
          echo '</div>';
          unset($_SESSION['errors']);
        }  
        $old_input = $_SESSION['old_input'] ?? [];
        unset($_SESSION['old_input']);
        ?>
        <div class="input-group">
          <label for="username">Username</label>
          <input
            type="text"
            id="username"
            placeholder="Enter your username"
            name="username"
            value="<?php echo htmlspecialchars($old_input['username'] ?? ''); ?>"
          />
        </div>

        <div class="input-group">
          <label for="username">Email</label>
          <input
            type="email"
            id="email"
            placeholder="Enter your email"
            name="email"
            value="<?php echo htmlspecialchars($old_input['email'] ?? ''); ?>"
          />
        </div>

        <div class="input-group">
          <label for="password">Password</label>
          <div class="password-input">
            <input
              type="password"
              id="password"
              placeholder="Enter your password"
              name="password"
            />
            <i
              class="fas fa-eye toggle-password"
              onclick="togglePassword(this)"
            ></i>
          </div>
        </div>

        <div class="input-group">
          <label for="conf-password">Confirm Password</label>
          <div class="password-input">
            <input
              type="password"
              id="conf-password"
              placeholder="Confirm password"
              name="cpassword"
            />
            <i
              class="fas fa-eye toggle-password"
              onclick="togglePassword(this)"
            ></i>
          </div>
        </div>

        <div class="input-group">
          <label>Date of Birth</label>
          <div class="birthday-selectors">
            <select class="dob-select" id="birth-day" required name="birth-day">
              <option value="" disabled <?= empty($selected_day) ? 'selected' : '' ?>>Day</option>
              <?php
              $selected_day = $old_input['birth-day'] ?? '';
              for ($i = 1; $i <= 31; $i++) {
                  $selected = ($i == $selected_day) ? 'selected' : '';
                  echo "<option value='$i' $selected>$i</option>";
              }
              ?>
            </select>

            <select
              class="dob-select"
              id="birth-month"
              required
              name="birth-month"
            >
              <option value="" disabled <?= empty($selected_month) ? 'selected' : '' ?>>Month</option>
              <?php
              $selected_month = $old_input['birth-month'] ?? '';
              $months = [
                  1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
                  5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
                  9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
              ];
              
              foreach ($months as $num => $name) {
                  $selected = ($num == $selected_month) ? 'selected' : '';
                  echo "<option value='$num' $selected>$name</option>";
              }
              ?>
            </select>

            <select
              class="dob-select"
              id="birth-year"
              required
              name="birth-year"
            >
              <option value="" disabled <?= empty($selected_year) ? 'selected' : '' ?>>Year</option>
              <?php
              $selected_year = $old_input['birth-year'] ?? '';
              $current_year = date("Y");
              
              for ($i = $current_year; $i >= 1900; $i--) {
                  $selected = ($i == $selected_year) ? 'selected' : '';
                  echo "<option value='$i' $selected>$i</option>";
              }
              ?>
            </select>
          </div>
        </div>

        <div class="options">
          <div class="remember-me">
            <input type="checkbox" id="terms" name="terms" />
            <label for="remember"
              >Accept
              <a href="" class="terms-and-conditions"
                >terms and conditions</a
              ></label
            >
          </div>
        </div>

        <button type="submit" class="login-btn">Register</button>

        <div class="register-link">
          <p>Already a member? <a href="loginpage.html">Login now</a></p>
        </div>
      </form>
    </div>
    <script src="scripts/togglePasswordScript.js"></script>
  </body>
</html>
