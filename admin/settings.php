<?php
// ── LOGIC ────────────────────────────────────────────────
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/components.php';
require_login();

if (!isset($_SESSION['packages'])) {
    $_SESSION['packages'] = [
        'Basic'    => ['price' => PKG_PRICES['Basic'],    'duration' => '2 hours',   'desc' => 'Ideal for small events and product launches. Includes event script and rehearsal.'],
        'Standard' => ['price' => PKG_PRICES['Standard'], 'duration' => '4 hours',   'desc' => 'Perfect for corporate dinners and award nights. Includes bilingual hosting and full run-of-show.'],
        'Premium'  => ['price' => PKG_PRICES['Premium'],  'duration' => 'Full Day',  'desc' => 'Full-day coverage for weddings and large conferences. Includes pre-event consultation and post-event summary.'],
    ];
}

$success = '';
$error   = '';
$tab     = $_GET['tab'] ?? 'profile';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['_action'] ?? '';

    if ($action === 'password') {
        $current = $_POST['current_password'] ?? '';
        $new     = $_POST['new_password']     ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        if ($current !== ADMIN_PASS) {
            $error = 'Current password is incorrect.';
        } elseif (strlen($new) < 6) {
            $error = 'New password must be at least 6 characters.';
        } elseif ($new !== $confirm) {
            $error = 'New passwords do not match.';
        } else {
            $success = 'Password updated. (Update ADMIN_PASS in auth.php to persist.)';
        }
        $tab = 'profile';

    } elseif ($action === 'contact') {
        $success = 'Profile information saved.';
        $tab = 'profile';

    } elseif ($action === 'site') {
        $success = 'Site settings saved.';
        $tab = 'profile';

    } elseif ($action === 'packages') {
        foreach (['Basic', 'Standard', 'Premium'] as $pkg) {
            $price    = (int)($_POST['price_' . $pkg]    ?? $_SESSION['packages'][$pkg]['price']);
            $duration = trim($_POST['duration_' . $pkg]  ?? $_SESSION['packages'][$pkg]['duration']);
            $desc     = trim($_POST['desc_' . $pkg]      ?? $_SESSION['packages'][$pkg]['desc']);
            $_SESSION['packages'][$pkg] = ['price' => $price, 'duration' => $duration, 'desc' => $desc];
        }
        $success = 'Package settings saved.';
        $tab = 'packages';
    }
}

$packages  = $_SESSION['packages'];
$pageTitle = 'Settings';

// ── TEMPLATE ─────────────────────────────────────────────
ob_start(); ?>

<?= render_page_header('Settings', 'Manage your profile and service packages') ?>

<?= $success ? render_alert($success) : '' ?>
<?= $error   ? render_alert($error, 'error') : '' ?>

<!-- Tabs -->
<div class="status-tabs" style="margin-bottom:1.5rem">
  <a href="?tab=profile"  class="tab <?= $tab === 'profile'  ? 'active' : '' ?>">Profile</a>
  <a href="?tab=packages" class="tab <?= $tab === 'packages' ? 'active' : '' ?>">Packages</a>
</div>

<?php if ($tab === 'profile'): ?>

  <div class="settings-grid">

    <!-- Profile Information -->
    <div class="card settings-card settings-card--wide">
      <div class="settings-card-header">
        <div class="settings-icon-wrap">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/>
          </svg>
        </div>
        <div>
          <h2 class="settings-card-title">Profile Information</h2>
          <p class="settings-card-sub">Your public-facing contact details</p>
        </div>
      </div>
      <form method="POST" action="settings.php?tab=profile" class="settings-form settings-form--grid">
        <input type="hidden" name="_action" value="contact" />
        <div class="settings-field">
          <label>Full Name</label>
          <input type="text" name="full_name" class="admin-input" value="Ameerul Iskandar" />
        </div>
        <div class="settings-field">
          <label>Professional Title</label>
          <input type="text" name="title" class="admin-input" value="Professional Emcee &amp; Host" />
        </div>
        <div class="settings-field">
          <label>Email Address</label>
          <input type="email" name="email" class="admin-input" value="ameerul@example.com" />
        </div>
        <div class="settings-field">
          <label>Phone / WhatsApp</label>
          <input type="text" name="phone" class="admin-input" value="+60 12-345 6789" />
        </div>
        <div class="settings-field">
          <label>Instagram Handle</label>
          <input type="text" name="instagram" class="admin-input" placeholder="@yourhandle" />
        </div>
        <div class="settings-field">
          <label>Facebook Page URL</label>
          <input type="text" name="facebook" class="admin-input" placeholder="https://facebook.com/..." />
        </div>
        <div class="settings-field">
          <label>LinkedIn URL</label>
          <input type="text" name="linkedin" class="admin-input" placeholder="https://linkedin.com/in/..." />
        </div>
        <div class="settings-field">
          <label>Site Tagline</label>
          <input type="text" name="tagline" class="admin-input" value="Your Story, My Stage." />
        </div>
        <div class="settings-actions settings-actions--full">
          <button type="submit" class="btn-gold-sm">Save Profile</button>
        </div>
      </form>
    </div>

    <!-- Change Password -->
    <div class="card settings-card">
      <div class="settings-card-header">
        <div class="settings-icon-wrap">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
            <path d="M7 11V7a5 5 0 0110 0v4"/>
          </svg>
        </div>
        <div>
          <h2 class="settings-card-title">Change Password</h2>
          <p class="settings-card-sub">Update your admin login credentials</p>
        </div>
      </div>
      <form method="POST" action="settings.php?tab=profile" class="settings-form">
        <input type="hidden" name="_action" value="password" />
        <div class="settings-field">
          <label>Current Password</label>
          <input type="password" name="current_password" class="admin-input" placeholder="Enter current password" required />
        </div>
        <div class="settings-field">
          <label>New Password</label>
          <input type="password" name="new_password" class="admin-input" placeholder="Min. 6 characters" required />
        </div>
        <div class="settings-field">
          <label>Confirm New Password</label>
          <input type="password" name="confirm_password" class="admin-input" placeholder="Repeat new password" required />
        </div>
        <div class="settings-actions">
          <button type="submit" class="btn-gold-sm">Update Password</button>
        </div>
      </form>
    </div>

    <!-- Site Settings -->
    <div class="card settings-card">
      <div class="settings-card-header">
        <div class="settings-icon-wrap">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <circle cx="12" cy="12" r="10"/>
            <line x1="2" y1="12" x2="22" y2="12"/>
            <path d="M12 2a15.3 15.3 0 010 20M12 2a15.3 15.3 0 000 20"/>
          </svg>
        </div>
        <div>
          <h2 class="settings-card-title">Site Settings</h2>
          <p class="settings-card-sub">SEO and analytics configuration</p>
        </div>
      </div>
      <form method="POST" action="settings.php?tab=profile" class="settings-form">
        <input type="hidden" name="_action" value="site" />
        <div class="settings-field">
          <label>Site Name</label>
          <input type="text" name="site_name" class="admin-input" value="Ameerul Iskandar" />
        </div>
        <div class="settings-field">
          <label>Meta Description</label>
          <input type="text" name="meta_desc" class="admin-input" placeholder="Short description for search engines…" />
        </div>
        <div class="settings-field">
          <label>Google Analytics ID <span class="settings-optional">(optional)</span></label>
          <input type="text" name="ga_id" class="admin-input" placeholder="G-XXXXXXXXXX" />
        </div>
        <div class="settings-actions">
          <button type="submit" class="btn-gold-sm">Save Settings</button>
        </div>
      </form>
    </div>

  </div>

<?php else: ?>

  <form method="POST" action="settings.php?tab=packages">
    <input type="hidden" name="_action" value="packages" />
    <div class="pkg-settings-grid">
      <?php foreach ($packages as $name => $pkg):
        $color = PKG_COLORS[$name]; ?>
        <div class="card pkg-settings-card">
          <div class="pkg-settings-badge" style="background:<?= $color ?>1a;border-color:<?= $color ?>40;color:<?= $color ?>">
            <?= $name ?>
          </div>
          <div class="settings-field" style="margin-top:1.25rem">
            <label>Price (RM)</label>
            <div class="input-prefix-wrap">
              <span class="input-prefix">RM</span>
              <input type="number" name="price_<?= $name ?>" class="admin-input input-prefixed" value="<?= $pkg['price'] ?>" min="0" step="50" required />
            </div>
          </div>
          <div class="settings-field">
            <label>Duration</label>
            <input type="text" name="duration_<?= $name ?>" class="admin-input" value="<?= htmlspecialchars($pkg['duration']) ?>" required />
          </div>
          <div class="settings-field">
            <label>Description</label>
            <textarea name="desc_<?= $name ?>" class="admin-input admin-textarea" rows="3" required><?= htmlspecialchars($pkg['desc']) ?></textarea>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
    <div style="margin-top:1.5rem">
      <button type="submit" class="btn-gold-sm">Save All Packages</button>
    </div>
  </form>

<?php endif; ?>

<?php
$pageContent = ob_get_clean();
require 'includes/layout.php';
