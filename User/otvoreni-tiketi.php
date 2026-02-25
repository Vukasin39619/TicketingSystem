<?php

include "../baza.php";

// Preuzmi ID grupe iz URL-a (npr. otvoreni-tiketi.php?group=3)
$groupID = isset($_GET['group']) ? intval($_GET['group']) : 0;

// Validacija ID-a
if ($groupID <= 0) {
  echo "<div style='color:#ff5e5e; text-align:center; margin-top:2rem;'>Nije prosleđen validan ID grupe!</div>";
  exit();
}

// Dohvati naziv grupe
$groupSql = "SELECT Name FROM assignmentgroup WHERE AssignmentGroupID = ?";
$groupStmt = mysqli_prepare($conn, $groupSql);
mysqli_stmt_bind_param($groupStmt, "i", $groupID);
mysqli_stmt_execute($groupStmt);
$groupResult = mysqli_stmt_get_result($groupStmt);
$groupRow = mysqli_fetch_assoc($groupResult);
$groupName = $groupRow ? $groupRow['Name'] : 'Nepoznata grupa';

// Dohvati sve otvorene tikete za ovu grupu
$sql = "SELECT CaseID, ShortDescription, Description, Status, TimeCreated 
        FROM `case` 
        WHERE AssignmentGroup = ? AND Status = 'Open'
        ORDER BY TimeCreated DESC";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $groupID);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$tickets = [];
while ($row = mysqli_fetch_assoc($result)) {
  $tickets[] = $row;
}

// Dodaj stilove direktno za preglednost (možeš prebaciti u CSS fajl)
?>
<!DOCTYPE html>
<html lang="sr">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Otvoreni Tiketi - <?= htmlspecialchars($groupName); ?></title>
  <link rel="stylesheet" href="../CSS/navbar.css">
  <link rel="stylesheet" href="../CSS/footer.css">
  <style>
    body {
      background: #121212;
      color: #e0e0e0;
      font-family: "Segoe UI", Arial, sans-serif;
    }

    .container {
      max-width: 850px;
      margin: 2rem auto;
      background: #1E1E1E;
      padding: 2rem 2.8rem;
      border-radius: 18px;
      box-shadow: 0 2px 18px rgba(40, 120, 180, 0.10);
    }

    h1 {
      color: #4A90E2;
      margin-top: 0;
      margin-bottom: 2rem;
      text-align: center;
    }

    .tickets-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 1rem;
      margin-bottom: 2rem;
    }

    .tickets-table th,
    .tickets-table td {
      border: 1px solid #333;
      padding: 0.7rem 1.2rem;
      text-align: left;
      background: #181b1d;
    }

    .tickets-table th {
      color: #a3bbf7;
      background: #263248;
    }

    .empty-msg {
      color: #ff5e5e;
      text-align: center;
      margin: 2.5rem 0;
      font-size: 1.2rem;
    }

    @media(max-width: 600px) {
      .container {
        padding: 0.7rem 0.3rem;
      }

      .tickets-table th,
      .tickets-table td {
        font-size: 0.98rem;
        padding: 0.5rem;
      }
    }
  </style>
</head>

<body>
  <?php include "../layout/navbarUser.php"; ?>
  <div class="container">
    <h1>Otvoreni tiketi za grupu: <?= htmlspecialchars($groupName); ?></h1>
    <?php if (count($tickets) == 0): ?>
      <div class="empty-msg">No open tickets</div>
    <?php else: ?>
      <table class="tickets-table">
        <thead>
          <tr>
            <th>Case ID</th>
            <th>Short Description</th>
            <th>Description</th>
            <th>Date</th>
            <th>State</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($tickets as $ticket): ?>
            <tr>
              <td>
                <a href="ticket-details.php?id=<?= $ticket['CaseID']; ?>" style="color:#4A90E2; font-weight:600; text-decoration:none;">
                  <?= $ticket['CaseID']; ?>
                </a>
              </td>

              <td><?= htmlspecialchars($ticket['ShortDescription']); ?></td>
              <td><?= htmlspecialchars($ticket['Description']); ?></td>
              <td><?= htmlspecialchars($ticket['TimeCreated']); ?></td>
              <td><?= htmlspecialchars($ticket['Status']); ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
    <a href="index-user.php" style="color:#4A90E2;font-weight:600;">&#8592; Nazad na assignment grupe</a>
  </div>
  <?php include "../layout/footer.php"; ?>
</body>

</html>