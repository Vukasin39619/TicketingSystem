<!DOCTYPE html>
<html lang="sr">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Login | Ticketing System</title>
<link rel="stylesheet" href="CSS/login.css">

<style>
  .login-wrapper:hover{
    box-shadow: 2px 2px 4px white;
  }
</style>

</head>
<body>
  <div class="login-wrapper">
    <div class="logo">
      <!-- Unesi svoju putanju do logo.png -->
      <img src="logo.png" alt="Ticketing System Logo">
      <span>Ticketing System</span>
    </div>
    <h2>Welcome!</h2>
    <form action="Backend/login.inc.php" method="post">
      <input type="email" name="email" placeholder="Email adresa" required>
      <input type="password" name="password" placeholder="Lozinka" required>
      <?php
    if (isset($_GET['error'])) {
      if ($_GET['error'] == 'emptyfields') echo '<div style="color:#ff5e5e;">Popunite sva polja!</div>';
      elseif ($_GET['error'] == 'stmtfailed') echo '<div style="color:#ff5e5e;">Greška sa bazom!</div>';
      elseif ($_GET['error'] == 'wrongpwd') echo '<div style="color:#ff5e5e;">Pogrešna lozinka!</div>';
      elseif ($_GET['error'] == 'nouser') echo '<div style="color:#ff5e5e;">Korisnik ne postoji!</div>';
    }
  ?>
      <button type="submit">Log in</button>
    </form>
    <div class="login-links">
      <a href="Registracija.php">Register</a> | <a href="#">Forgot password?</a>
    </div>
  </div>
   
</body>
</html>
