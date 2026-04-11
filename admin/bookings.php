<?php
require_once 'includes/auth.php';
require_once 'includes/data.php';
require_login();

$all = get_bookings();

// Filters
$filterStatus = $_GET['status'] ?? '';
$filterMonth  = $_GET['month']  ?? '';
$filterFrom   = $_GET['from']   ?? '';
$filterTo     = $_GET['to']     ?? '';
$search       = trim($_GET['q'] ?? '');

$filtered = array_filter($all, function($b) use ($filterStatus, $filterMonth, $filterFrom, $filterTo, $search) {
    if ($filterStatus && $b['status'] !== $filterStatus) return false;
    if ($filterMonth  && substr($b['date'],0,7) !== $filterMonth) return false;
    if ($filterFrom   && $b['date'] < $filterFrom) return false;
    if ($filterTo     && $b['date'] > $filterTo)   return false;
    if ($search) {
        $hay = strtolower($b['name'].$b['email'].$b['event_type'].$b['company']);
        if (strpos($hay, strtolower($search)) === false) return false;
    }
    return true;
});

// Statuses for filter dropdown
$statuses = ['Pending','Confirmed','Completed','Cancelled'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title>Bookings — Ameerul Iskandar Admin</title>
  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="assets/admin.css"/>
</head>
<body>

<?php include 'includes/sidebar.php'; ?>

<div class="main-wrap">
  <?php include 'includes/topbar.php'; ?>

  <div class="page-content">
    <div class="page-header">
      <div>
        <h1 class="page-title">Bookings</h1>
        <p class="page-sub"><?= count($filtered) ?> result<?= count($filtered)!=1?'s':'' ?> found</p>
      </div>
      <a href="../booking.html" target="_blank" class="btn-gold-sm">+ New Booking</a>
    </div>

    <!-- ---- FILTERS ---- -->
    <div class="card filter-card">
      <form method="GET" action="bookings.php" class="filters-form">
        <div class="filter-row">

          <div class="filter-group">
            <label>Search</label>
            <input type="text" name="q" placeholder="Name, email, company…" value="<?= htmlspecialchars($search) ?>"/>
          </div>

          <div class="filter-group">
            <label>Status</label>
            <select name="status">
              <option value="">All Statuses</option>
              <?php foreach ($statuses as $s): ?>
                <option value="<?= $s ?>" <?= $filterStatus===$s?'selected':'' ?>><?= $s ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="filter-group">
            <label>Month</label>
            <input type="month" name="month" value="<?= htmlspecialchars($filterMonth) ?>"/>
          </div>

          <div class="filter-group">
            <label>From Date</label>
            <input type="date" name="from" value="<?= htmlspecialchars($filterFrom) ?>"/>
          </div>

          <div class="filter-group">
            <label>To Date</label>
            <input type="date" name="to" value="<?= htmlspecialchars($filterTo) ?>"/>
          </div>

          <div class="filter-actions">
            <button type="submit" class="btn-gold-sm">Filter</button>
            <a href="bookings.php" class="btn-outline-sm">Reset</a>
          </div>

        </div>
      </form>
    </div>

    <!-- ---- STATUS TABS ---- -->
    <div class="status-tabs">
      <a href="bookings.php" class="tab <?= !$filterStatus?'active':'' ?>">All <span class="tab-count"><?= count($all) ?></span></a>
      <?php foreach ($statuses as $s):
        $cnt = count(array_filter($all, fn($b)=>$b['status']===$s)); ?>
        <a href="?status=<?= $s ?>" class="tab <?= $filterStatus===$s?'active':'' ?>"><?= $s ?> <span class="tab-count"><?= $cnt ?></span></a>
      <?php endforeach; ?>
    </div>

    <!-- ---- TABLE ---- -->
    <div class="card">
      <div class="table-wrap">
        <?php if (empty($filtered)): ?>
          <div class="empty-state">
            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2">
              <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <p>No bookings match your filters.</p>
            <a href="bookings.php" class="btn-outline-sm">Clear filters</a>
          </div>
        <?php else: ?>
        <table class="data-table">
          <thead>
            <tr>
              <th>#</th>
              <th>Client</th>
              <th>Company</th>
              <th>Event Type</th>
              <th>Event Date</th>
              <th>Package</th>
              <th>Duration</th>
              <th>Status</th>
              <th>Submitted</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($filtered as $b): ?>
            <tr>
              <td class="td-id">#<?= $b['id'] ?></td>
              <td>
                <div class="td-name"><?= htmlspecialchars($b['name']) ?></div>
                <div class="td-sub"><?= htmlspecialchars($b['email']) ?></div>
              </td>
              <td class="td-muted"><?= htmlspecialchars($b['company'] ?: '—') ?></td>
              <td><?= htmlspecialchars($b['event_type']) ?></td>
              <td>
                <div><?= date('d M Y', strtotime($b['date'])) ?></div>
                <div class="td-sub"><?= $b['time'] ?></div>
              </td>
              <td><span class="pkg-tag"><?= $b['package'] ?></span></td>
              <td class="td-muted"><?= $b['duration'] === 'fullday' ? 'Full Day' : $b['duration'] ?></td>
              <td><span class="badge <?= status_class($b['status']) ?>"><?= $b['status'] ?></span></td>
              <td class="td-muted"><?= date('d M', strtotime($b['created'])) ?></td>
              <td><a href="booking-detail.php?id=<?= $b['id'] ?>" class="tbl-btn">View</a></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        <?php endif; ?>
      </div>
    </div>

  </div>
</div>

<script src="assets/admin.js"></script>
</body>
</html>
