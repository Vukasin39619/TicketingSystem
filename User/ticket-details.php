<?php

include "../baza.php";

$caseID = isset($_GET['id']) ? intval($_GET['id']) : 0;

// --- FETCH TICKET ---
$sql = "SELECT c.*, 
        ag.Name AS AssignmentGroupName,
        cs.Name AS CategorySectorName,
        cat.Name AS CategoryName,
        subcat.Name AS SubCategoryName,
        u.Name AS OwnerName, u.UserID AS OwnerID
        FROM `case` c
        LEFT JOIN assignmentgroup ag ON c.AssignmentGroup = ag.AssignmentGroupID
        LEFT JOIN categorysector cs ON c.CategorySector = cs.CategorySectorID
        LEFT JOIN category cat ON c.Category = cat.CategoryID
        LEFT JOIN subcategory subcat ON c.SubCategory = subcat.SubCategoryID
        LEFT JOIN user u ON c.Owner = u.UserID
        WHERE c.CaseID = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $caseID);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$ticket = mysqli_fetch_assoc($result);

// --- EARLY EXIT IF NO TICKET ---
if (!$ticket) {
  echo "<div style='color:#ff5e5e;text-align:center;margin-top:2rem;'>Ticket not found!</div>";
  include "../layout/footer.php";
  exit;
}

// --- FETCH NOTES ---
$notes = [];
$notesql = "SELECT n.Text, n.DateTime, u.Name 
            FROM note n
            LEFT JOIN user u ON n.User = u.UserID
            WHERE n.Case = ?
            ORDER BY n.DateTime DESC";
$notestmt = mysqli_prepare($conn, $notesql);
mysqli_stmt_bind_param($notestmt, "i", $caseID);
mysqli_stmt_execute($notestmt);
$notesres = mysqli_stmt_get_result($notestmt);
while ($row = mysqli_fetch_assoc($notesres)) {
  $notes[] = $row;
}

// --- FETCH ATTACHMENTS ---
$attachments = [];
$attsql = "SELECT FileName, FilePath, ContentType, FileSize 
           FROM attachment WHERE `Case` = ?";

$attstmt = mysqli_prepare($conn, $attsql);
mysqli_stmt_bind_param($attstmt, "i", $caseID);
mysqli_stmt_execute($attstmt);
$attres = mysqli_stmt_get_result($attstmt);
while ($row = mysqli_fetch_assoc($attres)) {
  $attachments[] = $row;
}

// --- STATUS/EDIT AJAX HANDLERS ---
// (implement remaining save logic: status, title, desc, owner/category here as needed)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_POST['set_status'])) {
    $newStatus = $_POST['set_status'];
    $u = mysqli_prepare($conn, "UPDATE `case` SET Status=? WHERE CaseID=?");
    mysqli_stmt_bind_param($u, "si", $newStatus, $caseID);
    mysqli_stmt_execute($u);
    mysqli_stmt_close($u);
    exit("ok");
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Ticket Details</title>
  <link rel="stylesheet" href="../CSS/navbar.css">
  <link rel="stylesheet" href="../CSS/footer.css">
  <style>
    body {
      background: #16161a;
      color: #ececec;
      font-family: "Segoe UI", Arial, sans-serif;
    }

    .details-grid {
      display: grid;
      grid-template-columns: 2fr 1.2fr;
      gap: 2.3rem;
      max-width: 1120px;
      margin: auto;
      padding: 2.7rem 0;
    }

    @media(max-width:1050px) {
      .details-grid {
        grid-template-columns: 1fr;
      }
    }

    /* Title/desc */
    .main-col label {
      font-size: 1.13rem;
      color: #a3bbf7;
      font-weight: 600;
      margin-bottom: 0.5rem;
      display: block;
    }

    .main-col input,
    .main-col textarea {
      width: 100%;
      display: block;
      border-radius: 8px;
      background: #18181d;
      border: 1.5px solid #4A90E2;
      color: #e0e0e0;
      padding: 0.75rem;
      font-size: 1.05rem;
      margin-bottom: 0.8rem;
    }

    .main-col .save-title-btn {
      display: block;
      padding: 8px 2.6rem;
      border-radius: 8px;
      background: #2978B5;
      color: #fff;
      border: none;
      font-size: 1.10rem;
      font-weight: 600;
      margin-bottom: 1.15rem;
      cursor: pointer;
    }

    /* Assignment info */
    .kontejner-status {
      display: flex;
      justify-content: center;
      align-items: center;
    }

    .layout-grid {
      display: grid;
      grid-template-columns: 1.25fr 2fr;
      /* notes levo, details desno */
      gap: 2.3rem;
      max-width: 80%;
      margin: auto;
      padding: 2.7rem 0;
    }

    @media (max-width:1050px) {
      .layout-grid {
        grid-template-columns: 1fr;
        grid-template-rows: auto auto;
      }

      .ticket-notes-col,
      .ticket-details-col {
        min-width: 0;
        width: 100%;
        margin-bottom: 2rem;
      }
    }

    .ticket-notes-col {
      background: #18181d;
      border-radius: 14px;
      padding: 2rem 1.2rem;
      min-width: 0;
      width: 800px;
     box-shadow: 0.2px 0.2px 4px white;
      margin-bottom: 10%;

      
height: 94%;
      

    }
.ticket-notes-col:hover {
      
      box-shadow: 2px 2px 4px white, 2px 2px 4px white inset;
    }
    .ticket-details-col {
      background: #18181d;
      border-radius: 14px;
      padding: 2rem 1.5rem;
      min-width: 0;
      height: 634px;
      max-height:634px;
       box-shadow: 0.2px 0.2px 4px white;
             margin-bottom: 10%;

    }
    .ticket-details-col:hover {
      
      box-shadow: 2px 2px 4px white, 2px 2px 4px white inset;
    }
    .seemore-section{
      margin-bottom: 10%;
    }
    .caseid-badge {
  position: absolute;
  left: 60px;        
  top: 40px;         /* vertikalna pozicija od vrha kontejnera */
  background: transparent;
  border: 2px solid #e74c3c;
  color: #e74c3c;
  border-radius: 7px;
  padding: 8px 30px;
  font-size: 1.31rem;
  font-weight: 600;
  letter-spacing: .05em;
  text-align: center;
  box-shadow: 0 1.5px 7px #e74c3c60 inset;
  z-index: 10;
}
.kontejner-status {
  position: relative; /* da absolute radi u okviru status bloka */
}


  </style>
</head>

<body>
  
  <?php include "../layout/navbarUser.php"; ?>
  <div class="kontejner-status">
    

  <div class="caseid-badge">
    CASE - <?= htmlspecialchars($caseID); ?>
  </div>



    <div class="okvir-status">
      

      <?php include "../layout/status-user.php"; ?>
      <div class="okvir-podaci">

      </div>
    </div>

  </div>

<div class="layout-grid">
  <div class="ticket-notes-col">
    <?php include "../layout/ticket-notes.php"; ?>
  </div>
  <div class="ticket-details-col">
    <?php include "../layout/ticket-info.php"; ?>
    <!-- ovde možeš dodati još detalja, npr. status, owner, sve što ide u detalje -->
  </div>
</div>




  </div>
  <?php include "../layout/footer.php"; ?>

</body>

</html>