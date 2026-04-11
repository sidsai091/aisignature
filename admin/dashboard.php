<?php
require_once 'includes/auth.php';
require_once 'includes/data.php';
require_login();

$stats   = get_stats();
$revenue = get_monthly_revenue();
$all     = get_bookings();

// Calendar: current month bookings
$calYear  = (int)date('Y');
$calMonth = (int)date('m');
$firstDay = mktime(0,0,0,$calMonth,1,$calYear);
$daysInMonth = (int)date('t', $firstDay);
$startDow    = (int)date('N', $firstDay); // 1=Mon

// Index bookings by day
$calBookings = [];
foreach ($all as $b) {
    if (substr($b['date'],0,7) === date('Y-m')) {
        $d = (int)substr($b['date'],8,2);
        $calBookings[$d][] = $b;
    }
}

// Revenue chart data
$chartLabels = [];
$chartValues = [];
foreach ($revenue as $m => $v) {
    $chartLabels[] = date('M Y', strtotime($m.'-01'));
    $chartValues[] = $v;
}

// Recent bookings (last 5)
$recent = array_slice(array_reverse($all), 0, 5);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title>Dashboard — Ameerul Iskandar Admin</title>
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
        <h1 class="page-title">Dashboard</h1>
        <p class="page-sub">Welcome back, <?= htmlspecialchars($_SESSION['admin_user']) ?>. Here's what's happening.</p>
      </div>
      <a href="../booking.html" target="_blank" class="btn-gold-sm">+ New Booking</a>
    </div>

    <!-- ---- STAT CARDS ---- -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon icon-total">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/>
            <path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/>
          </svg>
        </div>
        <div class="stat-body">
          <div class="stat-num"><?= $stats['total'] ?></div>
          <div class="stat-label">Total Bookings</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon icon-pending">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
          </svg>
        </div>
        <div class="stat-body">
          <div class="stat-num"><?= $stats['pending'] ?></div>
          <div class="stat-label">Pending</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon icon-confirmed">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <polyline points="20 6 9 17 4 12"/>
          </svg>
        </div>
        <div class="stat-body">
          <div class="stat-num"><?= $stats['confirmed'] ?></div>
          <div class="stat-label">Confirmed</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon icon-revenue">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/>
          </svg>
        </div>
        <div class="stat-body">
          <div class="stat-num">$<?= number_format($stats['monthRevenue']) ?></div>
          <div class="stat-label">Revenue This Month</div>
        </div>
      </div>
    </div>

    <!-- ---- REVENUE CHART + CALENDAR ---- -->
    <div class="grid-two">

      <!-- Revenue Chart -->
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">Monthly Revenue</h3>
          <span class="card-badge">Confirmed + Completed</span>
        </div>
        <div class="chart-wrap">
          <canvas id="revenueChart" height="220"></canvas>
        </div>
      </div>

      <!-- Calendar -->
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">
            <?= date('F Y', $firstDay) ?>
          </h3>
          <span class="card-badge"><?= count($calBookings) ?> events</span>
        </div>
        <div class="calendar">
          <div class="cal-head">
            <?php foreach(['Mon','Tue','Wed','Thu','Fri','Sat','Sun'] as $d): ?>
              <div class="cal-dh"><?= $d ?></div>
            <?php endforeach; ?>
          </div>
          <div class="cal-body">
            <?php
            // Empty cells before first day
            for ($i = 1; $i < $startDow; $i++): ?>
              <div class="cal-cell empty"></div>
            <?php endfor;
            for ($day = 1; $day <= $daysInMonth; $day++):
              $isToday = ($day == (int)date('j') && $calMonth == (int)date('m') && $calYear == (int)date('Y'));
              $hasBook = isset($calBookings[$day]);
            ?>
              <div class="cal-cell <?= $isToday ? 'today' : '' ?> <?= $hasBook ? 'has-booking' : '' ?>">
                <span class="cal-num"><?= $day ?></span>
                <?php if ($hasBook): ?>
                  <span class="cal-dot" title="<?= count($calBookings[$day]) ?> booking(s)"></span>
                <?php endif; ?>
              </div>
            <?php endfor; ?>
          </div>
          <div class="cal-legend">
            <span><span class="cal-dot"></span> Event day</span>
            <span><span class="cal-today-dot"></span> Today</span>
          </div>
        </div>
      </div>
    </div>

    <!-- ---- RECENT BOOKINGS ---- -->
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Recent Enquiries</h3>
        <a href="bookings.php" class="card-link">View all →</a>
      </div>
      <div class="table-wrap">
        <table class="data-table">
          <thead>
            <tr>
              <th>#</th>
              <th>Client</th>
              <th>Event Type</th>
              <th>Date</th>
              <th>Package</th>
              <th>Status</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($recent as $b): ?>
            <tr>
              <td class="td-id">#<?= $b['id'] ?></td>
              <td>
                <div class="td-name"><?= htmlspecialchars($b['name']) ?></div>
                <div class="td-sub"><?= htmlspecialchars($b['email']) ?></div>
              </td>
              <td><?= htmlspecialchars($b['event_type']) ?></td>
              <td><?= date('d M Y', strtotime($b['date'])) ?></td>
              <td><span class="pkg-tag"><?= $b['package'] ?></span></td>
              <td><span class="badge <?= status_class($b['status']) ?>"><?= $b['status'] ?></span></td>
              <td><a href="booking-detail.php?id=<?= $b['id'] ?>" class="tbl-btn">View</a></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

  </div><!-- /.page-content -->
</div><!-- /.main-wrap -->

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="assets/admin.js"></script>
<script>
const labels = <?= json_encode($chartLabels) ?>;
const values = <?= json_encode($chartValues) ?>;
initRevenueChart('revenueChart', labels, values);
</script>
</body>
</html>
