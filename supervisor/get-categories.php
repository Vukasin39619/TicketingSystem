<?php
include "../baza.php";
$sectorID = intval($_GET['sectorID']);
$result = mysqli_query($conn, "SELECT CategoryID, Name FROM category WHERE IDCategorySector = $sectorID ORDER BY Name");
$categories = [];
while ($row = mysqli_fetch_assoc($result)) {
    $categories[] = $row;
}
header('Content-Type: application/json');
echo json_encode($categories);
?>
