<?php
/**
 * api/booked-dates.php
 * Returns booked dates as JSON for the booking form calendar.
 * Dates with status Confirmed or Pending are considered unavailable.
 */
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Include shared mock data (path: ../admin/includes/data.php)
require_once __DIR__ . '/../admin/includes/data.php';

$blockedStatuses = ['Confirmed', 'Pending'];
$bookedDates = [];

foreach (get_bookings() as $booking) {
    if (in_array($booking['status'], $blockedStatuses)) {
        $bookedDates[] = $booking['date']; // format: YYYY-MM-DD
    }
}

// Remove duplicates in case of multiple bookings on same day
$bookedDates = array_values(array_unique($bookedDates));

echo json_encode([
    'booked' => $bookedDates,
    'status' => 'ok'
]);
