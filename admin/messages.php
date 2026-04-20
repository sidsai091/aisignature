<?php
// ── LOGIC ────────────────────────────────────────────────
// header() redirect must happen before any ob_start
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/components.php';
require_login();

if (!isset($_SESSION['messages'])) {
    $_SESSION['messages'] = [
        ['id'=>1, 'name'=>'Hafiz Abdullah',   'email'=>'hafiz@mara.gov.my',      'phone'=>'+60 12-888 1234', 'subject'=>'Corporate Dinner Enquiry',  'message'=>'Hi, we are looking for an emcee for our annual corporate dinner in August. Around 500 guests, black tie. Please advise on availability and packages.', 'created'=>'2026-04-18 10:22', 'read'=>false],
        ['id'=>2, 'name'=>'Cheryl Ng',        'email'=>'cheryl@weddingco.my',     'phone'=>'+60 16-222 3344', 'subject'=>'Wedding Hosting',           'message'=>'We would like to book you for our wedding reception on 12 July 2026. Could you provide details on your Premium package and what is included?', 'created'=>'2026-04-17 14:05', 'read'=>false],
        ['id'=>3, 'name'=>'Rahim Othman',     'email'=>'rahim@techexpo.com.my',   'phone'=>'+60 19-555 6677', 'subject'=>'Tech Expo 2026',            'message'=>'We are organising a tech conference for 1,200 attendees in September. Looking for a bilingual emcee who can handle English and Malay. Are you available?', 'created'=>'2026-04-15 09:30', 'read'=>true],
        ['id'=>4, 'name'=>'Amanda Lee',       'email'=>'amanda@luxevents.com',    'phone'=>'+60 11-777 8899', 'subject'=>'Awards Night',              'message'=>'Enquiring on behalf of a client for an awards gala in June. 300 pax, venue is Mandarin Oriental. Please send your rate card.', 'created'=>'2026-04-12 16:45', 'read'=>true],
        ['id'=>5, 'name'=>'Dr. Suresh Kumar', 'email'=>'suresh@medconf.my',       'phone'=>'+60 17-100 2200', 'subject'=>'Medical Conference',        'message'=>'We need a professional emcee for a 2-day medical conference at KLCC. Day 1 is a symposium (300 pax) and Day 2 is a gala dinner (200 pax). What package would suit this?', 'created'=>'2026-04-10 11:00', 'read'=>true],
    ];
}

$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['_action'] ?? '';

    if ($action === 'mark_read') {
        $id = (int)($_POST['msg_id'] ?? 0);
        foreach ($_SESSION['messages'] as &$m) {
            if ($m['id'] === $id) { $m['read'] = true; break; }
        }
        unset($m);
    } elseif ($action === 'mark_unread') {
        $id = (int)($_POST['msg_id'] ?? 0);
        foreach ($_SESSION['messages'] as &$m) {
            if ($m['id'] === $id) { $m['read'] = false; break; }
        }
        unset($m);
    } elseif ($action === 'delete') {
        $id = (int)($_POST['msg_id'] ?? 0);
        $_SESSION['messages'] = array_values(array_filter($_SESSION['messages'], fn($m) => $m['id'] !== $id));
        $success = 'Message deleted.';
    } elseif ($action === 'mark_all_read') {
        foreach ($_SESSION['messages'] as &$m) $m['read'] = true;
        unset($m);
        $success = 'All messages marked as read.';
    }

    header('Location: messages.php' . ($success ? '?ok=1' : ''));
    exit;
}

if (isset($_GET['ok'])) $success = 'Done.';

$allMessages = $_SESSION['messages'];
$unreadCnt   = count(array_filter($allMessages, fn($m) => !$m['read']));
$readCnt     = count($allMessages) - $unreadCnt;
$filter      = $_GET['filter'] ?? 'all';

$messages = $allMessages;
if ($filter === 'unread') $messages = array_values(array_filter($allMessages, fn($m) => !$m['read']));
if ($filter === 'read')   $messages = array_values(array_filter($allMessages, fn($m) =>  $m['read']));

// Resolve single message view — also marks it read
$viewId  = (int)($_GET['id'] ?? 0);
$viewMsg = null;
if ($viewId) {
    foreach ($_SESSION['messages'] as &$m) {
        if ($m['id'] === $viewId) {
            $m['read'] = true;
            $viewMsg   = $m;
            break;
        }
    }
    unset($m);
}

$pageTitle = 'Messages';

// ── TEMPLATE ─────────────────────────────────────────────
ob_start();

if ($viewMsg): ?>

  <!-- SINGLE MESSAGE VIEW -->
  <div class="page-header">
    <div>
      <a href="messages.php" class="back-link">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
        Back to Messages
      </a>
      <h1 class="page-title" style="margin-top:0.5rem"><?= htmlspecialchars($viewMsg['subject']) ?></h1>
    </div>
    <div style="display:flex;gap:.75rem;align-items:center">
      <form method="POST">
        <input type="hidden" name="_action" value="<?= $viewMsg['read'] ? 'mark_unread' : 'mark_read' ?>" />
        <input type="hidden" name="msg_id" value="<?= $viewMsg['id'] ?>" />
        <button type="submit" class="btn-outline-sm"><?= $viewMsg['read'] ? 'Mark Unread' : 'Mark Read' ?></button>
      </form>
      <form method="POST" onsubmit="return confirm('Delete this message?')">
        <input type="hidden" name="_action" value="delete" />
        <input type="hidden" name="msg_id" value="<?= $viewMsg['id'] ?>" />
        <button type="submit" class="btn-outline-sm btn-danger-sm">Delete</button>
      </form>
    </div>
  </div>

  <div class="card msg-detail-card">
    <div class="msg-detail-meta">
      <div class="msg-avatar"><?= strtoupper(substr($viewMsg['name'], 0, 1)) ?></div>
      <div class="msg-meta-info">
        <div class="msg-sender-name"><?= htmlspecialchars($viewMsg['name']) ?></div>
        <div class="msg-sender-contact">
          <a href="mailto:<?= htmlspecialchars($viewMsg['email']) ?>"><?= htmlspecialchars($viewMsg['email']) ?></a>
          <?php if ($viewMsg['phone']): ?>
            · <a href="tel:<?= htmlspecialchars($viewMsg['phone']) ?>"><?= htmlspecialchars($viewMsg['phone']) ?></a>
          <?php endif; ?>
        </div>
      </div>
      <div class="msg-detail-time"><?= date('d M Y, H:i', strtotime($viewMsg['created'])) ?></div>
    </div>
    <div class="msg-detail-body">
      <?= nl2br(htmlspecialchars($viewMsg['message'])) ?>
    </div>
    <div class="msg-detail-actions">
      <a href="mailto:<?= htmlspecialchars($viewMsg['email']) ?>?subject=Re: <?= urlencode($viewMsg['subject']) ?>" class="btn-gold-sm">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right:6px"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
        Reply via Email
      </a>
      <?php if ($viewMsg['phone']): ?>
        <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $viewMsg['phone']) ?>" target="_blank" class="btn-outline-sm">WhatsApp</a>
      <?php endif; ?>
    </div>
  </div>

<?php else: ?>

  <!-- INBOX LIST -->
  <div class="page-header">
    <div>
      <h1 class="page-title">Messages
        <?php if ($unreadCnt): ?>
          <span class="inbox-unread-badge"><?= $unreadCnt ?></span>
        <?php endif; ?>
      </h1>
      <p class="page-sub">Enquiries from the contact form</p>
    </div>
    <?php if ($unreadCnt): ?>
      <form method="POST">
        <input type="hidden" name="_action" value="mark_all_read" />
        <button type="submit" class="btn-outline-sm">Mark all read</button>
      </form>
    <?php endif; ?>
  </div>

  <?= $success ? render_alert($success) : '' ?>

  <div class="status-tabs">
    <a href="messages.php"    class="tab <?= $filter === 'all'    ? 'active' : '' ?>">All <span class="tab-count"><?= count($allMessages) ?></span></a>
    <a href="?filter=unread"  class="tab <?= $filter === 'unread' ? 'active' : '' ?>">Unread <span class="tab-count"><?= $unreadCnt ?></span></a>
    <a href="?filter=read"    class="tab <?= $filter === 'read'   ? 'active' : '' ?>">Read <span class="tab-count"><?= $readCnt ?></span></a>
  </div>

  <div class="card">
    <?php if (empty($messages)): ?>
      <?= render_empty_state(
          '<svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>',
          'No messages here.'
      ) ?>
    <?php else: ?>
      <ul class="msg-list">
        <?php foreach ($messages as $m): ?>
          <li class="msg-item <?= !$m['read'] ? 'msg-unread' : '' ?>">
            <a href="?id=<?= $m['id'] ?>" class="msg-item-link">
              <div class="msg-avatar msg-avatar-sm"><?= strtoupper(substr($m['name'], 0, 1)) ?></div>
              <div class="msg-item-body">
                <div class="msg-item-top">
                  <span class="msg-item-name"><?= htmlspecialchars($m['name']) ?></span>
                  <span class="msg-item-time"><?= date('d M', strtotime($m['created'])) ?></span>
                </div>
                <div class="msg-item-subject"><?= htmlspecialchars($m['subject']) ?></div>
                <div class="msg-item-preview"><?= htmlspecialchars(substr($m['message'], 0, 90)) ?>…</div>
              </div>
              <?php if (!$m['read']): ?><span class="msg-unread-dot"></span><?php endif; ?>
            </a>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </div>

<?php endif;

$pageContent = ob_get_clean();
require 'includes/layout.php';
