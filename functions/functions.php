<?php 
// Provera praznih polja za registraciju
function emptyInputSignup($name, $email, $pwd, $pwdRepeat){
    return (empty($name) || empty($email) || empty($pwd) || empty($pwdRepeat));
}

// Provera validne email adrese
function invalidEmail($email){
    return (!filter_var($email, FILTER_VALIDATE_EMAIL));
}

// Provera da li se lozinke podudaraju
function pwdMatch($pwd, $pwdRepeat){
    return ($pwd !== $pwdRepeat);
}

// Provera da li korisnik postoji po email-u
function userExists($conn, $email){
    $sql = "SELECT * FROM `User` WHERE Email = ?;";
    $stmt = mysqli_stmt_init($conn);

    if (!mysqli_stmt_prepare($stmt, $sql)) {
        header("Location: ../signup.php?error=stmtfailed");
        exit();
    }
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $resultData = mysqli_stmt_get_result($stmt);
    if ($row = mysqli_fetch_assoc($resultData)) {
        return $row;
    } else {
        return false;
    }
    mysqli_stmt_close($stmt);
}

// Kreiranje korisnika
function createUser($conn, $name, $email, $pwd){
    $sql = "INSERT INTO `User` (Name, Email, Password, Role) VALUES (?, ?, ?, 'User');";
    $stmt = mysqli_stmt_init($conn);

    if (!mysqli_stmt_prepare($stmt, $sql)) {
        header("Location: ../signup.php?error=stmtfailed");
        exit();
    }
    $hashedPwd = password_hash($pwd, PASSWORD_DEFAULT);

    mysqli_stmt_bind_param($stmt, "sss", $name, $email, $hashedPwd);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    header("Location: ../success.signup.php?success");
    exit();
}

// Provera praznih polja za login
function emptyInputLogin($email, $pwd){
    return (empty($email) || empty($pwd));
}

// Login korisnika
function loginUser($conn, $email, $pwd){
    $userExists = userExists($conn, $email);
    if ($userExists === false) {
        header("Location: ../login.php?error=wronglogin");
        exit();
    }
    $pwdHashed = $userExists["Password"];
    if (!password_verify($pwd, $pwdHashed)) {
        header("Location: ../login.php?error=wronglogin");
        exit();
    } else {
        session_start();
        $_SESSION["userid"] = $userExists["UserID"];
        $_SESSION["username"] = $userExists["Name"];
        $_SESSION["userrole"] = $userExists["Role"];
        header("Location: ../index.php");
        exit();
    }
}

// Edit korisnika
function editUser($conn, $user_id, $user_name, $user_email, $user_password){
    $hashed_password = password_hash($user_password, PASSWORD_DEFAULT);
    $sql_upd = "UPDATE `User` SET Name = ?, Email = ?, Password = ? WHERE UserID = ?";
    $stmt = mysqli_stmt_init($conn);
    if (!mysqli_stmt_prepare($stmt, $sql_upd)) {
        echo "Greška pri ažuriranju!";
        exit();
    }
    mysqli_stmt_bind_param($stmt, "sssi", $user_name, $user_email, $hashed_password, $user_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    header("Location: admin.php");
    exit();
}

// Insert korisnika (za admin panel)
function insertUser($conn, $user_name, $user_email, $user_password, $role = 'User'){
    $sql = "INSERT INTO `User` (Name, Email, Password, Role) VALUES (?, ?, ?, ?);";
    $stmt = mysqli_stmt_init($conn);

    if(!mysqli_stmt_prepare($stmt, $sql)) {
        header("Location: ../insert.php?error=stmtfailed");
        exit();
    }
    $hashed_password = password_hash($user_password, PASSWORD_DEFAULT);

    mysqli_stmt_bind_param($stmt, "ssss", $user_name, $user_email, $hashed_password, $role);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    header("Location: ../admin.php");
    exit();
}


?>