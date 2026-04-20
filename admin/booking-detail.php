<?php
// ── LOGIC ────────────────────────────────────────────────
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/data.php';
require_once 'includes/components.php';
require_login();

$id      = (int)($_GET['id'] ?? 0);
$booking = get_booking_by_id($id);
if (!$booking) {
    header('Location: bookings.php');
    exit;
}

$saved     = false;
$savedNote = $booking['notes'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['_action'] ?? 'status';

    if ($action === 'status') {
        $newStatus = $_POST['status'] ?? $booking['status'];
        $newNote   = trim($_POST['notes'] ?? '');
        if (in_array($newStatus, BOOKING_STATUSES)) $booking['status'] = $newStatus;
        $savedNote        = $newNote;
        $booking['notes'] = $newNote;
        $saved = true;
    }

    if ($action === 'client') {
        $booking['name']    = trim($_POST['name']    ?? $booking['name']);
        $booking['email']   = trim($_POST['email']   ?? $booking['email']);
        $booking['phone']   = trim($_POST['phone']   ?? $booking['phone']);
        $booking['company'] = trim($_POST['company'] ?? $booking['company']);
        $saved = true;
    }

    if ($action === 'event') {
        $booking['event_type'] = trim($_POST['event_type'] ?? $booking['event_type']);
        $booking['venue']      = trim($_POST['venue']      ?? $booking['venue']);
        $booking['date']       = trim($_POST['date']       ?? $booking['date']);
        $booking['time']       = trim($_POST['time']       ?? $booking['time']);
        $booking['duration']   = trim($_POST['duration']   ?? $booking['duration']);
        $booking['package']    = trim($_POST['package']    ?? $booking['package']);
        $saved = true;
    }
}

$packagePrices = PKG_PRICES;
$pageTitle     = 'Booking #' . $booking['id'];

// Modal JS goes into $extraScripts so layout.php places it after admin.js
ob_start(); ?>
<script>
function openModal(id) {
  const m = document.getElementById(id);
  if (!m) return;
  m.classList.add('open');
  document.body.style.overflow = 'hidden';
}
function closeModal(id) {
  const m = document.getElementById(id);
  if (!m) return;
  m.classList.remove('open');
  document.body.style.overflow = '';
}
document.querySelectorAll('.modal-backdrop').forEach(function(backdrop) {
  backdrop.addEventListener('click', function(e) {
    if (e.target === backdrop) closeModal(backdrop.id);
  });
});
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') {
    document.querySelectorAll('.modal-backdrop.open').forEach(function(m) {
      closeModal(m.id);
    });
  }
});
</script>
<?php $extraScripts = ob_get_clean();

// ── TEMPLATE ─────────────────────────────────────────────
ob_start(); ?>

<?= render_breadcrumb([
    ['label' => 'Bookings', 'href' => 'bookings.php'],
    ['label' => '#' . $booking['id'] . ' — ' . $booking['name']],
]) ?>

<div class="page-header">
  <div>
    <h1 class="page-title">Booking #<?= $booking['id'] ?></h1>
    <p class="page-sub">Submitted <?= date('d F Y', strtotime($booking['created'])) ?></p>
  </div>
  <div style="display:flex;gap:.75rem;align-items:center;">
    <?= render_badge($booking['status']) ?>
    <a href="bookings.php" class="btn-outline-sm">← Back</a>
  </div>
</div>

<?= $saved ? render_alert('Changes saved successfully.') : '' ?>

<div class="detail-grid">

  <!-- LEFT: Client & Event Info -->
  <div class="detail-main">

    <!-- Client Info -->
    <div class="card detail-card">
      <div class="card-header">
        <h3 class="card-title">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" style="color:var(--gold)">
            <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/>
          </svg>
          Client Information
        </h3>
        <button type="button" class="btn-edit-card" onclick="openModal('clientModal')">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/>
            <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/>
          </svg>
          Edit
        </button>
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
        <h3 class="card-title">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" style="color:var(--gold)">
            <rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/>
            <line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
          </svg>
          Event Details
        </h3>
        <button type="button" class="btn-edit-card" onclick="openModal('eventModal')">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/>
            <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/>
          </svg>
          Edit
        </button>
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
            <div class="df-value"><?= BOOKING_DURATIONS[$booking['duration']] ?? $booking['duration'] ?></div>
          </div>
          <div class="detail-field">
            <div class="df-label">Package</div>
            <div class="df-value">
              <?= render_pkg_tag($booking['package']) ?>
              <span class="df-price">from RM<?= number_format($packagePrices[$booking['package']] ?? 0) ?></span>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div><!-- /.detail-main -->

  <!-- RIGHT: Status + Notes -->
  <div class="detail-aside">

    <div class="card detail-card">
      <div class="card-header">
        <h3 class="card-title">Update Booking</h3>
      </div>
      <form method="POST" action="booking-detail.php?id=<?= $booking['id'] ?>">
        <input type="hidden" name="_action" value="status" />
        <div class="form-group">
          <label>Booking Status</label>
          <select name="status" class="admin-select">
            <?php foreach (BOOKING_STATUSES as $s): ?>
              <option value="<?= $s ?>" <?= $booking['status'] === $s ? 'selected' : '' ?>><?= $s ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="status-guide">
          <div><?= render_badge('Pending') ?> Awaiting confirmation</div>
          <div><?= render_badge('Confirmed') ?> Booking confirmed</div>
          <div><?= render_badge('Completed') ?> Event done</div>
          <div><?= render_badge('Cancelled') ?> Booking cancelled</div>
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

<!-- MODAL: Edit Client Information -->
<div class="modal-backdrop" id="clientModal">
  <div class="modal">
    <div class="modal-header">
      <h3 class="modal-title">Edit Client Information</h3>
      <button type="button" class="modal-close" onclick="closeModal('clientModal')">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
        </svg>
      </button>
    </div>
    <form method="POST" action="booking-detail.php?id=<?= $booking['id'] ?>">
      <input type="hidden" name="_action" value="client" />
      <div class="modal-body">
        <div class="modal-row">
          <div class="modal-field">
            <label>Full Name</label>
            <input type="text" name="name" value="<?= htmlspecialchars($booking['name']) ?>" class="admin-input" required />
          </div>
          <div class="modal-field">
            <label>Email Address</label>
            <input type="email" name="email" value="<?= htmlspecialchars($booking['email']) ?>" class="admin-input" required />
          </div>
        </div>
        <div class="modal-row">
          <div class="modal-field">
            <label>Phone Number</label>
            <input type="text" name="phone" value="<?= htmlspecialchars($booking['phone']) ?>" class="admin-input" required />
          </div>
          <div class="modal-field">
            <label>Company / Organisation</label>
            <input type="text" name="company" value="<?= htmlspecialchars($booking['company']) ?>" class="admin-input" placeholder="Optional" />
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn-outline-sm" onclick="closeModal('clientModal')">Cancel</button>
        <button type="submit" class="btn-gold-sm">Save Changes</button>
      </div>
    </form>
  </div>
</div>

<!-- MODAL: Edit Event Details -->
<div class="modal-backdrop" id="eventModal">
  <div class="modal">
    <div class="modal-header">
      <h3 class="modal-title">Edit Event Details</h3>
      <button type="button" class="modal-close" onclick="closeModal('eventModal')">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
        </svg>
      </button>
    </div>
    <form method="POST" action="booking-detail.php?id=<?= $booking['id'] ?>">
      <input type="hidden" name="_action" value="event" />
      <div class="modal-body">
        <div class="modal-row">
          <div class="modal-field">
            <label>Event Type</label>
            <select name="event_type" class="admin-select">
              <?php foreach (EVENT_TYPES as $et): ?>
                <option value="<?= $et ?>" <?= $booking['event_type'] === $et ? 'selected' : '' ?>><?= $et ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="modal-field">
            <label>Venue / Location</label>
            <input type="text" name="venue" value="<?= htmlspecialchars($booking['venue']) ?>" class="admin-input" required />
          </div>
        </div>
        <div class="modal-row">
          <div class="modal-field">
            <label>Event Date</label>
            <input type="date" name="date" value="<?= $booking['date'] ?>" class="admin-input" required />
          </div>
          <div class="modal-field">
            <label>Start Time</label>
            <input type="time" name="time" value="<?= $booking['time'] ?>" class="admin-input" required />
          </div>
        </div>
        <div class="modal-row">
          <div class="modal-field">
            <label>Duration</label>
            <select name="duration" class="admin-select">
              <?php foreach (BOOKING_DURATIONS as $val => $label): ?>
                <option value="<?= $val ?>" <?= $booking['duration'] === $val ? 'selected' : '' ?>><?= $label ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="modal-field">
            <label>Package</label>
            <select name="package" class="admin-select">
              <?php foreach (PKG_PRICES as $pkg => $price): ?>
                <option value="<?= $pkg ?>" <?= $booking['package'] === $pkg ? 'selected' : '' ?>><?= $pkg ?> — RM<?= number_format($price) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn-outline-sm" onclick="closeModal('eventModal')">Cancel</button>
        <button type="submit" class="btn-gold-sm">Save Changes</button>
      </div>
    </form>
  </div>
</div>

<?php
$pageContent = ob_get_clean();
require 'includes/layout.php';
