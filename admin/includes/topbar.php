<header class="topbar">
  <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle sidebar">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <line x1="3" y1="6"  x2="21" y2="6"/>
      <line x1="3" y1="12" x2="21" y2="12"/>
      <line x1="3" y1="18" x2="21" y2="18"/>
    </svg>
  </button>
  <div class="topbar-right">
    <div class="topbar-date"><?= date('l, d F Y') ?></div>
    <div class="topbar-avatar"><?= strtoupper(substr($_SESSION['admin_user'],0,1)) ?></div>
  </div>
</header>
