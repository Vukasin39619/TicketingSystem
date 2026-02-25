<!DOCTYPE html>
<html lang="sr">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Ticketing Sistem</title>
  <link rel="stylesheet" href="../CSS/footer.css">
<link rel="stylesheet" href="../CSS/navbar.css">

<style>
.group-container {
  display: flex;
  flex-wrap: wrap;
  gap: 0.2rem;
  margin-top: 2rem;
}
.group-card {
  background: #1E1E1E;
  border: 2px solid #2978B5;
  border-radius: 16px;
  padding: 2rem 2.5rem;
  width: 240px;
  text-align: center;
  box-shadow: 0 2px 10px rgba(0,0,0,0.12);
  cursor: pointer;
  transition: border 0.2s, box-shadow 0.2s;
}
.group-card:hover {
  border-color: #4A90E2;
  box-shadow: 0 4px 16px rgba(41,120,181,0.18);
}
.group-title {
  font-size: 1.25rem;
  color: #4A90E2;
  margin-bottom: 0.7rem;
  font-weight: 500;
}
.group-count {
  font-size: 2.1rem;
  color: #a3bbf7;
  font-weight: bold;
}
.group-section-title {
  font-size: 1.4rem;
  color: #a3bbf7;
  margin: 1.8rem 0 0.6rem 0;
  font-weight: 600;
}
</style>
<?php
include "../baza.php";

// Dohvati grupe i broj otvorenih tiketa za svaku
$sql = "
SELECT
  ag.AssignmentGroupID,
  ag.Name AS GroupName,
  COUNT(c.CaseID) AS OpenCount
FROM assignmentgroup ag
LEFT JOIN `case` c ON c.AssignmentGroup = ag.AssignmentGroupID AND c.Status = 'Open'
GROUP BY ag.AssignmentGroupID, ag.Name
ORDER BY ag.Name
";
$result = mysqli_query($conn, $sql);

$groups = [];
while ($row = mysqli_fetch_assoc($result)) {
    $groups[] = $row;
}
?>
<?php
include "../baza.php";

$sql = "
SELECT
  ag.AssignmentGroupID,
  ag.Name AS GroupName,
  SUM(CASE WHEN c.Status = 'Open'    THEN 1 ELSE 0 END) AS OpenCount,
  SUM(CASE WHEN c.Status = 'Pending' THEN 1 ELSE 0 END) AS PendingCount
FROM assignmentgroup ag
LEFT JOIN `case` c ON c.AssignmentGroup = ag.AssignmentGroupID
GROUP BY ag.AssignmentGroupID, ag.Name
ORDER BY ag.Name
";
$result = mysqli_query($conn, $sql);

$groups = [];
while ($row = mysqli_fetch_assoc($result)) {
    $groups[] = $row;
}
?>

</head>
<body>

  <?php include "../layout/navbarUser.php"; ?>

    
  <!-- Ovde ide glavni sadržaj -->
<div style="max-width:100%;margin:2rem auto;">
  <div class="group-section-title">Open tickets</div>
  <div class="group-container">
    <?php foreach ($groups as $group): ?>
      <a href="otvoreni-tiketi.php?group=<?= $group['AssignmentGroupID']; ?>" style="text-decoration:none;">
        <div class="group-card">
          <div class="group-title"><?= htmlspecialchars($group['GroupName']); ?></div>
          <div class="group-count"><?= $group['OpenCount']; ?></div>
          <div style="font-size:0.98rem;color:#e0e0e0;margin-top:0.8rem;">Open tickets</div>
        </div>
      </a>
    <?php endforeach; ?>
  </div>

  <div class="group-section-title">Pending tickets</div>
  <div class="group-container">
    <?php foreach ($groups as $group): ?>
      <a href="pending-tiketi.php?group=<?= $group['AssignmentGroupID']; ?>" style="text-decoration:none;">
        <div class="group-card">
          <div class="group-title"><?= htmlspecialchars($group['GroupName']); ?></div>
          <div class="group-count"><?= $group['PendingCount']; ?></div>
          <div style="font-size:0.98rem;color:#e0e0e0;margin-top:0.8rem;">Pending tickets</div>
        </div>
      </a>
    <?php endforeach; ?>
  </div>
</div>
  <?php include "../layout/footer.php"; ?>



</body>
</html>
