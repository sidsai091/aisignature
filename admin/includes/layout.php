<?php
// =============================================
// LAYOUT — shared HTML wrapper for authenticated pages
// Expects in calling scope:
//   $pageTitle    (string) — inserted into <title>
//   $pageContent  (string) — output of ob_get_clean()
//   $extraHead    (string, optional) — extra tags inside <head>
//   $extraScripts (string, optional) — inline JS after admin.js
// =============================================
$extraHead    = $extraHead    ?? '';
$extraScripts = $extraScripts ?? '';
$cssVersion   = filemtime(__DIR__ . '/../assets/admin.css');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1.0" />
  <title><?= htmlspecialchars($pageTitle) ?> — <?= SITE_NAME ?> Admin</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link href="https://fonts.googleapis.com/css2?family=Sora:wght@600;700;800&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="assets/admin.css?v=<?= $cssVersion ?>" />
  <?= $extraHead ?>
</head>
<body>

  <?php include __DIR__ . '/sidebar.php'; ?>

  <div class="main-wrap">
    <?php include __DIR__ . '/topbar.php'; ?>
    <div class="page-content">
      <?= $pageContent ?>
    </div>
  </div>

  <script src="assets/admin.js"></script>
  <?= $extraScripts ?>
</body>
</html>
