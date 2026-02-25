<?php

include "../baza.php";
if(!isset($_SESSION)) { session_start(); }

if (!isset($_SESSION['userid'])) {
    die("Not logged in");
}
$userID = intval($_SESSION['userid']);

$sql = "SELECT CaseID, ShortDescription, Status, CategorySector, Category, TimeCreated
        FROM `case`
        WHERE Owner = ?
          AND Status IN ('Open','Pending','In Progress')
        ORDER BY TimeCreated DESC";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $userID);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$tickets = [];
while ($row = mysqli_fetch_assoc($res)) {
    $tickets[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My active tickets</title>
    <link rel="stylesheet" href="../CSS/footer.css">
    <link rel="stylesheet" href="../CSS/navbar.css">
    <style>
      body { background:#16161a;color:#e0e0e0;font-family:"Segoe UI",Arial,sans-serif;}
      .wrapper { max-width:1000px;margin:2.5rem auto;padding:1.5rem 2rem;background:#18181d;border-radius:14px;box-shadow:0 2px 18px rgba(0,0,0,0.35);}
      h1 { margin-top:0;margin-bottom:1.5rem;font-size:1.6rem;color:#a3bbf7;}
      table { width:100%;border-collapse:collapse;margin-top:0.5rem;}
      th,td { padding:0.6rem 0.7rem;border-bottom:1px solid #2a2a32;font-size:0.98rem;}
      th { text-align:left;color:#7fc1fc;font-weight:600;background:#202028;}
      tr:hover td { background:#20222b;}
      .status-pill { display:inline-block;padding:0.18rem 0.75rem;border-radius:999px;font-size:0.83rem;font-weight:600;}
      .st-open { background:#2978B5;color:#fff;}
      .st-pending { background:#E69333;color:#fff;}
      .st-progress { background:#afcc28;color:#1b1c1f;}
      a.case-link { color:#e0e0e0;text-decoration:none;}
      a.case-link:hover { color:#4A90E2;text-decoration:underline;}
      .empty-msg { margin-top:0.7rem;font-size:1rem;color:#bbbbc5;}
    </style>
</head>
<body>
<?php include "../layout/navbarUser.php"; ?>

<div class="wrapper">
  <h1>My active tickets (Open / Pending / In Progress)</h1>

  <?php if (empty($tickets)): ?>
    <div class="empty-msg">You currently have no active tickets.</div>
  <?php else: ?>
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Title</th>
          <th>Status</th>
          <th>Created</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($tickets as $t): ?>
          <tr>
            <td><a class="case-link" href="ticket-details.php?id=<?= $t['CaseID']; ?>">#<?= $t['CaseID']; ?></a></td>
            <td><?= htmlspecialchars($t['ShortDescription']); ?></td>
            <td>
              <?php
                $cls = $t['Status']=='Open' ? 'st-open' : ($t['Status']=='Pending' ? 'st-pending' : 'st-progress');
              ?>
              <span class="status-pill <?= $cls; ?>"><?= htmlspecialchars($t['Status']); ?></span>
            </td>
            <td><?= htmlspecialchars($t['TimeCreated']); ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<?php include "../layout/footer.php"; ?>
</body>
</html>
