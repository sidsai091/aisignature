<?php
// =============================================
// COMPONENTS — stateless HTML-returning helpers
// All functions return strings; nothing is echo'd directly.
// =============================================

function render_alert(string $message, string $type = 'success'): string {
    $class = $type === 'error' ? 'alert-error' : 'alert-success';
    return '<div class="' . $class . '">' . htmlspecialchars($message) . '</div>';
}

function render_page_header(string $title, string $subtitle = '', string $rightHtml = ''): string {
    $sub = $subtitle ? '<p class="page-sub">' . htmlspecialchars($subtitle) . '</p>' : '';
    $right = $rightHtml ? '<div>' . $rightHtml . '</div>' : '';
    return '<div class="page-header"><div><h1 class="page-title">' . htmlspecialchars($title) . '</h1>' . $sub . '</div>' . $right . '</div>';
}

function render_badge(string $status): string {
    $class = status_class($status);
    return '<span class="badge ' . $class . '">' . htmlspecialchars($status) . '</span>';
}

function render_pkg_tag(string $package): string {
    return '<span class="pkg-tag">' . htmlspecialchars($package) . '</span>';
}

function render_empty_state(string $svgIcon, string $text, string $actionHtml = ''): string {
    return '<div class="empty-state">' . $svgIcon . '<p>' . htmlspecialchars($text) . '</p>' . $actionHtml . '</div>';
}

function render_breadcrumb(array $crumbs): string {
    $parts = [];
    foreach ($crumbs as $i => $crumb) {
        $isLast = $i === array_key_last($crumbs);
        if ($isLast) {
            $parts[] = '<span>' . htmlspecialchars($crumb['label']) . '</span>';
        } else {
            $parts[] = '<a href="' . htmlspecialchars($crumb['href']) . '">' . htmlspecialchars($crumb['label']) . '</a>';
        }
    }
    return '<nav class="breadcrumb">' . implode('<span class="bc-sep">/</span>', $parts) . '</nav>';
}
