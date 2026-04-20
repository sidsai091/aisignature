<?php
// ── LOGIC ────────────────────────────────────────────────
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/data.php';
require_once 'includes/components.php';
require_login();

$stats    = get_stats();
$revenue  = get_monthly_revenue();
$all      = get_bookings();
$upcoming = get_upcoming_events(3);
$nextEvt  = get_next_event();
$pkgBreak = get_package_breakdown();

// Calendar: current month
$calYear     = (int)date('Y');
$calMonth    = (int)date('m');
$firstDay    = mktime(0, 0, 0, $calMonth, 1, $calYear);
$daysInMonth = (int)date('t', $firstDay);
$startDow    = (int)date('N', $firstDay);

$calBookings = [];
foreach ($all as $b) {
    if (substr($b['date'], 0, 7) === date('Y-m')) {
        $d = (int)substr($b['date'], 8, 2);
        $calBookings[$d][] = $b;
    }
}

// Chart data
$chartLabels = [];
$chartValues = [];
foreach ($revenue as $m => $v) {
    $chartLabels[] = date('M Y', strtotime($m . '-01'));
    $chartValues[] = $v;
}

// Recent bookings (last 5)
$recent = array_slice(array_reverse($all), 0, 5);

// Estimated total revenue
$estimatedRevenue = 0;
foreach ($pkgBreak as $pkg => $count) {
    $estimatedRevenue += $count * (PKG_PRICES[$pkg] ?? 0);
}

$pkgTotal  = array_sum($pkgBreak);
$pageTitle = 'Dashboard';

// Chart.js needed in <head>
$extraHead = '<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>';

// Inline chart init goes after admin.js
ob_start(); ?>
<script>
  const labels = <?= json_encode($chartLabels) ?>;
  const values = <?= json_encode($chartValues) ?>;
  initRevenueChart('revenueChart', labels, values);

  (function() {
    const canvas = document.getElementById('pkgChart');
    if (!canvas || typeof Chart === 'undefined') return;
    new Chart(canvas, {
      type: 'doughnut',
      data: {
        labels: ['Basic', 'Standard', 'Premium'],
        datasets: [{
          data: [<?= $pkgBreak['Basic'] ?>, <?= $pkgBreak['Standard'] ?>, <?= $pkgBreak['Premium'] ?>],
          backgroundColor: <?= json_encode(array_values(PKG_COLORS)) ?>,
          borderColor:     <?= json_encode(array_values(PKG_COLORS)) ?>,
          borderWidth: 0,
          hoverBackgroundColor: ['#1d4ed8','#8b6508','#15803d'],
        }]
      },
      options: {
        cutout: '72%',
        responsive: false,
        plugins: {
          legend: { display: false },
          tooltip: {
            backgroundColor: '#fff',
            borderColor: '#eaecf3',
            borderWidth: 1,
            titleColor: '#1e2433',
            bodyColor: '#8a93a8',
            padding: 10,
          }
        }
      }
    });
  })();
</script>
<?php $extraScripts = ob_get_clean();

// ── TEMPLATE ─────────────────────────────────────────────
ob_start(); ?>

<?= render_page_header(
    'Dashboard',
    'Welcome back, ' . htmlspecialchars($_SESSION['admin_user']) . '. Here\'s what\'s happening.',
    '<a href="../booking.html" target="_blank" class="btn-gold-sm">+ New Booking</a>'
) ?>

<!-- NEXT EVENT BANNER -->
<?php if ($nextEvt): ?>
  <div class="next-event-banner">
    <div class="neb-left">
      <div class="neb-icon">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
          <rect x="3" y="4" width="18" height="18" rx="2"/>
          <line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
        </svg>
      </div>
      <div>
        <div class="neb-label">Next Upcoming Event</div>
        <div class="neb-title"><?= htmlspecialchars($nextEvt['name']) ?> — <?= htmlspecialchars($nextEvt['event_type']) ?></div>
        <div class="neb-meta">
          <?= date('l, d F Y', strtotime($nextEvt['date'])) ?> &nbsp;·&nbsp;
          <?= $nextEvt['time'] ?> &nbsp;·&nbsp;
          <?= htmlspecialchars($nextEvt['venue']) ?>
        </div>
      </div>
    </div>
    <div class="neb-right">
      <div class="neb-countdown">
        <span class="neb-days"><?= days_until($nextEvt['date']) ?></span>
        <span class="neb-days-label">days away</span>
      </div>
      <?= render_badge($nextEvt['status']) ?>
      <a href="booking-detail.php?id=<?= $nextEvt['id'] ?>" class="btn-outline-sm">View →</a>
    </div>
  </div>
<?php endif; ?>

<!-- STAT CARDS -->
<div class="stats-grid stats-grid-6">
  <div class="stat-card">
    <div class="stat-icon icon-total">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
        <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/>
        <circle cx="9" cy="7" r="4"/>
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
    <div class="stat-icon icon-completed">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
        <path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>
      </svg>
    </div>
    <div class="stat-body">
      <div class="stat-num"><?= $stats['completed'] ?></div>
      <div class="stat-label">Completed</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon icon-cancelled">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
        <circle cx="12" cy="12" r="10"/>
        <line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>
      </svg>
    </div>
    <div class="stat-body">
      <div class="stat-num"><?= $stats['cancelled'] ?></div>
      <div class="stat-label">Cancelled</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon icon-revenue">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
        <line x1="12" y1="1" x2="12" y2="23"/>
        <path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/>
      </svg>
    </div>
    <div class="stat-body">
      <div class="stat-num">RM<?= number_format($stats['monthRevenue']) ?></div>
      <div class="stat-label">Revenue</div>
    </div>
  </div>
</div>

<!-- REVENUE CHART + CALENDAR -->
<div class="grid-two">

  <div class="card">
    <div class="card-header">
      <h3 class="card-title">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" style="color:var(--gold)">
          <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/>
          <polyline points="17 6 23 6 23 12"/>
        </svg>
        Monthly Revenue
      </h3>
      <span class="card-badge">Confirmed + Completed</span>
    </div>
    <div class="chart-wrap">
      <canvas id="revenueChart" height="220"></canvas>
    </div>
  </div>

  <div class="card">
    <div class="card-header">
      <h3 class="card-title">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" style="color:var(--gold)">
          <rect x="3" y="4" width="18" height="18" rx="2"/>
          <line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
        </svg>
        <?= date('F Y', $firstDay) ?>
      </h3>
      <span class="card-badge"><?= count($calBookings) ?> events</span>
    </div>
    <div class="calendar">
      <div class="cal-head">
        <?php foreach (['Mon','Tue','Wed','Thu','Fri','Sat','Sun'] as $d): ?>
          <div class="cal-dh"><?= $d ?></div>
        <?php endforeach; ?>
      </div>
      <div class="cal-body">
        <?php for ($i = 1; $i < $startDow; $i++): ?>
          <div class="cal-cell empty"></div>
        <?php endfor; ?>
        <?php for ($day = 1; $day <= $daysInMonth; $day++):
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

<!-- UPCOMING EVENTS + PACKAGE BREAKDOWN -->
<div class="grid-two">

  <div class="card">
    <div class="card-header">
      <h3 class="card-title">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" style="color:var(--gold)">
          <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
        </svg>
        Upcoming Events
      </h3>
      <a href="bookings.php" class="card-link">View all →</a>
    </div>
    <div class="upcoming-list">
      <?php if (empty($upcoming)): ?>
        <?= render_empty_state('<svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>', 'No upcoming events.') ?>
      <?php else: ?>
        <?php foreach ($upcoming as $b):
          $daysLeft = days_until($b['date']); ?>
          <div class="upcoming-item">
            <div class="upcoming-countdown <?= $daysLeft <= 7 ? 'urgent' : '' ?>">
              <span class="uc-num"><?= $daysLeft ?></span>
              <span class="uc-label">days</span>
            </div>
            <div class="upcoming-info">
              <div class="upcoming-name"><?= htmlspecialchars($b['name']) ?></div>
              <div class="upcoming-meta">
                <?= htmlspecialchars($b['event_type']) ?> &nbsp;·&nbsp;
                <?= date('d M Y', strtotime($b['date'])) ?> &nbsp;·&nbsp;
                <?= $b['time'] ?>
              </div>
              <div class="upcoming-venue">
                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/>
                  <circle cx="12" cy="10" r="3"/>
                </svg>
                <?= htmlspecialchars($b['venue']) ?>
              </div>
            </div>
            <div class="upcoming-right">
              <?= render_badge($b['status']) ?>
              <?= render_pkg_tag($b['package']) ?>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>

  <div class="card">
    <div class="card-header">
      <h3 class="card-title">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" style="color:var(--gold)">
          <path d="M21.21 15.89A10 10 0 118 2.83"/>
          <path d="M22 12A10 10 0 0012 2v10z"/>
        </svg>
        Package Breakdown
      </h3>
      <span class="card-badge"><?= $pkgTotal ?> total</span>
    </div>
    <div class="pkg-breakdown-wrap">
      <div class="pkg-donut-wrap">
        <canvas id="pkgChart" width="180" height="180"></canvas>
        <div class="pkg-donut-center">
          <span class="pkg-donut-num"><?= $pkgTotal ?></span>
          <span class="pkg-donut-label">bookings</span>
        </div>
      </div>
      <div class="pkg-legend">
        <?php foreach ($pkgBreak as $name => $count):
          $color = PKG_COLORS[$name];
          $pct   = $pkgTotal > 0 ? round(($count / $pkgTotal) * 100) : 0;
        ?>
          <div class="pkg-legend-item">
            <div class="pkg-legend-left">
              <span class="pkg-legend-dot" style="background:<?= $color ?>"></span>
              <span class="pkg-legend-name"><?= $name ?></span>
            </div>
            <div class="pkg-legend-right">
              <span class="pkg-legend-count"><?= $count ?></span>
              <span class="pkg-legend-pct"><?= $pct ?>%</span>
            </div>
          </div>
          <div class="pkg-progress-bar">
            <div class="pkg-progress-fill" style="width:<?= $pct ?>%;background:<?= $color ?>"></div>
          </div>
        <?php endforeach; ?>
        <div class="pkg-revenue-total">
          <span>Est. Total Revenue</span>
          <strong>RM<?= number_format($estimatedRevenue) ?></strong>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- RECENT BOOKINGS -->
<div class="card">
  <div class="card-header">
    <h3 class="card-title">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" style="color:var(--gold)">
        <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
        <polyline points="14 2 14 8 20 8"/>
      </svg>
      Recent Enquiries
    </h3>
    <a href="bookings.php" class="card-link">View all →</a>
  </div>
  <div class="table-wrap">
    <table class="data-table">
      <thead>
        <tr>
          <th>#</th><th>Client</th><th>Event Type</th><th>Date</th><th>Package</th><th>Status</th><th></th>
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
            <td><?= render_pkg_tag($b['package']) ?></td>
            <td><?= render_badge($b['status']) ?></td>
            <td><a href="booking-detail.php?id=<?= $b['id'] ?>" class="tbl-btn">View</a></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php
$pageContent = ob_get_clean();
require 'includes/layout.php';
