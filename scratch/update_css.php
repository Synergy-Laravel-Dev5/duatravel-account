<?php
$file = __DIR__ . '/../public/assets/css/custom-theme.css';
$content = file_get_contents($file);

$target = ".nav-tabs .nav-link.active,\r\n.nav-pills .nav-link.active {\r\n    background-color: var(--primary-teal) !important;\r\n    border-color: var(--primary-teal) !important;\r\n    color: #ffffff !important;\r\n}";

$replacement = ".booking-tabs .nav-link.active,\r\n.booking-tabs .nav-link.active *,\r\n.booking-tabs .nav-link.active i,\r\n.booking-tabs .nav-link.active span,\r\n.nav-tabs .nav-link.active,\r\n.nav-tabs .nav-link.active *,\r\n.nav-tabs .nav-link.active i,\r\n.nav-tabs .nav-link.active span,\r\n.nav-pills .nav-link.active,\r\n.nav-pills .nav-link.active *,\r\n.nav-pills .nav-link.active i {\r\n    background-color: var(--primary-teal) !important;\r\n    border-color: var(--primary-teal) !important;\r\n    color: #ffffff !important;\r\n    fill: #ffffff !important;\r\n    font-weight: 600;\r\n}";

if (strpos($content, $target) !== false) {
    $content = str_replace($target, $replacement, $content);
    file_put_contents($file, $content);
    echo "Updated custom-theme.css with CRLF successfully.\n";
} else {
    // Try LF
    $targetLF = str_replace("\r\n", "\n", $target);
    $replacementLF = str_replace("\r\n", "\n", $replacement);
    if (strpos($content, $targetLF) !== false) {
        $content = str_replace($targetLF, $replacementLF, $content);
        file_put_contents($file, $content);
        echo "Updated custom-theme.css with LF successfully.\n";
    } else {
        echo "Target not found in custom-theme.css!\n";
    }
}
