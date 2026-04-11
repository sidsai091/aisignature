<?php
// =============================================
// MOCK DATA — replace with DB queries later
// =============================================

function get_bookings() {
    return [
        ['id'=>1,  'name'=>'Sarah Mitchell',    'email'=>'sarah@apexfg.com',        'phone'=>'+60 12-111 2222', 'company'=>'Apex Financial Group',    'event_type'=>'Corporate Event',  'date'=>'2026-04-18', 'time'=>'19:00', 'venue'=>'Grand Hyatt KL',          'duration'=>'4hrs',    'package'=>'Standard', 'status'=>'Confirmed',  'notes'=>'VIP table arrangement needed.',       'created'=>'2026-03-20'],
        ['id'=>2,  'name'=>'James & Priya Tan', 'email'=>'james.tan@gmail.com',      'phone'=>'+60 11-333 4444', 'company'=>'',                         'event_type'=>'Wedding',          'date'=>'2026-05-03', 'time'=>'16:00', 'venue'=>'The Majestic Hotel',       'duration'=>'fullday', 'package'=>'Premium',  'status'=>'Confirmed',  'notes'=>'Bilingual hosting required (EN/CN).', 'created'=>'2026-03-22'],
        ['id'=>3,  'name'=>'Rachel Okonkwo',    'email'=>'rachel@novatech.io',       'phone'=>'+60 17-555 6666', 'company'=>'NovaTech Industries',      'event_type'=>'Product Launch',   'date'=>'2026-04-25', 'time'=>'14:00', 'venue'=>'Setia City Convention',   'duration'=>'4hrs',    'package'=>'Standard', 'status'=>'Pending',    'notes'=>'',                                    'created'=>'2026-03-28'],
        ['id'=>4,  'name'=>'David Lim',         'email'=>'david.lim@hrcorp.com',     'phone'=>'+60 16-777 8888', 'company'=>'HR Corp Malaysia',         'event_type'=>'Gala Dinner',      'date'=>'2026-04-30', 'time'=>'19:30', 'venue'=>'Mandarin Oriental KL',    'duration'=>'4hrs',    'package'=>'Standard', 'status'=>'Pending',    'notes'=>'400 pax, black tie event.',           'created'=>'2026-04-01'],
        ['id'=>5,  'name'=>'Nurul Ain',         'email'=>'nurul@klfoundation.org',   'phone'=>'+60 19-999 0000', 'company'=>'KL Community Foundation',  'event_type'=>'Charity Event',    'date'=>'2026-05-15', 'time'=>'18:00', 'venue'=>'Perdana Botanical Garden','duration'=>'4hrs',    'package'=>'Basic',    'status'=>'Pending',    'notes'=>'Outdoor event, weather contingency.', 'created'=>'2026-04-02'],
        ['id'=>6,  'name'=>'Tan Sri Ahmad',     'email'=>'ahmad@conglomco.my',       'phone'=>'+60 12-100 2000', 'company'=>'Conglom Holdings',         'event_type'=>'Awards Ceremony',  'date'=>'2026-03-14', 'time'=>'20:00', 'venue'=>'Sunway Pyramid Convention','duration'=>'4hrs',   'package'=>'Premium',  'status'=>'Completed',  'notes'=>'Went smoothly. Client very happy.',   'created'=>'2026-02-10'],
        ['id'=>7,  'name'=>'Lena Hoffmann',     'email'=>'lena@euromed.de',          'phone'=>'+49 176 1234 5678','company'=>'EuroMed GmbH',            'event_type'=>'Conference',       'date'=>'2026-03-22', 'time'=>'09:00', 'venue'=>'KLCC Convention Centre',  'duration'=>'fullday', 'package'=>'Premium',  'status'=>'Completed',  'notes'=>'International summit, 800 delegates.','created'=>'2026-02-15'],
        ['id'=>8,  'name'=>'Farah Zulkifli',    'email'=>'farah@glam.com.my',        'phone'=>'+60 13-200 3000', 'company'=>'Glam Events Sdn Bhd',      'event_type'=>'Corporate Event',  'date'=>'2026-06-12', 'time'=>'19:00', 'venue'=>'Four Seasons KL',         'duration'=>'4hrs',    'package'=>'Standard', 'status'=>'Pending',    'notes'=>'Annual dinner for 300 staff.',        'created'=>'2026-04-05'],
        ['id'=>9,  'name'=>'Marcus Wong',       'email'=>'marcus@startupx.io',       'phone'=>'+60 14-400 5000', 'company'=>'StartupX Asia',            'event_type'=>'Product Launch',   'date'=>'2026-06-20', 'time'=>'15:00', 'venue'=>'1 Utama Shopping Centre',  'duration'=>'2hrs',   'package'=>'Basic',    'status'=>'Cancelled',  'notes'=>'Client cancelled — venue issue.',     'created'=>'2026-04-06'],
        ['id'=>10, 'name'=>'Siti Norzahra',     'email'=>'siti@weddinglux.my',       'phone'=>'+60 18-600 7000', 'company'=>'',                         'event_type'=>'Wedding',          'date'=>'2026-07-05', 'time'=>'16:30', 'venue'=>'Shangri-La Hotel KL',     'duration'=>'fullday', 'package'=>'Premium',  'status'=>'Pending',    'notes'=>'Garden ceremony + ballroom dinner.',  'created'=>'2026-04-08'],
    ];
}

function get_booking_by_id($id) {
    foreach (get_bookings() as $b) {
        if ((int)$b['id'] === (int)$id) return $b;
    }
    return null;
}

function get_stats() {
    $all       = get_bookings();
    $thisMonth = date('Y-m');
    $total     = count($all);
    $pending   = 0; $confirmed = 0; $completed = 0; $cancelled = 0;
    $monthRevenue = 0;

    $packagePrices = ['Basic'=>800, 'Standard'=>1500, 'Premium'=>2800];

    foreach ($all as $b) {
        if ($b['status'] === 'Pending')   $pending++;
        if ($b['status'] === 'Confirmed') $confirmed++;
        if ($b['status'] === 'Completed') $completed++;
        if ($b['status'] === 'Cancelled') $cancelled++;
        if (substr($b['date'], 0, 7) === $thisMonth && in_array($b['status'], ['Confirmed','Completed'])) {
            $monthRevenue += $packagePrices[$b['package']] ?? 0;
        }
    }

    return compact('total','pending','confirmed','completed','cancelled','monthRevenue');
}

function get_monthly_revenue() {
    $all = get_bookings();
    $packagePrices = ['Basic'=>800, 'Standard'=>1500, 'Premium'=>2800];
    $months = [];
    foreach ($all as $b) {
        if (!in_array($b['status'], ['Confirmed','Completed'])) continue;
        $m = substr($b['date'], 0, 7);
        $months[$m] = ($months[$m] ?? 0) + ($packagePrices[$b['package']] ?? 0);
    }
    ksort($months);
    return $months;
}

function status_class($status) {
    return match($status) {
        'Confirmed'  => 'badge-confirmed',
        'Pending'    => 'badge-pending',
        'Completed'  => 'badge-completed',
        'Cancelled'  => 'badge-cancelled',
        default      => ''
    };
}
