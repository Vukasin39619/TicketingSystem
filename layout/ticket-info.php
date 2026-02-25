<?php
if (session_status() == PHP_SESSION_NONE) { session_start(); }
include "../baza.php";
$caseID = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch ticket podaci
$sqlTicket = "SELECT * FROM `case` WHERE CaseID = ?";
$stmt = mysqli_prepare($conn, $sqlTicket);
mysqli_stmt_bind_param($stmt, "i", $caseID);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$ticket = mysqli_fetch_assoc($res);

// Sectors
$sectors = [];
$resSec = mysqli_query($conn, "SELECT CategorySectorID, Name FROM categorysector ORDER BY Name");
while ($r = mysqli_fetch_assoc($resSec)) $sectors[] = $r;

// Categories
$categories = [];
if ($ticket) {
  $cid = intval($ticket['CategorySector']);
  $resCat = mysqli_query($conn, "SELECT CategoryID, Name FROM category WHERE IDCategorySector = $cid ORDER BY Name");
  while ($r = mysqli_fetch_assoc($resCat)) $categories[] = $r;
}

// Subcategories
$subcategories = [];
if ($ticket) {
  $cid = intval($ticket['Category']);
  $resSub = mysqli_query($conn, "SELECT SubCategoryID, Name FROM subcategory WHERE Category = $cid ORDER BY Name");
  while ($r = mysqli_fetch_assoc($resSub)) $subcategories[] = $r;
}

// Owners
$owners = [];
$resOwn = mysqli_query($conn, "SELECT UserID, Name FROM user ORDER BY Name");
while ($r = mysqli_fetch_assoc($resOwn)) $owners[] = $r;

// Assignment groups
$groups = [];
$resGrp = mysqli_query($conn, "SELECT AssignmentGroupID, Name FROM assignmentgroup ORDER BY Name");
while ($g = mysqli_fetch_assoc($resGrp)) $groups[] = $g;

// Agents sektora
$agents = [];
$currentSector = $ticket['CategorySector'];
$resAgents = mysqli_query($conn, "SELECT UserID, Name FROM user WHERE Role='Agent' AND SectorID=$currentSector");
while ($a = mysqli_fetch_assoc($resAgents)) $agents[] = $a;


// -- Update logika --
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['title'])) {
    $newTitle = $_POST['title'];
    $newDesc = $_POST['desc'];
    $catSector = $_POST['categorySector'] ?: null;
    $category = $_POST['category'] ?: null;
    $subcategory = $_POST['subcategory'] ?: null;
    $owner = $_POST['owner'] ?: null;
    $assignmentGroup = $_POST['assignmentGroup'] ?: null;
    $assignedOn = $_POST['assignedOn'] ?: null; // userID agenta

    $usql = "UPDATE `case`
        SET ShortDescription=?, Description=?, CategorySector=?, Category=?, SubCategory=?, Owner=?, AssignmentGroup=?, AssignedOn=?
        WHERE CaseID=?";
    $ustmt = mysqli_prepare($conn, $usql);
    mysqli_stmt_bind_param($ustmt, "ssiiiiiii", $newTitle, $newDesc, $catSector, $category, $subcategory, $owner, $assignmentGroup, $assignedOn, $caseID);
    mysqli_stmt_execute($ustmt);
    mysqli_stmt_close($ustmt);
    exit("ok");
}

$assignmentGroupID = $ticket['AssignmentGroup'];
$assignedOn = $ticket['AssignedOn'];
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
<style>
.kontejner { max-width: 740px; margin: 2.3rem auto; padding: 2rem; background: #18181d; border-radius: 14px; position: relative; }
.seemore-btn { display: block; margin: 0.7rem 0 1.1rem 0; background: #45d095; color: #fff; font-weight: 600; font-size: 1.08rem; padding: 0.72rem 2.3rem; border: none; border-radius: 8px; cursor: pointer; transition: background 0.2s;}
.seemore-btn:hover { background: #2fb17f; }
.edit-icon-btn { position: absolute; top: 20px; right: 22px; background: transparent; border: none; color: #4A90E2; font-size: 1.85rem; cursor: pointer; transition: color 0.2s; }
.edit-icon-btn:hover { color:#2978B5; }
.save-edit-btn { display: none; margin-top: 1.2rem; padding: 0.8rem 2.3rem; border-radius: 8px; background: #2978B5; color: #fff; border: none; font-size: 1.10rem; font-weight: 600; cursor: pointer; margin-bottom: 40px;}
input[readonly], textarea[readonly], select:disabled { background: #23262b; color: #888; border: 2px solid #414a62; cursor: not-allowed; }
label { font-size: 1.13rem; color: #a3bbf7; font-weight: 600; margin-bottom: 0.5rem; display: block; }
#title { width: 50%; font-size: 1.45rem; font-weight: 600; padding: 1.1rem 1rem; margin-bottom: 1.1rem; border-radius: 9px; border: 2px solid #4A90E2; background: #23262b; color: #e0e0e0;}
#desc { width: 100%; min-height: 115px; font-size: 1.18rem; font-weight: 500; padding: 1rem 1rem; margin-bottom: 1.2rem; border-radius: 9px; border: 2px solid #2978B5; background: #23262b; color: #e0e0e0; resize: vertical;}
.seemore-section { display: none; margin-top: 1.1rem; }
select { width: 95%; padding: 0.7rem; border-radius:8px; border: 1.5px solid #4A90E2; color: #e0e0e0; background: #181b1d; font-size:1.04rem; margin-bottom:0.85rem; }
.assign-label { color: #7fc1fc; font-weight: 600; font-size: 1.13rem; }
.assign-value { color: #e0e0e0; margin-left: 6px; font-size: 1.13rem; }
</style>

<div class="kontejner">
  <button class="edit-icon-btn" id="editToggleBtn" title="Edit"><i class="fas fa-pencil-alt"></i></button>
  <label for="title">Title</label>
  <input type="text" id="title" value="<?= htmlspecialchars($ticket['ShortDescription']); ?>" readonly>
  <label for="desc">Description</label>
  <textarea id="desc" readonly><?= htmlspecialchars($ticket['Description']); ?></textarea>
  <button class="seemore-btn" id="seeMoreBtn">See more</button>
  <div class="seemore-section" id="seeMoreSection">
    <label for="categorySector">Category sector</label>
    <select id="categorySector" disabled>
      <option value="">Select sector</option>
      <?php foreach ($sectors as $sec): ?>
        <option value="<?= $sec['CategorySectorID']; ?>"<?= ($ticket['CategorySector'] == $sec['CategorySectorID']) ? ' selected' : ''; ?>><?= htmlspecialchars($sec['Name']); ?></option>
      <?php endforeach; ?>
    </select>
    <label for="category">Category</label>
    <select id="category" disabled>
      <option value="">Select category</option>
      <?php foreach ($categories as $cat): ?>
        <option value="<?= $cat['CategoryID']; ?>"<?= ($ticket['Category'] == $cat['CategoryID']) ? ' selected' : ''; ?>><?= htmlspecialchars($cat['Name']); ?></option>
      <?php endforeach; ?>
    </select>
    <label for="subcategory">Subcategory</label>
    <select id="subcategory" disabled>
      <option value="">Select subcategory</option>
      <?php foreach ($subcategories as $subcat): ?>
        <option value="<?= $subcat['SubCategoryID']; ?>"<?= ($ticket['SubCategory'] == $subcat['SubCategoryID']) ? ' selected' : ''; ?>><?= htmlspecialchars($subcat['Name']); ?></option>
      <?php endforeach; ?>
    </select>
    <label for="owner">Owner</label>
    <select id="owner" disabled>
      <option value="">Select owner</option>
      <?php foreach ($owners as $user): ?>
        <option value="<?= $user['UserID']; ?>"<?= ($ticket['Owner'] == $user['UserID']) ? ' selected' : ''; ?>><?= htmlspecialchars($user['Name']); ?></option>
      <?php endforeach; ?>
    </select>
    <label for="assignmentGroup">Assignment group</label>
    <select id="assignmentGroup" disabled>
      <option value="">Select group</option>
      <?php foreach ($groups as $g): ?>
        <option value="<?= $g['AssignmentGroupID']; ?>"<?= ($assignmentGroupID == $g['AssignmentGroupID']) ? ' selected' : ''; ?>><?= htmlspecialchars($g['Name']); ?></option>
      <?php endforeach; ?>
    </select>
    <label for="assignedOn">Assigned on</label>
<select id="assignedOn" disabled>
  <option value="">Select agent</option>
  <?php foreach ($agents as $ag): ?>
    <option value="<?= $ag['UserID']; ?>"<?= ($assignedOn == $ag['UserID']) ? ' selected' : ''; ?>>
      <?= htmlspecialchars($ag['Name']); ?>
    </option>
  <?php endforeach; ?>
</select>

  </div>
  <button class="save-edit-btn" id="saveEditBtn">Save changes</button>
</div>

<script>
let editMode = false;
let seeMore = false;
document.getElementById('seeMoreBtn').onclick = function () {
  seeMore = !seeMore;
  document.getElementById('seeMoreSection').style.display = seeMore ? "block" : "none";
  this.textContent = seeMore ? "Hide details" : "See more";
};
document.getElementById('editToggleBtn').onclick = function () {
  editMode = !editMode;
  document.getElementById('title').readOnly = !editMode;
  document.getElementById('desc').readOnly = !editMode;
  document.getElementById('categorySector').disabled = !editMode;
  document.getElementById('category').disabled = !editMode;
  document.getElementById('subcategory').disabled = !editMode;
  document.getElementById('owner').disabled = !editMode;
  document.getElementById('assignmentGroup').disabled = !editMode;
  document.getElementById('assignedOn').disabled = !editMode;
  document.getElementById('saveEditBtn').style.display = editMode ? "inline-block" : "none";
};
document.getElementById('saveEditBtn').onclick = function () {
  var fd = new FormData();
  fd.append('title', document.getElementById('title').value);
  fd.append('desc', document.getElementById('desc').value);
  fd.append('categorySector', document.getElementById('categorySector').value);
  fd.append('category', document.getElementById('category').value);
  fd.append('subcategory', document.getElementById('subcategory').value);
  fd.append('owner', document.getElementById('owner').value);
  fd.append('assignmentGroup', document.getElementById('assignmentGroup').value);
  fd.append('assignedOn', document.getElementById('assignedOn').value);
  fetch(window.location.pathname + "?id=<?= $caseID ?>", {
    method:'POST',
    body:fd
  }).then(res=>res.text()).then(r=>{
    alert("Changes saved!");
    editMode = false;
    document.getElementById('editToggleBtn').click();
  });
};
document.getElementById('categorySector').addEventListener('change', function() {
  let sectorID = this.value;
  // Category update
  fetch('../get-categories.php?sectorID=' + sectorID)
    .then(response => response.json())
    .then(categories => {
      let categorySelect = document.getElementById('category');
      categorySelect.innerHTML = '<option value="">Select category</option>';
      categories.forEach(function(cat) {
        categorySelect.innerHTML += `<option value="${cat.CategoryID}">${cat.Name}</option>`;
      });
      document.getElementById('subcategory').innerHTML = '<option value="">Select subcategory</option>';
    });

  // AssignedOn update (users ROLE=Agent iz sektora)
  fetch('../get-agents.php?sectorID=' + sectorID)
    .then(response => response.json())
    .then(agents => {
      let assignedSelect = document.getElementById('assignedOn');
      assignedSelect.innerHTML = '<option value="">Select agent</option>';
      agents.forEach(function(agent) {
        assignedSelect.innerHTML += `<option value="${agent.UserID}">${agent.Name}</option>`;
      });
    });
});
document.getElementById('category').addEventListener('change', function() {
  fetch('../supervisor/get-subcategories.php?categoryID=' + this.value)
    .then(response => response.json())
    .then(subcategories => {
      let subcategorySelect = document.getElementById('subcategory');
      subcategorySelect.innerHTML = '<option value="">Select subcategory</option>';
      subcategories.forEach(function(sub) {
        subcategorySelect.innerHTML += `<option value="${sub.SubCategoryID}">${sub.Name}</option>`;
      });
    });
});
document.getElementById('categorySector').addEventListener('change', function() {
  let sectorID = this.value;
  
  // Category update
  fetch('../supervisor/get-categories.php?sectorID=' + sectorID)
    .then(response => response.json())
    .then(categories => {
      let categorySelect = document.getElementById('category');
      categorySelect.innerHTML = '<option value="">Select category</option>';
      categories.forEach(function(cat) {
        categorySelect.innerHTML += `<option value="${cat.CategoryID}">${cat.Name}</option>`;
      });
      document.getElementById('subcategory').innerHTML = '<option value="">Select subcategory</option>';
    });

  // ASSIGNEDON update — agenti iz tog sektora
  fetch('../supervisor/get-agents.php?sectorID=' + sectorID)
    .then(response => response.json())
    .then(agents => {
      let assignedSelect = document.getElementById('assignedOn');
      assignedSelect.innerHTML = '<option value="">Select agent</option>';
      agents.forEach(function(agent) {
        assignedSelect.innerHTML += `<option value="${agent.UserID}">${agent.Name}</option>`;
      });
    });
});

</script>
