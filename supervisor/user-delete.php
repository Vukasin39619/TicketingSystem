<?php
session_start();
include "../baza.php";

// Validacija - ID korisnika iz URL-a
$userID = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($userID <= 0) {
    echo "<div style='color:#ff5e5e; text-align:center; margin-top:2rem;'>Nevalidan ID korisnika!</div>";
    exit();
}

// Brisanje iz baze
$sql = "DELETE FROM user WHERE UserID = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $userID);
if (mysqli_stmt_execute($stmt)) {
    // Uspeh - povratak na listu
    header("Location: ../supervisor/Users.php?msg=deleted");
    exit();
} else {
    echo "<div style='color:#ff5e5e; text-align:center; margin-top:2rem;'>Greška pri brisanju korisnika!</div>";
}
mysqli_stmt_close($stmt);
?>
