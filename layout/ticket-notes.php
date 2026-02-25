<?php
include "../baza.php";
$caseID = isset($_GET['id']) ? intval($_GET['id']) : 0;

// --- POST handler za unos note-a ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['addNote'], $_POST['noteText'])) {
    if (!isset($_SESSION['userid'])) {
        echo "<div style='color:red;'>Niste prijavljeni!</div>";
    } else {
        $userID = intval($_SESSION['userid']);
        $text = trim($_POST['noteText']);
        $dt = date("Y-m-d H:i:s");
        if ($text !== '') {
            $sql = "INSERT INTO note (`Case`, `User`, Text, DateTime) VALUES (?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "iiss", $caseID, $userID, $text, $dt);
            mysqli_stmt_execute($stmt);
        }
    }
}

// --- POST handler za upload file ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['uploadFile'])) {
    if (!isset($_SESSION['userid'])) {
        echo "<div style='color:red;'>Niste prijavljeni!</div>";
    } else if (isset($_FILES['myfile']) && $_FILES['myfile']['error'] == UPLOAD_ERR_OK) {
        $userID = intval($_SESSION['userid']);
        $file = $_FILES['myfile'];
        $uploadDir = __DIR__ . "/../attachments/";
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
       $orgName = preg_replace('/[^A-Za-z0-9_.\-]/','_', $file['name']);
$filePath = $uploadDir . $orgName;
$i = 1;
$base = pathinfo($orgName, PATHINFO_FILENAME);
$ext = pathinfo($orgName, PATHINFO_EXTENSION);
while (file_exists($filePath)) {
    $orgName = $base . "_" . $i . '.' . $ext;
    $filePath = $uploadDir . $orgName;
    $i++;
}
move_uploaded_file($file['tmp_name'], $filePath);
$project = basename(dirname(__DIR__)); // automatski detektuje folder u htdocs
$pathDB = "/$project/attachments/$orgName";


        $sql = "INSERT INTO attachment (`Case`, FileName, FilePath, ContentType, FileSize, User, UploadDate)
                VALUES (?, ?, ?, ?, ?, ?, NOW())";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "isssii", $caseID, $orgName, $pathDB, $file['type'], $file['size'], $userID);
        mysqli_stmt_execute($stmt);
        echo "<div style='color:#45d095;font-weight:600;padding:6px;'>File uploaded!</div>";
    }
}

// --- Dohvati sve note za prikaz ---
$notes = [];
$notesql = "SELECT n.Text, n.DateTime, u.Role,u.Name FROM note n LEFT JOIN user u ON n.User = u.UserID WHERE n.Case = ? ORDER BY n.DateTime DESC;
";
$notestmt = mysqli_prepare($conn, $notesql);
mysqli_stmt_bind_param($notestmt, "i", $caseID);
mysqli_stmt_execute($notestmt);
$notesres = mysqli_stmt_get_result($notestmt);
while ($row = mysqli_fetch_assoc($notesres)) {
    $notes[] = $row;
}

// --- Dohvati sve ATTACHMENTE sa userom i vremenom ---
$attachments = [];
$attsql = "SELECT a.FileName, a.FilePath, a.ContentType, a.FileSize, a.UploadDate,u.Role, u.Name AS UserName
           FROM attachment a
           LEFT JOIN user u ON a.User = u.UserID
           WHERE a.Case = ?";
$attstmt = mysqli_prepare($conn, $attsql);
mysqli_stmt_bind_param($attstmt, "i", $caseID);
mysqli_stmt_execute($attstmt);
$attres = mysqli_stmt_get_result($attstmt);
while ($row = mysqli_fetch_assoc($attres)) {
    $attachments[] = $row;
}
?>
<style>
.notes-block { margin-top: 1.2rem; max-width: 600px; margin-left: auto; margin-right: auto; }
.notes-label { font-size: 1.15rem; color: #a3bbf7; font-weight: 600; margin-bottom: 7px; margin-left: 3px; }
.note-form { display: flex; gap: 12px; align-items: flex-start; margin-bottom: 16px; }
.note-editor { width: 100%; min-height: 55px; border-radius: 8px; background: #18181d; border: 1.5px solid #4A90E2; color: #e0e0e0; padding: 0.52rem; font-size: 1.08rem; resize: vertical; }
.note-add-btn { background: #45d095; color: #fff; padding: 8px 22px; border: none; border-radius: 8px; font-weight: 600; font-size: 1rem; cursor: pointer; }
.note-list { margin-top: 18px; }
.note-item { background: #22232b; border-radius: 8px; padding: 0.7rem 1rem; margin-bottom: 9px; }
.note-meta { font-size: 0.97rem; color: #9dc1fe; margin-bottom: 0.14rem; }
.custom-file-label { background: #4A90E2; color: #fff; padding: 9px 24px; border-radius: 8px; font-weight: 600; cursor: pointer; border: none; display: inline-block; margin-right: 14px; position: relative; }
.custom-file-label:hover { background: #2978B5; }
.attachment-list { margin-top:14px; }
.attachment-item { background: #22232b; border-radius:7px; padding:0.6rem 1rem; margin-bottom:7px; color:#a3bbf7; font-size:1rem; display:flex; flex-direction:column; gap:4px;}
.attachment-thumb { max-width:400px; max-height:400px; border-radius:7px; box-shadow:0 2px 7px #242435; margin-right:13px;}
.attachment-label { font-weight:600; margin-left:9px;}
.attachment-meta { font-size:0.95rem; color:#8dc1fa; margin-bottom:3px;}

</style>

<div class="notes-block">
 <div class="notes-label">Notes</div>
 <!-- NOTES -->
 <form class="note-form" method="post">
   <textarea class="note-editor" name="noteText" placeholder="Add a note..."></textarea>
   <button class="note-add-btn" type="submit" name="addNote" value="1">Add note</button>
 </form>

 <!-- ATTACHMENTS -->
 <form method="post" enctype="multipart/form-data">
   <label for="fileUpload" class="custom-file-label">
     Upload file
     <input type="file" id="fileUpload" name="myfile" style="display:none;" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.xls,.xlsx,.txt">
   </label>
   <button type="submit" class="note-add-btn" name="uploadFile" value="1">Add file</button>
 </form>

<!-- ATTACHMENTS PRINT -->
<div class="attachment-list">
 <?php foreach($attachments as $att): ?>
   <div class="attachment-item">
    <div class="attachment-meta"><?= htmlspecialchars($att['UserName'] ?? 'N/A'); ?> | <?= htmlspecialchars($att["Role"])  ?> | <?= htmlspecialchars($att['UploadDate']); ?></div>
    <?php
      $isImage = preg_match('/\.(jpg|jpeg|png|gif)$/i', $att['FileName']);
      $isPdf= preg_match('/\.pdf$/i', $att['FileName']);
      $isWord = preg_match('/\.(doc|docx)$/i', $att['FileName']);
    ?>
    <?php if($isImage): ?>
      <img src="<?= htmlspecialchars($att['FilePath']); ?>" alt="<?= htmlspecialchars($att['FileName']); ?>" class="attachment-thumb" title="<?= htmlspecialchars($att['FileName']); ?>">
      <span class="attachment-label"><?= htmlspecialchars($att['FileName']); ?> (<?= round($att['FileSize']/1024); ?> KB)</span>
    <?php elseif($isPdf): ?>
      <a href="<?= htmlspecialchars($att['FilePath']); ?>" download="<?= htmlspecialchars($att['FileName']); ?>"><span style="font-size:1.2rem;">📄</span> PDF: <?= htmlspecialchars($att['FileName']); ?></a>
      <span>(<?= round($att['FileSize']/1024); ?> KB)</span>
    <?php elseif($isWord): ?>
      <a href="<?= htmlspecialchars($att['FilePath']); ?>" download="<?= htmlspecialchars($att['FileName']); ?>"><span style="font-size:1.2rem;">📝</span> Word: <?= htmlspecialchars($att['FileName']); ?></a>
      <span>(<?= round($att['FileSize']/1024); ?> KB)</span>
    <?php else: ?>
      <a href="<?= htmlspecialchars($att['FilePath']); ?>" download="<?= htmlspecialchars($att['FileName']); ?>"><?= htmlspecialchars($att['FileName']); ?></a>
      <span>(<?= round($att['FileSize']/1024); ?> KB, <?= htmlspecialchars($att['ContentType']); ?>)</span>
    <?php endif; ?>
   </div>
 <?php endforeach; ?>
</div>

<!-- NOTES PRINT -->
 <div class="note-list" id="notesList">
   <?php foreach($notes as $note): ?>
     <div class="note-item">
       <div class="note-meta"><?= htmlspecialchars($note['Name']); ?> | <?= htmlspecialchars($note["Role"])  ?> | <?= htmlspecialchars($note['DateTime']); ?></div>
       <div><?= nl2br(htmlspecialchars($note['Text'])); ?></div>
     </div>
   <?php endforeach; ?>
 </div>
</div>
