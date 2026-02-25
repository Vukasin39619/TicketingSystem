<?php
session_start();
include "baza.php"; // uključuje konekciju na bazu

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $passwordRepeat = $_POST['passwordRepeat'] ?? '';

    // Provera praznih polja
    if (empty($name) || empty($email) || empty($password) || empty($passwordRepeat)) {
        $errorMsg = "Popuni sva polja!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMsg = "Email nije validan!";
    } elseif ($password !== $passwordRepeat) {
        $errorMsg = "Lozinke se ne poklapaju!";
    } else {
        // Provera da li korisnik već postoji
        $sql = "SELECT * FROM `User` WHERE Email = ?";
        $stmt = mysqli_stmt_init($conn);
        if (!mysqli_stmt_prepare($stmt, $sql)) {
            $errorMsg = "Greška sa bazom!";
        } else {
            mysqli_stmt_bind_param($stmt, "s", $email);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            if ($user = mysqli_fetch_assoc($result)) {
                $errorMsg = "Email već postoji!";
            } else {
                // Dodaj korisnika
                $hashedPwd = password_hash($password, PASSWORD_DEFAULT);
                $role = "User";
                $sqlInsert = "INSERT INTO `User` (Name, Email, Password, Role) VALUES (?, ?, ?, ?)";
                $stmtIns = mysqli_stmt_init($conn);
                if (!mysqli_stmt_prepare($stmtIns, $sqlInsert)) {
                    $errorMsg = "Greška sa bazom pri unosu!";
                } else {
                    mysqli_stmt_bind_param($stmtIns, "ssss", $name, $email, $hashedPwd, $role);
                    mysqli_stmt_execute($stmtIns);
                    mysqli_stmt_close($stmtIns);
                    $successMsg = "Uspešna registracija! Prijavite se.";
                }
            }
            mysqli_stmt_close($stmt);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="sr">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Registracija | Ticketing System</title>
    <link rel="stylesheet" href="CSS/registracija.css">

    <style>
  .register-wrapper:hover{
    box-shadow: 2px 2px 4px white;
  }
</style>
</head>
<body>
  <div class="register-wrapper">
    <h2>Registracija</h2>
    <form method="POST" action="">
      <input type="text" name="name" placeholder="Ime i prezime" required>
      <input type="email" name="email" placeholder="Email adresa" required>
      <input type="password" name="password" placeholder="Lozinka" required>
      <input type="password" name="passwordRepeat" placeholder="Ponovi lozinku" required>
      <button type="submit">Registruj se</button>
    </form>

    <?php if (!empty($errorMsg)) { ?>
      <div class="form-msg"><?php echo $errorMsg; ?></div>
    <?php } elseif (!empty($successMsg)) { ?>
      <div class="form-msg success"><?php echo $successMsg; ?></div>
      <div class="login-link"><a href="login.php">Prijavi se</a></div>
    <?php } else { ?>
      <div class="login-link">Već imaš nalog? <a href="login.php">Prijavi se</a></div>
    <?php } ?>
  </div>
</body>
</html>
