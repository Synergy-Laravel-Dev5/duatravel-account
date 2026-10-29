<?php
// Let's examine the transcript or scratch directory to find if we have any backups or diffs
$scratchFiles = glob('scratch/*');
foreach($scratchFiles as $f) {
    if (strpos($f, 'expense') !== false || strpos($f, 'et') !== false) {
        echo "$f (" . filesize($f) . " bytes)\n";
    }
}
