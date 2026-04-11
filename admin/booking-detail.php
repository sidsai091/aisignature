<?php
require_once 'includes/auth.php';
require_once 'includes/data.php';
require_login();

$id      = (int)($_GET['id'] ?? 0);
$booking = get_booking_by_id($id);
if (!$booking) {
    header('Location: bookings.php');
    exit;
}

$statuses  = ['Pending','Confirmed','Completed','Cancelled'];
$saved     = false;
$savedNote = $booking['notes'];

// Handle form submit (UI only — simulate save)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newStatus = $_POST['status'] ?? $booking['status'];
    $newNote   = trim($_POST['notes'] ?? '');
    if (in_array($newStatus, $statuses)) {
        $booking['status'] = $newStatus;
    }
    $savedNote       = $newNote;
    $booking['notes']= $newNote;
    $saved = true;
}

$packagePrices = ['Basic'=>800, 'Standard'=>1500, 'Premium'=>2800];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title>Booking #<?= $booking['id'] ?> — Admin</title>
  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="assets/admin.css"/>
</head>
<body>

<?php include 'includes/sidebar.php'; ?>

<div class="main-wrap">
  <?php include 'includes/topbar.php'; ?>

  <div class="page-content">

    <!-- Breadcrumb -->
    <div class="breadcrumb">
      <a href="bookings.php">Bookings</a>
      <span>/</span>
      <span>#<?= $booking['id'] ?> — <?= htmlspecialchars($booking['name']) ?></span>
    </div>

    <div class="page-header">
      <div>
        <h1 class="page-title">Booking #<?= $booking['id'] ?></h1>
        <p class="page-sub">Submitted <?= date('d F Y', strtotime($booking['created'])) ?></p>
      </div>
      <div style="display:flex;gap:.75rem;align-items:center;">
        <span class="badge <?= status_class($booking['status']) ?>" style="font-size:.85rem;padding:.4rem 1rem;"><?= $booking['status'] ?></span>
        <a href="bookings.php" class="btn-outline-sm">← Back</a>
      </div>
    </div>

    <?php if ($saved): ?>
      <div class="alert alert-success">Changes saved successfully.</div>
    <?php endif; ?>

    <div class="detail-grid">

      <!-- ---- LEFT: Client & Event Info ---- -->
      <div class="detail-main">

        <!-- Client Info -->
        <div class="card detail-card">
          <div class="card-header">
            <h3 class="card-title">Client Information</h3>
          </div>
          <div class="detail-fields">
            <div class="detail-row">
              <div class="detail-field">
                <div class="df-label">Full Name</div>
                <div class="df-value"><?= htmlspecialchars($booking['name']) ?></div>
              </div>
              <div class="detail-field">
                <div class="df-label">Email Address</div>
                <div class="df-value"><a href="mailto:<?= htmlspecialchars($booking['email']) ?>"><?= htmlspecialchars($booking['email']) ?></a></div>
              </div>
            </div>
            <div class="detail-row">
              <div class="detail-field">
                <div class="df-label">Phone Number</div>
                <div class="df-value"><a href="tel:<?= htmlspecialchars($booking['phone']) ?>"><?= htmlspecialchars($booking['phone']) ?></a></div>
              </div>
              <div class="detail-field">
                <div class="df-label">Company / Organisation</div>
                <div class="df-value"><?= htmlspecialchars($booking['company'] ?: '—') ?></div>
              </div>
            </div>
          </div>
        </div>

        <!-- Event Info -->
        <div class="card detail-card">
          <div class="card-header">
            <h3 class="card-title">Event Details</h3>
          </div>
          <div class="detail-fields">
            <div class="detail-row">
              <div class="detail-field">
                <div class="df-label">Event Type</div>
                <div class="df-value"><?= htmlspecialchars($booking['event_type']) ?></div>
              </div>
              <div class="detail-field">
                <div class="df-label">Venue / Location</div>
                <div class="df-value"><?= htmlspecialchars($booking['venue']) ?></div>
              </div>
            </div>
            <div class="detail-row">
              <div class="detail-field">
                <div class="df-label">Event Date</div>
                <div class="df-value"><?= date('l, d F Y', strtotime($booking['date'])) ?></div>
              </div>
              <div class="detail-field">
                <div class="df-label">Start Time</div>
                <div class="df-value"><?= $booking['time'] ?></div>
              </div>
            </div>
            <div class="detail-row">
              <div class="detail-field">
                <div class="df-label">Duration</div>
                <div class="df-value"><?= $booking['duration'] === 'fullday' ? 'Full Day (8+ Hours)' : $booking['duration'] ?></div>
              </div>
              <div class="detail-field">
                <div class="df-label">Package</div>
                <div class="df-value">
                  <span class="pkg-tag pkg-tag-lg"><?= $booking['package'] ?></span>
                  <span class="df-price">from $<?= number_format($packagePrices[$booking['package']] ?? 0) ?></span>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div><!-- /.detail-main -->

      <!-- ---- RIGHT: Status + Notes ---- -->
      <div class="detail-aside">

        <div class="card detail-card">
          <div class="card-header">
            <h3 class="card-title">Update Booking</h3>
          </div>
          <form method="POST" action="booking-detail.php?id=<?= $booking['id'] ?>">

            <div class="form-group">
              <label>Booking Status</label>
              <select name="status" class="admin-select">
                <?php foreach ($statuses as $s): ?>
                  <option value="<?= $s ?>" <?= $booking['status']===$s?'selected':'' ?>><?= $s ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <!-- Status colour guide -->
            <div class="status-guide">
              <div><span class="badge badge-pending">Pending</span> Awaiting confirmation</div>
              <div><span class="badge badge-confirmed">Confirmed</span> Booking confirmed</div>
              <div><span class="badge badge-completed">Completed</span> Event done</div>
              <div><span class="badge badge-cancelled">Cancelled</span> Booking cancelled</div>
            </div>

            <div class="form-group" style="margin-top:1.5rem;">
              <label>Internal Notes</label>
              <textarea name="notes" rows="6" class="admin-textarea" placeholder="Add private notes for admin use only…"><?= htmlspecialchars($savedNote) ?></textarea>
              <p class="field-hint">These notes are not visible to the client.</p>
            </div>

            <button type="submit" class="btn-gold-full">Save Changes</button>
          </form>
        </div>

        <!-- Quick Actions -->
        <div class="card detail-card">
          <div class="card-header"><h3 class="card-title">Quick Actions</h3></div>
          <div class="quick-actions">
            <a href="mailto:<?= htmlspecialchars($booking['email']) ?>" class="qa-btn">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                <polyline points="22,6 12,13 2,6"/>
              </svg>
              Email Client
            </a>
            <a href="tel:<?= htmlspecialchars($booking['phone']) ?>" class="qa-btn">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path d="M22 16.9v3a2 2 0 01-2.2 2 19.8 19.8 0 01-8.6-3.1 19.5 19.5 0 01-6-6 19.8 19.8 0 01-3.1-8.7A2 2 0 014.1 2h3a2 2 0 012 1.7 12.7 12.7 0 00.7 2.8 2 2 0 01-.5 2.1L8 9.9a16 16 0 006 6l1.3-1.3a2 2 0 012.1-.5c.9.3 1.8.5 2.8.7A2 2 0 0122 16.9z"/>
              </svg>
              Call Client
            </a>
            <a href="bookings.php" class="qa-btn qa-btn-neutral">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/>
              </svg>
              Back to List
            </a>
          </div>
        </div>

      </div><!-- /.detail-aside -->
    </div><!-- /.detail-grid -->

  </div>
</div>

<script src="assets/admin.js"></script>
</body>
</html>
