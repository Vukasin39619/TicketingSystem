<?php
include "../baza.php";
include "../Backend/auth.php";
requireRole(['Supervisor']);

// Assignment groups
$groups = [];
$gsql = "SELECT AssignmentGroupID, Name FROM assignmentgroup ORDER BY Name";
$gres = mysqli_query($conn, $gsql);
while ($row = mysqli_fetch_assoc($gres)) { $groups[] = $row; }

// Sectors
$sectors = [];
$ssql = "SELECT CategorySectorID, Name FROM categorysector ORDER BY Name";
$sres = mysqli_query($conn, $ssql);
while ($row = mysqli_fetch_assoc($sres)) { $sectors[] = $row; }

// Owners
$owners = [];
$osql = "SELECT UserID, Name FROM user ORDER BY Name";
$ores = mysqli_query($conn, $osql);
while ($row = mysqli_fetch_assoc($ores)) { $owners[] = $row; }

// Agents default (za prvi load, ako je nešto izabrano):
$agents = [];
if (!empty($_POST['sector'])) {
    $sectorID = intval($_POST['sector']);
    $agentsRes = mysqli_query($conn, "SELECT UserID, Name FROM user WHERE Role='Agent' AND SectorID=$sectorID");
    while ($row = mysqli_fetch_assoc($agentsRes)) $agents[] = $row;
}

$errorMsg = $successMsg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $shortDesc   = trim($_POST['short_desc'] ?? '');
    $desc        = trim($_POST['desc'] ?? '');
    $group       = $_POST['group'] ?? '';
    $sector      = $_POST['sector'] ?? '';
    $category    = $_POST['category'] ?? '';
    $subcategory = $_POST['subcategory'] ?? '';
    $owner       = $_POST['owner'] ?? '';
    $assignedOn  = $_POST['assignedOn'] ?? '';
    $status      = $_POST['status'] ?? 'Open';

    if (empty($shortDesc) || empty($desc) || empty($group) || empty($sector) || empty($category) || empty($subcategory) || empty($owner) || empty($assignedOn) || empty($status)) {
        $errorMsg = "Please fill all required fields!";
    } else {
        $sql = "INSERT INTO `case`
            (ShortDescription, Description, Status, AssignmentGroup, CategorySector, Category, SubCategory, Owner, AssignedOn)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sssiiiiii", $shortDesc, $desc, $status, $group, $sector, $category, $subcategory, $owner, $assignedOn);
        if (mysqli_stmt_execute($stmt)) {
            $successMsg = "Ticket created successfully!";
        } else {
            $errorMsg = "Failed to create ticket!";
        }
        mysqli_stmt_close($stmt);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Create ticket</title>
    <link rel="stylesheet" href="../CSS/footer.css">
    <link rel="stylesheet" href="../CSS/navbar.css">
    <style>
        body { background: #1a1a1f; color: #e0e0e0; font-family: "Segoe UI", Arial, sans-serif; }
        .container { max-width: 850px; margin: 3rem auto; background: #181b1d; padding: 2.4rem; border-radius: 20px; box-shadow: 0 2px 20px rgba(40,120,180,0.10);}
        h1 { color: #a3bbf7; margin:0 0 2rem 0; text-align:center; letter-spacing:2px; font-weight:600;}
        .flex-row { display: flex; gap:2.5rem; flex-wrap:wrap; justify-content: space-between;}
        .fieldset { background: #23262b; border-radius: 13px; padding: 1.35rem 1.37rem 1.5rem 1.37rem; margin-bottom:2rem; box-shadow:0 2px 8px rgba(60,122,186,0.09); flex:1 1 350px;}
        .fieldset legend { color: #7fc1fc; font-weight: 600; font-size:1.1rem; margin-bottom:0.85rem; letter-spacing:1px;}
        .form-group { margin-bottom: 1.0rem; display: flex; flex-direction: column;}
        label { margin-bottom:0.3rem; color:#a3bbf7; font-size:1.02rem;}
        input, select, textarea { border-radius: 7px; padding: 0.66rem; border: 1px solid #2b81c1; background: #23262b; color: #e0e0e0; font-size: 1rem;}
        textarea { resize: vertical; min-height:60px; }
        button[type=submit] { margin-top: 1.25rem; padding: 0.8rem 2.1rem; border-radius: 9px; background: #2978B5; color: #fff; border:none; font-size:1.09rem; font-weight:600; box-shadow:0 2px 12px rgba(41,120,181,0.10); cursor:pointer; float:right;}
        button[type=submit]:hover { background: #4A90E2;}
        .msg {font-size:1.08rem;margin-top:1.1rem;text-align:center;}
        .error {color:#ff5e5e;}
        .success {color:#45d095;}
        .back-link {display:block;color:#4A90E2;font-weight:500;text-align:center;margin-top:1.4rem;}
        .back-link:hover {color:#a3bbf7;}
        .state-pills { display: flex; justify-content: space-between; gap: 0.6rem; margin-bottom: 1.2rem;}
        .state-pill { flex: 1; padding: 0.85rem; font-weight: 600; border-radius: 20px; border:none; cursor:pointer; transition: background 0.2s, box-shadow 0.2s; color: #fff; font-size: 1.08rem; text-align: center; box-shadow: 0 2px 10px rgba(0,0,0,0.07); outline:none;}
        .state-pill.selected { box-shadow: 0 6px 23px #2196F390; border: 2.5px solid #fff;}
        .state-open    { background: #2978B5;}
        .state-progress{ background: #afcc28;}
        .state-pending { background: #E69333;}
        .state-resolved{ background: #45d095;}
        @media(max-width:960px){
            .container { max-width:99vw; padding:1rem 0.2rem;}
            .flex-row { flex-direction: column; gap:0.8rem;}
            .fieldset { margin-bottom:1rem; min-width:90vw;}
        }
    </style>
</head>
<body>
    <?php include "../layout/navbarSUP.php"; ?>
    <div class="container">
        <h1>Create ticket</h1>
         <?php if ($errorMsg): ?>
            <div class="msg error"><?= $errorMsg; ?></div>
        <?php elseif ($successMsg): ?>
            <div class="msg success"><?= $successMsg; ?></div>
        <?php endif; ?>
        <form method="POST" autocomplete="off">
            <div class="fieldset" style="max-width:unset;">
                <legend>Status</legend>
                <div class="state-pills">
                    <button type="button" class="state-pill state-open" onclick="setState('Open')" id="pill-open">Open</button>
                    <button type="button" class="state-pill state-progress" onclick="setState('In Progress')" id="pill-progress">In Progress</button>
                    <button type="button" class="state-pill state-pending" onclick="setState('Pending')" id="pill-pending">Pending</button>
                    <button type="button" class="state-pill state-resolved" onclick="setState('Resolved')" id="pill-resolved">Resolved</button>
                </div>
                <input type="hidden" name="status" id="status" value="<?= htmlspecialchars($_POST['status'] ?? 'Open'); ?>">
            </div>
            <div class="flex-row">
                <fieldset class="fieldset">
                    <legend>Title & description</legend>
                    <div class="form-group">
                        <label for="short_desc">Short title*</label>
                        <input type="text" name="short_desc" id="short_desc" required value="<?= htmlspecialchars($_POST['short_desc'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="desc">Detailed description*</label>
                        <textarea name="desc" id="desc" required><?= htmlspecialchars($_POST['desc'] ?? ''); ?></textarea>
                    </div>
                </fieldset>
                <fieldset class="fieldset">
                    <legend>Categories</legend>
                    <div class="form-group">
                        <label for="sector">Category sector*</label>
                        <select name="sector" id="sector" required>
                            <option value="">-- Select sector --</option>
                            <?php foreach($sectors as $sec): ?>
                                <option value="<?= $sec['CategorySectorID']; ?>"<?= ($_POST['sector'] ?? '') == $sec['CategorySectorID'] ? ' selected' : ''; ?>>
                                    <?= htmlspecialchars($sec['Name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="category">Category*</label>
                        <select name="category" id="category" required>
                            <option value="">-- Select category --</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="subcategory">Subcategory*</label>
                        <select name="subcategory" id="subcategory" required>
                            <option value="">-- Select subcategory --</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="assignedOn">Assigned on (agent)*</label>
                        <select name="assignedOn" id="assignedOn">
                            <option value="">-- Select agent --</option>
                            <?php foreach($agents as $agent): ?>
                                <option value="<?= $agent['UserID']; ?>"<?= ($_POST['assignedOn'] ?? '') == $agent['UserID'] ? ' selected' : ''; ?>>
                                    <?= htmlspecialchars($agent['Name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </fieldset>
            </div>
            <div class="flex-row">
                <fieldset class="fieldset">
                    <legend>Assignment & owner</legend>
                    <div class="form-group">
                        <label for="group">Assignment group*</label>
                        <select name="group" id="group" required>
                            <option value="">-- Select assignment group --</option>
                            <?php foreach($groups as $g): ?>
                                <option value="<?= $g['AssignmentGroupID']; ?>"<?= ($_POST['group'] ?? '') == $g['AssignmentGroupID'] ? ' selected' : ''; ?>>
                                    <?= htmlspecialchars($g['Name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="owner">Owner*</label>
                        <select name="owner" id="owner" required>
                            <option value="">-- Select owner --</option>
                            <?php foreach($owners as $usr): ?>
                                <option value="<?= $usr['UserID']; ?>"<?= ($_POST['owner'] ?? '') == $usr['UserID'] ? ' selected' : ''; ?>>
                                    <?= htmlspecialchars($usr['Name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </fieldset>
            </div>
            <button type="submit">Create ticket</button>
        </form>
        <a href="index-sup.php" class="back-link">&#8592; Back to dashboard</a>
    </div>
    <?php include "../layout/footer.php"; ?>
    <script>
    // Status pill selection
    function setState(state) {
        document.getElementById("status").value = state;
        document.getElementById('pill-open').classList.remove('selected');
        document.getElementById('pill-progress').classList.remove('selected');
        document.getElementById('pill-pending').classList.remove('selected');
        document.getElementById('pill-resolved').classList.remove('selected');
        if(state==="Open") document.getElementById('pill-open').classList.add('selected');
        if(state==="In Progress") document.getElementById('pill-progress').classList.add('selected');
        if(state==="Pending") document.getElementById('pill-pending').classList.add('selected');
        if(state==="Resolved") document.getElementById('pill-resolved').classList.add('selected');
    }
    setState(document.getElementById('status').value);

    document.getElementById('sector').addEventListener('change', function() {
        let sectorID = this.value;
        let categorySelect = document.getElementById('category');
        let subcategorySelect = document.getElementById('subcategory');
        categorySelect.innerHTML = '<option value="">-- Select category --</option>';
        subcategorySelect.innerHTML = '<option value="">-- Select subcategory --</option>';
        if (!sectorID) return;
        fetch('../supervisor/get-categories.php?sectorID=' + sectorID)
            .then(response => response.json())
            .then(categories => {
                categories.forEach(function(cat) {
                    categorySelect.innerHTML += `<option value="${cat.CategoryID}">${cat.Name}</option>`;
                });
            });
        // DYNAMIC AGENT FILL
        let assignedSelect = document.getElementById('assignedOn');
        assignedSelect.innerHTML = '<option value="">-- Select agent --</option>';
        fetch('../supervisor/get-agents.php?sectorID=' + sectorID)
            .then(response => response.json())
            .then(agents => {
                agents.forEach(function(agent) {
                    assignedSelect.innerHTML += `<option value="${agent.UserID}">${agent.Name}</option>`;
                });
            });
    });
    document.getElementById('category').addEventListener('change', function() {
        let categoryID = this.value;
        let subcategorySelect = document.getElementById('subcategory');
        subcategorySelect.innerHTML = '<option value="">-- Select subcategory --</option>';
        if (!categoryID) return;
        fetch('../supervisor/get-subcategories.php?categoryID=' + categoryID)
            .then(response => response.json())
            .then(subcategories => {
                subcategories.forEach(function(sub) {
                    subcategorySelect.innerHTML += `<option value="${sub.SubCategoryID}">${sub.Name}</option>`;
                });
            });
    });
    </script>
</body>
</html>
