<?php
include "../baza.php";

// Preuzmi ID korisnika iz URL-a
$userID = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($userID <= 0) {
    echo "<div style='color:#ff5e5e;text-align:center;margin-top:2rem;'>Nevalidan ID korisnika!</div>";
    exit();
}

// Dohvati korisnika
$sql = "SELECT UserID, Name, Email, Role, SectorID FROM user WHERE UserID = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $userID);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

// Dohvati sektore za dropdown
$sectors = [];
$sectorSql = "SELECT CategorySectorID, Name FROM categorysector";
$sectorRes = mysqli_query($conn, $sectorSql);
while ($row = mysqli_fetch_assoc($sectorRes)) {
    $sectors[] = $row;
}

$errorMsg = $successMsg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $role = $_POST['role'] ?? 'User';
    $sector = $_POST['sector'] ?? null;

    if (empty($name) || empty($email)) {
        $errorMsg = "Popuni sva polja!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMsg = "Email nije validan!";
    } else {
        // Izmeni korisnika u bazi
        $sqlUp = "UPDATE user SET Name = ?, Email = ?, Role = ?, SectorID = ? WHERE UserID = ?";
        $stmtUp = mysqli_prepare($conn, $sqlUp);
        mysqli_stmt_bind_param($stmtUp, "ssssi", $name, $email, $role, $sector, $userID);
        if (mysqli_stmt_execute($stmtUp)) {
            $successMsg = "Podaci su uspešno sačuvani!";
            // Osveži podatke
            $user['Name'] = $name;
            $user['Email'] = $email;
            $user['Role'] = $role;
            $user['SectorID'] = $sector;
        } else {
            $errorMsg = "Greška pri čuvanju!";
        }
        mysqli_stmt_close($stmtUp);
    }
}
?>
<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Korisnika</title>
    <link rel="stylesheet" href="../CSS/footer.css">
    <link rel="stylesheet" href="../CSS/navbar.css">
    <style>
        body { background: #121212; color: #e0e0e0; font-family: "Segoe UI", Arial, sans-serif;}
        .container { max-width: 420px; margin: 2.8rem auto; background: #1E1E1E; padding: 2rem 2.3rem; border-radius: 18px; box-shadow: 0 2px 18px rgba(40,120,180,0.10);}
        h1 { color: #4A90E2; margin:0 0 2rem 0; text-align:center; }
        .form-group { margin-bottom: 1.1rem; display: flex; flex-direction: column;}
        label { margin-bottom:0.5rem; color:#a3bbf7; font-size:1.01rem;}
        input, select { border-radius: 7px; padding: 0.7rem; border: 1px solid #4A90E2; background: #181b1d; color: #e0e0e0; font-size: 1rem;}
        button { margin-top: 1.3rem; padding: 0.8rem 1.9rem; border-radius: 9px; background: #2978B5; color: #fff; border:none; font-size:1.08rem; font-weight:600; box-shadow:0 2px 12px rgba(41,120,181,0.10); cursor:pointer;}
        button:hover { background: #4A90E2;}
        .msg {font-size:1.08rem;margin-top:1.1rem;text-align:center;}
        .error {color:#ff5e5e;}
        .success {color:#45d095;}
        .back-link {display:block;color:#4A90E2;font-weight:500;text-align:center;margin-top:1.4rem;}
        .back-link:hover {color:#a3bbf7;}
    </style>
</head>
<body>
    <?php include "../layout/navbarSUP.php"; ?>
    <div class="container">
        <h1>Edit Korisnika</h1>
        <form method="POST">
            <div class="form-group">
                <label for="name">Name </label>
                <input type="text" name="name" id="name" required value="<?= htmlspecialchars($user['Name'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" required value="<?= htmlspecialchars($user['Email'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="role">Role</label>
                <select name="role" id="role" required>
                    <option value="User"<?= ($user['Role'] == 'User') ? ' selected' : ''; ?>>User</option>
                    <option value="Agent"<?= ($user['Role'] == 'Agent') ? ' selected' : ''; ?>>Agent</option>
                    <option value="Supervisor"<?= ($user['Role'] == 'Supervisor') ? ' selected' : ''; ?>>Supervisor</option>
                </select>
            </div>
            <div class="form-group">
                <label for="sector">Sector</label>
                <select name="sector" id="sector">
                    <option value="">-- Choose --</option>
                    <?php foreach ($sectors as $sec): ?>
                        <option value="<?= $sec['CategorySectorID']; ?>"<?= ($user['SectorID'] == $sec['CategorySectorID']) ? ' selected' : ''; ?>>
                            <?= htmlspecialchars($sec['Name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit">Save</button>
        </form>
        <?php if ($errorMsg): ?>
            <div class="msg error"><?= $errorMsg; ?></div>
        <?php elseif ($successMsg): ?>
            <div class="msg success"><?= $successMsg; ?></div>
        <?php endif; ?>
        <a href="../supervisor/Users.php" class="back-link">&#8592; Back</a>
    </div>
    <?php include "../layout/footer.php"; ?>
</body>
</html>
