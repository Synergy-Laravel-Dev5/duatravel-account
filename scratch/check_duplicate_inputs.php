<?php
$html = file_get_contents("resources/views/booking/edit.blade.php");
preg_match_all('/name=["\']([^"\']+)["\']/', $html, $matches);
$names = [];
foreach ($matches[1] as $n) {
    if (!str_contains($n, 'hotels[') && !str_contains($n, 'transports[') && !str_contains($n, 'visas[') && !str_contains($n, 'persons[') && !str_contains($n, 'flight_persons[')) {
        if (!isset($names[$n])) $names[$n] = 0;
        $names[$n]++;
    }
}
foreach ($names as $k => $c) {
    if ($c > 1) {
        echo "Top-level Duplicate: $k ($c times)\n";
    }
}

// Now check inside the hotel loop in blade
// Look for duplicate names within the same hotel block template
$hotelBlockMatch = preg_match('/@foreach\s*\(\$booking->hotels.*?(?=@endforeach)/s', $html, $mHotel);
if ($hotelBlockMatch) {
    preg_match_all('/name=["\']([^"\']+)["\']/', $mHotel[0], $hMatches);
    $hNames = [];
    foreach ($hMatches[1] as $hn) {
        $clean = preg_replace('/hotels\[[^\]]+\]/', 'hotels[i]', $hn);
        if (!isset($hNames[$clean])) $hNames[$clean] = 0;
        $hNames[$clean]++;
    }
    foreach ($hNames as $k => $c) {
        if ($c > 1 && !str_contains($k, 'extra_rooms')) {
            echo "Hotel Loop Duplicate: $k ($c times)\n";
        }
    }
}

// Now check inside visa loop
$visaBlockMatch = preg_match('/@foreach\s*\(\$booking->visas.*?(?=@endforeach)/s', $html, $mVisa);
if ($visaBlockMatch) {
    preg_match_all('/name=["\']([^"\']+)["\']/', $mVisa[0], $vMatches);
    $vNames = [];
    foreach ($vMatches[1] as $vn) {
        $clean = preg_replace('/visas\[[^\]]+\]/', 'visas[i]', $vn);
        if (!isset($vNames[$clean])) $vNames[$clean] = 0;
        $vNames[$clean]++;
    }
    foreach ($vNames as $k => $c) {
        if ($c > 1) {
            echo "Visa Loop Duplicate: $k ($c times)\n";
        }
    }
}
echo "DONE\n";
