<?php
$content = file_get_contents('resources/views/booking/edit.blade.php');
preg_match_all('/<script\b[^>]*>(.*?)<\/script>/is', $content, $matches);
$allJs = implode("\n;\n", $matches[1]);
// Replace Blade directives in JS like @json(...), {{ ... }}, etc. with dummy JS values
$cleanJs = preg_replace('/@json\([^)]*\)/', '{}', $allJs);
$cleanJs = preg_replace('/\{\{[^}]*\}\}/', '"dummy"', $cleanJs);
$cleanJs = preg_replace('/@if\([^)]*\)/', 'if(true){', $cleanJs);
$cleanJs = preg_replace('/@endif/', '}', $cleanJs);
$cleanJs = preg_replace('/@php.*?@endphp/s', '', $cleanJs);
file_put_contents('scratch/test_edit_syntax.js', $cleanJs);
echo "Extracted " . strlen($cleanJs) . " bytes of JS\n";
