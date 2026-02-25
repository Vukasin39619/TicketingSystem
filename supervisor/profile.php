<?php
session_start();
include "../baza.php";

$userID = $_SESSION['userid'] ?? null;
if (!$userID) {
    header("Location: ../login.php");
    exit();
}

// Dohvati podatke korisnika
$sql = "SELECT Name, Email, Role, SectorID FROM user WHERE UserID = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $userID);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

// Sektori za dropdown
$sectors = [];
$sectorSql = "SELECT CategorySectorID, Name FROM categorysector";
$res = mysqli_query($conn, $sectorSql);
while ($row = mysqli_fetch_assoc($res)) {
    $sectors[] = $row;
}

$errorMsg = $successMsg = "";

// Obrada izmene
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $sector = $_POST['sector'] ?? null;
    $password = $_POST['password'] ?? '';
    $passwordRepeat = $_POST['passwordRepeat'] ?? '';

    if (empty($name) || empty($email)) {
        $errorMsg = "Please fill all fields!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMsg = "Invalid email format!";
    } elseif (!empty($password) && $password !== $passwordRepeat) {
        $errorMsg = "Passwords do not match!";
    } else {
        if (!empty($password)) {
            $hashedPwd = password_hash($password, PASSWORD_DEFAULT);
            $sqlUp = "UPDATE user SET Name=?, Email=?, SectorID=?, Password=? WHERE UserID=?";
            $stmtUp = mysqli_prepare($conn, $sqlUp);
            mysqli_stmt_bind_param($stmtUp, "ssisi", $name, $email, $sector, $hashedPwd, $userID);
        } else {
            $sqlUp = "UPDATE user SET Name=?, Email=?, SectorID=? WHERE UserID=?";
            $stmtUp = mysqli_prepare($conn, $sqlUp);
            mysqli_stmt_bind_param($stmtUp, "ssii", $name, $email, $sector, $userID);
        }
        if (mysqli_stmt_execute($stmtUp)) {
            $successMsg = "Profile updated successfully!";
            // Osveži podatke i sesiju
            $user['Name'] = $name;
            $user['Email'] = $email;
            $user['SectorID'] = $sector;
            $_SESSION['username'] = $name;
        } else {
            $errorMsg = "Failed to update!";
        }
        mysqli_stmt_close($stmtUp);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Profile</title>
    <link rel="stylesheet" href="../CSS/footer.css">
    <link rel="stylesheet" href="../CSS/navbar.css">
    <style>
        body { background: #1a1a1f; color: #e0e0e0; font-family: "Segoe UI", Arial, sans-serif; }
        .container { max-width: 440px; margin: 2.7rem auto; background: #181b1d; padding: 2.2rem 2.9rem; border-radius: 16px; box-shadow: 0 2px 18px rgba(40,120,180,0.09);}
        h1 { color: #4A90E2; margin:0 0 1.6rem 0; text-align:center; }
        .profile-box { background:#22252b; border-radius:13px; padding:1.3rem 1.1rem 1.5rem 1.1rem; margin-bottom:2rem; box-shadow:0 2px 8px rgba(60,122,186,0.09);}
        .form-group { margin-bottom: 1rem; display: flex; flex-direction: column;}
        label { margin-bottom:0.45rem; color:#a3bbf7; font-size:1.03rem;}
        input, select { border-radius: 7px; padding: 0.7rem; border: 1px solid #2b81c1; background: #23262b; color: #e0e0e0; font-size: 1rem;}
        button[type=submit] { margin-top: 1.1rem; padding: 0.74rem 1.7rem; border-radius: 9px; background: #2978B5; color: #fff; border:none; font-size:1.02rem; font-weight:600; box-shadow:0 2px 12px rgba(41,120,181,0.10); cursor:pointer;}
        button[type=submit]:hover { background: #4A90E2;}
        .msg {font-size:1.08rem;margin-top:1rem;text-align:center;}
        .error {color:#ff5e5e;}
        .success {color:#45d095;}
    </style>
</head>
<body>
    <?php include "../layout/navbarSUP.php"; ?>
    <div class="container">
        <h1>My Profile</h1>
        <div class="profile-box">
            <form method="POST" autocomplete="off">
                <div class="form-group">
                    <label for="name">Full name</label>
                    <input type="text" name="name" id="name" required value="<?= htmlspecialchars($user['Name'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="email">Email address</label>
                    <input type="email" name="email" id="email" required value="<?= htmlspecialchars($user['Email'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="role">Role</label>
                    <input type="text" id="role" value="<?= htmlspecialchars($user['Role'] ?? ''); ?>" disabled>
                </div>
                <div class="form-group">
                    <label for="sector">Sector</label>
                    <select name="sector" id="sector" required>
                        <option value="">-- Select sector --</option>
                        <?php foreach ($sectors as $sec): ?>
                            <option value="<?= $sec['CategorySectorID']; ?>"<?= ($user['SectorID'] == $sec['CategorySectorID']) ? ' selected' : ''; ?>>
                                <?= htmlspecialchars($sec['Name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="password">New password (optional)</label>
                    <input type="password" name="password" id="password" autocomplete="new-password">
                </div>
                <div class="form-group">
                    <label for="passwordRepeat">Repeat new password</label>
                    <input type="password" name="passwordRepeat" id="passwordRepeat" autocomplete="new-password">
                </div>
                <button type="submit">Update profile</button>
            </form>
            <?php if ($errorMsg): ?>
                <div class="msg error"><?= $errorMsg; ?></div>
            <?php elseif ($successMsg): ?>
                <div class="msg success"><?= $successMsg; ?></div>
            <?php endif; ?>
        </div>
        <a href="index-sup.php" class="back-link">&#8592; Back to dashboard</a>
    </div>
    <?php include "../layout/footer.php"; ?>
</body>
</html>
