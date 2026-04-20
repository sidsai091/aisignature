<?php
// =============================================
// CONFIG — single source of truth for shared constants
// No side effects, no session, no includes.
// =============================================

define('SITE_NAME', 'Ameerul Iskandar');

define('PKG_PRICES', [
    'Basic'    => 800,
    'Standard' => 1500,
    'Premium'  => 2800,
]);

define('PKG_COLORS', [
    'Basic'    => '#2563eb',
    'Standard' => '#b8860b',
    'Premium'  => '#16a34a',
]);

define('BOOKING_STATUSES', ['Pending', 'Confirmed', 'Completed', 'Cancelled']);

define('EVENT_TYPES', [
    'Corporate Event', 'Wedding', 'Gala Dinner', 'Product Launch',
    'Conference', 'Awards Ceremony', 'Charity Event', 'Other',
]);

define('BOOKING_DURATIONS', [
    '2hrs'    => '2 Hours',
    '4hrs'    => '4 Hours',
    'fullday' => 'Full Day (8+ Hours)',
]);
