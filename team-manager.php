<?php
// ─── CONFIG ───────────────────────────────────────────────────────────────────
$CSV_FILE  = 'team-page.csv';
$META_FILE = 'team-manager-meta.json'; // stores column types & archived cols

// ─── META: column definitions ─────────────────────────────────────────────────
// meta.json structure:
// { "columns": [ {"name":"...", "type":"data|page", "archived": false}, ... ] }
function loadMeta($metaFile, $csvHeader) {
  if (file_exists($metaFile)) {
    $m = json_decode(file_get_contents($metaFile), true);
    // sync any CSV header cols not yet in meta
    foreach ($csvHeader as $i => $h) {
      if (!isset($m['columns'][$i])) {
        $m['columns'][$i] = ['name' => $h, 'type' => 'data', 'archived' => false];
      }
    }
    return $m;
  }
  // Bootstrap from CSV header
  $cols = [];
  $pageIdxs = [11, 12, 13]; // known page-flag columns
  foreach ($csvHeader as $i => $h) {
    $cols[] = ['name' => $h, 'type' => in_array($i, $pageIdxs) ? 'page' : 'data', 'archived' => false];
  }
  $meta = ['columns' => $cols];
  file_put_contents($metaFile, json_encode($meta, JSON_PRETTY_PRINT));
  return $meta;
}

function saveMeta($metaFile, $meta) {
  file_put_contents($metaFile, json_encode($meta, JSON_PRETTY_PRINT));
}

// ─── CSV HELPERS ──────────────────────────────────────────────────────────────
function readCSV($file) {
  if (!file_exists($file)) return [[], []];
  $rows = array_map('str_getcsv', file($file));
  $header = array_shift($rows);
  return [$header, $rows];
}

function writeCSV($file, $header, $rows) {
  $fp = fopen($file, 'w');
  fputcsv($fp, $header);
  foreach ($rows as $row) fputcsv($fp, $row);
  fclose($fp);
}

function padRow($row, $total) {
  while (count($row) < $total) $row[] = '';
  return $row;
}

// ─── LOAD DATA ────────────────────────────────────────────────────────────────
[$csvHeader, $rows] = readCSV($CSV_FILE);
$meta = loadMeta($META_FILE, $csvHeader);
$totalCols = count($meta['columns']);
$rows = array_map(fn($r) => padRow($r, $totalCols), $rows);

// ─── HANDLE POST ──────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action = $_POST['action'] ?? '';
  [$csvHeader, $rows] = readCSV($CSV_FILE);
  $meta = loadMeta($META_FILE, $csvHeader);
  $rows = array_map(fn($r) => padRow($r, count($meta['columns'])), $rows);

  // ── SAVE ROW ──
  if ($action === 'save_row') {
    $idx = $_POST['idx'] ?? 'new';
    $rowData = array_fill(0, count($meta['columns']), '');
    foreach ($meta['columns'] as $i => $col) {
      $val = $_POST["col_$i"] ?? '';
      if ($col['type'] === 'page') {
        $val = ($val === 'TRUE' || $val === '1') ? 'TRUE' : 'FALSE';
      }
      $rowData[$i] = $val;
    }
    if ($idx === 'new') $rows[] = $rowData;
    else $rows[(int)$idx] = $rowData;
    writeCSV($CSV_FILE, array_column($meta['columns'], 'name'), $rows);
    echo json_encode(['ok' => true]); exit;
  }

  // ── DELETE ROW ──
  if ($action === 'delete_row') {
    $idx = (int)($_POST['idx'] ?? -1);
    array_splice($rows, $idx, 1);
    writeCSV($CSV_FILE, array_column($meta['columns'], 'name'), $rows);
    echo json_encode(['ok' => true]); exit;
  }

  // ── TOGGLE FLAG ──
  if ($action === 'toggle_flag') {
    $idx = (int)($_POST['idx'] ?? -1);
    $col = (int)($_POST['col'] ?? -1);
    $rows[$idx] = padRow($rows[$idx], count($meta['columns']));
    $current = trim($rows[$idx][$col] ?? '');
    $rows[$idx][$col] = ($current === 'TRUE') ? 'FALSE' : 'TRUE';
    writeCSV($CSV_FILE, array_column($meta['columns'], 'name'), $rows);
    echo json_encode(['ok' => true, 'val' => $rows[$idx][$col]]); exit;
  }

  // ── ADD COLUMN ──
  if ($action === 'add_column') {
    $name = trim($_POST['col_name'] ?? '');
    $type = $_POST['col_type'] === 'page' ? 'page' : 'data';
    if (!$name) { echo json_encode(['ok'=>false,'error'=>'Name required']); exit; }
    // Add to meta
    $meta['columns'][] = ['name' => $name, 'type' => $type, 'archived' => false];
    saveMeta($META_FILE, $meta);
    // Add empty cell to every CSV row
    $header = array_column($meta['columns'], 'name');
    $rows = array_map(fn($r) => padRow($r, count($meta['columns'])), $rows);
    $default = $type === 'page' ? 'FALSE' : '';
    foreach ($rows as &$r) $r[count($meta['columns'])-1] = $default;
    writeCSV($CSV_FILE, $header, $rows);
    echo json_encode(['ok' => true, 'idx' => count($meta['columns'])-1, 'col' => end($meta['columns'])]); exit;
  }

  // ── ARCHIVE COLUMN ──
  if ($action === 'archive_column') {
    $col = (int)($_POST['col'] ?? -1);
    if (isset($meta['columns'][$col])) {
      $meta['columns'][$col]['archived'] = true;
      saveMeta($META_FILE, $meta);
      echo json_encode(['ok' => true]); exit;
    }
    echo json_encode(['ok'=>false,'error'=>'Column not found']); exit;
  }

  // ── RESTORE COLUMN ──
  if ($action === 'restore_column') {
    $col = (int)($_POST['col'] ?? -1);
    if (isset($meta['columns'][$col])) {
      $meta['columns'][$col]['archived'] = false;
      saveMeta($META_FILE, $meta);
      echo json_encode(['ok' => true]); exit;
    }
    echo json_encode(['ok'=>false,'error'=>'Column not found']); exit;
  }

  // ── RENAME COLUMN ──
  if ($action === 'rename_column') {
    $col  = (int)($_POST['col'] ?? -1);
    $name = trim($_POST['name'] ?? '');
    if (!$name) { echo json_encode(['ok'=>false,'error'=>'Name required']); exit; }
    $meta['columns'][$col]['name'] = $name;
    saveMeta($META_FILE, $meta);
    // Update CSV header
    $header = array_column($meta['columns'], 'name');
    writeCSV($CSV_FILE, $header, $rows);
    echo json_encode(['ok' => true]); exit;
  }

  echo json_encode(['ok'=>false,'error'=>'Unknown action']); exit;
}

// ─── PREPARE FOR TEMPLATE ─────────────────────────────────────────────────────
$rows      = array_map(fn($r) => padRow($r, count($meta['columns'])), $rows);
$rowsJson  = json_encode($rows);
$metaJson  = json_encode($meta['columns']);

// Active page-flag columns
$pageFlags = [];
foreach ($meta['columns'] as $i => $col) {
  if ($col['type'] === 'page' && !$col['archived']) $pageFlags[$i] = $col['name'];
}
$pageFlagsJson = json_encode($pageFlags);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Team Manager — BIY</title>
<style>
:root {
  --bg:       #0f1117;
  --surface:  #181c27;
  --surface2: #1f2437;
  --border:   #2a3050;
  --accent:   #4f8ef7;
  --accent2:  #7c5cfc;
  --green:    #22c55e;
  --red:      #ef4444;
  --amber:    #f59e0b;
  --text:     #e8eaf0;
  --muted:    #7a82a0;
  --radius:   10px;
  --shadow:   0 4px 24px rgba(0,0,0,.45);
  --font:     'Inter', system-ui, sans-serif;
}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{font-family:var(--font);background:var(--bg);color:var(--text);min-height:100vh}
button{cursor:pointer;font-family:inherit}
input,select,textarea{font-family:inherit}
.app{display:grid;grid-template-rows:auto 1fr;min-height:100vh}

/* TOPBAR */
.topbar{display:flex;align-items:center;justify-content:space-between;padding:0 28px;height:60px;background:var(--surface);border-bottom:1px solid var(--border);position:sticky;top:0;z-index:100}
.topbar-brand{display:flex;align-items:center;gap:10px;font-weight:700;font-size:1rem;letter-spacing:.02em}
.topbar-brand .dot{width:8px;height:8px;border-radius:50%;background:var(--accent)}
.topbar-actions{display:flex;gap:10px;align-items:center}

/* MAIN */
.main{padding:28px}

/* STATS */
.stats{display:flex;gap:16px;margin-bottom:24px;flex-wrap:wrap}
.stat-card{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:14px 20px;display:flex;align-items:center;gap:12px;flex:1;min-width:140px}
.stat-icon{font-size:1.4rem}
.stat-label{font-size:.72rem;color:var(--muted);text-transform:uppercase;letter-spacing:.08em}
.stat-val{font-size:1.5rem;font-weight:700;line-height:1}

/* TOOLBAR */
.toolbar{display:flex;gap:12px;align-items:center;margin-bottom:20px;flex-wrap:wrap}
.search-wrap{position:relative;flex:1;min-width:200px}
.search-wrap input{width:100%;background:var(--surface);border:1px solid var(--border);color:var(--text);border-radius:var(--radius);padding:9px 14px 9px 38px;font-size:.9rem;outline:none;transition:border-color .2s}
.search-wrap input:focus{border-color:var(--accent)}
.search-icon{position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--muted);font-size:.9rem;pointer-events:none}
.filter-select{background:var(--surface);border:1px solid var(--border);color:var(--text);border-radius:var(--radius);padding:9px 14px;font-size:.9rem;outline:none;cursor:pointer}

/* BUTTONS */
.btn{display:inline-flex;align-items:center;gap:6px;padding:8px 16px;border-radius:var(--radius);font-size:.88rem;font-weight:600;border:none;transition:opacity .15s,transform .1s;white-space:nowrap}
.btn:hover{opacity:.85}
.btn:active{transform:scale(.97)}
.btn-primary{background:var(--accent);color:#fff}
.btn-success{background:var(--green);color:#fff}
.btn-danger{background:var(--red);color:#fff}
.btn-amber{background:var(--amber);color:#000}
.btn-ghost{background:transparent;border:1px solid var(--border);color:var(--text)}
.btn-sm{padding:5px 11px;font-size:.8rem;border-radius:7px}

/* TABLE */
.table-wrap{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);overflow:hidden;box-shadow:var(--shadow)}
.table-scroll{overflow-x:auto}
table{width:100%;border-collapse:collapse;font-size:.87rem}
thead{background:var(--surface2)}
th{padding:12px 14px;text-align:left;font-size:.72rem;text-transform:uppercase;letter-spacing:.08em;color:var(--muted);white-space:nowrap;border-bottom:1px solid var(--border);cursor:pointer;user-select:none}
th:hover{color:var(--text)}
th .sort-icon{margin-left:4px;opacity:.4}
th.sorted .sort-icon{opacity:1;color:var(--accent)}
td{padding:12px 14px;border-bottom:1px solid var(--border);vertical-align:middle;max-width:200px}
tr:last-child td{border-bottom:none}
tr:hover td{background:var(--surface2)}

/* MEMBER CELL */
.member-cell{display:flex;align-items:center;gap:10px}
.avatar{width:36px;height:36px;border-radius:50%;object-fit:cover;background:var(--surface2);border:2px solid var(--border);flex-shrink:0}
.avatar-placeholder{width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,var(--accent),var(--accent2));display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.8rem;color:#fff;flex-shrink:0}
.member-name{font-weight:600;white-space:nowrap}
.member-role{font-size:.78rem;color:var(--muted);white-space:nowrap}
.text-truncate{white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:160px;display:block}

/* FLAGS */
.flags{display:flex;gap:5px;flex-wrap:wrap}
.flag-btn{padding:3px 9px;border-radius:99px;font-size:.7rem;font-weight:700;border:1px solid transparent;cursor:pointer;transition:all .15s;letter-spacing:.03em}
.flag-btn.on{background:rgba(79,142,247,.15);color:var(--accent);border-color:rgba(79,142,247,.3)}
.flag-btn.off{background:transparent;color:var(--muted);border-color:var(--border)}
.flag-btn:hover{opacity:.8}

/* ROW ACTIONS */
.row-actions{display:flex;gap:6px;white-space:nowrap}

/* EMPTY */
.empty{text-align:center;padding:60px 20px;color:var(--muted)}
.empty-icon{font-size:3rem;margin-bottom:12px}

/* PAGINATION */
.pagination{display:flex;align-items:center;justify-content:space-between;padding:14px 18px;border-top:1px solid var(--border);flex-wrap:wrap;gap:10px}
.page-info{font-size:.82rem;color:var(--muted)}
.page-btns{display:flex;gap:6px}
.page-btn{width:32px;height:32px;border-radius:7px;display:flex;align-items:center;justify-content:center;font-size:.82rem;font-weight:600;border:1px solid var(--border);background:transparent;color:var(--text);cursor:pointer;transition:all .15s}
.page-btn:hover,.page-btn.active{background:var(--accent);border-color:var(--accent);color:#fff}
.page-btn:disabled{opacity:.3;cursor:default}

/* MODAL */
.modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.7);display:flex;align-items:center;justify-content:center;z-index:200;padding:20px;opacity:0;pointer-events:none;transition:opacity .2s}
.modal-overlay.open{opacity:1;pointer-events:all}
.modal{background:var(--surface);border:1px solid var(--border);border-radius:14px;width:100%;max-width:700px;max-height:90vh;display:flex;flex-direction:column;box-shadow:0 20px 60px rgba(0,0,0,.6);transform:translateY(20px);transition:transform .2s}
.modal-overlay.open .modal{transform:translateY(0)}
.modal-header{padding:20px 24px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between}
.modal-title{font-size:1.05rem;font-weight:700}
.modal-close{width:32px;height:32px;border-radius:7px;border:1px solid var(--border);background:transparent;color:var(--muted);font-size:1.1rem;display:flex;align-items:center;justify-content:center;cursor:pointer}
.modal-close:hover{color:var(--text);background:var(--surface2)}
.modal-body{padding:24px;overflow-y:auto;flex:1}
.modal-footer{padding:16px 24px;border-top:1px solid var(--border);display:flex;gap:10px;justify-content:flex-end}

/* FORM */
.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px}
.form-group{display:flex;flex-direction:column;gap:6px}
.form-group.full{grid-column:1/-1}
label{font-size:.78rem;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.07em}
.form-control{background:var(--surface2);border:1px solid var(--border);color:var(--text);border-radius:8px;padding:9px 12px;font-size:.88rem;outline:none;transition:border-color .2s;width:100%}
.form-control:focus{border-color:var(--accent)}
textarea.form-control{resize:vertical;min-height:70px}
.form-section{margin-bottom:20px}
.form-section-title{font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:var(--accent);margin-bottom:12px;padding-bottom:6px;border-bottom:1px solid var(--border)}

/* TOGGLES */
.toggle-group{display:flex;gap:14px;flex-wrap:wrap}
.toggle-label{display:flex;align-items:center;gap:8px;cursor:pointer}
.toggle-label input[type=checkbox]{display:none}
.toggle-pill{width:42px;height:24px;border-radius:12px;background:var(--border);position:relative;transition:background .2s}
.toggle-pill::after{content:'';position:absolute;top:3px;left:3px;width:18px;height:18px;border-radius:50%;background:#fff;transition:transform .2s}
.toggle-label input:checked+.toggle-pill{background:var(--accent)}
.toggle-label input:checked+.toggle-pill::after{transform:translateX(18px)}
.toggle-text{font-size:.85rem;font-weight:500}

/* TOAST */
.toast-container{position:fixed;bottom:24px;right:24px;z-index:400;display:flex;flex-direction:column;gap:8px}
.toast{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:12px 18px;display:flex;align-items:center;gap:10px;font-size:.88rem;box-shadow:var(--shadow);min-width:240px;animation:slideIn .25s ease}
.toast.success{border-left:3px solid var(--green)}
.toast.error{border-left:3px solid var(--red)}
@keyframes slideIn{from{transform:translateX(40px);opacity:0}to{transform:translateX(0);opacity:1}}

/* CONFIRM */
.confirm-overlay{position:fixed;inset:0;background:rgba(0,0,0,.7);display:flex;align-items:center;justify-content:center;z-index:300;opacity:0;pointer-events:none;transition:opacity .15s}
.confirm-overlay.open{opacity:1;pointer-events:all}
.confirm-box{background:var(--surface);border:1px solid var(--border);border-radius:14px;padding:28px;max-width:380px;width:100%;text-align:center;box-shadow:0 20px 60px rgba(0,0,0,.6)}
.confirm-icon{font-size:2.5rem;margin-bottom:12px}
.confirm-title{font-size:1rem;font-weight:700;margin-bottom:8px}
.confirm-msg{color:var(--muted);font-size:.88rem;margin-bottom:20px;line-height:1.5}
.confirm-actions{display:flex;gap:10px;justify-content:center}

/* COLUMNS PANEL */
.col-panel{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);margin-bottom:24px;overflow:hidden;display:none}
.col-panel.open{display:block}
.col-panel-header{padding:16px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between}
.col-panel-title{font-weight:700;font-size:.95rem}
.col-panel-body{padding:20px}
.col-list{display:flex;flex-direction:column;gap:8px;margin-bottom:20px}
.col-item{display:flex;align-items:center;gap:10px;padding:10px 14px;background:var(--surface2);border:1px solid var(--border);border-radius:8px}
.col-item.archived{opacity:.5;border-style:dashed}
.col-item-name{flex:1;font-weight:600;font-size:.88rem}
.col-item-type{font-size:.72rem;font-weight:700;padding:2px 8px;border-radius:99px}
.col-type-data{background:rgba(124,92,252,.15);color:var(--accent2);border:1px solid rgba(124,92,252,.3)}
.col-type-page{background:rgba(34,197,94,.12);color:var(--green);border:1px solid rgba(34,197,94,.25)}
.col-type-archived{background:rgba(122,130,160,.1);color:var(--muted);border:1px solid var(--border)}
.col-item-actions{display:flex;gap:6px}
.add-col-form{display:flex;gap:10px;align-items:flex-end;flex-wrap:wrap;padding-top:16px;border-top:1px solid var(--border)}
.add-col-form .form-group{flex:1;min-width:150px;margin:0}

/* TYPE BADGE in form */
.type-select{background:var(--surface2);border:1px solid var(--border);color:var(--text);border-radius:8px;padding:9px 12px;font-size:.88rem;outline:none;cursor:pointer;width:100%}

/* TABS in modal */
.tab-bar{display:flex;gap:4px;margin-bottom:20px;background:var(--surface2);border-radius:8px;padding:4px}
.tab-btn{flex:1;padding:7px 12px;border-radius:6px;font-size:.82rem;font-weight:600;border:none;background:transparent;color:var(--muted);cursor:pointer;transition:all .15s}
.tab-btn.active{background:var(--accent);color:#fff}
.tab-pane{display:none}
.tab-pane.active{display:block}

/* INLINE EDIT */
.inline-edit{display:flex;gap:6px;align-items:center}
.inline-edit input{background:var(--surface2);border:1px solid var(--accent);color:var(--text);border-radius:6px;padding:4px 8px;font-size:.82rem;outline:none;flex:1}

/* RESPONSIVE */
@media(max-width:700px){
  .main{padding:16px}
  .form-grid{grid-template-columns:1fr}
  .stats{gap:10px}
  .stat-card{min-width:100px}
  .topbar{padding:0 16px}
  .modal{max-height:100vh;border-radius:0}
  .modal-overlay{padding:0;align-items:flex-end}
  .add-col-form{flex-direction:column}
}
</style>
</head>
<body>
<div class="app">

<!-- TOPBAR -->
<header class="topbar">
  <div class="topbar-brand"><div class="dot"></div>BIY Team Manager</div>
  <div class="topbar-actions">
    <button class="btn btn-ghost btn-sm" onclick="toggleColPanel()">⚙️ Columns</button>
    <button class="btn btn-primary btn-sm" onclick="openModal('new')">+ Add Member</button>
  </div>
</header>

<main class="main">

  <!-- STATS -->
  <div class="stats" id="stats"></div>

  <!-- COLUMN MANAGER PANEL -->
  <div class="col-panel" id="colPanel">
    <div class="col-panel-header">
      <span class="col-panel-title">⚙️ Column Manager</span>
      <button class="btn btn-ghost btn-sm" onclick="toggleColPanel()">✕ Close</button>
    </div>
    <div class="col-panel-body">
      <div style="font-size:.82rem;color:var(--muted);margin-bottom:14px">
        Manage your CSV columns. Archiving a column hides it from the UI but keeps all data safe in the CSV.
      </div>
      <div class="col-list" id="colList"></div>
      <!-- ADD NEW COLUMN FORM -->
      <div class="add-col-form">
        <div class="form-group">
          <label>Column Name</label>
          <input class="form-control" id="newColName" placeholder="e.g. Bio, iu-team…">
        </div>
        <div class="form-group">
          <label>Column Type</label>
          <select class="type-select" id="newColType">
            <option value="data">📝 Data — stores text/info</option>
            <option value="page">🚩 Page flag — TRUE/FALSE toggle</option>
          </select>
        </div>
        <div class="form-group" style="justify-content:flex-end">
          <button class="btn btn-success" onclick="addColumn()">+ Add Column</button>
        </div>
      </div>
    </div>
  </div>

  <!-- TOOLBAR -->
  <div class="toolbar">
    <div class="search-wrap">
      <span class="search-icon">🔍</span>
      <input type="text" id="searchInput" placeholder="Search by name, role, school…" oninput="applyFilters()">
    </div>
    <select class="filter-select" id="pageFilter" onchange="applyFilters()"></select>
    <select class="filter-select" id="perPage" onchange="applyFilters()">
      <option value="10">10 / page</option>
      <option value="25">25 / page</option>
      <option value="50">50 / page</option>
    </select>
  </div>

  <!-- TABLE -->
  <div class="table-wrap">
    <div class="table-scroll">
      <table>
        <thead>
          <tr>
            <th onclick="sortBy(0)">Member <span class="sort-icon" id="sort-0">↕</span></th>
            <th onclick="sortBy(2)">Education <span class="sort-icon" id="sort-2">↕</span></th>
            <th>Pages</th>
            <th onclick="sortBy(16)">Home Town <span class="sort-icon" id="sort-16">↕</span></th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="tableBody"></tbody>
      </table>
    </div>
    <div id="emptyState" class="empty" style="display:none">
      <div class="empty-icon">🔍</div>
      <div>No members match your search.</div>
    </div>
    <div class="pagination" id="pagination"></div>
  </div>

</main>
</div>

<!-- MEMBER MODAL -->
<div class="modal-overlay" id="modalOverlay" onclick="closeModalOnBg(event)">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title" id="modalTitle">Edit Member</span>
      <button class="modal-close" onclick="closeModal()">✕</button>
    </div>
    <div class="modal-body">
      <div class="tab-bar">
        <button class="tab-btn active" onclick="switchTab('basic',this)">Basic Info</button>
        <button class="tab-btn" onclick="switchTab('personal',this)">Personal</button>
        <button class="tab-btn" onclick="switchTab('links',this)">Links</button>
        <button class="tab-btn" onclick="switchTab('pages',this)">Pages</button>
        <button class="tab-btn" onclick="switchTab('extra',this)">Extra Fields</button>
      </div>
      <form id="memberForm">
        <!-- BASIC -->
        <div class="tab-pane active" id="tab-basic">
          <div class="form-grid">
            <div class="form-group"><label>Name *</label><input class="form-control" name="col_0" required></div>
            <div class="form-group"><label>Position / Role</label><input class="form-control" name="col_1"></div>
            <div class="form-group"><label>Education 1</label><input class="form-control" name="col_2"></div>
            <div class="form-group"><label>Education 2</label><input class="form-control" name="col_3"></div>
            <div class="form-group"><label>Home Town</label><input class="form-control" name="col_16"></div>
            <div class="form-group"><label>Favorite Cities</label><input class="form-control" name="col_15"></div>
            <div class="form-group full"><label>Image URL</label><input class="form-control" name="col_14" placeholder="images/team-name.jpg"></div>
          </div>
        </div>
        <!-- PERSONAL -->
        <div class="tab-pane" id="tab-personal">
          <div class="form-grid">
            <div class="form-group"><label>Hobbies</label><textarea class="form-control" name="col_4"></textarea></div>
            <div class="form-group"><label>Heroes</label><textarea class="form-control" name="col_5"></textarea></div>
            <div class="form-group"><label>Least Favorite Thing</label><textarea class="form-control" name="col_6"></textarea></div>
            <div class="form-group"><label>Book Favs</label><textarea class="form-control" name="col_21"></textarea></div>
            <div class="form-group full"><label>Favorite Quote</label><textarea class="form-control" name="col_7"></textarea></div>
            <div class="form-group full"><label>Goal</label><textarea class="form-control" name="col_17"></textarea></div>
            <div class="form-group full"><label>Wish</label><textarea class="form-control" name="col_18"></textarea></div>
            <div class="form-group full"><label>Skills Wanted</label><textarea class="form-control" name="col_8"></textarea></div>
          </div>
        </div>
        <!-- LINKS -->
        <div class="tab-pane" id="tab-links">
          <div class="form-grid">
            <div class="form-group"><label>Website Label 1</label><input class="form-control" name="col_9"></div>
            <div class="form-group"><label>Website URL 1</label><input class="form-control" name="col_10" type="url"></div>
            <div class="form-group"><label>Website Label 2</label><input class="form-control" name="col_19"></div>
            <div class="form-group"><label>Website URL 2</label><input class="form-control" name="col_20" type="url"></div>
          </div>
        </div>
        <!-- PAGES -->
        <div class="tab-pane" id="tab-pages">
          <div style="margin-bottom:12px;font-size:.85rem;color:var(--muted)">Toggle which pages this member appears on.</div>
          <div class="toggle-group" id="pageToggles"></div>
        </div>
        <!-- EXTRA (dynamic columns) -->
        <div class="tab-pane" id="tab-extra">
          <div style="margin-bottom:12px;font-size:.85rem;color:var(--muted)">Custom columns you've added.</div>
          <div class="form-grid" id="extraFields"></div>
        </div>
        <input type="hidden" name="idx" id="formIdx">
        <input type="hidden" name="action" value="save_row">
      </form>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost" onclick="closeModal()">Cancel</button>
      <button class="btn btn-primary" onclick="submitForm()">Save Member</button>
    </div>
  </div>
</div>

<!-- CONFIRM DIALOG -->
<div class="confirm-overlay" id="confirmOverlay">
  <div class="confirm-box">
    <div class="confirm-icon" id="confirmIcon">⚠️</div>
    <div class="confirm-title" id="confirmTitle">Are you sure?</div>
    <div class="confirm-msg" id="confirmMsg"></div>
    <div class="confirm-actions">
      <button class="btn btn-ghost" onclick="closeConfirm()">Cancel</button>
      <button class="btn btn-danger" id="confirmOk">Confirm</button>
    </div>
  </div>
</div>

<!-- TOAST -->
<div class="toast-container" id="toastContainer"></div>

<script>
// ── DATA ──────────────────────────────────────────────────────────────────────
let allRows   = <?= $rowsJson ?>;
let colMeta   = <?= $metaJson ?>;   // [{name, type, archived}, ...]
let pageFlags = <?= $pageFlagsJson ?>; // {colIdx: label, ...}
let filtered  = [...allRows];
let sortCol   = -1, sortDir = 1;
let currentPage = 1;

// Core columns always shown in fixed tabs (not in Extra)
const CORE_COLS = [0,1,2,3,4,5,6,7,8,9,10,14,15,16,17,18,19,20,21];

function perPageVal() { return parseInt(document.getElementById('perPage').value) || 10; }

// ── PAGE FILTER POPULATE ──────────────────────────────────────────────────────
function populatePageFilter() {
  const sel = document.getElementById('pageFilter');
  sel.innerHTML = '<option value="">All pages</option>';
  Object.entries(pageFlags).forEach(([col, label]) => {
    sel.innerHTML += `<option value="${col}">${label}</option>`;
  });
}

// ── STATS ─────────────────────────────────────────────────────────────────────
function renderStats() {
  const total = allRows.length;
  let html = `<div class="stat-card"><div class="stat-icon">👥</div><div><div class="stat-label">Total Members</div><div class="stat-val">${total}</div></div></div>`;
  Object.entries(pageFlags).forEach(([col, label]) => {
    const count = allRows.filter(r => r[parseInt(col)] === 'TRUE').length;
    html += `<div class="stat-card"><div class="stat-icon">🚩</div><div><div class="stat-label">${escHtml(label)}</div><div class="stat-val">${count}</div></div></div>`;
  });
  document.getElementById('stats').innerHTML = html;
}

// ── COLUMN PANEL ─────────────────────────────────────────────────────────────
function toggleColPanel() {
  const p = document.getElementById('colPanel');
  p.classList.toggle('open');
  if (p.classList.contains('open')) renderColList();
}

function renderColList() {
  const list = document.getElementById('colList');
  list.innerHTML = colMeta.map((col, i) => {
    const typeLabel = col.archived ? 'archived' : col.type;
    const typeCls   = col.archived ? 'col-type-archived' : (col.type === 'page' ? 'col-type-page' : 'col-type-data');
    const typeText  = col.archived ? '📦 archived' : (col.type === 'page' ? '🚩 page flag' : '📝 data');
    return `<div class="col-item ${col.archived ? 'archived' : ''}" id="col-item-${i}">
      <div class="col-item-name" id="col-name-display-${i}">${escHtml(col.name)}</div>
      <span class="col-item-type ${typeCls}">${typeText}</span>
      <div class="col-item-actions">
        <button class="btn btn-ghost btn-sm" onclick="startRename(${i})" title="Rename">✏️</button>
        ${col.archived
          ? `<button class="btn btn-success btn-sm" onclick="restoreColumn(${i})" title="Restore">♻️ Restore</button>`
          : `<button class="btn btn-amber btn-sm" onclick="archiveColumn(${i})" title="Archive">📦 Archive</button>`
        }
      </div>
    </div>`;
  }).join('');
}

function startRename(i) {
  const display = document.getElementById(`col-name-display-${i}`);
  const current = colMeta[i].name;
  display.innerHTML = `<div class="inline-edit">
    <input id="rename-input-${i}" value="${escHtml(current)}">
    <button class="btn btn-primary btn-sm" onclick="submitRename(${i})">✓</button>
    <button class="btn btn-ghost btn-sm" onclick="renderColList()">✕</button>
  </div>`;
  document.getElementById(`rename-input-${i}`).focus();
}

function submitRename(i) {
  const val = document.getElementById(`rename-input-${i}`).value.trim();
  if (!val) return toast('Name cannot be empty', 'error');
  post({action:'rename_column', col:i, name:val}).then(r => {
    if (r.ok) { colMeta[i].name = val; if (colMeta[i].type === 'page') pageFlags[i] = val; renderColList(); populatePageFilter(); renderStats(); toast('Column renamed.','success'); }
    else toast(r.error || 'Failed','error');
  });
}

function addColumn() {
  const name = document.getElementById('newColName').value.trim();
  const type = document.getElementById('newColType').value;
  if (!name) return toast('Enter a column name first.', 'error');
  post({action:'add_column', col_name:name, col_type:type}).then(r => {
    if (r.ok) {
      const newIdx = r.idx;
      colMeta.push(r.col);
      allRows.forEach(row => { while(row.length <= newIdx) row.push(''); row[newIdx] = type==='page'?'FALSE':''; });
      filtered = [...allRows];
      if (type === 'page') pageFlags[newIdx] = name;
      document.getElementById('newColName').value = '';
      renderColList();
      renderStats();
      populatePageFilter();
      applyFilters();
      toast(`Column "${name}" added!`, 'success');
    } else toast(r.error || 'Failed', 'error');
  });
}

function archiveColumn(i) {
  showConfirm({
    icon:'📦', title:'Archive Column?',
    msg:`"${colMeta[i].name}" will be hidden from the UI. All existing data is kept safe in the CSV.`,
    btnText:'Archive', btnClass:'btn-amber',
    onConfirm: () => post({action:'archive_column', col:i}).then(r => {
      if (r.ok) {
        colMeta[i].archived = true;
        if (pageFlags[i]) delete pageFlags[i];
        renderColList(); renderStats(); populatePageFilter(); applyFilters();
        toast('Column archived.','success');
      } else toast('Failed','error');
    })
  });
}

function restoreColumn(i) {
  post({action:'restore_column', col:i}).then(r => {
    if (r.ok) {
      colMeta[i].archived = false;
      if (colMeta[i].type === 'page') pageFlags[i] = colMeta[i].name;
      renderColList(); renderStats(); populatePageFilter(); applyFilters();
      toast('Column restored!','success');
    } else toast('Failed','error');
  });
}

// ── FILTERS ───────────────────────────────────────────────────────────────────
function applyFilters() {
  const q    = document.getElementById('searchInput').value.toLowerCase();
  const page = document.getElementById('pageFilter').value;
  filtered = allRows.filter(r => {
    const matchQ = !q || [r[0],r[1],r[2],r[16]].some(v => (v||'').toLowerCase().includes(q));
    const matchP = !page || r[parseInt(page)] === 'TRUE';
    return matchQ && matchP;
  });
  if (sortCol >= 0) doSort();
  currentPage = 1;
  renderTable();
}

// ── SORT ──────────────────────────────────────────────────────────────────────
function sortBy(col) {
  if (sortCol === col) sortDir *= -1; else { sortCol = col; sortDir = 1; }
  document.querySelectorAll('.sort-icon').forEach(el => el.textContent = '↕');
  const el = document.getElementById('sort-' + col);
  if (el) el.textContent = sortDir === 1 ? '↑' : '↓';
  doSort(); renderTable();
}
function doSort() {
  filtered.sort((a,b) => {
    const av = (a[sortCol]||'').toLowerCase(), bv = (b[sortCol]||'').toLowerCase();
    return av < bv ? -sortDir : av > bv ? sortDir : 0;
  });
}

// ── TABLE ─────────────────────────────────────────────────────────────────────
function renderTable() {
  const pp    = perPageVal();
  const start = (currentPage-1)*pp;
  const page  = filtered.slice(start, start+pp);
  const tbody = document.getElementById('tableBody');
  const empty = document.getElementById('emptyState');
  if (!page.length) { tbody.innerHTML=''; empty.style.display='block'; document.getElementById('pagination').innerHTML=''; return; }
  empty.style.display = 'none';

  tbody.innerHTML = page.map(row => {
    const realIdx = allRows.indexOf(row);
    const name    = row[0]||'—', role=row[1]||'', edu=row[2]||'', town=row[16]||'', img=row[14]||'';
    const initials = name.split(' ').map(w=>w[0]).join('').slice(0,2).toUpperCase();
    const avatarHtml = img
      ? `<img class="avatar" src="${escHtml(img)}" onerror="this.style.display='none';this.nextElementSibling.style.display='flex'" alt=""><div class="avatar-placeholder" style="display:none">${initials}</div>`
      : `<div class="avatar-placeholder">${initials}</div>`;
    const flagsHtml = Object.entries(pageFlags).map(([col,label]) => {
      const on = row[parseInt(col)] === 'TRUE';
      return `<button class="flag-btn ${on?'on':'off'}" onclick="toggleFlag(${realIdx},${col})">${escHtml(label)}</button>`;
    }).join('');
    return `<tr>
      <td><div class="member-cell">${avatarHtml}<div><div class="member-name">${escHtml(name)}</div><div class="member-role">${escHtml(role)}</div></div></div></td>
      <td><span class="text-truncate" title="${escHtml(edu)}">${escHtml(edu)}</span></td>
      <td><div class="flags">${flagsHtml||'<span style="color:var(--muted);font-size:.78rem">No page flags</span>'}</div></td>
      <td>${escHtml(town)}</td>
      <td><div class="row-actions">
        <button class="btn btn-ghost btn-sm" onclick="openModal(${realIdx})">✏️ Edit</button>
        <button class="btn btn-danger btn-sm" onclick="confirmDelete(${realIdx},'${escHtml(name)}')">🗑</button>
      </div></td>
    </tr>`;
  }).join('');
  renderPagination();
}

// ── PAGINATION ────────────────────────────────────────────────────────────────
function renderPagination() {
  const pp=perPageVal(), total=filtered.length, pages=Math.ceil(total/pp);
  const start=(currentPage-1)*pp+1, end=Math.min(currentPage*pp,total);
  let btns='';
  for(let i=1;i<=pages;i++){
    if(pages>7&&i>2&&i<pages-1&&Math.abs(i-currentPage)>1){if(i===3||i===pages-2)btns+=`<span style="color:var(--muted);padding:0 4px">…</span>`;continue;}
    btns+=`<button class="page-btn ${i===currentPage?'active':''}" onclick="goPage(${i})">${i}</button>`;
  }
  document.getElementById('pagination').innerHTML=`
    <span class="page-info">Showing ${start}–${end} of ${total} members</span>
    <div class="page-btns">
      <button class="page-btn" onclick="goPage(${currentPage-1})" ${currentPage===1?'disabled':''}>‹</button>
      ${btns}
      <button class="page-btn" onclick="goPage(${currentPage+1})" ${currentPage===pages?'disabled':''}>›</button>
    </div>`;
}
function goPage(p){const pages=Math.ceil(filtered.length/perPageVal());if(p<1||p>pages)return;currentPage=p;renderTable();}

// ── MODAL ─────────────────────────────────────────────────────────────────────
function openModal(idx) {
  document.getElementById('modalTitle').textContent = idx==='new'?'Add Member':'Edit Member';
  document.getElementById('formIdx').value = idx;
  document.getElementById('memberForm').reset();
  switchTab('basic', document.querySelector('.tab-btn'));

  // Build page toggles
  const togDiv = document.getElementById('pageToggles');
  togDiv.innerHTML = Object.entries(pageFlags).map(([col, label]) =>
    `<label class="toggle-label">
      <input type="checkbox" name="col_${col}" value="TRUE">
      <span class="toggle-pill"></span>
      <span class="toggle-text">${escHtml(label)}</span>
    </label>`
  ).join('');

  // Build extra fields (non-core, non-archived, non-page)
  const extraDiv = document.getElementById('extraFields');
  const extraCols = colMeta.map((col,i)=>({col,i})).filter(({col,i}) =>
    !col.archived && col.type==='data' && !CORE_COLS.includes(i)
  );
  extraDiv.innerHTML = extraCols.length
    ? extraCols.map(({col,i}) =>
        `<div class="form-group ${extraCols.length===1?'full':''}">
          <label>${escHtml(col.name)}</label>
          <textarea class="form-control" name="col_${i}"></textarea>
        </div>`
      ).join('')
    : '<p style="color:var(--muted);font-size:.88rem">No custom data columns yet. Add some via ⚙️ Columns.</p>';

  // Populate values
  if (idx !== 'new') {
    const row = allRows[idx];
    document.getElementById('memberForm').querySelectorAll('[name^="col_"]').forEach(el => {
      const col = parseInt(el.name.replace('col_',''));
      const val = row[col]||'';
      if (el.type==='checkbox') el.checked=(val==='TRUE'); else el.value=val;
    });
  }
  document.getElementById('modalOverlay').classList.add('open');
}
function closeModal(){document.getElementById('modalOverlay').classList.remove('open')}
function closeModalOnBg(e){if(e.target===document.getElementById('modalOverlay'))closeModal()}

function switchTab(id, btn) {
  document.querySelectorAll('.tab-pane').forEach(p=>p.classList.remove('active'));
  document.querySelectorAll('.tab-btn').forEach(b=>b.classList.remove('active'));
  document.getElementById('tab-'+id).classList.add('active');
  if(btn) btn.classList.add('active');
}

function submitForm() {
  const form = document.getElementById('memberForm');
  const data = new FormData(form);
  // Ensure all page-flag cols send a value
  Object.keys(pageFlags).forEach(col => {
    if (!data.has(`col_${col}`)) data.set(`col_${col}`, 'FALSE');
    else data.set(`col_${col}`, 'TRUE');
  });
  data.set('action','save_row');
  fetch('',{method:'POST',body:data}).then(r=>r.json()).then(res=>{
    if(res.ok){toast('Member saved!','success');closeModal();reloadData();}
    else toast('Save failed.','error');
  }).catch(()=>toast('Network error.','error'));
}

// ── TOGGLE FLAG ───────────────────────────────────────────────────────────────
function toggleFlag(idx, col) {
  post({action:'toggle_flag',idx,col}).then(r=>{
    if(r.ok){allRows[idx][col]=r.val;applyFilters();renderStats();toast(`${pageFlags[col]} → ${r.val}`,'success');}
  });
}

// ── DELETE ────────────────────────────────────────────────────────────────────
function confirmDelete(idx, name) {
  showConfirm({
    icon:'🗑️', title:'Delete Member?',
    msg:`Remove "${name}" from the team? This cannot be undone.`,
    btnText:'Delete', btnClass:'btn-danger',
    onConfirm: () => post({action:'delete_row',idx}).then(r=>{
      if(r.ok){toast('Member deleted.','success');reloadData();}else toast('Failed.','error');
    })
  });
}

// ── CONFIRM DIALOG ────────────────────────────────────────────────────────────
function showConfirm({icon,title,msg,btnText,btnClass,onConfirm}){
  document.getElementById('confirmIcon').textContent = icon||'⚠️';
  document.getElementById('confirmTitle').textContent = title;
  document.getElementById('confirmMsg').textContent = msg;
  const btn = document.getElementById('confirmOk');
  btn.textContent = btnText||'Confirm';
  btn.className = `btn ${btnClass||'btn-danger'}`;
  btn.onclick = ()=>{closeConfirm();onConfirm();};
  document.getElementById('confirmOverlay').classList.add('open');
}
function closeConfirm(){document.getElementById('confirmOverlay').classList.remove('open')}

// ── RELOAD ────────────────────────────────────────────────────────────────────
function reloadData(){
  fetch(location.href).then(r=>r.text()).then(html=>{
    const m1=html.match(/let allRows\s*=\s*(\[[\s\S]*?\]);/);
    const m2=html.match(/let colMeta\s*=\s*(\[[\s\S]*?\]);/);
    const m3=html.match(/let pageFlags\s*=\s*(\{[\s\S]*?\});/);
    if(m1) allRows=JSON.parse(m1[1]);
    if(m2) colMeta=JSON.parse(m2[1]);
    if(m3) pageFlags=JSON.parse(m3[1]);
    filtered=[...allRows];
    renderStats();populatePageFilter();applyFilters();
  });
}

// ── HELPERS ───────────────────────────────────────────────────────────────────
function post(data){
  const fd=new FormData();
  Object.entries(data).forEach(([k,v])=>fd.append(k,v));
  return fetch('',{method:'POST',body:fd}).then(r=>r.json());
}
function toast(msg,type='success'){
  const el=document.createElement('div');
  el.className=`toast ${type}`;
  el.innerHTML=`<span>${type==='success'?'✅':'❌'}</span> ${msg}`;
  document.getElementById('toastContainer').appendChild(el);
  setTimeout(()=>el.remove(),3500);
}
function escHtml(str){return(str||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;')}

// ── INIT ──────────────────────────────────────────────────────────────────────
populatePageFilter();
renderStats();
applyFilters();
</script>
</body>
</html>