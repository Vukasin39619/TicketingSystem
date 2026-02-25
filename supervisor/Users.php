<?php
include "../baza.php";
include "../Backend/auth.php";
requireRole(['Supervisor']);
// Dohvati sve korisnike iz baze
$sql = "SELECT UserID, Name, Email, Role, SectorID FROM user ORDER BY UserID";
$result = mysqli_query($conn, $sql);

$users = [];
while ($row = mysqli_fetch_assoc($result)) {
    $users[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Korisnici - Administracija</title>
    <link rel="stylesheet" href="../CSS/footer.css">
    <link rel="stylesheet" href="../CSS/navbar.css">
    <style>
        body { background: #121212; color: #e0e0e0; font-family: "Segoe UI", Arial, sans-serif;}
        .container { max-width: 870px; margin: 2rem auto; background: #1E1E1E; padding: 2rem 2.7rem; border-radius: 18px; box-shadow: 0 2px 18px rgba(40,120,180,0.10); }
        h1 { color: #4A90E2; margin-top:0; margin-bottom:2.1rem; text-align:center; }
        .users-table { width: 100%; border-collapse: collapse; margin-bottom: 2rem; }
        .users-table th, .users-table td { border: 1px solid #333; padding: 0.7rem 1.2rem; text-align: left; background: #181b1d;}
        .users-table th { color: #a3bbf7; background: #263248; }
        .action-btn { padding: 5px 13px; border: none; border-radius: 5px; background: #2978B5; color: #fff; font-size: 1rem; font-weight: 500; cursor: pointer; transition: background 0.2s;}
        .action-btn:hover { background: #4A90E2; }
        .new-btn { margin-bottom: 1.3rem; padding: 9px 30px; border-radius: 7px; background: #4A90E2; color: #fff; font-size: 1.03rem; font-weight: 500; display:inline-block; text-decoration: none;}
        .new-btn:hover { background: #2978B5; }
    </style>
</head>
<body>
    <?php include "../layout/navbarSUP.php"; ?>
    <div class="container">
        <h1>Pregled korisnika</h1>
        <a href="user-new.php" class="new-btn">+ Novi korisnik</a>
        <table class="users-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Ime i prezime</th>
                    <th>Email</th>
                    <th>Rola</th>
                    <th>Sektor</th>
                    <th>Akcija</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($users as $user): ?>
                <tr>
                    <td><?= $user['UserID']; ?></td>
                    <td><?= htmlspecialchars($user['Name']); ?></td>
                    <td><?= htmlspecialchars($user['Email']); ?></td>
                    <td><?= htmlspecialchars($user['Role']); ?></td>
                    <td><?= htmlspecialchars($user['SectorID'] ?? "-"); ?></td>
                    <td>
                        <a href="user-edit.php?id=<?= $user['UserID']; ?>" class="action-btn">Edit</a>
                        <a href="user-delete.php?id=<?= $user['UserID']; ?>" class="action-btn" onclick="return confirm('Da li ste sigurni da želite da obrišete korisnika?');">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php include "../layout/footer.php"; ?>
</body>
</html>