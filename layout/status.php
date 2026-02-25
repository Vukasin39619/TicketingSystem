<style>
  .status-section {
    margin-top: 2.2rem;
    display: flex;
    justify-content: flex-start;
    align-items: center;
  }
  .status-pills-row {
    display: flex;
    gap: 2.3rem;
    align-items: center;
    width: 100%;
  }
  .status-pill {
    min-width: 128px;
    padding: 1.1rem 0;
    font-weight: 700;
    font-size: 1.32rem;
    border-radius: 17px;
    color: #fff;
    text-align: center;
    border: none;
    outline: none;
    cursor: pointer;
    background: #22252b;
    transition: box-shadow 0.2s, background 0.2s, color 0.2s, opacity 0.2s, filter 0.2s;
    box-shadow: 0 3px 22px #181b1d1a;
    font-family: "Segoe UI", Arial, sans-serif;
    letter-spacing: 1.2px;
    opacity: 0.4;
    filter: grayscale(1);
  }
  .status-selected {
    opacity: 1;
    filter: none;
    box-shadow: 0 4px 20px #2196F390, 0 0 0 4px #43b0ff44;
    border: 3px solid #fff;
  }
  .status-open    { background: #2978B5 !important; }
  .status-progress{ background: #c1d833 !important; color: #181b1d; }
  .status-pending { background: #FAA046 !important; color: #181b1d; }
  .status-resolved{ background: #45d095 !important; color: #181b1d; }
  body { background: #16161a; color: #ececec; font-family: "Segoe UI", Arial, sans-serif;}
  .save-status-btn {
    margin-left: 2rem;
    background: #45d095;
    color: #fff;
    font-weight: 700;
    font-size: 1rem;
    padding: 1rem 2rem;
    border: none;
    border-radius: 17px;
    cursor: pointer;
    box-shadow: 0 2px 24px #43b0ffc9;
    transition: background 0.2s;
  }
  .save-status-btn:hover { background: #42c992; }
</style>
<div class="status-section">
  <div class="status-pills-row">
    <button class="status-pill status-open<?= ($ticket['Status']=='Open' ? ' status-selected' : '') ?>" data-status="Open">Open</button>
    <button class="status-pill status-progress<?= ($ticket['Status']=='In Progress' ? ' status-selected' : '') ?>" data-status="In Progress">Progress</button>
    <button class="status-pill status-pending<?= ($ticket['Status']=='Pending' ? ' status-selected' : '') ?>" data-status="Pending">Pending</button>
    <button class="status-pill status-resolved<?= ($ticket['Status']=='Resolved' ? ' status-selected' : '') ?>" data-status="Resolved">Resolved</button>
    <button class="save-status-btn" id="saveStatusBtn">Save Status</button>
  </div>
</div>
<script>
let selectedStatus = document.querySelector('.status-selected')?.getAttribute('data-status') || "Open";
document.querySelectorAll('.status-pill').forEach(btn => {
  btn.addEventListener('click', function() {
    document.querySelectorAll('.status-pill').forEach(b=>{
      b.classList.remove('status-selected');
      b.style.opacity = "0.4";
      b.style.filter = "grayscale(1)";
    });
    btn.classList.add('status-selected');
    btn.style.opacity = "1";
    btn.style.filter = "none";
    selectedStatus = btn.getAttribute('data-status');
  });
});
document.getElementById('saveStatusBtn').onclick = function() {
  var fd = new FormData();
  fd.append('set_status', selectedStatus);
  fetch(window.location.pathname + "?id=<?= $caseID ?>", {
    method: 'POST',
    body: fd
  }).then(r=>r.text()).then(r=>{
    if(r.trim()=="ok") alert("Status saved!");
    else alert("Error!");
  });
};
</script>
