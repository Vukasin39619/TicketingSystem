<?php
include "../baza.php";
$sectorID = isset($_GET['sectorID']) ? intval($_GET['sectorID']) : 0;
$result = mysqli_query($conn, "SELECT UserID, Name FROM user WHERE Role='Agent' AND SectorID=$sectorID");
$agents = [];
while ($r = mysqli_fetch_assoc($result)) $agents[] = $r;
header('Content-Type: application/json');
echo json_encode($agents);
?>