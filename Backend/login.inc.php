<?php
session_start();
include "../baza.php"; // fajl sa konekcijom ka MySQL bazi

// Provera da li je forma poslata
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // Provera praznih polja
    if (empty($email) || empty($password)) {
        header("Location: login.php?error=emptyfields");
        exit();
    }

    // Pronađi korisnika po email-u
    $sql = "SELECT * FROM `User` WHERE Email = ?";
    $stmt = mysqli_stmt_init($conn);
    if (!mysqli_stmt_prepare($stmt, $sql)) {
        header("Location: login.php?error=stmtfailed");
        exit();
    }
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($user = mysqli_fetch_assoc($result)) {
        // Provera lozinke
        if (password_verify($password, $user['Password'])) {
            // Login uspešan
            $_SESSION['userid'] = $user['UserID'];
            $_SESSION['username'] = $user['Name'];
            $_SESSION['userrole'] = $user['Role'];

            // Redirekcija na stranicu prema ulozi
            if ($user['Role'] === 'Supervisor') {
                header("Location: ../supervisor/index-sup.php");
                exit();
            } elseif ($user['Role'] === 'Agent') {
                header("Location: ../User/index-user.php");
                exit();
            } elseif ($user['Role'] === 'User') {
                header("Location: ../User/index-user.php");
                exit();
            }
        } else {
            header("Location: login.php?error=wrongpwd");
            exit();
        }
    } else {
        header("Location: login.php?error=nouser");
        exit();
    }
    mysqli_stmt_close($stmt);
    mysqli_close($conn);

} else {
    header("Location: login.php");
    exit();
}
?>
