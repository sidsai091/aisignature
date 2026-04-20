<?php
// ── LOGIC ────────────────────────────────────────────────
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/data.php';
require_once 'includes/components.php';
require_login();

if (!isset($_SESSION['blocked_dates'])) {
    $_SESSION['blocked_dates'] = ['2026-04-22', '2026-04-23'];
}

$success = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['_action'] ?? '';

    if ($action === 'block') {
        $date = $_POST['block_date'] ?? '';
        if (!$date || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $error = 'Invalid date format.';
        } elseif (in_array($date, $_SESSION['blocked_dates'])) {
            $error = 'That date is already blocked.';
        } else {
            $_SESSION['blocked_dates'][] = $date;
            sort($_SESSION['blocked_dates']);
            $success = 'Date blocked: ' . date('d M Y', strtotime($date));
        }
    } elseif ($action === 'block_range') {
        $from = $_POST['range_from'] ?? '';
        $to   = $_POST['range_to']   ?? '';
        if (!$from || !$to) {
            $error = 'Please select both From and To dates.';
        } elseif ($from > $to) {
            $error = 'From date must be before To date.';
        } else {
            $cur   = new DateTime($from);
            $end   = new DateTime($to);
            $added = 0;
            while ($cur <= $end) {
                $d = $cur->format('Y-m-d');
                if (!in_array($d, $_SESSION['blocked_dates'])) {
                    $_SESSION['blocked_dates'][] = $d;
                    $added++;
                }
                $cur->modify('+1 day');
            }
            sort($_SESSION['blocked_dates']);
            $success = "$added date(s) blocked from " . date('d M', strtotime($from)) . ' to ' . date('d M Y', strtotime($to));
        }
    } elseif ($action === 'unblock') {
        $date = $_POST['unblock_date'] ?? '';
        $key  = array_search($date, $_SESSION['blocked_dates']);
        if ($key !== false) {
            array_splice($_SESSION['blocked_dates'], $key, 1);
            $success = 'Date unblocked: ' . date('d M Y', strtotime($date));
        }
    }
}

$blockedDates = $_SESSION['blocked_dates'];
$bookings     = get_bookings();

$bookedDates = [];
foreach ($bookings as $b) {
    if (in_array($b['status'], ['Confirmed', 'Pending'])) {
        $bookedDates[$b['date']] = $b;
    }
}

// Calendar vars
$viewYear  = (int)($_GET['y'] ?? date('Y'));
$viewMonth = (int)($_GET['m'] ?? date('n'));
if ($viewMonth < 1)  { $viewMonth = 12; $viewYear--; }
if ($viewMonth > 12) { $viewMonth = 1;  $viewYear++; }

$today       = date('Y-m-d');
$firstDay    = mktime(0, 0, 0, $viewMonth, 1, $viewYear);
$daysInMonth = (int)date('t', $firstDay);
$startDow    = (int)date('N', $firstDay);

$prevM = $viewMonth - 1; $prevY = $viewYear;
if ($prevM < 1)  { $prevM = 12; $prevY--; }
$nextM = $viewMonth + 1; $nextY = $viewYear;
if ($nextM > 12) { $nextM = 1;  $nextY++; }

// Pre-sort upcoming bookings
$futureBookings = array_values(array_filter(
    $bookings,
    fn($b) => in_array($b['status'], ['Confirmed', 'Pending']) && $b['date'] >= $today
));
usort($futureBookings, fn($a, $b) => strcmp($a['date'], $b['date']));

$pageTitle = 'Availability';

// ── TEMPLATE ─────────────────────────────────────────────
ob_start(); ?>

<?= render_page_header('Availability', 'Manage blocked dates and view booking calendar') ?>

<?= $success ? render_alert($success) : '' ?>
<?= $error   ? render_alert($error, 'error') : '' ?>

<div class="avail-layout">

  <!-- LEFT: Calendar -->
  <div class="avail-calendar-wrap card">
    <div class="cal-nav">
      <a href="?m=<?= $prevM ?>&y=<?= $prevY ?>" class="cal-nav-btn">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
      </a>
      <span class="cal-month-label"><?= date('F Y', $firstDay) ?></span>
      <a href="?m=<?= $nextM ?>&y=<?= $nextY ?>" class="cal-nav-btn">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
      </a>
    </div>

    <div class="cal-legend">
      <span class="cal-leg-dot cal-leg-booked"></span> Booked
      <span class="cal-leg-dot cal-leg-blocked"></span> Blocked
      <span class="cal-leg-dot cal-leg-today"></span> Today
    </div>

    <div class="cal-grid">
      <?php foreach (['Mon','Tue','Wed','Thu','Fri','Sat','Sun'] as $dow): ?>
        <div class="cal-dow"><?= $dow ?></div>
      <?php endforeach; ?>

      <?php for ($i = 1; $i < $startDow; $i++): ?>
        <div class="cal-cell cal-empty"></div>
      <?php endfor; ?>

      <?php for ($d = 1; $d <= $daysInMonth; $d++):
        $dateStr   = sprintf('%04d-%02d-%02d', $viewYear, $viewMonth, $d);
        $isToday   = $dateStr === $today;
        $isBooked  = isset($bookedDates[$dateStr]);
        $isBlocked = in_array($dateStr, $blockedDates);
        $isPast    = $dateStr < $today;
        $cls = 'cal-cell';
        if ($isToday)   $cls .= ' cal-today';
        if ($isBooked)  $cls .= ' cal-booked';
        if ($isBlocked) $cls .= ' cal-blocked';
        if ($isPast)    $cls .= ' cal-past';
      ?>
        <div class="<?= $cls ?>" title="<?= $dateStr ?>">
          <span class="cal-day-num"><?= $d ?></span>
          <?php if ($isBooked):  ?><span class="cal-dot"></span><?php endif; ?>
          <?php if ($isBlocked): ?><span class="cal-x">×</span><?php endif; ?>
        </div>
      <?php endfor; ?>
    </div>
  </div>

  <!-- RIGHT: Controls -->
  <div class="avail-controls">

    <div class="card avail-ctrl-card">
      <h3 class="avail-ctrl-title">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
        Block a Date
      </h3>
      <form method="POST" class="avail-form">
        <input type="hidden" name="_action" value="block" />
        <div class="settings-field">
          <label>Date</label>
          <input type="date" name="block_date" class="admin-input" min="<?= $today ?>" required />
        </div>
        <button type="submit" class="btn-gold-sm" style="margin-top:0.5rem">Block Date</button>
      </form>
    </div>

    <div class="card avail-ctrl-card">
      <h3 class="avail-ctrl-title">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        Block a Range
      </h3>
      <form method="POST" class="avail-form">
        <input type="hidden" name="_action" value="block_range" />
        <div class="settings-field">
          <label>From</label>
          <input type="date" name="range_from" class="admin-input" min="<?= $today ?>" required />
        </div>
        <div class="settings-field">
          <label>To</label>
          <input type="date" name="range_to" class="admin-input" min="<?= $today ?>" required />
        </div>
        <button type="submit" class="btn-gold-sm" style="margin-top:0.5rem">Block Range</button>
      </form>
    </div>

    <div class="card avail-ctrl-card avail-blocked-list">
      <h3 class="avail-ctrl-title">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
        Blocked Dates
      </h3>
      <?php if (empty($blockedDates)): ?>
        <p class="avail-empty">No dates blocked.</p>
      <?php else: ?>
        <ul class="blocked-list">
          <?php foreach ($blockedDates as $bd): ?>
            <li class="blocked-item">
              <span class="blocked-date"><?= date('d M Y', strtotime($bd)) ?></span>
              <form method="POST" style="display:inline">
                <input type="hidden" name="_action" value="unblock" />
                <input type="hidden" name="unblock_date" value="<?= $bd ?>" />
                <button type="submit" class="btn-unblock">Unblock</button>
              </form>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </div>

  </div>
</div>

<!-- Upcoming booked dates -->
<div class="card" style="margin-top:1.5rem">
  <div style="padding:1.5rem 1.75rem 0">
    <div class="card-title-row">
      <h2 class="card-section-title">Upcoming Bookings on Calendar</h2>
    </div>
  </div>
  <?php if (empty($futureBookings)): ?>
    <p style="color:var(--muted);font-size:.875rem;padding:0 1.75rem 1.5rem">No upcoming bookings.</p>
  <?php else: ?>
    <div class="table-wrap">
      <table class="data-table">
        <thead><tr>
          <th>Date</th><th>Client</th><th>Event Type</th><th>Venue</th><th>Status</th><th></th>
        </tr></thead>
        <tbody>
          <?php foreach ($futureBookings as $b): ?>
            <tr>
              <td><strong><?= date('d M Y', strtotime($b['date'])) ?></strong><div class="td-sub"><?= $b['time'] ?></div></td>
              <td><div class="td-name"><?= htmlspecialchars($b['name']) ?></div><div class="td-sub"><?= htmlspecialchars($b['email']) ?></div></td>
              <td><?= htmlspecialchars($b['event_type']) ?></td>
              <td class="td-muted"><?= htmlspecialchars($b['venue']) ?></td>
              <td><?= render_badge($b['status']) ?></td>
              <td><a href="booking-detail.php?id=<?= $b['id'] ?>" class="tbl-btn">View</a></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<?php
$pageContent = ob_get_clean();
require 'includes/layout.php';
