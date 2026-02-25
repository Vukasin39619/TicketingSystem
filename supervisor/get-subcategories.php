<?php
include "../baza.php";
$categoryID = intval($_GET['categoryID']);
$result = mysqli_query($conn, "SELECT SubCategoryID, Name FROM subcategory WHERE Category = $categoryID ORDER BY Name");
$subcategories = [];
while ($row = mysqli_fetch_assoc($result)) {
    $subcategories[] = $row;
}
header('Content-Type: application/json');
echo json_encode($subcategories);
?>
